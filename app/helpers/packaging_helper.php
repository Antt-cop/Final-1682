<?php
/**
 * Packaging & Inventory Decomposition Helper for Dong Khoi Store (B2B Wholesale)
 */

if (!function_exists('get_product_packaging_spec')) {
    /**
     * Human-readable packaging specification
     * e.g. "1 Box = 4 Packs = 24 Cans (1 Pack = 6 Cans)" or "1 Box = 30 Packs"
     */
    function get_product_packaging_spec($p) {
        $box_unit = !empty($p['box_unit']) ? $p['box_unit'] : 'Box';
        $conv_rate = max(1, intval($p['conversion_rate'] ?? 1));
        $base_unit = !empty($p['unit_type']) ? $p['unit_type'] : 'Item';
        $pack_unit = !empty($p['pack_unit']) ? $p['pack_unit'] : null;
        $pack_size = intval($p['pack_size'] ?? 1);

        if ($pack_unit && $pack_size > 1 && $conv_rate > $pack_size) {
            $packs_per_box = floor($conv_rate / $pack_size);
            return "1 $box_unit = $packs_per_box $pack_unit = $conv_rate $base_unit (1 $pack_unit = $pack_size $base_unit)";
        }
        return "1 $box_unit = $conv_rate $base_unit";
    }
}

if (!function_exists('get_product_sale_units')) {
    /**
     * Returns structured sales unit tiers for storefront purchase
     * E.g. for Coca-Cola: [ 'pack' => [ 'price' => 60000, 'ratio' => 6, ... ], 'box' => [ 'price' => 228000, 'ratio' => 24, ... ] ]
     */
    function get_product_sale_units($p) {
        $units = [];
        $base_unit = !empty($p['unit_type']) ? $p['unit_type'] : 'Item';
        $box_unit = !empty($p['box_unit']) ? $p['box_unit'] : 'Box';
        $conv_rate = max(1, intval($p['conversion_rate'] ?? 1));
        $pack_unit = !empty($p['pack_unit']) ? $p['pack_unit'] : null;
        $pack_size = intval($p['pack_size'] ?? 1);

        // Pack unit (if enabled and min_order_unit is pack)
        if ($pack_unit && $pack_size > 1 && ($p['min_order_unit'] ?? 'pack') === 'pack') {
            $price_pack = !empty($p['price_pack']) ? floatval($p['price_pack']) : floatval($p['base_price']);
            $units['pack'] = [
                'type' => 'pack',
                'name' => $pack_unit,
                'label' => "$pack_unit ($pack_size $base_unit)",
                'short_label' => $pack_unit,
                'ratio' => $pack_size,
                'price' => $price_pack,
                'is_default' => true
            ];
        }

        // Box unit (Wholesale tier)
        $price_box = !empty($p['price_box']) ? floatval($p['price_box']) : (
            isset($units['pack']) ? ($units['pack']['price'] * ($conv_rate / $pack_size)) : floatval($p['base_price'])
        );
        $packs_per_box = ($pack_unit && $pack_size > 1) ? floor($conv_rate / $pack_size) : 0;
        $box_sublabel = $packs_per_box > 0 
            ? "$packs_per_box $pack_unit / $conv_rate $base_unit"
            : "$conv_rate $base_unit";

        $units['box'] = [
            'type' => 'box',
            'name' => $box_unit,
            'label' => "$box_unit ($box_sublabel)",
            'short_label' => $box_unit,
            'ratio' => $conv_rate,
            'price' => $price_box,
            'is_default' => empty($units['pack'])
        ];

        return $units;
    }
}

if (!function_exists('decompose_stock_wms')) {
    /**
     * B2B Warehouse stock decomposition:
     * Decomposes base stock into full Boxes and Packs, dropping loose remainder (whole cases only).
     */
    function decompose_stock_wms($stock_quantity, $conversion_rate, $pack_size = 1, $box_unit = 'Box', $pack_unit = 'Pack') {
        $stock = max(0, intval($stock_quantity));
        $rate = max(1, intval($conversion_rate));
        $psize = max(1, intval($pack_size));

        $boxes = floor($stock / $rate);
        $rem_after_boxes = $stock % $rate;
        $packs = ($psize > 1 && !empty($pack_unit)) ? floor($rem_after_boxes / $psize) : 0;
        $loose_remainder = ($psize > 1 && !empty($pack_unit)) ? ($rem_after_boxes % $psize) : $rem_after_boxes;

        $parts = [];
        if ($boxes > 0) {
            $parts[] = "<strong>" . number_format($boxes) . "</strong> $box_unit";
        }
        if ($packs > 0) {
            $parts[] = "<strong>" . number_format($packs) . "</strong> $pack_unit";
        }
        if (empty($parts)) {
            $text = "0 $box_unit";
        } else {
            $text = implode(' + ', $parts);
        }

        return [
            'boxes' => (int)$boxes,
            'packs' => (int)$packs,
            'loose' => (int)$loose_remainder, // Discarded in B2B warehouse display
            'display' => $text,
            'total_base' => $stock
        ];
    }
}

