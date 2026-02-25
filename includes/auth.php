<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Check if user is logged in, redirect if not
 */
function requireUserLogin()
{
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/user/login.php');
        exit;
    }
}

/**
 * Check if admin is logged in, redirect if not
 */
function requireAdminLogin()
{
    if (!isset($_SESSION['admin_id'])) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Check if user is logged in (non-blocking)
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Check if admin is logged in (non-blocking)
 */
function isAdminLoggedIn()
{
    return isset($_SESSION['admin_id']);
}

/**
 * Get current user data
 */
function getCurrentUser()
{
    if (!isLoggedIn())
        return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, phone, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Sanitize input
 */
function sanitize($input)
{
    return htmlspecialchars(strip_tags(trim($input)));
}

/**
 * Generate CSRF token
 */
function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCsrf($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get cart count for logged-in user
 */
function getCartCount()
{
    if (!isLoggedIn())
        return 0;
    $db = getDB();
    $stmt = $db->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return (int) ($result['total'] ?? 0);
}

/**
 * Get wishlist count for logged-in user
 */
function getWishlistCount()
{
    if (!isLoggedIn())
        return 0;
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return (int) ($result['total'] ?? 0);
}

/**
 * Flash message helpers
 */
function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Format price
 */
function formatPrice($price)
{
    return '₹' . number_format((float) $price, 2);
}

/**
 * Resolve product image URL.
 *
 * Priority:
 *   1. If image starts with 'prod_' it was uploaded via admin → use /uploads/products/
 *   2. Otherwise treat it as a demo/static asset → use /assets/images/products/
 *   3. If image is empty/null, fall back to /assets/images/products/1.jpg
 */
function getProductImageUrl($image)
{
    if (empty($image)) {
        return SITE_URL . '/assets/images/products/1.jpg';
    }
    // Admin-uploaded files are always named prod_XXXXX.*
    if (strpos($image, 'prod_') === 0) {
        return SITE_URL . '/uploads/products/' . $image;
    }
    // Static/demo images in assets
    return SITE_URL . '/assets/images/products/' . $image;
}

/**
 * Generate unique transaction ID
 */
function generateTransactionId()
{
    return 'TXN' . strtoupper(bin2hex(random_bytes(8))) . time();
}

/**
 * Generate order number
 */
function generateOrderNumber()
{
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}
