<?php
$page_name = 'home';
$page_title = 'Dong Khoi Store | B2B Wholesale Marketplace for Packaged Food & Beverages';
require_once __DIR__ . '/bootstrap.php';

// Load Blue Theme Stylesheet
$extra_styles = [
    BASE_URL . '/assets/css/shopee-home.css?v=' . time()
];

include INCLUDES_PATH . '/header.php';

$conn = getDbConnection();
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$cat_filter   = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$sort_by      = isset($_GET['sort']) ? trim($_GET['sort']) : 'default';
$active_tab   = isset($_GET['tab']) ? trim($_GET['tab']) : 'all';

// Remove accents for smart search matching
function remove_accents($str) {
    if (function_exists('transliterator_transliterate')) {
        $str = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $str);
    } else {
        $str = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
    }
    return strtolower(trim($str));
}

// 1. Fetch categories and count products
$categories = [];
$cat_sql = "SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            GROUP BY c.id 
            ORDER BY c.id ASC";
$cat_res = $conn->query($cat_sql);
if ($cat_res) {
    while ($crow = $cat_res->fetch_assoc()) {
        $categories[] = $crow;
    }
}

// 2. Fetch all products
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id";
if ($cat_filter > 0) {
    $sql .= " WHERE p.category_id = " . $cat_filter;
}
$sql .= " ORDER BY p.id ASC";

$raw_products = [];
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $raw_products[] = $row;
    }
}

// 3. Search filtering
$products = [];
if ($search_query !== '') {
    $sq_clean = remove_accents($search_query);
    $words = array_filter(explode(' ', $sq_clean));

    foreach ($raw_products as $p) {
        $p_name_clean = remove_accents($p['name'] ?? '');
        $p_cat_clean  = remove_accents($p['category_name'] ?? '');
        $score = 0;

        if (mb_strpos($p_name_clean, $sq_clean) !== false) {
            $score += 100;
        } elseif (mb_strpos($p_cat_clean, $sq_clean) !== false) {
            $score += 60;
        }

        $matched_words = 0;
        foreach ($words as $w) {
            if (mb_strlen($w) >= 2 && (mb_strpos($p_name_clean, $w) !== false || mb_strpos($p_cat_clean, $w) !== false)) {
                $matched_words++;
            }
        }
        if (count($words) > 0 && $matched_words > 0) {
            $score += ($matched_words / count($words)) * 50;
        }

        if ($score > 0) {
            $p['search_score'] = $score;
            $products[] = $p;
        }
    }

    usort($products, function($a, $b) {
        return ($b['search_score'] ?? 0) <=> ($a['search_score'] ?? 0);
    });
} else {
    $products = $raw_products;
}

// 4. Sort handling
if ($sort_by === 'price_asc') {
    usort($products, fn($a, $b) => $a['base_price'] <=> $b['base_price']);
} elseif ($sort_by === 'price_desc') {
    usort($products, fn($a, $b) => $b['base_price'] <=> $a['base_price']);
} elseif ($sort_by === 'name_asc') {
    usort($products, fn($a, $b) => strcmp($a['name'], $b['name']));
}

// 5. Flash Sale sample products
$flash_sale_items = array_slice($raw_products, 0, 6);
?>

<!-- =========================================================================
     1. BLUE SEARCH & TRENDING KEYWORDS BAR
     ========================================================================= -->
<div class="shopee-search-wrapper">
    <div class="container">
        <form method="GET" action="<?= BASE_URL ?>/index.php" class="shopee-search-form">
            <div class="shopee-search-box">
                <input type="text" name="search" class="shopee-search-input" 
                       placeholder="Free shipping on bulk orders - Search wholesale beverages, canned foods, instant noodles, sausages..." 
                       value="<?= htmlspecialchars($search_query) ?>" autocomplete="off">
                <button type="button" class="shopee-camera-btn" id="btnOpenImageSearch" title="Search by image (AI Vision)">
                    <i class="fas fa-camera"></i>
                    <span class="camera-ai-tag">AI</span>
                </button>
                <button type="submit" class="shopee-search-btn">
                    <i class="fas fa-search"></i>
                    <span>SEARCH</span>
                </button>
            </div>
        </form>
        <div class="shopee-trending-tags">
            <span class="text-white-50 mr-1" style="font-size: 12px;"><i class="fas fa-fire mr-1" style="color: #ffd839;"></i> Trending:</span>
            <a href="<?= BASE_URL ?>/index.php?search=Hao+Hao">Hao Hao Noodles</a>
            <a href="<?= BASE_URL ?>/index.php?search=Coca">Coca-Cola 320ml</a>
            <a href="<?= BASE_URL ?>/index.php?search=Green+Tea">Green Tea 500ml</a>
            <a href="<?= BASE_URL ?>/index.php?search=Tuna">Canned Tuna</a>
            <a href="<?= BASE_URL ?>/index.php?search=Sausage">Duc Viet Sausage</a>
            <a href="<?= BASE_URL ?>/index.php?search=ST25">ST25 Rice 5kg</a>
            <a href="<?= BASE_URL ?>/index.php?search=Pepsi">Pepsi 320ml</a>
            <a href="<?= BASE_URL ?>/index.php?cat=1">Wholesale Drinks</a>
        </div>
    </div>
