<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireUserLogin();

$pageTitle = 'My Cart';
$db = getDB();
$userId = $_SESSION['user_id'];

$cartItems = $db->query("
    SELECT c.id as cart_id, c.quantity, p.id as product_id,
           p.name, p.price, p.old_price, p.image, p.stock,
           cat.name as category_name
    FROM cart c
    JOIN products p ON p.id = c.product_id
    LEFT JOIN categories cat ON cat.id = p.category_id
    WHERE c.user_id = $userId
    ORDER BY c.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $subtotal >= 999 ? 0 : 49;
$total = $subtotal + $shipping;

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/extra.css">

<main>
    <section class="cart-section">
        <div class="container">

            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
                <span>/</span>
                <span class="current">Shopping Cart</span>
            </div>

            <h1 style="font-size:var(--fs-2);font-weight:700;color:var(--eerie-black);margin-bottom:30px;">
                Shopping Cart <span style="color:var(--sonic-silver);font-size:var(--fs-6);">(
                    <?php echo count($cartItems); ?> items)
                </span>
            </h1>

            <?php if (empty($cartItems)): ?>
                <div class="empty-state">
                    <ion-icon name="cart-outline"></ion-icon>
                    <h3>Your cart is empty</h3>
                    <p>Looks like you haven't added anything to your cart yet.</p>
                    <a href="<?php echo SITE_URL; ?>/products.php" class="btn-primary"
                        style="display:inline-block;width:auto;padding:12px 36px;">Start Shopping</a>
                </div>
            <?php else: ?>

                <div class="cart-grid">
                    <!-- Cart Items -->
                    <div>
                        <div style="overflow-x:auto;">
                            <table class="cart-table" id="cartTable">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item):
                                        $imgSrc = getProductImageUrl($item['image']);
                                        $lineTotal = $item['price'] * $item['quantity'];
                                        ?>
                                        <tr id="cart-row-<?php echo $item['cart_id']; ?>">
                                            <td data-label="Product">
                                                <div class="cart-product-flex">
                                                    <div class="cart-product-img">
                                                        <img src="<?php echo $imgSrc; ?>"
                                                            alt="<?php echo sanitize($item['name']); ?>">
                                                    </div>
                                                    <div>
                                                        <p class="cart-product-name">
                                                            <?php echo sanitize($item['name']); ?>
                                                        </p>
                                                        <p class="cart-product-category">
                                                            <?php echo sanitize($item['category_name']); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="Price">
                                                <?php echo formatPrice($item['price']); ?>
                                            </td>
                                            <td data-label="Quantity">
                                                <div class="cart-qty-group">
                                                    <button onclick="updateQty(<?php echo $item['cart_id']; ?>, -1)">−</button>
                                                    <input type="number" value="<?php echo $item['quantity']; ?>" min="1"
                                                        max="<?php echo $item['stock']; ?>"
                                                        id="qty-<?php echo $item['cart_id']; ?>"
                                                        onchange="setQty(<?php echo $item['cart_id']; ?>, this.value)">
                                                    <button onclick="updateQty(<?php echo $item['cart_id']; ?>, 1)">+</button>
                                                </div>
                                            </td>
                                            <td data-label="Total" id="total-<?php echo $item['cart_id']; ?>">
                                                <?php echo formatPrice($lineTotal); ?>
                                            </td>
                                            <td>
                                                <button class="btn-remove" onclick="removeItem(<?php echo $item['cart_id']; ?>)"
                                                    title="Remove">
                                                    <ion-icon name="trash-outline"></ion-icon>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div>
                        <div class="order-summary-card">
                            <h3>Order Summary</h3>

                            <!-- Coupon -->
                            <div class="coupon-form">
                                <input type="text" id="couponCode" placeholder="Coupon code">
                                <button onclick="applyCoupon()">Apply</button>
                            </div>
                            <div id="couponMsg" style="font-size:var(--fs-9);margin-bottom:10px;"></div>

                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span id="subtotal">
                                    <?php echo formatPrice($subtotal); ?>
                                </span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span>
                                    <?php echo $shipping == 0 ? '<span style="color:hsl(152,51%,52%)">FREE</span>' : formatPrice($shipping); ?>
                                </span>
                            </div>
                            <?php if ($shipping > 0): ?>
                                <p style="font-size:var(--fs-9);color:var(--sonic-silver);margin-bottom:10px;">
                                    Add
                                    <?php echo formatPrice(999 - $subtotal); ?> more for free shipping!
                                </p>
                            <?php endif; ?>
                            <div class="summary-row">
                                <span>Discount</span>
                                <span id="discountRow" style="color:hsl(152,51%,52%);">₹0.00</span>
                            </div>
                            <div class="summary-row total">
                                <span>Total</span>
                                <span id="grandTotal">
                                    <?php echo formatPrice($total); ?>
                                </span>
                            </div>
                            <a href="<?php echo SITE_URL; ?>/user/checkout.php" class="btn-primary"
                                style="margin-top:20px;display:block;text-align:center;">
                                Proceed to Checkout
                            </a>
                            <a href="<?php echo SITE_URL; ?>/products.php" class="btn-secondary"
                                style="display:block;text-align:center;margin-top:12px;">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
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

function apiCart(data) {
    return fetch(SITE_URL + "/includes/cart_action.php", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: new URLSearchParams({...data, csrf_token: CSRF})
    }).then(r => r.json());
}

function removeItem(cartId) {
    if (!confirm("Remove this item?")) return;
    apiCart({action:"remove", cart_id: cartId}).then(data => {
        if (data.success) {
            document.getElementById("cart-row-" + cartId).remove();
            document.querySelectorAll("#cartCount").forEach(el => el.textContent = data.cart_count);
            location.reload();
        }
    });
}

function updateQty(cartId, delta) {
    const inp = document.getElementById("qty-" + cartId);
    let qty = parseInt(inp.value) + delta;
    if (qty < 1) { removeItem(cartId); return; }
    inp.value = qty;
    setQty(cartId, qty);
}

function setQty(cartId, qty) {
    apiCart({action:"update", cart_id: cartId, quantity: qty}).then(data => {
        if (data.success) {
            document.getElementById("total-" + cartId).textContent = data.line_total;
            document.querySelectorAll("#cartCount").forEach(el => el.textContent = data.cart_count);
            location.reload();
        }
    });
}

function applyCoupon() {
    const code = document.getElementById("couponCode").value.trim();
    if (!code) return;
    fetch(SITE_URL + "/includes/coupon_check.php", {
        method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body: "code=" + encodeURIComponent(code) + "&csrf_token=" + CSRF
    }).then(r=>r.json()).then(data => {
        const msg = document.getElementById("couponMsg");
        if (data.success) {
            msg.style.color = "hsl(152,51%,40%)";
            msg.textContent = "Coupon applied! -" + data.discount_display;
            document.getElementById("discountRow").textContent = "-" + data.discount_display;
            sessionStorage.setItem("coupon_code", code);
            sessionStorage.setItem("coupon_discount", data.discount);
        } else {
            msg.style.color = "hsl(0,70%,50%)";
            msg.textContent = data.message || "Invalid coupon";
        }
    });
}
</script>';
include __DIR__ . '/../includes/footer.php';
?>