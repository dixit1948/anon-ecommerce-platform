<?php
/**
 * Razorpay – Create Order (Developer / Test Mode)
 * Called via AJAX from the checkout page.
 * Returns JSON: { "razorpay_order_id": "order_XXXXX" } or { "error": "..." }
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Must be a POST request and user must be logged-in
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$amount = isset($input['amount']) ? (float) $input['amount'] : 0;

if ($amount <= 0) {
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

// Razorpay amounts are in paise (1 INR = 100 paise)
$amountInPaise = (int) round($amount * 100);

// Build the request payload
$receiptId = 'rcpt_' . uniqid();
$payload = json_encode([
    'amount' => $amountInPaise,
    'currency' => 'INR',
    'receipt' => $receiptId,
    'notes' => ['source' => 'Anon eCommerce Checkout'],
]);

// Hit the Razorpay Orders API using cURL
$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_USERPWD => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload),
    ],
    // --- Local XAMPP fix: disable SSL verification for dev ---
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    // --------------------------------------------------------
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_DNS_USE_GLOBAL_CACHE => false,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['error' => 'cURL error: ' . $curlError]);
    exit;
}

$data = json_decode($response, true);

if ($httpCode !== 200 || empty($data['id'])) {
    $errDesc = $data['error']['description'] ?? 'Razorpay API returned an error.';
    echo json_encode(['error' => $errDesc]);
    exit;
}

// Return the Razorpay order ID to the frontend
echo json_encode(['razorpay_order_id' => $data['id']]);
