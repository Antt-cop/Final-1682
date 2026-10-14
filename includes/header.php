<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
// Set default page title and current page
if (!isset($page_title)) {
    $page_title = 'DONG KHOI';
}
if (!isset($page_name)) {
    $page_name = '';
}

// Redirect rejected users
if (isset($_SESSION['user_id']) && isset($_SESSION['status']) && $_SESSION['status'] === 'rejected') {
    $current_file = basename($_SERVER['PHP_SELF']);
    $allowed_files = ['logout.php', 'login.php'];
    if (!in_array($current_file, $allowed_files)) {
        header('Location: ' . BASE_URL . '/public/login.php?rejected=1');
        exit;
    }
}

// db already loaded via bootstrap.php
$header_conn = getDbConnection();

$header_cart_items = [];
$header_sub_total = 0;
$header_cart_count = 0;

if (!empty($_SESSION['cart'])) {
    $resolved_header = resolve_cart_items($_SESSION['cart'], $header_conn);
    $header_cart_items = $resolved_header['items'];
    $header_sub_total = $resolved_header['grand_total'];
    $header_cart_count = $resolved_header['count'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Site Metas -->
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Site Icons -->
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/images/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/apple-touch-icon.png">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap.min.css">
    <!-- Site CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>">
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css?v=<?= time() ?>">
    <!-- Unified Blue Theme CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme-unified.css?v=<?= time() ?>">
    <!-- AI Visual Search (Gemini Vision) CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/image-search.css?v=<?= time() ?>">
    <?php
    if (isset($extra_styles)) {
        foreach ($extra_styles as $style) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($style) . '">' . "\n";
        }
    }
    ?>

    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
        window.isLoggedIn = <?= (isset($_SESSION['user_id']) || isset($_SESSION['user_name'])) ? 'true' : 'false' ?>;
        var isLoggedIn = window.isLoggedIn;
    </script>
</head>
<body>
    <!-- Start Main Top -->
    <div class="main-top">
        <div class="container-fluid">
            <div class="row align-items-center">
                <!-- Left: Shopee Quick Utility Links -->
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 d-none d-md-block">
                    <div class="shopee-top-left text-white" style="font-size: 13px; line-height: 28px;">
                        <span class="mr-3"><i class="fas fa-store-alt mr-1"></i> B2B Wholesale & Canteen Hub</span>
                        <span class="mr-3"><i class="fas fa-shield-alt mr-1"></i> 100% Genuine Quality</span>
                        <span><i class="fas fa-phone-alt mr-1"></i> Hotline: <strong style="color: #ffd839;">09 730 25 730</strong></span>
                    </div>
                </div>
                <!-- Right: Navigation & Auth Links -->
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="our-link text-right">
                        <ul>
                            <li><a href="<?= BASE_URL ?>/public/contact-us.php"><i class="fas fa-question-circle"></i> Help & Support</a></li>
                            <li><a href="<?= BASE_URL ?>/public/wishlist.php"><i class="fas fa-heart" style="color: #ff4d4f;"></i> Wishlist</a></li>
                            <?php if (isset($_SESSION['user_name'])): ?>
                                <li><a href="<?= BASE_URL ?>/public/my-account.php"><i class="fa fa-user-circle"></i> <?=htmlspecialchars($_SESSION['user_name'])?></a></li>
                                <li><a href="<?= BASE_URL ?>/app/handlers/logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
                            <?php else: ?>
                                <li><a href="<?= BASE_URL ?>/public/register.php"><i class="fa fa-user-plus"></i> Register</a></li>
                                <li><a href="<?= BASE_URL ?>/public/login.php"><i class="fa fa-sign-in-alt"></i> Login</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Main Top -->

    <!-- Start Header -->
    <header class="main-header">
        <!-- Start Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light navbar-default bootsnav">
            <div class="container">
                <!-- Start Header Navigation -->
                <div class="navbar-header">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-menu" aria-controls="navbars-rs-food" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa fa-bars"></i>
                </button>
                    <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>/index.php" style="padding: 4px 0; height: auto;">
                        <div class="shopee-brand-icon mr-2" style="flex-shrink: 0;"><i class="fa fa-shopping-bag"></i></div>
                        <div class="d-flex flex-column justify-content-center" style="line-height: 1;">
                            <h2 class="m-0 text-white font-weight-bold" style="font-size: 22px; line-height: 1.1; letter-spacing: 0.5px; margin: 0 !important; padding: 0 !important;">DONG KHOI</h2>
                            <small style="color: #ffe0b2; font-size: 10px; font-weight: 600; letter-spacing: 0.5px; line-height: 1; margin: 0 !important; margin-top: 2px !important; padding: 0 !important; display: block;">B2B CANTEEN &amp; WHOLESALE</small>
                        </div>
                    </a>
                </div>
                <!-- End Header Navigation -->
                <div class="collapse navbar-collapse" id="navbar-menu">
                    <ul class="nav navbar-nav ml-auto" data-in="fadeInDown" data-out="fadeOutUp">
                        <li class="nav-item<?php echo ($page_name == 'home') ? ' active' : ''; ?>"><a class="nav-link" href="<?= BASE_URL ?>/index.php">Home</a></li>
                        <li class="nav-item<?php echo ($page_name == 'shop') ? ' active' : ''; ?>"><a class="nav-link" href="<?= BASE_URL ?>/public/shop.php">Shop</a></li>
                        <li class="nav-item<?php echo ($page_name == 'about') ? ' active' : ''; ?>"><a class="nav-link" href="<?= BASE_URL ?>/public/about.php">About Us</a></li>
                        <li class="nav-item<?php echo ($page_name == 'contact') ? ' active' : ''; ?>"><a class="nav-link" href="<?= BASE_URL ?>/public/contact-us.php">Contact Us</a></li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item<?php echo ($page_name == 'admin') ? ' active' : ''; ?>"><a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-tachometer-alt mr-1"></i> Admin</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <!-- Start Attribute Navigation -->
                <div class="attr-nav">
                    <ul>
                        <!-- <li class="search"><a href="#"><i class="fa fa-search"></i></a></li> -->
                        <li class="side-menu">
							<a href="#">
								<span class="cart-icon-box">
									<i class="fa fa-shopping-bag"></i>
									<span class="badge"><?= $header_cart_count ?></span>
								</span>
								<p>My Cart</p>
							</a>
						</li>
                    </ul>
                </div>
                <!-- End Attribute Navigation -->
            </div>
            <!-- Start Side Menu -->
            <div class="side">
                <a href="#" class="close-side"><i class="fa fa-times"></i></a>
                <li class="cart-box">
                    <ul class="cart-list">
                        <?php if(empty($header_cart_items)): ?>
                            <li><p class="text-center pt-3">Your cart is empty.</p></li>
                        <?php else: ?>
                            <?php foreach($header_cart_items as $item): ?>
                            <li>
                                <a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $item['id'] ?>" class="photo"><img src="<?= asset_img($item['image_url'] ?? '') ?>" class="cart-thumb" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.src='<?= asset_img('images/img-pro-01.jpg') ?>'" /></a>
                                <h6><a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?> </a></h6>
                                <p><?= $item['cart_qty'] ?>x <span class="badge badge-info"><?= htmlspecialchars($item['unit_label'] ?? $item['unit_name']) ?></span> - <span class="price"><?= format_vnd($item['unit_price'] ?? $item['base_price']) ?></span></p>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <li class="total">
                            <a href="<?= BASE_URL ?>/public/cart.php" class="btn btn-default hvr-hover btn-cart">VIEW CART</a>
                            <span class="float-right"><strong>Total</strong>: <?= format_vnd($header_sub_total) ?></span>
                        </li>
                    </ul>
                </li>
            </div>
            <!-- End Side Menu -->
        </nav>
        <!-- End Navigation -->
    </header>
    <!-- End Main Top -->

    <!-- Start Top Search -->
    <div class="top-search">
        <div class="container">
            <form method="GET" action="<?= BASE_URL ?>/index.php">
                <div class="input-group">
                    <span class="input-group-addon"><button type="submit" style="background:none;border:none;padding:0;cursor:pointer;"><i class="fa fa-search"></i></button></span>
                    <input type="text" name="search" class="form-control" placeholder="Search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <span class="input-group-addon"><button type="button" class="btn-open-image-search" style="background:none;border:none;padding:0 8px;cursor:pointer;color:#0b74e5;" title="Search by image (AI Vision)"><i class="fa fa-camera"></i></button></span>
                    <span class="input-group-addon close-search"><i class="fa fa-times"></i></span>
                </div>
            </form>
        </div>
    </div>
    <!-- End Top Search -->


