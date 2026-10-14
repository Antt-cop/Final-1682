<?php
/**
 * Scratch script: Reduce products to 5 per category
 * Keeps the 5 products with the lowest ID in each category,
 * deletes the rest (products 6-15 per category).
 */
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/bootstrap.php'; }
require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

// Show current count
echo "=== BEFORE ===\n";
$res = $conn->query('SELECT category_id, COUNT(*) as total FROM products GROUP BY category_id ORDER BY category_id');
while ($r = $res->fetch_assoc()) {
    echo "Category {$r['category_id']}: {$r['total']} products\n";
}

// Delete products ranked > 5 in each category (keep lowest 5 IDs per category)
$sql = "DELETE p FROM products p
        WHERE p.id NOT IN (
            SELECT id FROM (
                SELECT id FROM products p2
                WHERE p2.category_id = p.category_id
                ORDER BY p2.id ASC
                LIMIT 5
            ) AS keep_ids
        )";

// MySQL doesn't support referencing the same table like that directly,
// so we do it per category:
$cats = $conn->query('SELECT DISTINCT category_id FROM products ORDER BY category_id');
$deleted = 0;
while ($cat = $cats->fetch_assoc()) {
    $cat_id = (int)$cat['category_id'];
    // Get the 5 lowest IDs to keep
    $keep_res = $conn->query("SELECT id FROM products WHERE category_id = $cat_id ORDER BY id ASC LIMIT 5");
    $keep_ids = [];
    while ($k = $keep_res->fetch_assoc()) {
        $keep_ids[] = (int)$k['id'];
    }
    if (empty($keep_ids)) continue;

    $ids_str = implode(',', $keep_ids);
    $del = $conn->query("DELETE FROM products WHERE category_id = $cat_id AND id NOT IN ($ids_str)");
    $affected = $conn->affected_rows;
    $deleted += $affected;
    echo "Category $cat_id: deleted $affected products (kept IDs: $ids_str)\n";
}

echo "\nTotal deleted: $deleted products\n\n";

// Show after count
echo "=== AFTER ===\n";
$res2 = $conn->query('SELECT category_id, COUNT(*) as total FROM products GROUP BY category_id ORDER BY category_id');
while ($r = $res2->fetch_assoc()) {
    echo "Category {$r['category_id']}: {$r['total']} products\n";
}

$conn->close();
echo "\nDone!\n";
