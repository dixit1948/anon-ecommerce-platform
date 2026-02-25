<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Login required', 'redirect' => SITE_URL . '/user/login.php']);
    exit;
}

$action = sanitize($_POST['action'] ?? '');
$productId = (int) ($_POST['product_id'] ?? 0);
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));
$db = getDB();
$userId = $_SESSION['user_id'];

switch ($action) {
    case 'add':
        // Check product exists
        $stmt = $db->prepare("SELECT id, stock FROM products WHERE id = ? AND is_active = 1");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        if ($product['stock'] < 1) {
            echo json_encode(['success' => false, 'message' => 'Out of stock']);
            exit;
        }
        // Check if already in cart
        $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->bind_param("ii", $newQty, $existing['id']);
        } else {
            $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $userId, $productId, $quantity);
        }
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Added to cart', 'cart_count' => getCartCount()]);
        break;

    case 'remove':
        $cartId = (int) ($_POST['cart_id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cartId, $userId);
        $stmt->execute();
        echo json_encode(['success' => true, 'cart_count' => getCartCount()]);
        break;

    case 'update':
        $cartId = (int) ($_POST['cart_id'] ?? 0);
        if ($quantity < 1) {
            $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $cartId, $userId);
        } else {
            $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("iii", $quantity, $cartId, $userId);
        }
        $stmt->execute();
        // Calculate new line total
        $stmt = $db->prepare("SELECT c.quantity, p.price FROM cart c JOIN products p ON p.id = c.product_id WHERE c.id = ? AND c.user_id = ?");
        $stmt->bind_param("ii", $cartId, $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $lineTotal = $row ? number_format($row['quantity'] * $row['price'], 2) : '0.00';
        echo json_encode(['success' => true, 'line_total' => '$' . $lineTotal, 'cart_count' => getCartCount()]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
