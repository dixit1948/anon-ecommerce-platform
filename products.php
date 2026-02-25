<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Products';
$db = getDB();

$search = sanitize($_GET['search'] ?? '');
$category = sanitize($_GET['category'] ?? '');
$minPrice = (float) ($_GET['min_price'] ?? 0);
$maxPrice = (float) ($_GET['max_price'] ?? 99999);
$sort = sanitize($_GET['sort'] ?? 'newest');
$onSale = isset($_GET['sale']) && $_GET['sale'] == 1;

$where = ['p.is_active = 1'];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $s = "%$search%";
    $params[] = $s;
    $params[] = $s;
    $types .= 'ss';
}
if ($category !== '') {
    $where[] = 'c.name = ?';
    $params[] = $category;
    $types .= 's';
}
if ($minPrice > 0) {
    $where[] = 'p.price >= ?';
    $params[] = $minPrice;
    $types .= 'd';
}
if ($maxPrice < 99999) {
    $where[] = 'p.price <= ?';
    $params[] = $maxPrice;
    $types .= 'd';
}
if ($onSale) {
    $where[] = 'p.old_price > p.price';
}

$orderBy = match ($sort) {
    'price_asc' => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'name' => 'p.name ASC',
    default => 'p.created_at DESC',
};

$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE " . implode(' AND ', $where) . " ORDER BY $orderBy";
$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$categories = $db->query("SELECT DISTINCT name FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/extra.css">

<main>
    <!-- Page Hero -->
    <div class="page-hero">
        <div class="container">
            <h1>
                <?php echo $search ? 'Search: ' . htmlspecialchars($search) : ($category ?: 'All Products'); ?>
            </h1>
            <p>
                <?php echo count($products); ?> product
                <?php echo count($products) != 1 ? 's' : ''; ?> found
            </p>
        </div>
    </div>

    <div class="container">
        <!-- Filter Bar -->
        <form method="GET" action=""
            style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:28px;align-items:flex-end;background:var(--white);border:1px solid var(--cultured);border-radius:var(--border-radius-md);padding:16px 20px;">

            <div class="form-group" style="flex:1;min-width:170px;margin:0;">
                <label style="font-size:var(--fs-9);font-weight:600;margin-bottom:4px;">Category</label>
                <select name="category"
                    style="padding:8px 12px;border:1.5px solid var(--cultured);border-radius:var(--border-radius-sm);font-size:var(--fs-8);width:100%;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo sanitize($cat['name']); ?>" <?php echo $category === $cat['name'] ? 'selected' : ''; ?>>
                            <?php echo sanitize($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="flex:1;min-width:140px;margin:0;">
                <label style="font-size:var(--fs-9);font-weight:600;margin-bottom:4px;">Min Price</label>
                <input type="number" name="min_price" min="0" placeholder="0" value="<?php echo $minPrice ?: ''; ?>"
                    style="padding:8px 12px;border:1.5px solid var(--cultured);border-radius:var(--border-radius-sm);font-size:var(--fs-8);width:100%;">
            </div>

            <div class="form-group" style="flex:1;min-width:140px;margin:0;">
                <label style="font-size:var(--fs-9);font-weight:600;margin-bottom:4px;">Max Price</label>
                <input type="number" name="max_price" min="0" placeholder="Any"
                    value="<?php echo $maxPrice < 99999 ? $maxPrice : ''; ?>"
                    style="padding:8px 12px;border:1.5px solid var(--cultured);border-radius:var(--border-radius-sm);font-size:var(--fs-8);width:100%;">
            </div>

            <div class="form-group" style="flex:1;min-width:140px;margin:0;">
                <label style="font-size:var(--fs-9);font-weight:600;margin-bottom:4px;">Sort By</label>
                <select name="sort"
                    style="padding:8px 12px;border:1.5px solid var(--cultured);border-radius:var(--border-radius-sm);font-size:var(--fs-8);width:100%;">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                    <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High
                    </option>
                    <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low
                    </option>
                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                </select>
            </div>

            <div style="display:flex;gap:8px;align-items:flex-end;">
                <button type="submit" class="btn-primary" style="width:auto;padding:9px 22px;">Filter</button>
                <a href="<?php echo SITE_URL; ?>/products.php" class="btn-secondary">Clear</a>
            </div>
        </form>

        <!-- Product Grid -->
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <ion-icon name="search-outline"></ion-icon>
                <h3>No products found</h3>
                <p>Try adjusting your filters or search term.</p>
                <a href="<?php echo SITE_URL; ?>/products.php" class="btn-primary"
                    style="display:inline-block;width:auto;padding:12px 36px;">View All Products</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $prod):
                    $imgSrc = getProductImageUrl($prod['image']);
                    $discount = $prod['old_price'] > 0 ? round((($prod['old_price'] - $prod['price']) / $prod['old_price']) * 100) : 0;
                    ?>
                    <div class="product-card">
                        <?php if ($discount > 0): ?><span class="badge-sale">-
                                <?php echo $discount; ?>%
                            </span>
                        <?php endif; ?>
                        <div class="product-card-img">
                            <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $prod['id']; ?>">
                                <img src="<?php echo $imgSrc; ?>" alt="<?php echo sanitize($prod['name']); ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="product-card-body">
                            <p class="product-card-category">
                                <?php echo sanitize($prod['category_name']); ?>
                            </p>
                            <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $prod['id']; ?>">
                                <h3 class="product-card-title">
                                    <?php echo sanitize($prod['name']); ?>
                                </h3>
                            </a>
                            <div class="product-card-price">
                                <span class="price">
                                    <?php echo formatPrice($prod['price']); ?>
                                </span>
                                <?php if ($prod['old_price'] > 0): ?><del>
                                        <?php echo formatPrice($prod['old_price']); ?>
                                    </del>
                                <?php endif; ?>
                            </div>
                            <?php if ($prod['stock'] > 0): ?>
                                <div class="product-card-actions">
                                    <button class="btn-add-cart" onclick="addToCart(<?php echo $prod['id']; ?>, this)">Add to
                                        Cart</button>
                                    <button class="btn-wishlist" onclick="toggleWishlist(<?php echo $prod['id']; ?>, this)">
                                        <ion-icon name="heart-outline"></ion-icon>
                                    </button>
                                </div>
                            <?php else: ?>
                                <p style="color:var(--bittersweet);font-size:var(--fs-9);font-weight:600;margin-top:8px;">Out of
                                    Stock</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php
$extraJs = '
<script>
function addToCart(productId, btn) {
    btn.textContent = "Adding..."; btn.disabled = true;
    fetch("' . SITE_URL . '/includes/cart_action.php", {
        method:"POST", headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:"action=add&product_id="+productId+"&quantity=1&csrf_token=' . generateCsrfToken() . '"
    }).then(r=>r.json()).then(data=>{
        if(data.success){
            btn.textContent="Added!";
            document.querySelectorAll("#cartCount").forEach(el=>el.textContent=data.cart_count);
            setTimeout(()=>{btn.textContent="Add to Cart";btn.disabled=false;},1500);
        } else {
            btn.textContent="Login Required"; setTimeout(()=>{btn.textContent="Add to Cart";btn.disabled=false;},2000);
            if(data.redirect) window.location.href=data.redirect;
        }
    });
}
function toggleWishlist(id, btn) {
    fetch("' . SITE_URL . '/includes/wishlist_action.php", {
        method:"POST", headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:"action=toggle&product_id="+id+"&csrf_token=' . generateCsrfToken() . '"
    }).then(r=>r.json()).then(data=>{
        if(data.success){
            btn.innerHTML=data.in_wishlist?"<ion-icon name=\"heart\"></ion-icon>":"<ion-icon name=\"heart-outline\"></ion-icon>";
            btn.classList.toggle("active",data.in_wishlist);
            document.querySelectorAll("#wishlistCount").forEach(el=>el.textContent=data.wishlist_count);
        } else if(data.redirect) window.location.href=data.redirect;
    });
}
</script>';
include __DIR__ . '/includes/footer.php';
?>