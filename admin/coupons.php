<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pageTitle = 'Coupons';
$db = getDB();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    if ($action === 'add') {
        $code = strtoupper(sanitize($_POST['code'] ?? ''));
        $type = $_POST['discount_type'] === 'percent' ? 'percent' : 'fixed';
        $value = (float) ($_POST['discount_value'] ?? 0);
        $min_order = (float) ($_POST['min_order'] ?? 0);
        $max_uses = (int) ($_POST['max_uses'] ?? 999);
        $expiry = sanitize($_POST['expiry_date'] ?? '');
        $stmt = $db->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, max_uses, expiry_date, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("ssddis", $code, $type, $value, $min_order, $max_uses, $expiry);
        $stmt->execute();
        $message = 'Coupon created!';
    } elseif ($action === 'delete') {
        $id = (int) $_POST['id'];
        $db->query("DELETE FROM coupons WHERE id = $id");
        $message = 'Coupon deleted!';
    } elseif ($action === 'toggle') {
        $id = (int) $_POST['id'];
        $db->query("UPDATE coupons SET is_active = NOT is_active WHERE id = $id");
        $message = 'Coupon status toggled!';
    }
}

$coupons = $db->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/admin_header.php';
?>

<main class="admin-main">
    <div class="admin-page-header" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h1>Coupons</h1>
            <p>Manage discount codes</p>
        </div>
        <button class="admin-btn admin-btn-primary"
            onclick="document.getElementById('addCouponForm').style.display='block'">
            <ion-icon name="add-outline"></ion-icon> New Coupon
        </button>
    </div>

    <?php if ($message): ?>
        <div class="flash-message flash-success"
            style="position:relative;top:auto;right:auto;margin-bottom:18px;animation:none;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Add Form -->
    <div id="addCouponForm" style="display:none;margin-bottom:20px;" class="admin-card">
        <div style="padding:22px;">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Create New Coupon</h3>
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="add">
                <div class="admin-form-grid">
                    <div class="form-group">
                        <label>Coupon Code *</label>
                        <input type="text" name="code" required placeholder="SAVE20" style="text-transform:uppercase;">
                    </div>
                    <div class="form-group">
                        <label>Discount Type *</label>
                        <select name="discount_type">
                            <option value="percent">Percentage (%)</option>
                            <option value="fixed">Fixed ($)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Discount Value *</label>
                        <input type="number" name="discount_value" step="0.01" min="0" required placeholder="20">
                    </div>
                    <div class="form-group">
                        <label>Min Order Amount ($)</label>
                        <input type="number" name="min_order" step="0.01" min="0" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label>Max Uses</label>
                        <input type="number" name="max_uses" min="1" placeholder="100">
                    </div>
                    <div class="form-group">
                        <label>Expiry Date *</label>
                        <input type="date" name="expiry_date" required>
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:14px;">
                    <button type="submit" class="admin-btn admin-btn-primary">Create Coupon</button>
                    <button type="button" class="admin-btn admin-btn-secondary"
                        onclick="document.getElementById('addCouponForm').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Min Order</th>
                        <th>Used/Max</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $c):
                        $expired = strtotime($c['expiry_date']) < time(); ?>
                        <tr>
                            <td style="font-family:monospace;font-weight:700;font-size:15px;color:var(--admin-accent);">
                                <?php echo htmlspecialchars($c['code']); ?>
                            </td>
                            <td>
                                <?php echo $c['discount_type'] === 'percent' ? 'Percent (%)' : 'Fixed ($)'; ?>
                            </td>
                            <td style="font-weight:700;">
                                <?php echo $c['discount_type'] === 'percent' ? $c['discount_value'] . '%' : '$' . number_format($c['discount_value'], 2); ?>
                            </td>
                            <td>
                                <?php echo $c['min_order_amount'] > 0 ? '$' . number_format($c['min_order_amount'], 2) : '—'; ?>
                            </td>
                            <td>
                                <?php echo (int) $c['used_count']; ?> /
                                <?php echo (int) $c['max_uses']; ?>
                            </td>
                            <td style="color:<?php echo $expired ? 'hsl(0,70%,50%)' : 'inherit'; ?>">
                                <?php echo date('d M Y', strtotime($c['expiry_date'])); ?>
                                <?php echo $expired ? ' (Expired)' : ''; ?>
                            </td>
                            <td>
                                <span
                                    style="background:<?php echo $c['is_active'] ? 'hsl(152,60%,92%)' : 'hsl(0,100%,96%)'; ?>;color:<?php echo $c['is_active'] ? 'hsl(152,51%,30%)' : 'hsl(0,70%,45%)'; ?>;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                                    <?php echo $c['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                        <button class="admin-btn admin-btn-secondary"
                                            style="padding:5px 10px;font-size:12px;">
                                            <?php echo $c['is_active'] ? 'Disable' : 'Enable'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Delete this coupon?')"
                                        style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                        <button class="admin-btn admin-btn-danger"
                                            style="padding:5px 10px;font-size:12px;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include __DIR__ . '/admin_footer.php'; ?>