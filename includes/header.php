<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
// Set default page title and current page
if (!isset($page_title)) {
    $page_title = 'B2B Canteen Distribution Platform';
}
if (!isset($page_name)) {
    $page_name = '';
}

// Redirect rejected users to the rejection page to prevent accessing normal pages
if (isset($_SESSION['user_id']) && isset($_SESSION['status']) && $_SESSION['status'] === 'rejected') {
    $current_file = basename($_SERVER['PHP_SELF']);
    $allowed_files = ['rejected.php', 'register_seller.php', 'logout.php'];
    if (!in_array($current_file, $allowed_files)) {
        header('Location: /freshshop-master/seller/rejected.php');
        exit;
    }
}

// db already loaded via bootstrap.php
$header_conn = getDbConnection();

$header_cart_items = [];
$header_sub_total = 0;
$header_cart_count = 0;

if (!empty($_SESSION['cart'])) {
    $header_cart_count = count($_SESSION['cart']);
    $ids = implode(',', array_keys($_SESSION['cart']));
    $res = $header_conn->query("SELECT * FROM products WHERE id IN ($ids)");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $qty = $_SESSION['cart'][$row['id']];
            $row['cart_qty'] = $qty;
            $item_total = $qty * $row['base_price'];
            $header_sub_total += $item_total;
            $header_cart_items[] = $row;
        }
    }
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
    <link rel="shortcut icon" href="/freshshop-master/assets/images/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="/freshshop-master/assets/images/apple-touch-icon.png">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/freshshop-master/assets/css/bootstrap.min.css">
    <!-- Site CSS -->
    <link rel="stylesheet" href="/freshshop-master/assets/css/style.css">
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="/freshshop-master/assets/css/responsive.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/freshshop-master/assets/css/custom.css?v=<?= time() ?>">
    <?php
    if (isset($extra_styles)) {
        foreach ($extra_styles as $style) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($style) . '">' . "\n";
        }
    }
    ?>

</head>
<body>
    <!-- Start Main Top -->
    <div class="main-top">
        <div class="container-fluid">
            <div class="row">
                <!-- Left: Offer Carousel -->
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="text-slid-box">
                        <div id="offer-box" class="carouselTicker">
                            <ul class="offer-box">
                                <li><i class="fab fa-opencart"></i> 20%</li>
                                <li><i class="fab fa-opencart"></i> 50% - 80%</li>
                                <li><i class="fab fa-opencart"></i> Off 10%!</li>
                                <li><i class="fab fa-opencart"></i> Off 50%!</li>
                                <li><i class="fab fa-opencart"></i> Off 10%!</li>
                                <li><i class="fab fa-opencart"></i> 50% - 80%</li>
                                <li><i class="fab fa-opencart"></i> 20%</li>
                                <li><i class="fab fa-opencart"></i> Off 50%</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Right: Navigation & Auth Links -->
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="our-link text-right">
                        <ul>
                            <li><a href="/freshshop-master/public/my-account.php"><i class="fa fa-user s_color"></i> My Account</a></li>
                            <li><a href="/freshshop-master/public/contact-us.php"><i class="fas fa-headset"></i> Contact Us</a></li>
                            <?php if (isset($_SESSION['user_name'])): ?>
                                <li><a href="/freshshop-master/public/my-account.php"><i class="fa fa-user s_color"></i> <?=htmlspecialchars($_SESSION['user_name'])?></a></li>
                                <li><a href="/freshshop-master/app/handlers/logout.php"><i class="fa fa-sign-out-alt s_color"></i> Logout</a></li>
                            <?php else: ?>
                                <li><a href="/freshshop-master/public/register.php"><i class="fa fa-user-plus s_color"></i> Register</a></li>
                                <li><a href="/freshshop-master/public/login.php"><i class="fa fa-sign-in-alt s_color"></i> Login</a></li>
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
                    <a class="navbar-brand" href="index.php" style="padding-top: 15px;"><h2 class="m-0" style="color: #b0b435;"><strong>CanteenB2B</strong></h2></a>
                </div>
                <!-- End Header Navigation -->
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="navbar-menu">
                    <ul class="nav navbar-nav ml-auto" data-in="fadeInDown" data-out="fadeOutUp">
                        <li class="nav-item<?php echo ($page_name == 'home') ? ' active' : ''; ?>"><a class="nav-link" href="/freshshop-master/index.php">Home</a></li>
                        <li class="nav-item<?php echo ($page_name == 'shop') ? ' active' : ''; ?>"><a class="nav-link" href="/freshshop-master/public/shop.php">Shop</a></li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item<?php echo ($page_name == 'admin') ? ' active' : ''; ?>"><a class="nav-link" href="/freshshop-master/admin/dashboard.php">Admin Dashboard</a></li>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'distributor'): ?>
                        <li class="nav-item<?php echo ($page_name == 'seller') ? ' active' : ''; ?>"><a class="nav-link" href="/freshshop-master/seller/products.php">My Products</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <!-- /.navbar-collapse -->
                <!-- Start Attribute Navigation -->
                <div class="attr-nav">
                    <ul>
                        <li class="search"><a href="#"><i class="fa fa-search"></i></a></li>
                        <li class="side-menu">
							<a href="#">
								<i class="fa fa-shopping-bag"></i>
								<span class="badge"><?= $header_cart_count ?></span>
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
                                <a href="#" class="photo"><img src="/freshshop-master/<?= htmlspecialchars($item['image_url'] ?? 'assets/images/img-pro-01.jpg') ?>" class="cart-thumb" alt="" onerror="this.src='/freshshop-master/assets/images/img-pro-01.jpg'" /></a>
                                <h6><a href="/freshshop-master/public/shop-detail.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?> </a></h6>
                                <p><?= $item['cart_qty'] ?>x - <span class="price">$<?= number_format($item['base_price'], 2) ?></span></p>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <li class="total">
                            <a href="/freshshop-master/public/cart.php" class="btn btn-default hvr-hover btn-cart">VIEW CART</a>
                            <span class="float-right"><strong>Total</strong>: $<?= number_format($header_sub_total, 2) ?></span>
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
            <form method="GET" action="index.php">
                <div class="input-group">
                    <span class="input-group-addon"><button type="submit" style="background:none;border:none;padding:0;cursor:pointer;"><i class="fa fa-search"></i></button></span>
                    <input type="text" name="search" class="form-control" placeholder="Search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <span class="input-group-addon close-search"><i class="fa fa-times"></i></span>
                </div>
            </form>
        </div>
    </div>
    <!-- End Top Search -->
