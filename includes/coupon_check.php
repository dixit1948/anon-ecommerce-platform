<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

$code = sanitize($_POST['code'] ?? '');
if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Enter a coupon code.']);
    exit;
}

$db = getDB();
$stmt = $db->prepare("
    SELECT * FROM coupons
    WHERE code = ?
      AND is_active = 1
      AND expiry_date >= CURDATE()
      AND used_count < max_uses
");
$stmt->bind_param("s", $code);
$stmt->execute();
$coupon = $stmt->get_result()->fetch_assoc();

if (!$coupon) {
    echo json_encode(['success' => false, 'message' => 'Invalid, expired, or used-up coupon.']);
    exit;
}

// Compute display discount based on a dummy subtotal
// The actual discount is computed per-order in checkout.php
$display = $coupon['discount_type'] === 'percent'
    ? $coupon['discount_value'] . '%'
    : '$' . number_format($coupon['discount_value'], 2);

$_SESSION['coupon_code'] = $code;

echo json_encode([
    'success' => true,
    'discount' => $coupon['discount_value'],
    'discount_type' => $coupon['discount_type'],
    'discount_display' => $display,
    'message' => 'Coupon applied! ' . $display . ' off',
]);
