<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized access');
}

require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    die('Invalid order ID');
}

// Fetch order with user institution details, batch, and agreement
$order_res = $conn->query("
    SELECT o.*, u.username, u.email, u.contact_phone, u.institution_name, u.shipping_address as user_address,
           b.batch_code, b.scheduled_date as batch_scheduled_date,
           a.agreement_code, a.title as agreement_title
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    LEFT JOIN delivery_batches b ON o.batch_id = b.id
    LEFT JOIN standing_agreements a ON b.agreement_id = a.id
    WHERE o.id = $order_id
");

if (!$order_res || $order_res->num_rows === 0) {
    die('Order not found');
}
$order = $order_res->fetch_assoc();

// Authorization: admin, shipper, or order owner
$user_role = $_SESSION['role'] ?? '';
if (!in_array($user_role, ['admin', 'shipper']) && $order['user_id'] != $_SESSION['user_id']) {
    die('Unauthorized access');
}

// Fetch items with product SKU, lot, storage zone
$items = [];
$items_res = $conn->query("
    SELECT oi.*, p.name as product_name, p.sku, p.lot_number, p.storage_zone, p.unit_type, p.expiration_date
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = $order_id
");
if ($items_res) {
    while ($row = $items_res->fetch_assoc()) {
        $items[] = $row;
    }
}

// Calculate subtotal from items to determine any discount
$items_subtotal = 0;
foreach ($items as $it) {
    $items_subtotal += $it['total_price'];
}
$shipping_fee = floatval($order['ghn_fee'] ?? 0);
$grand_total  = floatval($order['total_amount']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B2B Invoice #<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?> - Dong Khoi Store</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333;
        }
        .invoice-box {
            max-width: 860px;
            margin: 30px auto;
            padding: 35px 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .invoice-header {
            border-bottom: 2px solid #0056b3;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .badge-b2b {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
        }
        .info-card {
            background: #fafbfc;
            border: 1px solid #edf2f7;
            border-radius: 6px;
            padding: 15px;
            height: 100%;
        }
        .info-card h6 {
            font-weight: 700;
            margin-bottom: 10px;
            color: #2d3748;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table-items th {
            background-color: #f8fafc;
            color: #4a5568;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #cbd5e0;
        }
        .table-items td {
            font-size: 13px;
            vertical-align: middle;
        }
        .total-summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px 20px;
        }
        .pod-box {
            margin-top: 35px;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e0;
        }
        .pod-col {
            text-align: center;
        }
        .pod-col .sign-title {
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            color: #2d3748;
            margin-bottom: 4px;
        }
        .pod-col .sign-sub {
            font-size: 11px;
            color: #718096;
            font-style: italic;
            margin-bottom: 60px;
        }
        .pod-col .sign-name {
            font-weight: 600;
            font-size: 13px;
            color: #2d3748;
        }
        @media print {
            body { 
                background-color: #fff; 
                margin: 0;
            }
            .invoice-box {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 15px 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>
</head>
<body>
    <div class="container invoice-box">
        <!-- 1. Header -->
        <div class="invoice-header d-flex justify-content-between align-items-center">
            <div>
                <div class="d-flex align-items-center mb-1">
                    <span style="display:inline-flex; width:34px; height:34px; border-radius:6px; background:#0056b3; color:#fff; align-items:center; justify-content:center; font-weight:900; font-size:16px; margin-right:10px;">DK</span>
                    <h3 class="mb-0 font-weight-bold" style="color:#0056b3; letter-spacing: 0.5px;">DONG KHOI STORE</h3>
                </div>
                <small class="text-muted text-uppercase font-weight-bold">B2B Wholesale FMCG &amp; Institutional Canteen Hub</small>
            </div>
            <div class="text-right">
                <h4 class="mb-0 font-weight-bold" style="color:#2d3748;">B2B COMMERCIAL INVOICE</h4>
                <div class="text-muted" style="font-size: 13px;">Invoice No: <strong>INV-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></strong></div>
                <small class="text-muted">Issue Date: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></small>
            </div>
        </div>

        <!-- 2. Two Columns: From & Bill To -->
        <div class="row mb-4">
            <div class="col-sm-6 mb-3 mb-sm-0">
                <div class="info-card">
                    <h6><i class="fas fa-warehouse text-primary mr-1"></i> Supplier / Distributor (Seller):</h6>
                    <div class="font-weight-bold text-dark">DONG KHOI WHOLESALE FMCG HUB (DONG KHOI STORE)</div>
                    <div class="small text-muted"><i class="fas fa-map-marker-alt mr-1"></i> 123 Dong Khoi Street, Ben Nghe Ward, District 1, HCMC</div>
                    <div class="small text-muted"><i class="fas fa-phone mr-1"></i> B2B Hotline: <strong>1900 6828</strong> - 028 3822 5588</div>
                    <div class="small text-muted"><i class="fas fa-id-card mr-1"></i> Tax ID (MST): <strong>0316821682</strong></div>
                    <div class="small text-muted"><i class="fas fa-shield-alt text-success mr-1"></i> Food Safety Cert (ATTP): <strong>456/2026/ATTP-HCM</strong></div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="info-card">
                    <h6><i class="fas fa-building text-success mr-1"></i> Buyer / Canteen Institution:</h6>
                    <div class="font-weight-bold text-dark"><?= htmlspecialchars($order['institution_name'] ?: 'Canteen ' . $order['username']) ?></div>
                    <div class="small text-muted"><i class="fas fa-user mr-1"></i> Contact Person: <strong><?= htmlspecialchars($order['username']) ?></strong></div>
                    <div class="small text-muted"><i class="fas fa-phone mr-1"></i> Phone: <strong><?= htmlspecialchars($order['contact_phone'] ?: 'N/A') ?></strong></div>
                    <div class="small text-muted"><i class="fas fa-envelope mr-1"></i> Email: <?= htmlspecialchars($order['email'] ?: 'N/A') ?></div>
                    <div class="small text-muted"><i class="fas fa-truck mr-1"></i> Delivery Address: <?= htmlspecialchars($order['shipping_address'] ?: 'N/A') ?></div>
                </div>
            </div>
        </div>

        <!-- 3. Logistics & Order Status Meta -->
        <div class="row mb-4">
            <div class="col-sm-12">
                <?php if (!empty($order['batch_code'])): ?>
                    <div class="alert alert-info py-2 px-3 mb-3 small" style="border-radius: 6px;">
                        <i class="fas fa-boxes mr-1"></i> <strong>SCM Fulfillment:</strong> Consolidated into Delivery Release <strong>#<?= htmlspecialchars($order['batch_code']) ?></strong> under Master Contract <strong>#<?= htmlspecialchars($order['agreement_code']) ?></strong> (Scheduled Delivery: <?= date('M d, Y', strtotime($order['batch_scheduled_date'])) ?>).
                    </div>
                <?php elseif (($order['order_type'] ?? '') === 'urgent_ad_hoc'): ?>
                    <div class="alert alert-warning py-2 px-3 mb-3 small" style="border-radius: 6px;">
                        <i class="fas fa-bolt mr-1"></i> <strong>SCM Priority Restock:</strong> Dispatched immediately under institutional trade credit terms. Settled via periodic Statement of Account.
                    </div>
                <?php endif; ?>

                <table class="table table-sm table-bordered mb-0" style="background:#fcfdfd;">
                    <tbody>
                        <tr>
                            <td style="width: 25%;"><strong>Payment Method:</strong></td>
                            <td style="width: 25%;">
                                <?php
                                $pay_map = [
                                    'b2b_credit'    => '<span class="badge badge-primary badge-b2b" style="background:#1d4ed8;">Trade Credit (Deferred)</span>',
                                    'cod'           => '<span class="badge badge-secondary badge-b2b">Cash on Delivery (COD)</span>',
                                    'vnpay'         => '<span class="badge badge-primary badge-b2b" style="background:#005baa;">VNPAY Gateway</span>',
                                    'momo'          => '<span class="badge badge-danger badge-b2b" style="background:#a50064;">MoMo E-Wallet</span>',
                                    'bank_transfer' => '<span class="badge badge-info badge-b2b">B2B Wire Transfer</span>',
                                    'e_wallet'      => '<span class="badge badge-primary badge-b2b">E-Wallet</span>',
                                ];
                                echo $pay_map[$order['payment_method']] ?? '<span class="badge badge-light border">'.htmlspecialchars($order['payment_method']).'</span>';
                                ?>
                            </td>
                            <td style="width: 25%;"><strong>Payment Status:</strong></td>
                            <td style="width: 25%;">
                                <?php if ($order['payment_status'] === 'paid'): ?>
                                    <span class="badge badge-success badge-b2b"><i class="fas fa-check-circle mr-1"></i>Paid</span>
                                <?php elseif (($order['payment_mode'] ?? '') === 'deferred_to_cycle'): ?>
                                    <span class="badge badge-info badge-b2b"><i class="fas fa-file-invoice mr-1"></i>Deferred to Statement (SOA)</span>
                                <?php else: ?>
                                    <span class="badge badge-warning badge-b2b text-dark"><i class="fas fa-clock mr-1"></i>Unpaid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Fulfillment Status:</strong></td>
                            <td>
                                <?php
                                $st_map = [
                                    'pending'    => '<span class="badge badge-warning text-dark badge-b2b">Awaiting Dispatch</span>',
                                    'processing' => '<span class="badge badge-info badge-b2b">Order Picking / Packing</span>',
                                    'shipped'    => '<span class="badge badge-primary badge-b2b">In Transit</span>',
                                    'delivered'  => '<span class="badge badge-success badge-b2b">Delivered</span>',
                                    'cancelled'  => '<span class="badge badge-danger badge-b2b">Cancelled</span>',
                                ];
                                echo $st_map[$order['status']] ?? htmlspecialchars($order['status']);
                                ?>
                            </td>
                            <td><strong>Logistics Tracking (GHN):</strong></td>
                            <td>
                                <strong class="text-primary"><i class="fas fa-barcode mr-1"></i><?= htmlspecialchars($order['tracking_code'] ?: 'Coordinating GHN Dispatch') ?></strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Items Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-items mb-0">
                <thead>
                    <tr>
                        <th style="width: 5%;" class="text-center">No.</th>
                        <th style="width: 15%;">SKU</th>
                        <th style="width: 38%;">Item Description &amp; Packaging</th>
                        <th style="width: 12%;" class="text-center">Lot / Expiry</th>
                        <th style="width: 8%;" class="text-center">Unit</th>
                        <th style="width: 7%;" class="text-center">Qty</th>
                        <th style="width: 15%;" class="text-right">Unit Price</th>
                        <th style="width: 15%;" class="text-right">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stt = 1;
                    foreach ($items as $item): 
                    ?>
                    <tr>
                        <td class="text-center"><?= $stt++ ?></td>
                        <td><code><?= htmlspecialchars($item['sku'] ?? 'BEV-GRT-' . str_pad($item['product_id'], 3, '0', STR_PAD_LEFT)) ?></code></td>
                        <td>
                            <strong><?= htmlspecialchars($item['product_name'] ?? 'Product #' . $item['product_id']) ?></strong>
                            <?php if (!empty($item['unit_name'])): ?>
                                <br><small class="text-muted"><i class="fas fa-boxes text-success mr-1"></i>Dispatch Unit: <?= htmlspecialchars($item['unit_name']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <small class="badge badge-light border"><?= htmlspecialchars($item['lot_number'] ?: 'LOT2026') ?></small>
                            <?php if (!empty($item['expiration_date'])): ?>
                                <br><small class="text-muted"><?= date('d/m/Y', strtotime($item['expiration_date'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center font-weight-bold"><?= htmlspecialchars($item['unit_name'] ?? 'Box') ?></td>
                        <td class="text-center font-weight-bold"><?= $item['quantity'] ?></td>
                        <td class="text-right"><?= format_vnd($item['price_per_unit']) ?></td>
                        <td class="text-right font-weight-bold text-dark"><?= format_vnd($item['total_price']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 5. Total calculation -->
        <div class="row mb-3">
            <div class="col-sm-7">
                <div class="p-3 bg-light rounded border" style="font-size: 12px;">
                    <div class="font-weight-bold text-dark mb-1"><i class="fas fa-shield-alt text-success mr-1"></i> Distributor Quality Commitments:</div>
                    <ul class="mb-0 pl-3 text-muted">
                        <li>100% genuine merchandise accompanied by official VAT invoices and Food Safety (ATTP) certifications.</li>
                        <li>Guaranteed minimum shelf-life of 6+ months upon delivery.</li>
                        <li>Stored and dispatched under temperature-controlled WMS protocols (Dry / Chilled / Frozen).</li>
                    </ul>
                </div>
            </div>
            <div class="col-sm-5">
                <div class="total-summary-card">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Merchandise Subtotal:</span>
                        <strong class="text-dark"><?= format_vnd($items_subtotal) ?></strong>
                    </div>
                    <?php if ($shipping_fee > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping Fee (GHN Express):</span>
                        <strong><?= format_vnd($shipping_fee) ?></strong>
                    </div>
                    <?php endif; ?>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-primary">GRAND TOTAL:</h6>
                        <h4 class="mb-0 font-weight-bold text-danger"><?= format_vnd($grand_total) ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Proof of Delivery (POD) - 3 Signatures -->
        <div class="pod-box">
            <div class="row">
                <div class="col-4 pod-col">
                    <div class="sign-title">WAREHOUSE DISPATCH</div>
                    <div class="sign-sub">(Signed, Stamped &amp; Full Name)</div>
                    <div class="sign-name">Wholesale Logistics Dept.</div>
                </div>
                <div class="col-4 pod-col">
                    <div class="sign-title">LOGISTICS CARRIER / GHN</div>
                    <div class="sign-sub">(Signature &amp; Full Name)</div>
                    <div class="sign-name">GHN Express Driver</div>
                </div>
                <div class="col-4 pod-col">
                    <div class="sign-title">HEAD CHEF / CANTEEN RECEIVER</div>
                    <div class="sign-sub">(Received Intact &amp; Signed)</div>
                    <div class="sign-name"><?= htmlspecialchars($order['username']) ?></div>
                </div>
            </div>
        </div>

        <!-- 7. Buttons (No Print) -->
        <div class="text-center mt-4 pt-3 border-top no-print">
            <button class="btn btn-primary px-4 mr-2" onclick="window.print()">
                <i class="fas fa-print mr-1"></i> Print Delivery Invoice
            </button>
            <button class="btn btn-outline-secondary px-4" onclick="window.close()">
                <i class="fas fa-times mr-1"></i> Close
            </button>
        </div>
    </div>
</body>
</html>
