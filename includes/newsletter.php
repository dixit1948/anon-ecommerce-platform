<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$db = getDB();
$stmt = $db->prepare("INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)");
$stmt->bind_param("s", $email);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Thank you for subscribing! 🎉']);
} else {
    echo json_encode(['success' => true, 'message' => 'You are already subscribed!']);
}
