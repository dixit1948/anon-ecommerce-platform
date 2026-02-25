<?php
require_once __DIR__ . '/../includes/auth.php';
$cartCount = getCartCount();
$wishlistCount = getWishlistCount();
$user = getCurrentUser();
$csrf = generateCsrfToken();
$flash = getFlash();

// Get categories for nav
$db = getDB();
$cats = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo isset($pageTitle) ? sanitize($pageTitle) . ' - Anon eCommerce' : 'Anon eCommerce'; ?>
    </title>
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/images/logo/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style-prefix.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
</head>

<body>

    <div class="overlay" data-overlay></div>

    <?php if ($flash): ?>
        <div class="flash-message flash-<?php echo $flash['type']; ?>" id="flashMsg">
            <?php echo htmlspecialchars($flash['message']); ?>
            <button onclick="document.getElementById('flashMsg').remove()"
                style="background:none;border:none;cursor:pointer;float:right;font-size:18px;">&times;</button>
        </div>
    <?php endif; ?>

    <!-- MODAL -->
    <div class="modal" data-modal>
        <div class="modal-close-overlay" data-modal-overlay></div>
        <div class="modal-content">
            <button class="modal-close-btn" data-modal-close>
                <ion-icon name="close-outline"></ion-icon>
            </button>
            <div class="newsletter-img">
                <img src="<?php echo SITE_URL; ?>/assets/images/newsletter.png" alt="subscribe newsletter" width="400"
                    height="400">
            </div>
            <div class="newsletter">
                <form action="<?php echo SITE_URL; ?>/includes/newsletter.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                    <div class="newsletter-header">
                        <h3 class="newsletter-title">Subscribe Newsletter.</h3>
                        <p class="newsletter-desc">Subscribe the <b>Anon</b> to get latest products and discount update.
                        </p>
                    </div>
                    <input type="email" name="email" class="email-field" placeholder="Email Address" required>
                    <button type="submit" class="btn-newsletter">Subscribe</button>
                </form>
            </div>
        </div>
    </div>

    <!-- NOTIFICATION TOAST -->
    <!-- <div class="notification-toast" data-toast>
        <button class="toast-close-btn" data-toast-close>
            <ion-icon name="close-outline"></ion-icon>
        </button>
        <div class="toast-banner">
            <img src="<?php echo SITE_URL; ?>/assets/images/products/jewellery-1.jpg" alt="Rose Gold Earrings"
                width="80" height="70">
        </div>
        <div class="toast-detail">
            <p class="toast-message">Someone just bought</p>
            <p class="toast-title">Rose Gold Earrings</p>
            <p class="toast-meta"><time datetime="PT2M">2 Minutes</time> ago</p>
        </div>
    </div> -->

    <!-- HEADER -->
    <header>
        <div class="header-top">
            <div class="container">
                <ul class="header-social-container">
                    <li><a href="#" class="social-link"><ion-icon name="logo-facebook"></ion-icon></a></li>
                    <li><a href="#" class="social-link"><ion-icon name="logo-twitter"></ion-icon></a></li>
                    <li><a href="#" class="social-link"><ion-icon name="logo-instagram"></ion-icon></a></li>
                    <li><a href="#" class="social-link"><ion-icon name="logo-linkedin"></ion-icon></a></li>
                </ul>
                <div class="header-alert-news">
                    <p><b>Free Shipping</b> This Week Order Over - ₹999</p>
                </div>
                <div class="header-top-actions">
                    <?php if (isLoggedIn()): ?>
                        <span style="color:var(--salmon-pink);font-size:var(--fs-8);">Hi,
                            <?php echo sanitize(explode(' ', $user['name'])[0]); ?>!
                        </span>
                        <a href="<?php echo SITE_URL; ?>/user/logout.php"
                            style="font-size:var(--fs-8);color:var(--sonic-silver);">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/user/login.php"
                            style="font-size:var(--fs-8);color:var(--sonic-silver);">Login</a>
                        <a href="<?php echo SITE_URL; ?>/user/register.php"
                            style="font-size:var(--fs-8);color:var(--sonic-silver);">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="header-main">
            <div class="container">
                <a href="<?php echo SITE_URL; ?>/index.php" class="header-logo">
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo/logo.svg" alt="Anon's logo" width="120"
                        height="36">
                </a>
                <div class="header-search-container">
                    <form action="<?php echo SITE_URL; ?>/products.php" method="GET">
                        <input type="search" name="search" class="search-field" placeholder="Enter your product name..."
                            value="<?php echo isset($_GET['search']) ? sanitize($_GET['search']) : ''; ?>">
                        <button class="search-btn" type="submit">
                            <ion-icon name="search-outline"></ion-icon>
                        </button>
                    </form>
                </div>
                <div class="header-user-actions">
                    <a href="<?php echo isLoggedIn() ? SITE_URL . '/user/profile.php' : SITE_URL . '/user/login.php'; ?>"
                        class="action-btn">
                        <ion-icon name="person-outline"></ion-icon>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/user/wishlist.php" class="action-btn">
                        <ion-icon name="heart-outline"></ion-icon>
                        <span class="count" id="wishlistCount">
                            <?php echo $wishlistCount; ?>
                        </span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/user/cart.php" class="action-btn">
                        <ion-icon name="bag-handle-outline"></ion-icon>
                        <span class="count" id="cartCount">
                            <?php echo $cartCount; ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>

        <nav class="desktop-navigation-menu">
            <div class="container">
                <ul class="desktop-menu-category-list">
                    <li class="menu-category">
                        <a href="<?php echo SITE_URL; ?>/index.php" class="menu-title">Home</a>
                    </li>
                    <li class="menu-category">
                        <a href="<?php echo SITE_URL; ?>/products.php" class="menu-title">Categories</a>
                        <div class="dropdown-panel">
                            <ul class="dropdown-panel-list">
                                <li class="menu-title"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Electronics">Electronics</a>
                                </li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Electronics&sub=Laptop">Laptop</a>
                                </li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Electronics&sub=Camera">Camera</a>
                                </li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Electronics&sub=Headphone">Headphone</a>
                                </li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Electronics"><img
                                            src="<?php echo SITE_URL; ?>/assets/images/electronics-banner-1.jpg"
                                            alt="electronics" width="250" height="119"></a></li>
                            </ul>
                            <ul class="dropdown-panel-list">
                                <li class="menu-title"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Men's">Men's</a></li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Men's&sub=Formal">Formal</a>
                                </li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Men's&sub=Casual">Casual</a>
                                </li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Men's&sub=Sports">Sports</a>
                                </li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Men's"><img
                                            src="<?php echo SITE_URL; ?>/assets/images/mens-banner.jpg" alt="men's"
                                            width="250" height="119"></a></li>
                            </ul>
                            <ul class="dropdown-panel-list">
                                <li class="menu-title"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Women's">Women's</a></li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Women's&sub=Dress">Dress
                                        &amp; Frock</a></li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Women's&sub=Cosmetics">Cosmetics</a>
                                </li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Women's&sub=Bags">Bags</a>
                                </li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Women's"><img
                                            src="<?php echo SITE_URL; ?>/assets/images/womens-banner.jpg" alt="women's"
                                            width="250" height="119"></a></li>
                            </ul>
                            <ul class="dropdown-panel-list">
                                <li class="menu-title"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Jewelry">Jewelry</a></li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Jewelry&sub=Earrings">Earrings</a>
                                </li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Jewelry&sub=Necklace">Necklace</a>
                                </li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Jewelry&sub=Bracelets">Bracelets</a>
                                </li>
                                <li class="panel-list-item"><a
                                        href="<?php echo SITE_URL; ?>/products.php?category=Jewelry"><img
                                            src="<?php echo SITE_URL; ?>/assets/images/electronics-banner-2.jpg"
                                            alt="jewelry" width="250" height="119"></a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="menu-category"><a href="<?php echo SITE_URL; ?>/products.php?category=Men's"
                            class="menu-title">Men's</a></li>
                    <li class="menu-category"><a href="<?php echo SITE_URL; ?>/products.php?category=Women's"
                            class="menu-title">Women's</a></li>
                    <li class="menu-category"><a href="<?php echo SITE_URL; ?>/products.php?category=Jewelry"
                            class="menu-title">Jewelry</a></li>
                    <li class="menu-category"><a href="<?php echo SITE_URL; ?>/products.php?category=Perfume"
                            class="menu-title">Perfume</a></li>
                    <li class="menu-category"><a href="<?php echo SITE_URL; ?>/products.php?sale=1"
                            class="menu-title">Hot Offers</a></li>
                </ul>
            </div>
        </nav>

        <!-- Mobile Bottom Nav -->
        <div class="mobile-bottom-navigation">
            <button class="action-btn" data-mobile-menu-open-btn><ion-icon name="menu-outline"></ion-icon></button>
            <a href="<?php echo SITE_URL; ?>/user/cart.php" class="action-btn">
                <ion-icon name="bag-handle-outline"></ion-icon>
                <span class="count">
                    <?php echo $cartCount; ?>
                </span>
            </a>
            <a href="<?php echo SITE_URL; ?>/index.php" class="action-btn"><ion-icon name="home-outline"></ion-icon></a>
            <a href="<?php echo SITE_URL; ?>/user/wishlist.php" class="action-btn">
                <ion-icon name="heart-outline"></ion-icon>
                <span class="count">
                    <?php echo $wishlistCount; ?>
                </span>
            </a>
            <button class="action-btn" data-mobile-menu-open-btn><ion-icon name="grid-outline"></ion-icon></button>
        </div>

        <!-- Mobile Nav Menu -->
        <nav class="mobile-navigation-menu has-scrollbar" data-mobile-menu>
            <div class="menu-top">
                <h2 class="menu-title">Menu</h2>
                <button class="menu-close-btn" data-mobile-menu-close-btn><ion-icon
                        name="close-outline"></ion-icon></button>
            </div>
            <ul class="mobile-menu-category-list">
                <li class="menu-category"><a href="<?php echo SITE_URL; ?>/index.php" class="menu-title">Home</a></li>
                <li class="menu-category"><a href="<?php echo SITE_URL; ?>/products.php" class="menu-title">All
                        Products</a></li>
                <li class="menu-category"><a href="<?php echo SITE_URL; ?>/products.php?category=Men's"
                        class="menu-title">Men's</a></li>
                <li class="menu-category"><a href="<?php echo SITE_URL; ?>/products.php?category=Women's"
                        class="menu-title">Women's</a></li>
                <li class="menu-category"><a href="<?php echo SITE_URL; ?>/products.php?category=Jewelry"
                        class="menu-title">Jewelry</a></li>
                <li class="menu-category"><a href="<?php echo SITE_URL; ?>/products.php?category=Electronics"
                        class="menu-title">Electronics</a></li>
                <?php if (isLoggedIn()): ?>
                    <li class="menu-category"><a href="<?php echo SITE_URL; ?>/user/profile.php" class="menu-title">My
                            Profile</a></li>
                    <li class="menu-category"><a href="<?php echo SITE_URL; ?>/user/orders.php" class="menu-title">My
                            Orders</a></li>
                    <li class="menu-category"><a href="<?php echo SITE_URL; ?>/user/logout.php"
                            class="menu-title">Logout</a></li>
                <?php else: ?>
                    <li class="menu-category"><a href="<?php echo SITE_URL; ?>/user/login.php" class="menu-title">Login</a>
                    </li>
                    <li class="menu-category"><a href="<?php echo SITE_URL; ?>/user/register.php"
                            class="menu-title">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>