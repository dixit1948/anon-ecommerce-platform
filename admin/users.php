<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pageTitle = 'Customers';
$db = getDB();

$users = $db->query("
    SELECT u.*,
           COUNT(DISTINCT o.id) as order_count,
           COALESCE(SUM(o.total_amount),0) as total_spent
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/admin_header.php';
?>

<main class="admin-main">
    <div class="admin-page-header">
        <h1>Customers</h1>
        <p>
            <?php echo count($users); ?> registered users
        </p>
    </div>

    <div class="admin-card">
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div
                                        style="width:36px;height:36px;border-radius:50%;background:var(--admin-accent);color:white;font-weight:700;font-size:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <?php echo strtoupper($u['name'][0]); ?>
                                    </div>
                                    <span style="font-weight:600;">
                                        <?php echo sanitize($u['name']); ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php echo sanitize($u['email']); ?>
                            </td>
                            <td>
                                <?php echo sanitize($u['phone'] ?? '—'); ?>
                            </td>
                            <td style="font-weight:600;text-align:center;">
                                <?php echo (int) $u['order_count']; ?>
                            </td>
                            <td style="font-weight:700;color:hsl(152,51%,40%);">
                                <?php echo formatPrice($u['total_spent']); ?>
                            </td>
                            <td>
                                <?php echo date('d M Y', strtotime($u['created_at'])); ?>
                            </td>
                            <td>
                                <form method="POST" action="" onsubmit="return confirm('Toggle user status?')">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <button type="submit"
                                        class="admin-btn <?php echo $u['is_active'] ? 'admin-btn-danger' : 'admin-btn-success'; ?>"
                                        style="padding:5px 12px;font-size:12px;">
                                        <?php echo $u['is_active'] ? 'Disable' : 'Enable'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php
// Handle user status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $uid = (int) $_POST['user_id'];
    $db->query("UPDATE users SET is_active = NOT is_active WHERE id = $uid");
    header('Location: ' . SITE_URL . '/admin/users.php');
    exit;
}
include __DIR__ . '/admin_footer.php';
?>