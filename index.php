<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Home';
$db = getDB();

// New Arrivals
$newArrivals = $db->query("
    SELECT p.*, c.name as category_name
    FROM products p LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1 ORDER BY p.created_at DESC LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

// Trending (most ordered)
$trending = $db->query("
    SELECT p.*, c.name as category_name, COALESCE(SUM(oi.quantity),0) as total_sold
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN order_items oi ON oi.product_id = p.id
    WHERE p.is_active = 1
    GROUP BY p.id ORDER BY total_sold DESC LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

// Top Rated
$topRated = $db->query("
    SELECT p.*, c.name as category_name
    FROM products p LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1 ORDER BY p.price DESC LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

// Best Sellers for sidebar
$bestSellers = $db->query("
    SELECT p.*, COALESCE(SUM(oi.quantity),0) as total_sold
    FROM products p
    LEFT JOIN order_items oi ON oi.product_id = p.id
    WHERE p.is_active = 1
    GROUP BY p.id ORDER BY total_sold DESC LIMIT 4
")->fetch_all(MYSQLI_ASSOC);

// All categories for sidebar
$allCategories = $db->query("
    SELECT c.id, c.name, COUNT(p.id) as cnt
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
    GROUP BY c.id, c.name ORDER BY c.name
")->fetch_all(MYSQLI_ASSOC);

// New products (bottom grid)
$newProducts = $db->query("
    SELECT p.*, c.name as category_name
    FROM products p LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1 ORDER BY p.created_at DESC LIMIT 8
")->fetch_all(MYSQLI_ASSOC);

// Deal of the Day (biggest discount)
$deal = $db->query("
    SELECT p.*, c.name as category_name,
           ROUND(((p.old_price - p.price)/p.old_price)*100) as discount_pct
    FROM products p LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1 AND p.old_price > 0
    ORDER BY discount_pct DESC LIMIT 1
")->fetch_assoc();

include __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/extra.css">

<main>

    <!-- BANNER -->
    <div class="banner">
        <div class="container">
            <div class="slider-container has-scrollbar">
                <div class="slider-item">
                    <img src="<?php echo SITE_URL; ?>/assets/images/banner-2.jpg" alt="Modern sunglasses"
                        class="banner-img">
                    <div class="banner-content">
                        <p class="banner-subtitle">Trending Accessories</p>
                        <h2 class="banner-title">Modern Sunglasses</h2>
                        <p class="banner-text">starting at &#8377; <b>799</b>.00</p>
                        <a href="<?php echo SITE_URL; ?>/products.php?category=Accessories" class="banner-btn">Shop
                            now</a>
                    </div>
                </div>
                <div class="slider-item">
                    <img src="<?php echo SITE_URL; ?>/assets/images/banner-1.jpg" alt="Women's fashion sale"
                        class="banner-img">
                    <div class="banner-content">
                        <p class="banner-subtitle">Trending item</p>
                        <h2 class="banner-title">Women's latest fashion sale</h2>
                        <p class="banner-text">starting at &#8377; <b>999</b>.00</p>
                        <a href="<?php echo SITE_URL; ?>/products.php?category=Women's" class="banner-btn">Shop now</a>
                    </div>
                </div>
                <div class="slider-item">
                    <img src="<?php echo SITE_URL; ?>/assets/images/banner-3.jpg" alt="Summer sale" class="banner-img">
                    <div class="banner-content">
                        <p class="banner-subtitle">Sale Offer</p>
                        <h2 class="banner-title">New fashion summer sale</h2>
                        <p class="banner-text">starting at &#8377; <b>1,499</b>.00</p>
                        <a href="<?php echo SITE_URL; ?>/products.php?sale=1" class="banner-btn">Shop now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CATEGORY STRIP -->
    <div class="category">
        <div class="container">
            <div class="category-item-container has-scrollbar">
                <?php
                $catIcons = [
                    'Men\'s' => 'tee.svg',
                    'Women\'s' => 'dress.svg',
                    'Kids' => 'shorts.svg',
                    'Footwear' => 'shoes.svg',
                    'Accessories' => 'hat.svg',
                    'Electronics' => 'watch.svg',
                    'Cosmetics' => 'cosmetics.svg',
                    'Jewelry' => 'jewelry.svg',
                    'Bags' => 'bag.svg',
                    'Watches' => 'watch.svg',
                    'Sportswear' => 'jacket.svg',
                    'Perfume' => 'perfume.svg',
                ];
                foreach ($allCategories as $cat):
                    $icon = $catIcons[$cat['name']] ?? 'dress.svg';
                    ?>
                        <div class="category-item">
                            <div class="category-img-box">
                                <img src="<?php echo SITE_URL; ?>/assets/images/icons/<?php echo $icon; ?>"
                                    alt="<?php echo sanitize($cat['name']); ?>" width="30">
                            </div>
                            <div class="category-content-box">
                                <div class="category-content-flex">
                                    <h3 class="category-item-title"><?php echo sanitize($cat['name']); ?></h3>
                                    <p class="category-item-amount">(<?php echo (int) $cat['cnt']; ?>)</p>
                                </div>
                                <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo urlencode($cat['name']); ?>"
                                    class="category-btn">Show all</a>
                            </div>
                        </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- MAIN PRODUCT AREA: Sidebar + 3 Column Showcase -->
    <div class="product-container">
        <div class="container">
            <div class="product-box">

                <!-- SIDEBAR -->
                <div class="product-sidebar">

                    <!-- Category List -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-title">Category</h3>
                        <ul class="sidebar-list">
                            <?php foreach ($allCategories as $cat): ?>
                                    <li>
                                        <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo urlencode($cat['name']); ?>"
                                            class="sidebar-link">
                                            <?php echo sanitize($cat['name']); ?>
                                            <span>+</span>
                                        </a>
                                    </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Best Sellers -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-title">Best Sellers</h3>
                        <?php foreach ($bestSellers as $bs):
                            $bImg = getProductImageUrl($bs['image']);
                            ?>
                                <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $bs['id']; ?>"
                                    class="sidebar-product">
                                    <img src="<?php echo $bImg; ?>" alt="<?php echo sanitize($bs['name']); ?>" width="70"
                                        height="70">
                                    <div class="sidebar-product-info">
                                        <p class="sidebar-product-name"><?php echo sanitize($bs['name']); ?></p>
                                        <div class="sidebar-product-rating">
                                            <ion-icon name="star"></ion-icon>
                                            <ion-icon name="star"></ion-icon>
                                            <ion-icon name="star"></ion-icon>
                                            <ion-icon name="star"></ion-icon>
                                            <ion-icon name="star-outline"></ion-icon>
                                        </div>
                                        <p class="sidebar-product-price">
                                            <?php echo formatPrice($bs['price']); ?>
                                            <?php if ($bs['old_price'] > 0): ?>
                                                    <del><?php echo formatPrice($bs['old_price']); ?></del>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </a>
                        <?php endforeach; ?>
                    </div>

                </div>

                <!-- RIGHT SIDE: 3-panel + Deal of the Day -->
                <div class="showcase-right-col">

                <!-- 3-COLUMN SHOWCASE: New Arrivals | Trending | Top Rated -->
                <div class="showcase-three-panel">

                    <!-- New Arrivals -->
                    <div class="showcase-panel">
                        <h2 class="showcase-panel-title">New Arrivals</h2>
                        <div class="showcase-list">
                            <?php foreach ($newArrivals as $prod):
                                $imgSrc = getProductImageUrl($prod['image']);
                                ?>
                                    <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $prod['id']; ?>"
                                        class="showcase">
                                        <div class="showcase-img-box">
                                            <img src="<?php echo $imgSrc; ?>" alt="<?php echo sanitize($prod['name']); ?>"
                                                class="showcase-img" width="75" height="75" loading="lazy">
                                        </div>
                                        <div class="showcase-content">
                                            <p class="showcase-category"><?php echo sanitize($prod['category_name']); ?></p>
                                            <h4 class="showcase-title"><?php echo sanitize(substr($prod['name'], 0, 28)); ?>...
                                            </h4>
                                            <div class="showcase-rating">
                                                <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                                                <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                                                <ion-icon name="star-outline"></ion-icon>
                                            </div>
                                            <div class="price-box">
                                                <p class="price"><?php echo formatPrice($prod['price']); ?></p>
                                                <?php if ($prod['old_price'] > 0): ?>
                                                        <del><?php echo formatPrice($prod['old_price']); ?></del>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Trending -->
                    <div class="showcase-panel">
                        <h2 class="showcase-panel-title">Trending</h2>
                        <div class="showcase-list">
                            <?php foreach ($trending as $prod):
                                $imgSrc = getProductImageUrl($prod['image']);
                                ?>
                                    <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $prod['id']; ?>"
                                        class="showcase">
                                        <div class="showcase-img-box">
                                            <img src="<?php echo $imgSrc; ?>" alt="<?php echo sanitize($prod['name']); ?>"
                                                class="showcase-img" width="75" height="75" loading="lazy">
                                        </div>
                                        <div class="showcase-content">
                                            <p class="showcase-category"><?php echo sanitize($prod['category_name']); ?></p>
                                            <h4 class="showcase-title"><?php echo sanitize(substr($prod['name'], 0, 28)); ?>...
                                            </h4>
                                            <div class="showcase-rating">
                                                <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                                                <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                                                <ion-icon name="star-outline"></ion-icon>
                                            </div>
                                            <div class="price-box">
                                                <p class="price"><?php echo formatPrice($prod['price']); ?></p>
                                                <?php if ($prod['old_price'] > 0): ?>
                                                        <del><?php echo formatPrice($prod['old_price']); ?></del>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Top Rated -->
                    <div class="showcase-panel">
                        <h2 class="showcase-panel-title">Top Rated</h2>
                        <div class="showcase-list">
                            <?php foreach ($topRated as $prod):
                                $imgSrc = getProductImageUrl($prod['image']);
                                ?>
                                    <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $prod['id']; ?>"
                                        class="showcase">
                                        <div class="showcase-img-box">
                                            <img src="<?php echo $imgSrc; ?>" alt="<?php echo sanitize($prod['name']); ?>"
                                                class="showcase-img" width="75" height="75" loading="lazy">
                                        </div>
                                        <div class="showcase-content">
                                            <p class="showcase-category"><?php echo sanitize($prod['category_name']); ?></p>
                                            <h4 class="showcase-title"><?php echo sanitize(substr($prod['name'], 0, 28)); ?>...
                                            </h4>
                                            <div class="showcase-rating">
                                                <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                                                <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                                                <ion-icon name="star-outline"></ion-icon>
                                            </div>
                                            <div class="price-box">
                                                <p class="price"><?php echo formatPrice($prod['price']); ?></p>
                                                <?php if ($prod['old_price'] > 0): ?>
                                                        <del><?php echo formatPrice($prod['old_price']); ?></del>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div><!-- end showcase-three-panel -->

                <!-- DEAL OF THE DAY (inside right col, below 3 panels) -->
                <?php if ($deal): ?>
                <div class="deal-of-day" style="margin-top:20px;">
                    <div class="deal-img">
                        <?php $dImg = getProductImageUrl($deal['image'] ?: 'clothes-2.jpg'); ?>
                        <img src="<?php echo $dImg; ?>" alt="<?php echo sanitize($deal['name']); ?>">
                    </div>
                    <div class="deal-content">
                        <p class="deal-badge"><?php echo $deal['discount_pct']; ?>% Discount — Deal of the Day</p>
                        <div class="deal-rating">
                            <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                            <ion-icon name="star-outline"></ion-icon>
                        </div>
                        <h2 class="deal-title"><?php echo sanitize($deal['name']); ?></h2>
                        <p class="deal-text"><?php echo sanitize(substr($deal['description'] ?? '', 0, 120)); ?>...</p>
                        <div class="deal-price">
                            <span class="deal-new-price"><?php echo formatPrice($deal['price']); ?></span>
                            <del class="deal-old-price"><?php echo formatPrice($deal['old_price']); ?></del>
                        </div>
                        <?php
                        $sold  = max(0, 60 - $deal['stock']);
                        $avail = $deal['stock'];
                        $pct   = $avail > 0 ? min(100, round($sold / ($sold + $avail) * 100)) : 100;
                        ?>
                        <div class="deal-stock-bar">
                            <p class="deal-stock">Already Sold: <b><?php echo $sold; ?></b> &nbsp;|&nbsp; Available: <b><?php echo $avail; ?></b></p>
                            <div class="deal-progress-track">
                                <div class="deal-progress-fill" style="width:<?php echo $pct; ?>%"></div>
                            </div>
                        </div>
                        <p class="deal-countdown-label">Hurry Up! Offer Ends In:</p>
                        <div class="deal-countdown" id="dealCountdown">
                            <div class="countdown-box"><span id="cdDays">00</span><small>Days</small></div>
                            <div class="countdown-box"><span id="cdHours">00</span><small>Hours</small></div>
                            <div class="countdown-box"><span id="cdMins">00</span><small>Min</small></div>
                            <div class="countdown-box"><span id="cdSecs">00</span><small>Sec</small></div>
                        </div>
                        <button class="banner-btn deal-cart-btn" style="margin-top:20px;border:none;cursor:pointer;"
                            onclick="addToCart(<?php echo $deal['id']; ?>, this)">Add to Cart</button>
                    </div>
                </div>
                <?php endif; ?>

                </div><!-- end showcase-right-col -->
            </div><!-- end product-box -->
        </div>
    </div><!-- end product-container -->

    <!-- NEW PRODUCTS GRID -->
    <div class="container" style="margin-bottom:50px;">
        <h2 class="title" style="margin-bottom:24px;">New Products</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:24px;">
            <?php foreach ($newProducts as $prod):
                $imgSrc = getProductImageUrl($prod['image']);
                $discount = $prod['old_price'] > 0
                    ? round((($prod['old_price'] - $prod['price']) / $prod['old_price']) * 100) : 0;
                ?>
                    <div class="product-card">
                        <?php if ($discount > 0): ?>
                                <span class="badge-sale">-<?php echo $discount; ?>%</span>
                        <?php endif; ?>
                        <div class="product-card-img">
                            <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $prod['id']; ?>">
                                <img src="<?php echo $imgSrc; ?>" alt="<?php echo sanitize($prod['name']); ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="product-card-body">
                            <p class="product-card-category"><?php echo sanitize($prod['category_name']); ?></p>
                            <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $prod['id']; ?>">
                                <h3 class="product-card-title"><?php echo sanitize($prod['name']); ?></h3>
                            </a>
                            <div class="product-card-price">
                                <span class="price"><?php echo formatPrice($prod['price']); ?></span>
                                <?php if ($prod['old_price'] > 0): ?><del><?php echo formatPrice($prod['old_price']); ?></del><?php endif; ?>
                            </div>
                            <div class="showcase-rating" style="margin-bottom:10px;">
                                <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                                <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                                <ion-icon name="star-outline"></ion-icon>
                            </div>
                            <div class="product-card-actions">
                                <button class="btn-add-cart" onclick="addToCart(<?php echo $prod['id']; ?>, this)">Add to Cart</button>
                                <button class="btn-wishlist" onclick="toggleWishlist(<?php echo $prod['id']; ?>, this)" title="Wishlist">
                                    <ion-icon name="heart-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:32px;">
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn-primary"
                style="display:inline-block;width:auto;padding:13px 40px;">View All Products</a>
        </div>
    </div>

    <!-- CTA + OUR SERVICES (side by side) -->
    <div class="container" style="margin-bottom:50px;">
        <div class="cta-services-row">

            <!-- CTA Banner -->
            <div class="cta-banner-box" style="position:relative;border-radius:12px;overflow:hidden;">
                <img src="<?php echo SITE_URL; ?>/assets/images/cta-banner.jpg" alt="Summer Collection"
                    style="width:100%;height:100%;object-fit:cover;min-height:260px;">
                <div style="position:absolute;top:50%;left:32px;transform:translateY(-50%);background:hsla(0,0%,100%,0.88);padding:20px 24px;border-radius:10px;">
                    <p style="color:var(--salmon-pink);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin-bottom:6px;">25% Discount</p>
                    <h2 style="font-size:var(--fs-2);font-weight:800;line-height:1.2;margin-bottom:6px;">Summer Collection</h2>
                    <p style="font-size:var(--fs-8);color:var(--sonic-silver);margin-bottom:14px;">Starting @ &#8377;10</p>
                    <a href="<?php echo SITE_URL; ?>/products.php?sale=1" class="banner-btn" style="font-size:var(--fs-8);padding:9px 22px;">Shop Now</a>
                </div>
            </div>

            <!-- Our Services -->
            <div class="our-services-box">
                <h3 class="services-title">Our Services</h3>
                <ul class="services-list">
                    <li class="service-item">
                        <div class="service-icon" style="background:hsl(353,100%,95%);">
                            <ion-icon name="globe-outline" style="color:hsl(353,100%,58%);"></ion-icon>
                        </div>
                        <div><p class="service-name">Worldwide Delivery</p><p class="service-sub">For Order Over &#8377;100</p></div>
                    </li>
                    <li class="service-item">
                        <div class="service-icon" style="background:hsl(152,60%,93%);">
                            <ion-icon name="rocket-outline" style="color:hsl(152,51%,40%);"></ion-icon>
                        </div>
                        <div><p class="service-name">Next Day Delivery</p><p class="service-sub">UK Orders Only</p></div>
                    </li>
                    <li class="service-item">
                        <div class="service-icon" style="background:hsl(210,100%,94%);">
                            <ion-icon name="headset-outline" style="color:hsl(210,80%,50%);"></ion-icon>
                        </div>
                        <div><p class="service-name">Best Online Support</p><p class="service-sub">Hours: 8AM - 11PM</p></div>
                    </li>
                    <li class="service-item">
                        <div class="service-icon" style="background:hsl(40,100%,93%);">
                            <ion-icon name="refresh-outline" style="color:hsl(40,85%,45%);"></ion-icon>
                        </div>
                        <div><p class="service-name">Return Policy</p><p class="service-sub">Easy &amp; Free Return</p></div>
                    </li>
                    <li class="service-item">
                        <div class="service-icon" style="background:hsl(270,80%,95%);">
                            <ion-icon name="shield-checkmark-outline" style="color:hsl(270,60%,50%);"></ion-icon>
                        </div>
                        <div><p class="service-name">30% Money Back</p><p class="service-sub">For Order Over &#8377;100</p></div>
                    </li>
                </ul>
            </div>

        </div>
    </div>

    <!-- BLOG SECTION -->
    <div class="container" style="margin-bottom:60px;">
        <h2 class="title" style="margin-bottom:24px;">From The Blog</h2>
        <div class="blog-grid">
            <article class="blog-card">
                <a href="#" class="blog-img-box">
                    <img src="<?php echo SITE_URL; ?>/assets/images/blog-1.jpg" alt="Fashion Trends" loading="lazy">
                </a>
                <div class="blog-body">
                    <p class="blog-category">Fashion</p>
                    <h3 class="blog-title"><a href="#">Top Fashion Trends: How to Win the Style Battle</a></h3>
                    <p class="blog-meta">By <span>Mr Pawar</span> / Mar 15, <?php echo date('Y'); ?></p>
                </div>
            </article>
            <article class="blog-card">
                <a href="#" class="blog-img-box">
                    <img src="<?php echo SITE_URL; ?>/assets/images/blog-2.jpg" alt="EBT vendors" loading="lazy">
                </a>
                <div class="blog-body">
                    <p class="blog-category">Shopping</p>
                    <h3 class="blog-title"><a href="#">EBT vendors: Claim your Share of SNAP Online Revenue</a></h3>
                    <p class="blog-meta">By <span>Mr Salsa</span> / Feb 10, <?php echo date('Y'); ?></p>
                </div>
            </article>
            <article class="blog-card">
                <a href="#" class="blog-img-box">
                    <img src="<?php echo SITE_URL; ?>/assets/images/blog-3.jpg" alt="Curbside fashion" loading="lazy">
                </a>
                <div class="blog-body">
                    <p class="blog-category">Lifestyle</p>
                    <h3 class="blog-title"><a href="#">Curbside Fashion Trends: How to Win the Pickup Battle</a></h3>
                    <p class="blog-meta">By <span>Mr Pawar</span> / Mar 15, <?php echo date('Y'); ?></p>
                </div>
            </article>
        </div>
    </div>

</main>

<?php
$extraJs = '
<script>
// ── Add to Cart ───────────────────────────────────────────────────────────────
function addToCart(productId, btn) {
    const orig = btn.textContent;
    btn.textContent = "Adding..."; btn.disabled = true;
    fetch("' . SITE_URL . '/includes/cart_action.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "action=add&product_id=" + productId + "&quantity=1&csrf_token=' . ($csrf ?? generateCsrfToken()) . '"
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.textContent = "Added ✓";
            document.querySelectorAll("#cartCount").forEach(el => el.textContent = data.cart_count);
            setTimeout(() => { btn.textContent = orig; btn.disabled = false; }, 1500);
        } else {
            btn.textContent = data.message || "Login Required";
            setTimeout(() => { btn.textContent = orig; btn.disabled = false; }, 2000);
            if (data.redirect) window.location.href = data.redirect;
        }
    });
}

// ── Wishlist toggle ────────────────────────────────────────────────────────────
function toggleWishlist(productId, btn) {
    fetch("' . SITE_URL . '/includes/wishlist_action.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "action=toggle&product_id=" + productId + "&csrf_token=' . ($csrf ?? generateCsrfToken()) . '"
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = data.in_wishlist ? "<ion-icon name=\"heart\"></ion-icon>" : "<ion-icon name=\"heart-outline\"></ion-icon>";
            btn.classList.toggle("active", data.in_wishlist);
            document.querySelectorAll("#wishlistCount").forEach(el => el.textContent = data.wishlist_count);
        } else if (data.redirect) { window.location.href = data.redirect; }
    });
}

// ── Deal Countdown ────────────────────────────────────────────────────────────
(function() {
    const endTime = new Date();
    endTime.setHours(23, 59, 59, 0); // ends at midnight today

    function pad(n) { return String(n).padStart(2, "0"); }

    function tick() {
        const now  = new Date();
        let diff   = Math.max(0, Math.floor((endTime - now) / 1000));
        const days = Math.floor(diff / 86400); diff %= 86400;
        const hrs  = Math.floor(diff / 3600);  diff %= 3600;
        const mins = Math.floor(diff / 60);
        const secs = diff % 60;
        const d = document.getElementById("cdDays");
        const h = document.getElementById("cdHours");
        const m = document.getElementById("cdMins");
        const s = document.getElementById("cdSecs");
        if (d) d.textContent = pad(days);
        if (h) h.textContent = pad(hrs);
        if (m) m.textContent = pad(mins);
        if (s) s.textContent = pad(secs);
    }
    tick();
    setInterval(tick, 1000);
})();
</script>';

include __DIR__ . '/includes/footer.php';
?>