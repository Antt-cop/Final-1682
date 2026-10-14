<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name = 'gallery';
$page_title = 'Product Gallery - Dong Khoi Store';
include INCLUDES_PATH . '/header.php';

$conn = getDbConnection();
$gallery_items = [];
$res = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'active' ORDER BY p.id ASC LIMIT 24");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $gallery_items[] = $row;
    }
}

$cat_class_map = [
    1 => 'beverages',
    2 => 'canned',
    3 => 'chilled',
    4 => 'dry'
];
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Product Gallery</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                        <li class="breadcrumb-item active">Gallery</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <!-- Start Gallery  -->
    <div class="products-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="title-all text-center">
                        <h1>Wholesale Product Showcase</h1>
                        <p>Explore our premium B2B wholesale product portfolio across canteen & institutional categories.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="special-menu text-center">
                        <div class="button-group filter-button-group">
                            <button class="active" data-filter="*">All Categories</button>
                            <button data-filter=".beverages">Beverages</button>
                            <button data-filter=".canned">Canned Foods</button>
                            <button data-filter=".chilled">Chilled & Fresh</button>
                            <button data-filter=".dry">Dry Goods & Staples</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row special-list">
                <?php foreach ($gallery_items as $item): 
                    $catClass = $cat_class_map[$item['category_id']] ?? 'dry';
                ?>
                <div class="col-lg-3 col-md-6 special-grid <?= $catClass ?>">
                    <div class="products-single fix">
                        <div class="box-img-hover">
                            <div class="type-lb">
                                <p class="sale"><?= htmlspecialchars($item['category_name'] ?? 'B2B') ?></p>
                            </div>
                            <img src="<?= asset_img($item['image_url'] ?? '') ?>" class="img-fluid" alt="<?= htmlspecialchars($item['name']) ?>" style="height: 220px; width: 100%; object-fit: contain; padding: 10px; background: #fff;" onerror="this.src='<?= asset_img('images/img-pro-01.jpg') ?>'">
                            <div class="mask-icon">
                                <ul>
                                    <li><a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $item['id'] ?>" data-toggle="tooltip" data-placement="right" title="View"><i class="fas fa-eye"></i></a></li>
                                </ul>
                                <a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $item['id'] ?>" class="cart">Order Now</a>
                            </div>
                        </div>
                        <div class="why-text text-center">
                            <h4><?= htmlspecialchars($item['name']) ?></h4>
                            <h5><?= format_vnd($item['base_price']) ?></h5>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <!-- End Gallery  -->

<?php
include INCLUDES_PATH . '/footer.php';
?>
