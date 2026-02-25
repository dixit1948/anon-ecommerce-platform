<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Admin Login';
$error = '';

if (isAdminLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, password FROM admins WHERE email = ? AND is_active = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $db->query("UPDATE admins SET last_login = NOW() WHERE id = {$admin['id']}");
        header('Location: ' . SITE_URL . '/admin/dashboard.php');
        exit;
    } else {
        $error = 'Invalid admin credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – Anon</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/extra.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, hsl(220, 30%, 12%) 0%, hsl(240, 25%, 18%) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }

        .admin-login-card {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 18px;
            padding: 48px 44px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 24px 80px hsla(0, 0%, 0%, 0.35);
            backdrop-filter: blur(10px);
        }

        .admin-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .admin-logo span {
            display: inline-block;
            background: linear-gradient(135deg, hsl(353, 100%, 60%), hsl(29, 90%, 60%));
            color: white;
            font-size: 28px;
            font-weight: 800;
            padding: 12px 28px;
            border-radius: 12px;
            letter-spacing: 2px;
        }

        .admin-login-card h1 {
            font-size: 22px;
            font-weight: 700;
            color: hsl(220, 30%, 15%);
            text-align: center;
            margin-bottom: 6px;
        }

        .admin-login-card p {
            text-align: center;
            color: hsl(220, 10%, 55%);
            font-size: 14px;
            margin-bottom: 28px;
        }
    </style>
</head>

<body>
    <div class="admin-login-card">
        <div class="admin-logo"><span>ANON</span></div>
        <h1>Admin Panel</h1>
        <p>Secure administrator access</p>

        <?php if ($error): ?>
            <div class="flash-message flash-error"
                style="position:relative;top:auto;right:auto;margin-bottom:18px;animation:none;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Admin Email</label>
                <input type="email" id="email" name="email" placeholder="admin@anon.com" required
                    value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-primary" style="margin-top:8px;">Sign In to Admin Panel</button>
        </form>


    </div>
</body>

</html>