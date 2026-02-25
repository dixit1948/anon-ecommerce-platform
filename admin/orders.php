<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pageTitle = 'Orders';
$db = getDB();
$message = '';

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = (int) $_POST['order_id'];
    $status = sanitize($_POST['status'] ?? '');
    $allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($status, $allowed)) {
        $db->query("UPDATE orders SET status = '$status' WHERE id = $orderId");
        $message = 'Order status updated!';
    }
}

// Filters
$filterStatus = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');

$where = ['1=1'];
if ($filterStatus)
    $where[] = "o.status = '$filterStatus'";
if ($search)
    $where[] = "(o.order_number LIKE '%$search%' OR u.name LIKE '%$search%')";

$orders = $db->query("
    SELECT o.*, u.name as user_name, u.email as user_email
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/admin_header.php';
?>

<main class="admin-main">
    <div class="admin-page-header">
        <h1>Orders</h1>
        <p>
            <?php echo count($orders); ?> orders found
        </p>
    </div>

    <?php if ($message): ?>
        <div class="flash-message flash-success"
            style="position:relative;top:auto;right:auto;margin-bottom:18px;animation:none;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="admin-card" style="margin-bottom:20px;">
        <form method="GET" style="padding:16px 22px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div>
                <label
                    style="font-size:12px;font-weight:600;color:var(--admin-muted);display:block;margin-bottom:4px;">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Order # or Customer"
                    style="padding:8px 12px;border:1.5px solid var(--admin-border);border-radius:8px;font-size:13px;width:200px;">
            </div>
            <div>
                <label
                    style="font-size:12px;font-weight:600;color:var(--admin-muted);display:block;margin-bottom:4px;">Status</label>
                <select name="status"
                    style="padding:8px 12px;border:1.5px solid var(--admin-border);border-radius:8px;font-size:13px;">
                    <option value="">All Statuses</option>
                    <?php foreach (['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo $filterStatus === $s ? 'selected' : ''; ?>>
                            <?php echo ucfirst($s); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="admin-btn admin-btn-primary">Filter</button>
            <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="admin-btn admin-btn-secondary">Clear</a>
        </form>
    </div>

    <div class="admin-card">
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td style="font-weight:700;">
                                <?php echo htmlspecialchars($order['order_number']); ?>
                            </td>
                            <td>
                                <p style="font-weight:600;">
                                    <?php echo sanitize($order['user_name']); ?>
                                </p>
                                <p style="font-size:12px;color:var(--admin-muted);">
                                    <?php echo sanitize($order['user_email']); ?>
                                </p>
                            </td>
                            <td style="font-weight:700;">
                                <?php echo formatPrice($order['total_amount']); ?>
                            </td>
                            <td>
                                <?php echo strtoupper($order['payment_method']); ?>
                            </td>
                            <td><span class="order-status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span></td>
                            <td>
                                <?php echo date('d M Y', strtotime($order['created_at'])); ?>
                            </td>
                            <td>
                                <form method="POST" style="display:flex;gap:6px;align-items:center;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status"
                                        style="padding:5px 8px;border:1.5px solid var(--admin-border);border-radius:6px;font-size:12px;">
                                        <?php foreach (['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $s): ?>
                                            <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>
                                                >
                                                <?php echo ucfirst($s); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="admin-btn admin-btn-success"
                                        style="padding:5px 10px;font-size:12px;">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include __DIR__ . '/admin_footer.php'; ?>