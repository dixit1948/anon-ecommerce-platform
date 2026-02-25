<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$productId = (int) ($_GET['id'] ?? 0);
$db = getDB();

$product = $db->query("
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.id = $productId AND p.is_active = 1
")->fetch_assoc();

if (!$product) {
    header('Location: ' . SITE_URL . '/products.php');
    exit;
}

$pageTitle = $product['name'];

// Related products
$catId = (int) $product['category_id'];
$related = $db->query("
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.category_id = $catId AND p.id != $productId AND p.is_active = 1
    LIMIT 4
")->fetch_all(MYSQLI_ASSOC);

// Check if in wishlist
$inWishlist = false;
if (isLoggedIn()) {
    $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $productId);
    $stmt->execute();
    $inWishlist = $stmt->get_result()->num_rows > 0;
}

$imgSrc = getProductImageUrl($product['image']);
$discount = $product['old_price'] > 0 ? round((($product['old_price'] - $product['price']) / $product['old_price']) * 100) : 0;

include __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/extra.css">

<main>
    <section class="product-detail-section">
        <div class="container">

            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>/index.php">Home</a><span>/</span>
                <a
                    href="<?php echo SITE_URL; ?>/products.php?category=<?php echo urlencode($product['category_name']); ?>">
                    <?php echo sanitize($product['category_name']); ?>
                </a><span>/</span>
                <span class="current">
                    <?php echo sanitize($product['name']); ?>
                </span>
            </div>

            <div class="product-detail-grid">

                <!-- Image -->
                <div class="product-detail-img">
                    <?php if ($discount > 0): ?>
                        <div style="position:absolute;top:14px;left:14px;z-index:1;" class="badge-sale">-
                            <?php echo $discount; ?>% OFF
                        </div>
                    <?php endif; ?>
                    <img src="<?php echo $imgSrc; ?>" alt="<?php echo sanitize($product['name']); ?>"
                        style="width:100%;height:100%;object-fit:cover;">
                </div>

                <!-- Info -->
                <div class="product-detail-info">
                    <p class="product-card-category" style="margin-bottom:8px;">
                        <?php echo sanitize($product['category_name']); ?>
                    </p>
                    <h1>
                        <?php echo sanitize($product['name']); ?>
                    </h1>

                    <div class="product-stars">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <ion-icon name="star"></ion-icon>
                        <?php endfor; ?>
                        <span style="font-size:var(--fs-8);color:var(--sonic-silver);margin-left:6px;">(4.5 / 5)</span>
                    </div>

                    <div class="product-detail-price">
                        <span class="current-price">
                            <?php echo formatPrice($product['price']); ?>
                        </span>
                        <?php if ($product['old_price'] > 0): ?>
                            <span class="old-price">
                                <?php echo formatPrice($product['old_price']); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($product['description']): ?>
                        <p class="product-detail-desc">
                            <?php echo nl2br(sanitize($product['description'])); ?>
                        </p>
                    <?php endif; ?>

                    <!-- Stock -->
                    <div style="margin-bottom:16px;">
                        <?php if ($product['stock'] > 0): ?>
                            <span style="color:hsl(152,51%,40%);font-size:var(--fs-8);font-weight:600;">● In Stock (
                                <?php echo $product['stock']; ?> available)
                            </span>
                        <?php else: ?>
                            <span style="color:var(--bittersweet);font-size:var(--fs-8);font-weight:600;">● Out of
                                Stock</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($product['stock'] > 0): ?>
                        <!-- Quantity -->
                        <div class="qty-control">
                            <label>Quantity:</label>
                            <div class="qty-input-group">
                                <button type="button" onclick="changeQty(-1)">−</button>
                                <input type="number" id="productQty" value="1" min="1"
                                    max="<?php echo $product['stock']; ?>">
                                <button type="button" onclick="changeQty(1)">+</button>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="detail-actions">
                            <button class="btn-primary" id="addCartBtn"
                                onclick="addToCartDetail(<?php echo $productId; ?>)">
                                Add to Cart
                            </button>
                            <button class="btn-wishlist <?php echo $inWishlist ? 'active' : ''; ?>" id="wishBtn"
                                onclick="toggleWishlistDetail(<?php echo $productId; ?>)"
                                title="<?php echo $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
                                <ion-icon name="<?php echo $inWishlist ? 'heart' : 'heart-outline'; ?>"></ion-icon>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Features -->
                    <div style="border-top:1px solid var(--cultured);padding-top:20px;margin-top:10px;">
                        <div style="display:flex;gap:20px;flex-wrap:wrap;">
                            <div
                                style="display:flex;align-items:center;gap:8px;font-size:var(--fs-8);color:var(--sonic-silver);">
                                <ion-icon name="car-outline"
                                    style="font-size:20px;color:var(--salmon-pink);"></ion-icon>
                                Free shipping over ₹999
                            </div>
                            <div
                                style="display:flex;align-items:center;gap:8px;font-size:var(--fs-8);color:var(--sonic-silver);">
                                <ion-icon name="shield-checkmark-outline"
                                    style="font-size:20px;color:var(--salmon-pink);"></ion-icon>
                                Secure payment
                            </div>
                            <div
                                style="display:flex;align-items:center;gap:8px;font-size:var(--fs-8);color:var(--sonic-silver);">
                                <ion-icon name="refresh-outline"
                                    style="font-size:20px;color:var(--salmon-pink);"></ion-icon>
                                30-day returns
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Related Products -->
            <?php if (!empty($related)): ?>
                <div style="margin-top:60px;">
                    <h2 class="title">Related Products</h2>
                    <div class="products-grid">
                        <?php foreach ($related as $rel):
                            $relImg = getProductImageUrl($rel['image']);
                            ?>
                            <div class="product-card">
                                <div class="product-card-img">
                                    <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $rel['id']; ?>">
                                        <img src="<?php echo $relImg; ?>" alt="<?php echo sanitize($rel['name']); ?>"
                                            loading="lazy">
                                    </a>
                                </div>
                                <div class="product-card-body">
                                    <p class="product-card-category">
                                        <?php echo sanitize($rel['category_name']); ?>
                                    </p>
                                    <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $rel['id']; ?>">
                                        <h3 class="product-card-title">
                                            <?php echo sanitize($rel['name']); ?>
                                        </h3>
                                    </a>
                                    <div class="product-card-price">
                                        <span class="price">
                                            <?php echo formatPrice($rel['price']); ?>
                                        </span>
                                        <?php if ($rel['old_price'] > 0): ?><del>
                                                <?php echo formatPrice($rel['old_price']); ?>
                                            </del>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-card-actions">
                                        <button class="btn-add-cart" onclick="addToCart(<?php echo $rel['id']; ?>, this)">Add to
                                            Cart</button>
                                        <button class="btn-wishlist" onclick="toggleWishlist(<?php echo $rel['id']; ?>, this)">
                                            <ion-icon name="heart-outline"></ion-icon>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </section>
</main>

<?php
$csrf = generateCsrfToken();
$extraJs = '
<script>
const CSRF = "' . $csrf . '";
const SITE_URL = "' . SITE_URL . '";

function changeQty(delta) {
    const inp = document.getElementById("productQty");
    const max = parseInt(inp.max);
    let v = parseInt(inp.value) + delta;
    if (v < 1) v = 1;
    if (v > max) v = max;
    inp.value = v;
}

function addToCartDetail(productId) {
    const qty = document.getElementById("productQty").value;
    const btn = document.getElementById("addCartBtn");
    btn.textContent = "Adding..."; btn.disabled = true;
    fetch(SITE_URL + "/includes/cart_action.php", {
        method:"POST", headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:"action=add&product_id="+productId+"&quantity="+qty+"&csrf_token="+CSRF
    }).then(r=>r.json()).then(data=>{
        if(data.success){
            btn.textContent="Added to Cart! ✓";
            document.querySelectorAll("#cartCount").forEach(el=>el.textContent=data.cart_count);
            setTimeout(()=>{btn.textContent="Add to Cart";btn.disabled=false;},2000);
        } else {
            alert(data.message || "Something went wrong");
            btn.textContent="Add to Cart"; btn.disabled=false;
            if(data.redirect) window.location.href=data.redirect;
        }
    });
}

function toggleWishlistDetail(productId) {
    fetch(SITE_URL + "/includes/wishlist_action.php", {
        method:"POST", headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:"action=toggle&product_id="+productId+"&csrf_token="+CSRF
    }).then(r=>r.json()).then(data=>{
        if(data.success){
            const btn = document.getElementById("wishBtn");
            btn.innerHTML = data.in_wishlist ? "<ion-icon name=\"heart\"></ion-icon>" : "<ion-icon name=\"heart-outline\"></ion-icon>";
            btn.classList.toggle("active", data.in_wishlist);
            document.querySelectorAll("#wishlistCount").forEach(el=>el.textContent=data.wishlist_count);
        } else if(data.redirect) window.location.href=data.redirect;
    });
}

function addToCart(productId, btn) {
    btn.textContent="Adding..."; btn.disabled=true;
    fetch(SITE_URL+"/includes/cart_action.php",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:"action=add&product_id="+productId+"&quantity=1&csrf_token="+CSRF}).then(r=>r.json()).then(data=>{
        if(data.success){btn.textContent="Added!";document.querySelectorAll("#cartCount").forEach(el=>el.textContent=data.cart_count);setTimeout(()=>{btn.textContent="Add to Cart";btn.disabled=false;},1500);}
        else{if(data.redirect)window.location.href=data.redirect;}
    });
}
function toggleWishlist(id, btn){
    fetch(SITE_URL+"/includes/wishlist_action.php",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:"action=toggle&product_id="+id+"&csrf_token="+CSRF}).then(r=>r.json()).then(data=>{
        if(data.success){btn.innerHTML=data.in_wishlist?"<ion-icon name=\"heart\"></ion-icon>":"<ion-icon name=\"heart-outline\"></ion-icon>";btn.classList.toggle("active",data.in_wishlist);}
        else if(data.redirect)window.location.href=data.redirect;
    });
}
</script>';
include __DIR__ . '/includes/footer.php';
?>