<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireUserLogin();

$pageTitle = 'Checkout';
$db = getDB();
$userId = $_SESSION['user_id'];
$user = getCurrentUser();

// Get cart items
$cartItems = $db->query("
    SELECT c.id as cart_id, c.quantity, p.id as product_id,
           p.name, p.price, p.image, p.stock
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.user_id = $userId
")->fetch_all(MYSQLI_ASSOC);

if (empty($cartItems)) {
    setFlash('error', 'Your cart is empty.');
    header('Location: ' . SITE_URL . '/user/cart.php');
    exit;
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $subtotal >= 999 ? 0 : 49;
$discount = 0;
$couponId = null;

// Handle coupon from session
if (isset($_SESSION['coupon_code'])) {
    $code = $_SESSION['coupon_code'];
    $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND expiry_date >= CURDATE() AND used_count < max_uses");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $coupon = $stmt->get_result()->fetch_assoc();
    if ($coupon) {
        $couponId = $coupon['id'];
        if ($coupon['discount_type'] === 'percent') {
            $discount = $subtotal * ($coupon['discount_value'] / 100);
        } else {
            $discount = min($coupon['discount_value'], $subtotal);
        }
    }
}

$total = max(0, $subtotal + $shipping - $discount);

// Handle form submission (COD / UPI / Card – non-Razorpay methods)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $pincode = sanitize($_POST['pincode'] ?? '');
    $country = sanitize($_POST['country'] ?? 'India');
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'cod');

    if (empty($name) || empty($address) || empty($city) || empty($pincode)) {
        setFlash('error', 'Please fill all required address fields.');
    } else {
        $orderNum = generateOrderNumber();
        $shippingAddr = "$address, $city, $state $pincode, $country";

        // Create order
        $stmt = $db->prepare("INSERT INTO orders (user_id, order_number, total_amount, shipping_amount, discount_amount, coupon_id, shipping_address, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("isddddss", $userId, $orderNum, $total, $shipping, $discount, $couponId, $shippingAddr, $paymentMethod);
        $stmt->execute();
        $orderId = $db->insert_id;

        // Insert order items
        foreach ($cartItems as $item) {
            $lineTotal = $item['price'] * $item['quantity'];
            $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiddd", $orderId, $item['product_id'], $item['quantity'], $item['price'], $lineTotal);
            $stmt->execute();
            $db->query("UPDATE products SET stock = stock - {$item['quantity']} WHERE id = {$item['product_id']}");
        }

        // Clear cart
        $db->query("DELETE FROM cart WHERE user_id = $userId");

        // Update coupon usage
        if ($couponId) {
            $db->query("UPDATE coupons SET used_count = used_count + 1 WHERE id = $couponId");
            unset($_SESSION['coupon_code']);
        }

        // Payment handling
        $transactionId = generateTransactionId();
        $paymentStatus = 'pending';

        if ($paymentMethod === 'cod') {
            $paymentStatus = 'pending';
        } else {
            // Simulate success for UPI / Card (sandbox)
            $paymentStatus = 'completed';
            $db->query("UPDATE orders SET status = 'processing' WHERE id = $orderId");
        }

        $stmt = $db->prepare("INSERT INTO payments (order_id, user_id, payment_method, amount, transaction_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iisdss", $orderId, $userId, $paymentMethod, $total, $transactionId, $paymentStatus);
        $stmt->execute();

        $_SESSION['order_success'] = [
            'order_id' => $orderId,
            'order_number' => $orderNum,
            'total' => $total,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
        ];

        header('Location: ' . SITE_URL . '/user/order_success.php');
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/extra.css">

<main>
    <section class="checkout-section">
        <div class="container">

            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>/index.php">Home</a><span>/</span>
                <a href="<?php echo SITE_URL; ?>/user/cart.php">Cart</a><span>/</span>
                <span class="current">Checkout</span>
            </div>

            <h1 style="font-size:var(--fs-2);font-weight:700;color:var(--eerie-black);margin-bottom:30px;">Secure
                Checkout</h1>

            <form method="POST" action="" id="checkoutForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

                <div class="checkout-grid">

                    <!-- LEFT: Address + Payment -->
                    <div>

                        <!-- Shipping Address -->
                        <div class="checkout-card">
                            <h3><span>1</span> Shipping Address</h3>
                            <div class="checkout-form-grid">
                                <div class="form-group">
                                    <label>Full Name *</label>
                                    <input type="text" name="name" id="fname" required
                                        value="<?php echo sanitize($user['name']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" name="email" id="femail" required
                                        value="<?php echo sanitize($user['email']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Phone *</label>
                                    <input type="tel" name="phone" id="fphone" required
                                        value="<?php echo sanitize($user['phone'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Pincode *</label>
                                    <input type="text" name="pincode" id="fpincode" required placeholder="110001">
                                </div>
                                <div class="form-group full">
                                    <label>Street Address *</label>
                                    <input type="text" name="address" id="faddress" required
                                        placeholder="House no., Street, Area">
                                </div>
                                <div class="form-group">
                                    <label>City *</label>
                                    <input type="text" name="city" id="fcity" required placeholder="New Delhi">
                                </div>
                                <div class="form-group">
                                    <label>State</label>
                                    <input type="text" name="state" id="fstate" placeholder="Delhi">
                                </div>
                                <div class="form-group">
                                    <label>Country</label>
                                    <select name="country" id="fcountry">
                                        <option value="India">India</option>
                                        <option value="USA">United States</option>
                                        <option value="UK">United Kingdom</option>
                                        <option value="Canada">Canada</option>
                                        <option value="Australia">Australia</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="checkout-card">
                            <h3><span>2</span> Payment Method</h3>

                            <div class="payment-method-list">

                                <!-- COD -->
                                <div class="payment-method-item selected" id="pm-cod" onclick="selectPayment('cod')">
                                    <label class="payment-method-label">
                                        <input type="radio" name="payment_method" value="cod" checked>
                                        <ion-icon name="cash-outline" class="payment-icon"></ion-icon>
                                        <div>
                                            <strong>Cash on Delivery</strong>
                                            <p style="font-size:var(--fs-9);color:var(--sonic-silver);margin-top:2px;">
                                                Pay when your order arrives</p>
                                        </div>
                                    </label>
                                </div>

                                <!-- ✅ RAZORPAY Online Payment -->
                                <div class="payment-method-item" id="pm-razorpay" onclick="selectPayment('razorpay')">
                                    <label class="payment-method-label">
                                        <input type="radio" name="payment_method" value="razorpay">
                                        <ion-icon name="card-outline" class="payment-icon"
                                            style="color:#2563eb;"></ion-icon>
                                        <div>
                                            <strong style="color:#1d4ed8;">
                                                💳 Razorpay – Pay Online
                                            </strong>
                                            <p style="font-size:var(--fs-9);color:var(--sonic-silver);margin-top:2px;">
                                                UPI · Cards · Net Banking · Wallets
                                            </p>
                                        </div>
                                        <!-- Razorpay badge -->
                                        <span
                                            style="margin-left:auto;background:hsl(220,100%,97%);border:1px solid hsl(220,90%,83%);color:hsl(220,80%,45%);font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;white-space:nowrap;">
                                            TEST MODE
                                        </span>
                                    </label>
                                    <div class="payment-details" id="razorpay-details" style="display:none;">
                                        <div
                                            style="background:hsl(220,100%,97%);border:1px solid hsl(220,80%,83%);border-radius:var(--border-radius-sm);padding:14px 16px;font-size:var(--fs-8);color:hsl(220,60%,40%);display:flex;align-items:flex-start;gap:10px;">
                                            <span style="font-size:20px;flex-shrink:0;">🔵</span>
                                            <div>
                                                <strong>Razorpay Test / Developer Mode</strong><br>
                                                No real money is charged. Click <em>"Pay Now"</em> and the Razorpay
                                                popup will open. Use any test card or UPI to complete a simulated
                                                payment.
                                                <br><br>
                                                <strong>Test Card:</strong> 4111 1111 1111 1111 | CVV: any 3 digits |
                                                Expiry: any future date
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- UPI (simulated sandbox) -->
                                <div class="payment-method-item" id="pm-upi" onclick="selectPayment('upi')">
                                    <label class="payment-method-label">
                                        <input type="radio" name="payment_method" value="upi">
                                        <ion-icon name="phone-portrait-outline" class="payment-icon"></ion-icon>
                                        <div>
                                            <strong>UPI Payment (Simulated)</strong>
                                            <p style="font-size:var(--fs-9);color:var(--sonic-silver);margin-top:2px;">
                                                Google Pay, PhonePe, Paytm, etc.</p>
                                        </div>
                                    </label>
                                    <div class="payment-details" id="upi-details" style="display:none;">
                                        <div class="form-group">
                                            <label>UPI ID</label>
                                            <input type="text" name="upi_id" placeholder="yourname@upi" id="upiInput">
                                        </div>
                                        <div
                                            style="background:hsl(40,100%,97%);border:1px solid hsl(40,90%,75%);border-radius:var(--border-radius-sm);padding:12px 14px;font-size:var(--fs-8);color:hsl(40,60%,40%);">
                                            <strong>🟡 Sandbox Mode:</strong> Any UPI ID will simulate a successful
                                            payment.
                                        </div>
                                    </div>
                                </div>

                                <!-- Card (simulated sandbox) -->
                                <div class="payment-method-item" id="pm-card" onclick="selectPayment('card')">
                                    <label class="payment-method-label">
                                        <input type="radio" name="payment_method" value="card">
                                        <ion-icon name="card-outline" class="payment-icon"></ion-icon>
                                        <div>
                                            <strong>Credit / Debit Card (Simulated)</strong>
                                            <p style="font-size:var(--fs-9);color:var(--sonic-silver);margin-top:2px;">
                                                Visa, Mastercard, RuPay</p>
                                        </div>
                                    </label>
                                    <div class="payment-details" id="card-details" style="display:none;">
                                        <div class="checkout-form-grid">
                                            <div class="form-group full">
                                                <label>Card Number</label>
                                                <input type="text" name="card_number" placeholder="1234 5678 9012 3456"
                                                    maxlength="19" onkeyup="formatCard(this)" id="cardNum">
                                            </div>
                                            <div class="form-group">
                                                <label>Cardholder Name</label>
                                                <input type="text" name="card_name" placeholder="John Doe">
                                            </div>
                                            <div class="form-group">
                                                <label>Expiry Date</label>
                                                <input type="text" name="card_expiry" placeholder="MM/YY" maxlength="5">
                                            </div>
                                            <div class="form-group">
                                                <label>CVV</label>
                                                <input type="password" name="card_cvv" placeholder="•••" maxlength="4">
                                            </div>
                                        </div>
                                        <div
                                            style="background:hsl(40,100%,97%);border:1px solid hsl(40,90%,75%);border-radius:var(--border-radius-sm);padding:12px 14px;font-size:var(--fs-8);color:hsl(40,60%,40%);">
                                            <strong>🟡 Sandbox Mode:</strong> Use any card details. No real payment
                                            processed.
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <!-- RIGHT: Order Summary -->
                    <div>
                        <div class="order-summary-card" style="position:sticky;top:20px;">
                            <h3>Order Summary</h3>

                            <?php foreach ($cartItems as $item):
                                $imgSrc = getProductImageUrl($item['image']);
                                ?>
                                <div
                                    style="display:flex;gap:12px;padding:10px 0;border-bottom:1px solid var(--cultured);align-items:center;">
                                    <div
                                        style="width:50px;height:50px;border-radius:var(--border-radius-sm);overflow:hidden;flex-shrink:0;border:1px solid var(--cultured);">
                                        <img src="<?php echo $imgSrc; ?>" alt="<?php echo sanitize($item['name']); ?>"
                                            style="width:100%;height:100%;object-fit:cover;">
                                    </div>
                                    <div style="flex:1;">
                                        <p style="font-size:var(--fs-8);font-weight:600;color:var(--eerie-black);">
                                            <?php echo sanitize($item['name']); ?>
                                        </p>
                                        <p style="font-size:var(--fs-9);color:var(--sonic-silver);">Qty:
                                            <?php echo $item['quantity']; ?>
                                        </p>
                                    </div>
                                    <span style="font-size:var(--fs-8);font-weight:600;">
                                        <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>

                            <div style="margin-top:14px;">
                                <div class="summary-row"><span>Subtotal</span><span>
                                        <?php echo formatPrice($subtotal); ?>
                                    </span></div>
                                <div class="summary-row"><span>Shipping</span><span>
                                        <?php echo $shipping == 0 ? '<span style="color:hsl(152,51%,52%)">FREE</span>' : formatPrice($shipping); ?>
                                    </span></div>
                                <?php if ($discount > 0): ?>
                                    <div class="summary-row"><span>Discount</span><span style="color:hsl(152,51%,42%);">-
                                            <?php echo formatPrice($discount); ?>
                                        </span></div>
                                <?php endif; ?>
                                <div class="summary-row total"><span>Total</span><span>
                                        <?php echo formatPrice($total); ?>
                                    </span></div>
                            </div>

                            <button type="submit" class="btn-primary" style="margin-top:20px;" id="placeOrderBtn">
                                Place Order —
                                <?php echo formatPrice($total); ?>
                            </button>
                            <p
                                style="font-size:var(--fs-9);color:var(--sonic-silver);text-align:center;margin-top:12px;">
                                🔒 Secured with 256-bit SSL encryption
                            </p>
                        </div>
                    </div>

                </div>
            </form>

        </div>
    </section>
</main>

<?php
$extraJs = '
<!-- Razorpay Checkout SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
// ── Config passed from PHP ────────────────────────────────────────────────────
const RAZORPAY_KEY   = "' . RAZORPAY_KEY_ID . '";
const ORDER_TOTAL    = ' . $total . ';   // in INR
const SITE_URL       = "' . SITE_URL . '";
const USER_NAME      = "' . addslashes(sanitize($user['name'])) . '";
const USER_EMAIL     = "' . addslashes(sanitize($user['email'])) . '";
const USER_PHONE     = "' . addslashes(sanitize($user['phone'] ?? '')) . '";

// ── Payment Method Selection ──────────────────────────────────────────────────
function selectPayment(method) {
    ["cod","razorpay","upi","card"].forEach(m => {
        document.getElementById("pm-" + m).classList.remove("selected");
        const det = document.getElementById(m + "-details");
        if (det) det.style.display = "none";
    });
    document.getElementById("pm-" + method).classList.add("selected");
    document.getElementById("pm-" + method).querySelector("input").checked = true;
    const details = document.getElementById(method + "-details");
    if (details) details.style.display = "block";
}

function formatCard(inp) {
    let v = inp.value.replace(/\D/g, "").substring(0, 16);
    inp.value = v.replace(/(.{4})/g, "$1 ").trim();
}

// ── Form submit handler ───────────────────────────────────────────────────────
document.getElementById("checkoutForm").addEventListener("submit", function(e) {
    const method = document.querySelector("input[name=payment_method]:checked").value;

    if (method === "razorpay") {
        e.preventDefault();            // Stop normal form submission
        startRazorpayPayment();
        return;
    }

    // For COD / UPI / Card: normal form submit with loading indicator
    const btn = document.getElementById("placeOrderBtn");
    btn.textContent = "Processing...";
    btn.disabled = true;
});

// ── Collect form data helper ──────────────────────────────────────────────────
function getFormData() {
    return {
        name   : document.getElementById("fname").value,
        email  : document.getElementById("femail").value,
        phone  : document.getElementById("fphone").value,
        address: document.getElementById("faddress").value,
        city   : document.getElementById("fcity").value,
        state  : document.getElementById("fstate").value,
        pincode: document.getElementById("fpincode").value,
        country: document.getElementById("fcountry").value,
    };
}

// ── Razorpay flow ─────────────────────────────────────────────────────────────
async function startRazorpayPayment() {
    const btn = document.getElementById("placeOrderBtn");

    // Basic address validation before opening Razorpay popup
    const fd = getFormData();
    if (!fd.name || !fd.address || !fd.city || !fd.pincode) {
        alert("Please fill all required shipping address fields first.");
        return;
    }

    btn.textContent = "Opening Razorpay...";
    btn.disabled = true;

    try {
        // Step 1 – Ask our server to create a Razorpay order
        const createRes = await fetch(SITE_URL + "/user/razorpay_create_order.php", {
            method : "POST",
            headers: { "Content-Type": "application/json" },
            body   : JSON.stringify({ amount: ORDER_TOTAL }),
        });
        const createData = await createRes.json();

        if (createData.error) {
            alert("Error: " + createData.error);
            btn.textContent = "Place Order — ₹" + ORDER_TOTAL.toFixed(2);
            btn.disabled = false;
            return;
        }

        // Step 2 – Open Razorpay checkout popup
        const options = {
            key            : RAZORPAY_KEY,
            amount         : Math.round(ORDER_TOTAL * 100),   // paise
            currency       : "INR",
            name           : "Anon eCommerce",
            description    : "Order Payment (Test Mode)",
            order_id       : createData.razorpay_order_id,
            prefill: {
                name   : USER_NAME,
                email  : USER_EMAIL,
                contact: USER_PHONE,
            },
            theme : { color: "#2563eb" },

            // ── Success handler ───────────────────────────────────────────────
            handler: async function (response) {
                btn.textContent = "Verifying Payment...";
                btn.disabled = true;

                // Step 3 – Verify signature on server & place order in DB
                const verifyRes = await fetch(SITE_URL + "/user/razorpay_verify.php", {
                    method : "POST",
                    headers: { "Content-Type": "application/json" },
                    body   : JSON.stringify({
                        razorpay_payment_id : response.razorpay_payment_id,
                        razorpay_order_id   : response.razorpay_order_id,
                        razorpay_signature  : response.razorpay_signature,
                        formData            : fd,
                    }),
                });
                const verifyData = await verifyRes.json();

                if (verifyData.success) {
                    window.location.href = verifyData.redirect;
                } else {
                    alert("Payment verification failed: " + (verifyData.error || "Unknown error"));
                    btn.textContent = "Place Order — ₹" + ORDER_TOTAL.toFixed(2);
                    btn.disabled = false;
                }
            },

            // ── Dismissed / cancelled ─────────────────────────────────────────
            modal: {
                ondismiss: function () {
                    btn.textContent = "Place Order — ₹" + ORDER_TOTAL.toFixed(2);
                    btn.disabled = false;
                }
            }
        };

        const rzp = new Razorpay(options);

        rzp.on("payment.failed", function (response) {
            alert("Payment failed: " + response.error.description);
            btn.textContent = "Place Order — ₹" + ORDER_TOTAL.toFixed(2);
            btn.disabled = false;
        });

        rzp.open();

    } catch (err) {
        console.error(err);
        alert("Something went wrong. Please try again.");
        btn.textContent = "Place Order — ₹" + ORDER_TOTAL.toFixed(2);
        btn.disabled = false;
    }
}
</script>';
include __DIR__ . '/../includes/footer.php';
?>