<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireUserLogin();

$pageTitle = 'My Profile';
$db = getDB();
$userId = $_SESSION['user_id'];
$user = getCurrentUser();

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'update_profile') {
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        if (empty($name)) {
            $error = 'Name cannot be empty.';
        } else {
            $stmt = $db->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $phone, $userId);
            $stmt->execute();
            $_SESSION['user_name'] = $name;
            setFlash('success', 'Profile updated successfully!');
            header('Location: ' . SITE_URL . '/user/profile.php');
            exit;
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $newPwd = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!password_verify($current, $row['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPwd) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($newPwd !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $hashed = password_hash($newPwd, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $userId);
            $stmt->execute();
            setFlash('success', 'Password changed successfully!');
            header('Location: ' . SITE_URL . '/user/profile.php');
            exit;
        }
    }
}

// Stats
$totalOrders = $db->query("SELECT COUNT(*) as c FROM orders WHERE user_id = $userId")->fetch_assoc()['c'];
$totalSpent = $db->query("SELECT COALESCE(SUM(total_amount),0) as s FROM orders WHERE user_id = $userId")->fetch_assoc()['s'];
$wishCount = getWishlistCount();

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/extra.css">

<main>
    <section class="profile-section">
        <div class="container">

            <?php if ($error): ?>
                <div class="flash-message flash-error"
                    style="position:relative;top:auto;right:auto;margin-bottom:20px;animation:none;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="profile-grid">

                <!-- Sidebar -->
                <div class="profile-sidebar">
                    <div class="profile-avatar">
                        <?php echo strtoupper($user['name'][0]); ?>
                    </div>
                    <p class="profile-name">
                        <?php echo sanitize($user['name']); ?>
                    </p>
                    <p class="profile-email">
                        <?php echo sanitize($user['email']); ?>
                    </p>

                    <!-- Stats -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:16px 0;text-align:center;">
                        <div style="background:hsl(353,100%,97%);border-radius:var(--border-radius-sm);padding:12px;">
                            <p style="font-size:var(--fs-2);font-weight:800;color:var(--salmon-pink);">
                                <?php echo $totalOrders; ?>
                            </p>
                            <p style="font-size:var(--fs-10);color:var(--sonic-silver);">Orders</p>
                        </div>
                        <div style="background:hsl(152,51%,95%);border-radius:var(--border-radius-sm);padding:12px;">
                            <p style="font-size:var(--fs-3);font-weight:800;color:hsl(152,51%,40%);">
                                <?php echo formatPrice($totalSpent); ?>
                            </p>
                            <p style="font-size:var(--fs-10);color:var(--sonic-silver);">Spent</p>
                        </div>
                    </div>

                    <ul class="profile-nav">
                        <li><a href="<?php echo SITE_URL; ?>/user/profile.php" class="active"><ion-icon
                                    name="person-outline"></ion-icon> My Profile</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/user/orders.php"><ion-icon name="bag-outline"></ion-icon>
                                My Orders</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/user/wishlist.php"><ion-icon
                                    name="heart-outline"></ion-icon> Wishlist (
                                <?php echo $wishCount; ?>)
                            </a></li>
                        <li><a href="<?php echo SITE_URL; ?>/user/logout.php"><ion-icon
                                    name="log-out-outline"></ion-icon> Logout</a></li>
                    </ul>
                </div>

                <!-- Main Content -->
                <div>

                    <!-- Edit Profile -->
                    <div class="profile-main" style="margin-bottom:24px;">
                        <h2>Personal Information</h2>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_profile">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="name" required
                                        value="<?php echo sanitize($user['name']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" value="<?php echo sanitize($user['email']); ?>" readonly
                                        style="background:var(--cultured);cursor:not-allowed;">
                                </div>
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="tel" name="phone"
                                        value="<?php echo sanitize($user['phone'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Member Since</label>
                                    <input type="text"
                                        value="<?php echo date('d M Y', strtotime($user['created_at'])); ?>" readonly
                                        style="background:var(--cultured);cursor:not-allowed;">
                                </div>
                            </div>
                            <button type="submit" class="btn-primary" style="width:auto;padding:11px 32px;">Save
                                Changes</button>
                        </form>
                    </div>

                    <!-- Change Password -->
                    <div class="profile-main">
                        <h2>Change Password</h2>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="change_password">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" required
                                    placeholder="Enter current password">
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                                <div class="form-group">
                                    <label>New Password</label>
                                    <input type="password" name="new_password" required placeholder="Min 6 characters">
                                </div>
                                <div class="form-group">
                                    <label>Confirm New Password</label>
                                    <input type="password" name="confirm_password" required
                                        placeholder="Repeat new password">
                                </div>
                            </div>
                            <button type="submit" class="btn-primary" style="width:auto;padding:11px 32px;">Change
                                Password</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>