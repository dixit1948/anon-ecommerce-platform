<?php
/**
 * Razorpay – Verify Payment & Place Order (Developer / Test Mode)
 * Called via AJAX after the Razorpay popup succeeds.
 *
 * Expects JSON body:
 * {
 *   "razorpay_payment_id" : "pay_XXXX",
 *   "razorpay_order_id"   : "order_XXXX",
 *   "razorpay_signature"  : "...",
 *   "formData"            : { name, email, phone, address, city, state, pincode, country }
 * }
 *
 * Returns JSON: { "success": true, "redirect": "/user/order_success.php" }
 *            or { "error": "..." }
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$razorpayPaymentId = $input['razorpay_payment_id'] ?? '';
$razorpayOrderId = $input['razorpay_order_id'] ?? '';
$razorpaySignature = $input['razorpay_signature'] ?? '';
$formData = $input['formData'] ?? [];

// ── 1. Verify the signature ──────────────────────────────────────────────────
// Razorpay signs:  SHA256_HMAC( razorpay_order_id + "|" + razorpay_payment_id )
$expectedSignature = hash_hmac(
    'sha256',
    $razorpayOrderId . '|' . $razorpayPaymentId,
    RAZORPAY_KEY_SECRET
);

if (!hash_equals($expectedSignature, $razorpaySignature)) {
    echo json_encode(['error' => 'Payment verification failed. Invalid signature.']);
    exit;
}

// ── 2. Prepare cart & totals ─────────────────────────────────────────────────
$db = getDB();
$userId = (int) $_SESSION['user_id'];

$cartItems = $db->query("
    SELECT c.id as cart_id, c.quantity, p.id as product_id,
           p.name, p.price, p.image, p.stock
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.user_id = $userId
")->fetch_all(MYSQLI_ASSOC);

if (empty($cartItems)) {
    echo json_encode(['error' => 'Cart is empty.']);
    exit;
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $subtotal >= 999 ? 0 : 49;
$discount = 0;
$couponId = null;

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

// ── 3. Sanitise shipping address ─────────────────────────────────────────────
$name = sanitize($formData['name'] ?? '');
$address = sanitize($formData['address'] ?? '');
$city = sanitize($formData['city'] ?? '');
$state = sanitize($formData['state'] ?? '');
$pincode = sanitize($formData['pincode'] ?? '');
$country = sanitize($formData['country'] ?? 'India');

if (empty($name) || empty($address) || empty($city) || empty($pincode)) {
    echo json_encode(['error' => 'Missing required address fields.']);
    exit;
}

$shippingAddr = "$address, $city, $state $pincode, $country";
$paymentMethod = 'razorpay';

// ── 4. Insert Order ───────────────────────────────────────────────────────────
$orderNum = generateOrderNumber();
$stmt = $db->prepare("INSERT INTO orders (user_id, order_number, total_amount, shipping_amount, discount_amount, coupon_id, shipping_address, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'processing', NOW())");
$stmt->bind_param("isddddss", $userId, $orderNum, $total, $shipping, $discount, $couponId, $shippingAddr, $paymentMethod);
$stmt->execute();
$orderId = $db->insert_id;

// ── 5. Insert Order Items & reduce stock ─────────────────────────────────────
foreach ($cartItems as $item) {
    $lineTotal = $item['price'] * $item['quantity'];
    $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiddd", $orderId, $item['product_id'], $item['quantity'], $item['price'], $lineTotal);
    $stmt->execute();
    $db->query("UPDATE products SET stock = stock - {$item['quantity']} WHERE id = {$item['product_id']}");
}

// ── 6. Clear cart ─────────────────────────────────────────────────────────────
$db->query("DELETE FROM cart WHERE user_id = $userId");

// ── 7. Update coupon usage ────────────────────────────────────────────────────
if ($couponId) {
    $db->query("UPDATE coupons SET used_count = used_count + 1 WHERE id = $couponId");
    unset($_SESSION['coupon_code']);
}

// ── 8. Record payment ─────────────────────────────────────────────────────────
$stmt = $db->prepare("INSERT INTO payments (order_id, user_id, payment_method, amount, transaction_id, status, created_at) VALUES (?, ?, ?, ?, ?, 'completed', NOW())");
$stmt->bind_param("iisds", $orderId, $userId, $paymentMethod, $total, $razorpayPaymentId);
$stmt->execute();

// ── 9. Store session data for order_success page ──────────────────────────────
$_SESSION['order_success'] = [
    'order_id' => $orderId,
    'order_number' => $orderNum,
    'total' => $total,
    'payment_method' => 'razorpay',
    'transaction_id' => $razorpayPaymentId,
];

echo json_encode([
    'success' => true,
    'redirect' => SITE_URL . '/user/order_success.php',
]);
