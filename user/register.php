<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Register';
$error = '';
$success = '';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/user/profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email already registered. Please login.';
        } else {
            $hashedPwd = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (name, email, phone, password, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashedPwd);
            if ($stmt->execute()) {
                setFlash('success', 'Account created successfully! Please login.');
                header('Location: ' . SITE_URL . '/user/login.php');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/extra.css">

<main>
    <section class="auth-section">
        <div class="auth-card" style="max-width:500px;">
            <h2>Create Account</h2>
            <p class="subtitle">Join Anon for exclusive deals &amp; offers</p>

            <?php if ($error): ?>
                <div class="flash-message flash-error"
                    style="position:relative;top:auto;right:auto;margin-bottom:20px;animation:none;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" placeholder="John Doe" required
                        value="<?php echo isset($_POST['name']) ? sanitize($_POST['name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required
                        value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="+1 234 567 8900"
                        value="<?php echo isset($_POST['phone']) ? sanitize($_POST['phone']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password * <small style="color:var(--sonic-silver);">(min 6
                            chars)</small></label>
                    <input type="password" id="password" name="password" placeholder="Create a strong password"
                        required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                        placeholder="Repeat your password" required>
                </div>

                <button type="submit" class="btn-primary" style="margin-top:8px;">Create Account</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="<?php echo SITE_URL; ?>/user/login.php">Sign in</a></p>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>