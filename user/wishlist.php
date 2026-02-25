<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireUserLogin();

$pageTitle = 'My Wishlist';
$db = getDB();
$userId = $_SESSION['user_id'];

$wishlistItems = $db->query("
    SELECT w.id as wish_id, w.product_id, p.name, p.price, p.old_price, p.image, p.stock,
           c.name as category_name
    FROM wishlist w
    JOIN products p ON p.id = w.product_id
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE w.user_id = $userId
    ORDER BY w.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/extra.css">

<main>
    <section style="padding:40px 0;">
        <div class="container">

            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
                <span>/</span>
                <span class="current">My Wishlist</span>
            </div>

            <h1 style="font-size:var(--fs-2);font-weight:700;color:var(--eerie-black);margin-bottom:30px;">
                My Wishlist <span style="color:var(--sonic-silver);font-size:var(--fs-6);">(
                    <?php echo count($wishlistItems); ?> items)
                </span>
            </h1>

            <?php if (empty($wishlistItems)): ?>
                <div class="empty-state">
                    <ion-icon name="heart-outline"></ion-icon>
                    <h3>Your wishlist is empty</h3>
                    <p>Save items you love and add them to cart when you're ready.</p>
                    <a href="<?php echo SITE_URL; ?>/products.php" class="btn-primary"
                        style="display:inline-block;width:auto;padding:12px 36px;">Browse Products</a>
                </div>
            <?php else: ?>
                <div class="wishlist-grid" id="wishlistGrid">
                    <?php foreach ($wishlistItems as $item):
                        $imgSrc = getProductImageUrl($item['image']);
                        ?>
                        <div class="wishlist-card" id="wish-<?php echo $item['wish_id']; ?>">
                            <div class="wishlist-card-img">
                                <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $item['product_id']; ?>">
                                    <img src="<?php echo $imgSrc; ?>" alt="<?php echo sanitize($item['name']); ?>">
                                </a>
                            </div>
                            <div class="wishlist-card-body">
                                <p class="product-card-category">
                                    <?php echo sanitize($item['category_name']); ?>
                                </p>
                                <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $item['product_id']; ?>">
                                    <h3 class="product-card-title">
                                        <?php echo sanitize($item['name']); ?>
                                    </h3>
                                </a>
                                <div class="product-card-price">
                                    <span class="price">
                                        <?php echo formatPrice($item['price']); ?>
                                    </span>
                                    <?php if ($item['old_price'] > 0): ?><del>
                                            <?php echo formatPrice($item['old_price']); ?>
                                        </del>
                                    <?php endif; ?>
                                </div>
                                <div class="wishlist-card-actions">
                                    <?php if ($item['stock'] > 0): ?>
                                        <button class="btn-add-cart"
                                            onclick="moveToCart(<?php echo $item['product_id']; ?>, <?php echo $item['wish_id']; ?>, this)">
                                            Move to Cart
                                        </button>
                                    <?php else: ?>
                                        <span style="font-size:var(--fs-9);color:var(--bittersweet);font-weight:600;">Out of
                                            Stock</span>
                                    <?php endif; ?>
                                    <button class="btn-remove"
                                        onclick="removeWish(<?php echo $item['wish_id']; ?>, <?php echo $item['product_id']; ?>)"
                                        title="Remove">
                                        <ion-icon name="trash-outline"></ion-icon>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
$extraJs = '
<script>
const CSRF = "' . generateCsrfToken() . '";
const SITE_URL = "' . SITE_URL . '";

function moveToCart(productId, wishId, btn) {
    btn.textContent = "Adding...";
    btn.disabled = true;
    fetch(SITE_URL + "/includes/cart_action.php", {
        method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:"action=add&product_id="+productId+"&quantity=1&csrf_token="+CSRF
    }).then(r=>r.json()).then(data=>{
        if(data.success) {
            // Remove from wishlist
            removeWish(wishId, productId);
            document.querySelectorAll("#cartCount").forEach(el=>el.textContent=data.cart_count);
        } else {
            btn.textContent = "Move to Cart";
            btn.disabled = false;
            alert(data.message || "Error");
        }
    });
}

function removeWish(wishId, productId) {
    fetch(SITE_URL + "/includes/wishlist_action.php", {
        method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:"action=remove&product_id="+productId+"&csrf_token="+CSRF
    }).then(r=>r.json()).then(data=>{
        if(data.success) {
            const card = document.getElementById("wish-"+wishId);
            card.style.opacity = "0";
            card.style.transform = "scale(0.9)";
            card.style.transition = "all 0.3s ease";
            setTimeout(()=>card.remove(), 300);
            document.querySelectorAll("#wishlistCount").forEach(el=>el.textContent=data.wishlist_count);
        }
    });
}
</script>';
include __DIR__ . '/../includes/footer.php';
?>