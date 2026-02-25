<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$db = getDB();

// Stats
$totalRevenue = $db->query("SELECT COALESCE(SUM(total_amount),0) as t FROM orders WHERE status != 'cancelled'")->fetch_assoc()['t'];
$totalOrders = $db->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$totalProducts = $db->query("SELECT COUNT(*) as c FROM products WHERE is_active=1")->fetch_assoc()['c'];
$totalUsers = $db->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$pendingOrders = $db->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
$lowStockProducts = $db->query("SELECT COUNT(*) as c FROM products WHERE stock <= 5 AND is_active=1")->fetch_assoc()['c'];

// Recent orders
$recentOrders = $db->query("
    SELECT o.*, u.name as user_name
    FROM orders o JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC LIMIT 8
")->fetch_all(MYSQLI_ASSOC);

// Sales by month (last 6 months)
$salesData = $db->query("
    SELECT DATE_FORMAT(created_at, '%b %Y') as month,
           SUM(total_amount) as revenue,
           COUNT(*) as count
    FROM orders
    WHERE status != 'cancelled' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY created_at ASC
")->fetch_all(MYSQLI_ASSOC);

// Top products
$topProducts = $db->query("
    SELECT p.name, p.image, p.price, SUM(oi.quantity) as sold, SUM(oi.total) as revenue
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    GROUP BY oi.product_id
    ORDER BY sold DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/admin_header.php';
?>

<main class="admin-main">

    <!-- Page Title -->
    <div class="admin-page-header">
        <h1>Dashboard</h1>
        <p>Welcome back, <strong>
                <?php echo sanitize($_SESSION['admin_name']); ?>
            </strong> 👋</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">

        <div class="stat-card" style="border-top:4px solid hsl(353,100%,65%);">
            <div class="stat-icon" style="background:hsl(353,100%,95%);color:hsl(353,100%,55%);">
                <ion-icon name="cash-outline"></ion-icon>
            </div>
            <div class="stat-info">
                <p class="stat-label">Total Revenue</p>
                <p class="stat-value">
                    <?php echo formatPrice($totalRevenue); ?>
                </p>
            </div>
        </div>

        <div class="stat-card" style="border-top:4px solid hsl(210,80%,60%);">
            <div class="stat-icon" style="background:hsl(210,100%,95%);color:hsl(210,80%,55%);">
                <ion-icon name="bag-outline"></ion-icon>
            </div>
            <div class="stat-info">
                <p class="stat-label">Total Orders</p>
                <p class="stat-value">
                    <?php echo number_format($totalOrders); ?>
                </p>
            </div>
        </div>

        <div class="stat-card" style="border-top:4px solid hsl(152,51%,52%);">
            <div class="stat-icon" style="background:hsl(152,51%,93%);color:hsl(152,51%,40%);">
                <ion-icon name="people-outline"></ion-icon>
            </div>
            <div class="stat-info">
                <p class="stat-label">Total Users</p>
                <p class="stat-value">
                    <?php echo number_format($totalUsers); ?>
                </p>
            </div>
        </div>

        <div class="stat-card" style="border-top:4px solid hsl(40,90%,55%);">
            <div class="stat-icon" style="background:hsl(40,100%,94%);color:hsl(40,80%,45%);">
                <ion-icon name="cube-outline"></ion-icon>
            </div>
            <div class="stat-info">
                <p class="stat-label">Active Products</p>
                <p class="stat-value">
                    <?php echo number_format($totalProducts); ?>
                </p>
            </div>
        </div>

        <div class="stat-card" style="border-top:4px solid hsl(270,60%,60%);">
            <div class="stat-icon" style="background:hsl(270,80%,96%);color:hsl(270,60%,50%);">
                <ion-icon name="time-outline"></ion-icon>
            </div>
            <div class="stat-info">
                <p class="stat-label">Pending Orders</p>
                <p class="stat-value">
                    <?php echo (int) $pendingOrders; ?>
                </p>
            </div>
        </div>

        <div class="stat-card" style="border-top:4px solid hsl(0,80%,60%);">
            <div class="stat-icon" style="background:hsl(0,100%,96%);color:hsl(0,70%,55%);">
                <ion-icon name="warning-outline"></ion-icon>
            </div>
            <div class="stat-info">
                <p class="stat-label">Low Stock Items</p>
                <p class="stat-value">
                    <?php echo (int) $lowStockProducts; ?>
                </p>
            </div>
        </div>

    </div>

    <div class="admin-two-col">

        <!-- Recent Orders -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Recent Orders</h2>
                <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="admin-link">View All →</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><a href="<?php echo SITE_URL; ?>/admin/orders.php?id=<?php echo $order['id']; ?>"
                                        class="admin-link">
                                        <?php echo htmlspecialchars($order['order_number']); ?>
                                    </a></td>
                                <td>
                                    <?php echo sanitize($order['user_name']); ?>
                                </td>
                                <td>
                                    <?php echo formatPrice($order['total_amount']); ?>
                                </td>
                                <td><span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span></td>
                                <td>
                                    <?php echo date('d M Y', strtotime($order['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Products -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Top Selling Products</h2>
                <a href="<?php echo SITE_URL; ?>/admin/products.php" class="admin-link">Manage →</a>
            </div>
            <?php foreach ($topProducts as $tp):
                $tImg = getProductImageUrl($tp['image']);
                ?>
                <div
                    style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--admin-border);">
                    <img src="<?php echo $tImg; ?>" style="width:44px;height:44px;object-fit:cover;border-radius:8px;"
                        alt="">
                    <div style="flex:1;">
                        <p style="font-size:14px;font-weight:600;color:var(--admin-text);">
                            <?php echo sanitize($tp['name']); ?>
                        </p>
                        <p style="font-size:12px;color:var(--admin-muted);">
                            <?php echo (int) $tp['sold']; ?> sold
                        </p>
                    </div>
                    <span style="font-size:14px;font-weight:700;color:var(--admin-accent);">
                        <?php echo formatPrice($tp['revenue']); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <!-- Sales Chart -->
    <div class="admin-card" style="margin-top:20px;">
        <div class="admin-card-header">
            <h2>Revenue (Last 6 Months)</h2>
        </div>
        <canvas id="salesChart" height="80"></canvas>
    </div>

</main>

<?php
$labels = json_encode(array_column($salesData, 'month'));
$values = json_encode(array_column($salesData, 'revenue'));
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById("salesChart").getContext("2d");
    new Chart(ctx, {
        type: "bar",
        data: {
            labels: <?php echo $labels; ?>,
            datasets: [{
                label: "Revenue ($)",
                data: <?php echo $values; ?>,
                backgroundColor: "hsla(353,100%,65%,0.75)",
                borderColor: "hsl(353,100%,55%)",
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: "hsla(0,0%,0%,0.05)" } },
                x: { grid: { display: false } }
            }
        }
    });
</script>

<?php include __DIR__ . '/admin_footer.php'; ?>