</div>

<div class="container my-3">
    <!-- =========================================================================
         2. HERO SECTION (2:1 SPLIT CAROUSEL + 2 STACKED BANNERS)
         ========================================================================= -->
    <div class="shopee-hero-section">
        <div class="row no-gutters">
            <!-- Left Main Slider (66.6% width) -->
            <div class="col-lg-8 pr-lg-2 mb-3 mb-lg-0">
                <div id="shopeeHeroCarousel" class="carousel slide shopee-main-slider" data-ride="carousel" data-interval="4000">
                    <ol class="carousel-indicators">
                        <li data-target="#shopeeHeroCarousel" data-slide-to="0" class="active"></li>
                        <li data-target="#shopeeHeroCarousel" data-slide-to="1"></li>
                        <li data-target="#shopeeHeroCarousel" data-slide-to="2"></li>
                    </ol>
                    <div class="carousel-inner h-100">
                        <div class="carousel-item active h-100">
                            <img src="<?= BASE_URL ?>/assets/images/slider-b2b-1.png" class="d-block w-100" alt="B2B Wholesale Expo">
                            <div class="shopee-slider-overlay">
                                <span class="badge badge-warning px-2 py-1 mb-2 font-weight-bold" style="background:#ffd839; color:#003e87;">B2B WHOLESALE EXPO</span>
                                <h3>BULK ORDERING FOR CANTEENS - FACTORY PRICES</h3>
                                <p>Discounts up to 35% for schools, canteens, and institutional catering</p>
                            </div>
                        </div>
                        <div class="carousel-item h-100">
                            <img src="<?= BASE_URL ?>/assets/images/slider-b2b-2.png" class="d-block w-100" alt="Freeship Xtra">
                            <div class="shopee-slider-overlay">
                                <span class="badge badge-success px-2 py-1 mb-2 font-weight-bold" style="background:#00bfa5;">FREESHIP XTRA</span>
                                <h3>2-HOUR EXPRESS METRO DELIVERY</h3>
                                <p>Free shipping on bulk orders from 500,000 VND with on-site return warranty</p>
                            </div>
                        </div>
                        <div class="carousel-item h-100">
                            <img src="<?= BASE_URL ?>/assets/images/slider-b2b-3.png" class="d-block w-100" alt="100% Genuine">
                            <div class="shopee-slider-overlay">
                                <span class="badge badge-primary px-2 py-1 mb-2 font-weight-bold" style="background:#0b74e5;">100% GENUINE</span>
                                <h3>VERIFIED ORIGIN - OFFICIAL VAT INVOICES</h3>
                                <p>Fresh production dates, certified food safety and hygiene standards</p>
                            </div>
                        </div>
                    </div>
                    <a class="carousel-control-prev" href="#shopeeHeroCarousel" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#shopeeHeroCarousel" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            </div>

            <!-- Right Side Stacked Banners (33.3% width) -->
            <div class="col-lg-4 pl-lg-2">
                <div class="shopee-side-banners">
                    <a href="<?= BASE_URL ?>/index.php?cat=1" class="shopee-side-banner-card">
                        <img src="<?= asset_img('images/banner-01.jpg') ?>" alt="Case Wholesale Discount">
                        <div class="shopee-side-banner-content">
                            <span class="shopee-side-banner-tag">CASE DISCOUNT</span>
                            <h4 class="shopee-side-banner-title">BUY BY THE CASE<br>EXTRA 15% OFF</h4>
                            <p class="shopee-side-banner-desc">Applicable to soft drinks & instant noodles</p>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?cat=2" class="shopee-side-banner-card">
                        <img src="<?= asset_img('images/banner-02.jpg') ?>" alt="Partner Welcome Voucher">
                        <div class="shopee-side-banner-content style-navy">
                            <span class="shopee-side-banner-tag" style="background:#00bfa5; color:#fff;">VOUCHER 200K</span>
                            <h4 class="shopee-side-banner-title">FIRST CANTEEN ORDER<br>GET 200,000 VND OFF</h4>
                            <p class="shopee-side-banner-desc">Flexible monthly statement credit reconciliation</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- 10 Quick Service Icons -->
        <div class="shopee-quick-services">
            <div class="shopee-service-grid">
                <a href="#flashSaleSection" class="shopee-service-item">
                    <div class="shopee-service-icon bg-blue"><i class="fas fa-bolt"></i></div>
                    <span class="shopee-service-label">Flash Sale</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php" class="shopee-service-item">
                    <div class="shopee-service-icon bg-teal"><i class="fas fa-truck-moving"></i></div>
                    <span class="shopee-service-label">Free Shipping</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php" class="shopee-service-item">
                    <div class="shopee-service-icon bg-yellow"><i class="fas fa-tags"></i></div>
                    <span class="shopee-service-label">Vouchers</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php?cat=1" class="shopee-service-item">
                    <div class="shopee-service-icon bg-cyan"><i class="fas fa-wine-bottle"></i></div>
                    <span class="shopee-service-label">Beverages</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php?cat=2" class="shopee-service-item">
                    <div class="shopee-service-icon bg-indigo"><i class="fas fa-fish"></i></div>
                    <span class="shopee-service-label">Canned Goods</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php?cat=4" class="shopee-service-item">
                    <div class="shopee-service-icon bg-purple"><i class="fas fa-utensils"></i></div>
                    <span class="shopee-service-label">Dry Foods</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php?cat=3" class="shopee-service-item">
                    <div class="shopee-service-icon bg-blue"><i class="fas fa-drumstick-bite"></i></div>
                    <span class="shopee-service-label">Fresh & Chilled</span>
                </a>
                <a href="<?= BASE_URL ?>/public/contact-us.php" class="shopee-service-item">
                    <div class="shopee-service-icon bg-teal"><i class="fas fa-file-invoice-dollar"></i></div>
                    <span class="shopee-service-label">VAT Invoices</span>
                </a>
                <a href="<?= BASE_URL ?>/public/contact-us.php" class="shopee-service-item">
                    <div class="shopee-service-icon bg-cyan"><i class="fas fa-clock"></i></div>
                    <span class="shopee-service-label">2H Delivery</span>
                </a>
                <a href="<?= BASE_URL ?>/public/my-account.php" class="shopee-service-item">
                    <div class="shopee-service-icon bg-yellow"><i class="fas fa-award"></i></div>
                    <span class="shopee-service-label">VIP Partners</span>
                </a>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         3. FLASH SALE SECTION
         ========================================================================= -->
    <div class="shopee-flash-sale-section" id="flashSaleSection">
        <div class="shopee-fs-header">
            <div class="shopee-fs-title-box">
                <div class="shopee-fs-logo">
                    <i class="fas fa-bolt"></i>
                    <span>FLASH SALE</span>
                </div>
                <div class="shopee-countdown-timer">
                    <span class="shopee-timer-box" id="fsHour">02</span>
                    <span class="shopee-timer-sep">:</span>
                    <span class="shopee-timer-box" id="fsMinute">45</span>
                    <span class="shopee-timer-sep">:</span>
                    <span class="shopee-timer-box" id="fsSecond">18</span>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/index.php#dailyDiscoverSection" class="shopee-fs-link">
                <span>View All</span>
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        <div class="shopee-fs-body">
            <div class="row">
                <?php 
                $discounts = [35, 28, 25, 30, 22, 40];
                $solds     = [38, 52, 24, 46, 19, 63];
                foreach ($flash_sale_items as $idx => $f): 
                    $disc = $discounts[$idx % count($discounts)];
                    $sold = $solds[$idx % count($solds)];
                    $old_price = $f['base_price'] * (1 + $disc / 100);
                    $percent_bar = min(100, round(($sold / 70) * 100));
                ?>
                <div class="col-6 col-md-4 col-lg-2 mb-3 mb-lg-0">
                    <a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $f['id'] ?>" class="shopee-fs-item">
                        <div class="shopee-fs-img-wrap">
                            <span class="shopee-discount-badge">-<?= $disc ?>%</span>
                            <img src="<?= asset_img($f['image_url'] ?? '') ?>" 
                                 alt="<?= htmlspecialchars($f['name']) ?>" 
                                 onerror="this.src='<?= asset_img('images/img-pro-01.jpg') ?>'">
                        </div>
                        <div class="shopee-fs-price"><?= format_vnd($f['base_price']) ?></div>
                        <div class="shopee-fs-old-price"><?= format_vnd($old_price) ?></div>
                        <div class="shopee-fs-progress-box">
                            <div class="shopee-fs-progress-bar" style="width: <?= $percent_bar ?>%;"></div>
                            <span class="shopee-fs-progress-text"><i class="fas fa-fire"></i> SOLD <?= $sold ?></span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         4. FEATURED CATEGORIES
         ========================================================================= -->
    <div class="shopee-categories-section">
        <div class="shopee-section-title-bar">
            <span>FEATURED CATEGORIES</span>
            <span class="badge badge-light text-muted font-weight-normal" style="font-size: 12px;"><?= count($raw_products) ?> wholesale products available</span>
        </div>
        <div class="shopee-cat-grid">
            <?php 
            $cat_icons = [
                1 => ['icon' => 'fa-wine-bottle',   'color' => '#e8f3ff', 'text' => '#0b74e5', 'en' => 'Beverages & Soft Drinks'],
                2 => ['icon' => 'fa-fish',          'color' => '#e6faf7', 'text' => '#00bfa5', 'en' => 'Canned & Preserved Foods'],
                3 => ['icon' => 'fa-drumstick-bite', 'color' => '#fffbe6', 'text' => '#d97706', 'en' => 'Chilled & Fresh Foods'],
                4 => ['icon' => 'fa-box',           'color' => '#eef2ff', 'text' => '#4338ca', 'en' => 'Noodles, Rice & Dry Goods']
            ];
            foreach ($categories as $cat): 
                $icon_info = $cat_icons[$cat['id']] ?? ['icon' => 'fa-shopping-basket', 'color' => '#f3e8ff', 'text' => '#7e22ce', 'en' => $cat['name']];
            ?>
            <a href="<?= BASE_URL ?>/index.php?cat=<?= $cat['id'] ?>#dailyDiscoverSection" 
               class="shopee-cat-card <?= ($cat_filter === (int)$cat['id']) ? 'border-primary' : '' ?>">
                <div class="shopee-cat-icon-wrap" style="background: <?= $icon_info['color'] ?>; color: <?= $icon_info['text'] ?>;">
                    <i class="fas <?= $icon_info['icon'] ?>"></i>
                </div>
                <div class="shopee-cat-name"><?= htmlspecialchars($icon_info['en']) ?></div>
                <div class="shopee-cat-count"><?= $cat['product_count'] ?> products</div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- =========================================================================
         5. DONG KHOI MALL COMMITMENTS
         ========================================================================= -->
    <div class="shopee-mall-section">
        <div class="shopee-mall-header">
            <div class="shopee-mall-brand">
                <span class="badge badge-light text-primary px-2 py-1 font-weight-bold mr-2" style="font-size: 12px; letter-spacing: 1px;">MALL</span>
                <span>DONG KHOI MALL - OFFICIAL WHOLESALE DISTRIBUTION</span>
            </div>
            <div class="shopee-mall-commitments">
                <div class="shopee-mall-commit-item">
                    <i class="fas fa-undo-alt" style="color: #ffd839;"></i>
                    <span>7-Day Free Returns</span>
                </div>
                <div class="shopee-mall-commit-item">
                    <i class="fas fa-shield-alt" style="color: #ffd839;"></i>
                    <span>100% Authentic Brands</span>
                </div>
                <div class="shopee-mall-commit-item">
                    <i class="fas fa-shipping-fast" style="color: #ffd839;"></i>
                    <span>Fast Canteen Delivery</span>
                </div>
                <div class="shopee-mall-commit-item">
                    <i class="fas fa-receipt" style="color: #ffd839;"></i>
                    <span>Full VAT Tax Invoices</span>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         6. DAILY DISCOVER (PRODUCT FEED)
         ========================================================================= -->
    <div class="shopee-daily-discover" id="dailyDiscoverSection">
        <!-- Sticky Tab Navigation -->
        <div class="shopee-tab-nav">
            <a href="<?= BASE_URL ?>/index.php#dailyDiscoverSection" 
               class="shopee-tab-btn <?= ($cat_filter === 0 && $sort_by === 'default') ? 'active' : '' ?>">
                <i class="fas fa-star text-warning"></i> DAILY DISCOVER
            </a>
            <a href="<?= BASE_URL ?>/index.php?sort=best_seller#dailyDiscoverSection" 
               class="shopee-tab-btn <?= ($sort_by === 'best_seller') ? 'active' : '' ?>">
                <i class="fas fa-fire text-danger"></i> BEST SELLERS
            </a>
            <a href="<?= BASE_URL ?>/index.php?sort=price_asc#dailyDiscoverSection" 
               class="shopee-tab-btn <?= ($sort_by === 'price_asc') ? 'active' : '' ?>">
                <i class="fas fa-arrow-down text-success"></i> PRICE: LOW TO HIGH
            </a>
            <a href="<?= BASE_URL ?>/index.php?sort=price_desc#dailyDiscoverSection" 
               class="shopee-tab-btn <?= ($sort_by === 'price_desc') ? 'active' : '' ?>">
                <i class="fas fa-arrow-up text-primary"></i> PRICE: HIGH TO LOW
            </a>
        </div>

        <!-- Category Filter Chips -->
        <div class="shopee-filter-chips">
            <a href="<?= BASE_URL ?>/index.php#dailyDiscoverSection" 
               class="shopee-chip-btn <?= ($cat_filter === 0) ? 'active' : '' ?>">All (<?= count($raw_products) ?>)</a>
            <?php foreach ($categories as $c): ?>
            <a href="<?= BASE_URL ?>/index.php?cat=<?= $c['id'] ?>#dailyDiscoverSection" 
               class="shopee-chip-btn <?= ($cat_filter === (int)$c['id']) ? 'active' : '' ?>">
                <?= htmlspecialchars($c['name']) ?> (<?= $c['product_count'] ?>)
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Product Grid -->
        <?php if (empty($products)): ?>
            <div class="bg-white rounded p-5 text-center shadow-sm">
                <img src="<?= BASE_URL ?>/assets/images/img-pro-01.jpg" style="width: 80px; opacity: 0.5;" class="mb-3" alt="">
                <h5 class="text-muted">No matching wholesale products found!</h5>
                <p class="text-muted mb-3" style="font-size: 13px;">Try searching with another keyword or explore our full catalog.</p>
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-sm btn-outline-primary px-4">View All Products</a>
            </div>
        <?php else: ?>
            <div class="row no-gutters mx-n1" id="productGridContainer">
                <?php 
                $card_discounts = [15, 20, 25, 12, 18, 30, 10, 22];
                $card_ratings   = [4.9, 5.0, 4.8, 4.9, 4.7, 5.0];
                $card_solds     = ['1.2k', '850', '2.4k', '640', '1.8k', '3.1k', '490', '920'];

                foreach ($products as $idx => $p): 
                    $disc = $card_discounts[$idx % count($card_discounts)];
                    $rate = $card_ratings[$idx % count($card_ratings)];
                    $sold = $card_solds[$idx % count($card_solds)];
                    $old_price = $p['base_price'] * (1 + $disc / 100);
                    $is_mall = ($idx % 3 === 0);
                ?>
                <div class="col-6 col-md-4 col-lg-3 px-1">
                    <div class="shopee-product-card">
                        <?php if ($is_mall): ?>
                            <span class="shopee-mall-badge">MALL</span>
                        <?php else: ?>
                            <span class="shopee-favorite-badge"><i class="fas fa-check"></i> Preferred</span>
                        <?php endif; ?>

                        <div class="shopee-card-img-wrap">
                            <span class="shopee-discount-badge">-<?= $disc ?>%</span>
                            <a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $p['id'] ?>">
                                <img src="<?= asset_img($p['image_url'] ?? '') ?>" 
                                     alt="<?= htmlspecialchars($p['name']) ?>" 
                                     loading="lazy"
                                     onerror="this.src='<?= BASE_URL ?>/assets/images/img-pro-01.jpg'">
                            </a>
                            <span class="shopee-shipping-tag"><i class="fas fa-truck-moving"></i> Freeship Xtra</span>
                        </div>

                        <div class="shopee-card-body">
                            <a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $p['id'] ?>" class="shopee-card-title">
                                <?= htmlspecialchars($p['name']) ?>
                            </a>

                            <div class="shopee-tag-pill-row">
                                <span class="shopee-tag-pill">Wholesale Case</span>
                                <span class="shopee-tag-pill" style="color: #00bfa5; border-color: #00bfa5;">Canteen Grade</span>
                            </div>

                            <div class="shopee-price-row">
                                <span class="shopee-card-price"><?= format_vnd($p['base_price']) ?></span>
                                <span class="shopee-card-old-price"><?= format_vnd($old_price) ?></span>
                            </div>

                            <div class="shopee-social-row">
                                <div class="shopee-rating">
                                    <i class="fas fa-star"></i>
                                    <span class="shopee-rating-score"><?= $rate ?></span>
                                </div>
                                <span class="shopee-sold-count">Sold <?= $sold ?></span>
                            </div>

                            <div class="shopee-location-row">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                                <span>Dong Khoi Hub - HCMC</span>
                            </div>

                            <div class="shopee-card-actions">
                                <button type="button" class="shopee-btn-add-cart add-to-cart-btn" data-id="<?= $p['id'] ?>">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                                <a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $p['id'] ?>" class="shopee-btn-view-detail" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Load More Button -->
            <div class="shopee-load-more-wrap">
                <button type="button" class="shopee-load-more-btn" id="shopeeLoadMoreBtn">
                    <span>Load More Products</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- =========================================================================
         7. TRUST PILLARS (B2B COMMITMENTS)
         ========================================================================= -->
    <div class="shopee-trust-section">
        <div class="shopee-trust-grid">
            <div class="shopee-trust-item">
                <div class="shopee-trust-icon"><i class="fas fa-boxes"></i></div>
                <div>
                    <h5 class="shopee-trust-title">Direct Factory Prices</h5>
                    <p class="shopee-trust-sub">Directly sourced from top brands, providing the best wholesale margins.</p>
                </div>
            </div>
            <div class="shopee-trust-item">
                <div class="shopee-trust-icon"><i class="fas fa-shipping-fast"></i></div>
                <div>
                    <h5 class="shopee-trust-title">Express 2-Hour Delivery</h5>
                    <p class="shopee-trust-sub">Dedicated delivery fleet serving schools, canteens, and enterprises.</p>
                </div>
            </div>
            <div class="shopee-trust-item">
                <div class="shopee-trust-icon"><i class="fas fa-certificate"></i></div>
                <div>
                    <h5 class="shopee-trust-title">100% Certified Food Safety</h5>
                    <p class="shopee-trust-sub">Fresh shelf life, transparent origins, and complete VAT documentation.</p>
                </div>
            </div>
            <div class="shopee-trust-item">
                <div class="shopee-trust-icon"><i class="fas fa-headset"></i></div>
                <div>
                    <h5 class="shopee-trust-title">24/7 Dedicated Support</h5>
                    <p class="shopee-trust-sub">Instant quotation, contract support, and flexible credit lines.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     8. FLOATING ACTION TOOLS (CHAT / CART)
     ========================================================================= -->
