<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Login';
$error = '';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/user/profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = ? AND is_active = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            // Update last login
            $db->query("UPDATE users SET last_login = NOW() WHERE id = {$user['id']}");

            setFlash('success', 'Welcome back, ' . $user['name'] . '!');
            $redirect = $_SESSION['redirect_after_login'] ?? SITE_URL . '/user/profile.php';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/extra.css">

<main>
    <section class="auth-section">
        <div class="auth-card">
            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to your Anon account</p>

            <?php if ($error): ?>
                <div class="flash-message flash-error"
                    style="position:relative;top:auto;right:auto;margin-bottom:20px;animation:none;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required
                        value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Your password" required>
                </div>

                <button type="submit" class="btn-primary" style="margin-top:8px;">Sign In</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/user/register.php">Create one</a></p>
                <p style="margin-top:8px;"><a href="<?php echo SITE_URL; ?>/admin/login.php"
                        style="color:var(--sonic-silver);">Admin Login →</a></p>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>