if (!function_exists('parse_cart_key')) {
    function parse_cart_key($cart_key) {
        if (strpos((string)$cart_key, ':') !== false) {
            list($pid, $unit) = explode(':', (string)$cart_key, 2);
            return [intval($pid), trim($unit)];
        }
        return [intval($cart_key), 'default'];
    }
}

if (!function_exists('build_cart_key')) {
    function build_cart_key($product_id, $unit_type = 'box') {
        return intval($product_id) . ':' . trim($unit_type);
    }
}

if (!function_exists('resolve_cart_items')) {
    /**
     * Resolves the session cart into full product details with pricing by packaging unit
     */
    function resolve_cart_items($cart, $conn) {
        $result = [
            'items' => [],
            'sub_total' => 0,
            'bulk_discount' => 0,
            'grand_total' => 0,
            'count' => 0
        ];

        if (empty($cart) || !is_array($cart)) {
            return $result;
        }

        // Collect all distinct product IDs
        $pids = [];
        foreach ($cart as $key => $qty) {
            list($pid, $unit) = parse_cart_key($key);
            if ($pid > 0 && intval($qty) > 0) {
                $pids[] = $pid;
            }
        }
        $pids = array_unique($pids);
        if (empty($pids)) {
            return $result;
        }

        // Fetch products in one query
        $id_list = implode(',', $pids);
        $res = $conn->query("SELECT * FROM products WHERE id IN ($id_list)");
        $products_by_id = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $products_by_id[$row['id']] = $row;
            }
        }

        // Process each cart entry
        foreach ($cart as $key => $qty) {
            $qty = intval($qty);
            if ($qty <= 0) continue;

            list($pid, $unit_choice) = parse_cart_key($key);
            if (!isset($products_by_id[$pid])) continue;

            $product = $products_by_id[$pid];
            $available_units = get_product_sale_units($product);

            // Determine which unit was selected
            if ($unit_choice === 'pack' && isset($available_units['pack'])) {
                $unit_info = $available_units['pack'];
            } elseif ($unit_choice === 'box' && isset($available_units['box'])) {
                $unit_info = $available_units['box'];
            } else {
                // Default: use pack if default, or box
                $default_key = isset($available_units['pack']) && !empty($available_units['pack']['is_default']) ? 'pack' : 'box';
                $unit_info = $available_units[$default_key] ?? reset($available_units);
            }

            $unit_price = floatval($unit_info['price']);
            $unit_ratio = intval($unit_info['ratio']);
            $item_total = $qty * $unit_price;

            // Bulk discount tier (wholesale incentive: >=20 5%, >=50 10%, >=100 15%)
            $discount_percent = 0;
            if ($qty >= 100) {
                $discount_percent = 15;
            } elseif ($qty >= 50) {
                $discount_percent = 10;
            } elseif ($qty >= 20) {
                $discount_percent = 5;
            }

            $item_discount = $item_total * ($discount_percent / 100);
            $final_total = $item_total - $item_discount;
            $required_base_stock = $qty * $unit_ratio;

            $item_data = $product;
            $item_data['cart_key'] = (string)$key;
            $item_data['cart_qty'] = $qty;
            $item_data['unit_code'] = $unit_info['type'];
            $item_data['unit_name'] = $unit_info['name'];
            $item_data['unit_label'] = $unit_info['label'];
            $item_data['unit_price'] = $unit_price;
            $item_data['unit_ratio'] = $unit_ratio;
            $item_data['item_total'] = $item_total;
            $item_data['discount_percent'] = $discount_percent;
            $item_data['item_discount'] = $item_discount;
            $item_data['final_total'] = $final_total;
            $item_data['required_base_stock'] = $required_base_stock;
            $item_data['is_sufficient_stock'] = (intval($product['stock_quantity']) >= $required_base_stock);

            $result['items'][] = $item_data;
            $result['sub_total'] += $item_total;
            $result['bulk_discount'] += $item_discount;
        }

        $result['grand_total'] = $result['sub_total'] - $result['bulk_discount'];
        $result['count'] = count($result['items']);

        return $result;
    }
}