<div class="shopee-floating-tools">
    <a href="https://zalo.me" target="_blank" class="shopee-float-btn shopee-float-zalo" title="B2B Support Chat">
        <i class="fas fa-comment-dots"></i>
    </a>
</div>

<script>
// Flash Sale Countdown Timer & Interaction Scripts
(function() {
    // 1. Countdown timer
    var hours = 2, minutes = 45, seconds = 18;
    var hElem = document.getElementById('fsHour');
    var mElem = document.getElementById('fsMinute');
    var sElem = document.getElementById('fsSecond');

    function padZero(num) {
        return num < 10 ? '0' + num : num;
    }

    if (hElem && mElem && sElem) {
        setInterval(function() {
            seconds--;
            if (seconds < 0) {
                seconds = 59;
                minutes--;
                if (minutes < 0) {
                    minutes = 59;
                    hours--;
                    if (hours < 0) {
                        hours = 3;
                    }
                }
            }
            hElem.textContent = padZero(hours);
            mElem.textContent = padZero(minutes);
            sElem.textContent = padZero(seconds);
        }, 1000);
    }

    // 2. Load More button
    var loadMoreBtn = document.getElementById('shopeeLoadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            var btn = this;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading more wholesale items...';
            setTimeout(function() {
                btn.innerHTML = '<i class="fas fa-check text-primary"></i> All wholesale products loaded!';
                btn.classList.add('disabled');
                btn.style.opacity = '0.7';
            }, 800);
        });
    }
})();
</script>

<?php
include INCLUDES_PATH . '/footer.php';
?>
