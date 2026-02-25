<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'redirect' => SITE_URL . '/user/login.php']);
    exit;
}

$action = sanitize($_POST['action'] ?? '');
$productId = (int) ($_POST['product_id'] ?? 0);
$db = getDB();
$userId = $_SESSION['user_id'];

if ($action === 'toggle') {
    // Check if in wishlist
    $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if ($existing) {
        $stmt = $db->prepare("DELETE FROM wishlist WHERE id = ?");
        $stmt->bind_param("i", $existing['id']);
        $stmt->execute();
        $inWishlist = false;
    } else {
        $stmt = $db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $inWishlist = true;
    }

    echo json_encode([
        'success' => true,
        'in_wishlist' => $inWishlist,
        'wishlist_count' => getWishlistCount()
    ]);
} elseif ($action === 'remove') {
    $stmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    echo json_encode(['success' => true, 'wishlist_count' => getWishlistCount()]);
} else {
    echo json_encode(['success' => false]);
}
