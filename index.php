<?php
$page_name = 'home';
$page_title = 'B2B Canteen Distribution Platform';
require_once __DIR__ . '/bootstrap.php';
include INCLUDES_PATH . '/header.php';

$conn = getDbConnection();
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

function remove_accents($str) {
    $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
    $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
    $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
    $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
    $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
    $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
    $str = preg_replace('/(đ)/', 'd', $str);
    $str = preg_replace('/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/', 'A', $str);
    $str = preg_replace('/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/', 'E', $str);
    $str = preg_replace('/(Ì|Í|Ị|Ỉ|Ĩ)/', 'I', $str);
    $str = preg_replace('/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/', 'O', $str);
    $str = preg_replace('/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/', 'U', $str);
    $str = preg_replace('/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/', 'Y', $str);
    $str = preg_replace('/(Đ)/', 'D', $str);
    return $str;
}

$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id";

$products = [];
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        if ($search_query !== '') {
            $name = mb_strtolower($row['name'] ?? '', 'UTF-8');
            $cat = mb_strtolower($row['category_name'] ?? '', 'UTF-8');
            $sq = mb_strtolower($search_query, 'UTF-8');
            
            $name_no_accents = remove_accents($name);
            $cat_no_accents = remove_accents($cat);
            $sq_no_accents = remove_accents($sq);
            
            // 1. Direct match (with and without accents)
            if (mb_strpos($name, $sq) !== false || mb_strpos($cat, $sq) !== false || 
                strpos($name_no_accents, $sq_no_accents) !== false || strpos($cat_no_accents, $sq_no_accents) !== false) {
                $row['score'] = 100;
                $products[] = $row;
                continue;
            }
            
            // 2. Fuzzy match (similar_text) on unaccented strings
            similar_text($sq_no_accents, $name_no_accents, $percent_name);
            similar_text($sq_no_accents, $cat_no_accents, $percent_cat);
            $max_percent = max($percent_name, $percent_cat);
            
            // 3. Word matching
            $words = preg_split('/\s+/', $sq_no_accents);
            $word_matched = false;
            foreach ($words as $w) {
                if (strlen($w) >= 2) {
                    if (strpos($name_no_accents, $w) !== false || strpos($cat_no_accents, $w) !== false) {
                        $word_matched = true;
                        break;
                    }
                }
            }
            
            if ($max_percent > 45 || $word_matched) {
                $row['score'] = $word_matched ? max($max_percent, 60) : $max_percent;
                $products[] = $row;
            }
        } else {
            $products[] = $row;
        }
    }
}

if ($search_query !== '') {
    usort($products, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });
}
?>

    <!-- Start Slider -->
    <div id="slides-shop" class="cover-slides">
        <ul class="slides-container">
            <li class="text-center">
                <img src="images/banner-01.jpg" alt="">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h1 class="m-b-20"><strong>Welcome To <br> CanteenB2B</strong></h1>
                            <p class="m-b-40">Your reliable partner for wholesale packaged food and beverages. <br> Seamless bulk ordering for institutions, canteens, and cafeterias.</p>
                            <p><a class="btn hvr-hover" href="shop.php">Browse Bulk Catalog</a></p>
                        </div>
                    </div>
                </div>
            </li>
            <li class="text-center">
                <img src="images/banner-02.jpg" alt="">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h1 class="m-b-20"><strong>Welcome To <br> CanteenB2B</strong></h1>
                            <p class="m-b-40">Your reliable partner for wholesale packaged food and beverages. <br> Seamless bulk ordering for institutions, canteens, and cafeterias.</p>
                            <p><a class="btn hvr-hover" href="shop.php">Browse Bulk Catalog</a></p>
                        </div>
                    </div>
                </div>
            </li>
            <li class="text-center">
                <img src="images/banner-03.jpg" alt="">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h1 class="m-b-20"><strong>Welcome To <br> CanteenB2B</strong></h1>
                            <p class="m-b-40">Your reliable partner for wholesale packaged food and beverages. <br> Seamless bulk ordering for institutions, canteens, and cafeterias.</p>
                            <p><a class="btn hvr-hover" href="shop.php">Browse Bulk Catalog</a></p>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
        <div class="slides-navigation">
            <a href="#" class="next"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
            <a href="#" class="prev"><i class="fa fa-angle-left" aria-hidden="true"></i></a>
        </div>
    </div>
    <!-- End Slider -->
    <!-- Start Products  -->
    <div class="products-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="title-all text-center">
                        <h1>Packaged Food & Beverages</h1>
                        <p>Discover our wide range of high-quality products available for bulk ordering.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="special-menu text-center">
                        <div class="button-group filter-button-group">
                            <button class="active" data-filter="*">All</button>
                            <button data-filter=".top-featured">Top featured</button>
                            <button data-filter=".best-seller">Best seller</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row special-list">
                <?php if (empty($products)): ?>
                    <div class="col-12 text-center">
                        <p>No products found matching your search.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                    <?php 
                        // Assigning random filter class since there's no specific flag in DB right now
                        $filter_class = (rand(0, 1) == 0) ? 'top-featured' : 'best-seller'; 
                    ?>
                    <div class="col-lg-3 col-md-6 special-grid <?= $filter_class ?>">
                        <div class="products-single fix">
                            <div class="box-img-hover">
                                <div class="type-lb">
                                    <p class="sale"><?= htmlspecialchars($p['category_name']) ?></p>
                                </div>
                                <img src="<?= htmlspecialchars($p['image_url'] ?? 'images/img-pro-01.jpg') ?>" class="img-fluid" alt="<?= htmlspecialchars($p['name']) ?>">
                                <div class="mask-icon">
                                    <ul>
                                        <li><a href="/freshshop-master/public/shop-detail.php?id=<?= $p['id'] ?>" data-toggle="tooltip" data-placement="right" title="View"><i class="fas fa-eye"></i></a></li>
                                        <li><a href="#" data-toggle="tooltip" data-placement="right" title="Compare"><i class="fas fa-sync-alt"></i></a></li>
                                        <li><a href="#" data-toggle="tooltip" data-placement="right" title="Add to Wishlist"><i class="far fa-heart"></i></a></li>
                                    </ul>
                                    <a href="#" class="cart add-to-cart-btn" data-id="<?= $p['id'] ?>">Add to Cart</a>
                                </div>
                            </div>
                            <div class="why-text">
                                <h4><?= htmlspecialchars($p['name']) ?></h4>
                                <h5>$<?= number_format($p['base_price'], 2) ?></h5>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- End Products  -->

    <!-- Start Blog  -->
    <!-- End Blog  -->

<?php
include INCLUDES_PATH . '/footer.php';
?>
