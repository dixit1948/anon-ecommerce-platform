<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pageTitle = 'Sales Reports';
$db = getDB();

// Date range filter
$from = sanitize($_GET['from'] ?? date('Y-m-01'));
$to = sanitize($_GET['to'] ?? date('Y-m-d'));

// Summary stats for range
$summary = $db->query("
    SELECT
        COUNT(*) as total_orders,
        COALESCE(SUM(total_amount),0) as total_revenue,
        COALESCE(AVG(total_amount),0) as avg_order,
        COALESCE(SUM(shipping_amount),0) as total_shipping,
        COALESCE(SUM(discount_amount),0) as total_discounts
    FROM orders
    WHERE status != 'cancelled'
      AND DATE(created_at) BETWEEN '$from' AND '$to'
")->fetch_assoc();

// Orders by day
$dailySales = $db->query("
    SELECT DATE(created_at) as d, COUNT(*) as orders, SUM(total_amount) as revenue
    FROM orders
    WHERE status != 'cancelled' AND DATE(created_at) BETWEEN '$from' AND '$to'
    GROUP BY DATE(created_at)
    ORDER BY d ASC
")->fetch_all(MYSQLI_ASSOC);

// Orders by status
$byStatus = $db->query("
    SELECT status, COUNT(*) as cnt, COALESCE(SUM(total_amount),0) as rev
    FROM orders
    WHERE DATE(created_at) BETWEEN '$from' AND '$to'
    GROUP BY status
")->fetch_all(MYSQLI_ASSOC);

// Top categories
$topCats = $db->query("
    SELECT c.name, SUM(oi.quantity) as units, SUM(oi.total) as revenue
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN categories c ON c.id = p.category_id
    JOIN orders o ON o.id = oi.order_id
    WHERE o.status != 'cancelled' AND DATE(o.created_at) BETWEEN '$from' AND '$to'
    GROUP BY c.id
    ORDER BY revenue DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Payment methods breakdown
$payMethods = $db->query("
    SELECT payment_method, COUNT(*) as cnt, SUM(total_amount) as rev
    FROM orders
    WHERE DATE(created_at) BETWEEN '$from' AND '$to'
    GROUP BY payment_method
")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/admin_header.php';
?>

<main class="admin-main">
    <div class="admin-page-header"
        style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;">
        <div>
            <h1>Sales Reports</h1>
            <p>Revenue analytics &amp; business insights</p>
        </div>
        <button onclick="window.print()" class="admin-btn admin-btn-secondary">
            <ion-icon name="print-outline"></ion-icon> Print Report
        </button>
    </div>

    <!-- Date Filter -->
    <div class="admin-card" style="margin-bottom:22px;">
        <form method="GET" style="padding:16px 22px;display:flex;gap:14px;flex-wrap:wrap;align-items:flex-end;">
            <div>
                <label
                    style="font-size:12px;font-weight:600;color:var(--admin-muted);display:block;margin-bottom:4px;">From
                    Date</label>
                <input type="date" name="from" value="<?php echo $from; ?>"
                    style="padding:8px 12px;border:1.5px solid var(--admin-border);border-radius:8px;font-size:13px;">
            </div>
            <div>
                <label
                    style="font-size:12px;font-weight:600;color:var(--admin-muted);display:block;margin-bottom:4px;">To
                    Date</label>
                <input type="date" name="to" value="<?php echo $to; ?>"
                    style="padding:8px 12px;border:1.5px solid var(--admin-border);border-radius:8px;font-size:13px;">
            </div>
            <button type="submit" class="admin-btn admin-btn-primary">Generate Report</button>
            <div style="display:flex;gap:8px;">
                <a href="?from=<?php echo date('Y-m-d'); ?>&to=<?php echo date('Y-m-d'); ?>"
                    class="admin-btn admin-btn-secondary" style="font-size:12px;">Today</a>
                <a href="?from=<?php echo date('Y-m-01'); ?>&to=<?php echo date('Y-m-d'); ?>"
                    class="admin-btn admin-btn-secondary" style="font-size:12px;">This Month</a>
                <a href="?from=<?php echo date('Y-01-01'); ?>&to=<?php echo date('Y-m-d'); ?>"
                    class="admin-btn admin-btn-secondary" style="font-size:12px;">This Year</a>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid" style="margin-bottom:22px;">
        <div class="stat-card" style="border-top:4px solid hsl(353,100%,65%);">
            <div class="stat-icon" style="background:hsl(353,100%,95%);color:hsl(353,100%,55%);">
                <ion-icon name="cash-outline"></ion-icon>
            </div>
            <div class="stat-info">
                <p class="stat-label">Total Revenue</p>
                <p class="stat-value">
                    <?php echo formatPrice($summary['total_revenue']); ?>
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
                    <?php echo number_format($summary['total_orders']); ?>
                </p>
            </div>
        </div>
        <div class="stat-card" style="border-top:4px solid hsl(152,51%,52%);">
            <div class="stat-icon" style="background:hsl(152,51%,93%);color:hsl(152,51%,40%);">
                <ion-icon name="trending-up-outline"></ion-icon>
            </div>
            <div class="stat-info">
                <p class="stat-label">Avg. Order Value</p>
                <p class="stat-value">
                    <?php echo formatPrice($summary['avg_order']); ?>
                </p>
            </div>
        </div>
        <div class="stat-card" style="border-top:4px solid hsl(40,90%,55%);">
            <div class="stat-icon" style="background:hsl(40,100%,94%);color:hsl(40,80%,45%);">
                <ion-icon name="pricetag-outline"></ion-icon>
            </div>
            <div class="stat-info">
                <p class="stat-label">Total Discounts Given</p>
                <p class="stat-value">
                    <?php echo formatPrice($summary['total_discounts']); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:22px;">

        <!-- Daily Revenue Chart -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Daily Revenue &amp; Orders</h2>
            </div>
            <div style="padding:20px;">
                <canvas id="dailyChart" height="100"></canvas>
            </div>
        </div>

        <!-- Status Breakdown -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Orders by Status</h2>
            </div>
            <div style="padding:20px;">
                <canvas id="statusChart" height="180"></canvas>
                <div style="margin-top:16px;">
                    <?php foreach ($byStatus as $s): ?>
                        <div
                            style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--admin-border);font-size:13px;">
                            <span><span class="order-status status-<?php echo $s['status']; ?>">
                                    <?php echo ucfirst($s['status']); ?>
                                </span></span>
                            <span style="font-weight:600;">
                                <?php echo (int) $s['cnt']; ?> orders —
                                <?php echo formatPrice($s['rev']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Bottom Row -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:22px;">

        <!-- Top Categories -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Top Categories</h2>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Units Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topCats as $cat): ?>
                            <tr>
                                <td style="font-weight:600;">
                                    <?php echo sanitize($cat['name']); ?>
                                </td>
                                <td>
                                    <?php echo number_format($cat['units']); ?>
                                </td>
                                <td style="font-weight:700;color:hsl(152,51%,40%);">
                                    <?php echo formatPrice($cat['revenue']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Payment Methods</h2>
            </div>
            <div style="padding:20px;">
                <canvas id="payChart" height="180"></canvas>
                <div style="margin-top:16px;">
                    <?php foreach ($payMethods as $pm): ?>
                        <div
                            style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--admin-border);font-size:13px;">
                            <span style="font-weight:600;text-transform:uppercase;">
                                <?php echo htmlspecialchars($pm['payment_method']); ?>
                            </span>
                            <span>
                                <?php echo (int) $pm['cnt']; ?> orders —
                                <?php echo formatPrice($pm['rev']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Daily Orders Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Daily Breakdown</h2>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Orders</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dailySales as $day): ?>
                        <tr>
                            <td>
                                <?php echo date('D, d M Y', strtotime($day['d'])); ?>
                            </td>
                            <td style="font-weight:600;">
                                <?php echo (int) $day['orders']; ?>
                            </td>
                            <td style="font-weight:700;">
                                <?php echo formatPrice($day['revenue']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dailySales)): ?>
                        <tr>
                            <td colspan="3" style="text-align:center;color:var(--admin-muted);padding:30px;">No data for
                                selected range</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<?php
$dailyLabels = json_encode(array_map(fn($d) => date('d M', strtotime($d['d'])), $dailySales));
$dailyRevenue = json_encode(array_column($dailySales, 'revenue'));
$dailyOrders = json_encode(array_column($dailySales, 'orders'));
$statusLabels = json_encode(array_map(fn($s) => ucfirst($s['status']), $byStatus));
$statusData = json_encode(array_column($byStatus, 'cnt'));
$payLabels = json_encode(array_map(fn($p) => strtoupper($p['payment_method']), $payMethods));
$payData = json_encode(array_column($payMethods, 'cnt'));
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Daily Chart
    new Chart(document.getElementById("dailyChart"), {
        type: "line",
        data: {
            labels: <?php echo $dailyLabels; ?>,
        datasets: [
            { label: "Revenue ($)", data: <?php echo $dailyRevenue; ?>, borderColor: "hsl(353,100%,60%)", backgroundColor: "hsla(353,100%,60%,0.1)", tension: 0.4, fill: true, yAxisID: 'y' },
        { label: "Orders", data: <?php echo $dailyOrders; ?>, borderColor: "hsl(210,80%,60%)", backgroundColor: "hsla(210,80%,60%,0.1)", tension: 0.4, fill: true, yAxisID: 'y1' }
        ]
    },
        options: {
        responsive: true,
        interaction: { mode: "index", intersect: false },
        scales: {
            y: { type: "linear", position: "left", beginAtZero: true, title: { display: true, text: "Revenue ($)" } },
            y1: { type: "linear", position: "right", beginAtZero: true, grid: { drawOnChartArea: false }, title: { display: true, text: "Orders" } }
        }
    }
});
    // Status Doughnut
    new Chart(document.getElementById("statusChart"), {
        type: "doughnut",
        data: {
            labels: <?php echo $statusLabels; ?>,
        datasets: [{ data: <?php echo $statusData; ?>, backgroundColor: ["hsl(40,100%,70%)", "hsl(210,80%,70%)", "hsl(270,60%,70%)", "hsl(152,51%,60%)", "hsl(0,80%,70%)"] }]
    },
        options: { responsive: true, plugins: { legend: { position: "bottom" } } }
});
    // Payment Doughnut
    new Chart(document.getElementById("payChart"), {
        type: "doughnut",
        data: {
            labels: <?php echo $payLabels; ?>,
        datasets: [{ data: <?php echo $payData; ?>, backgroundColor: ["hsl(353,100%,70%)", "hsl(210,80%,65%)", "hsl(40,90%,65%)"] }]
    },
        options: { responsive: true, plugins: { legend: { position: "bottom" } } }
});
</script>

<style>
    @media print {

        .admin-sidebar,
        .admin-topbar,
        form,
        button {
            display: none !important;
        }

        .admin-wrapper {
            margin: 0 !important;
        }

        .admin-main {
            padding: 10px !important;
        }
    }
</style>

<?php include __DIR__ . '/admin_footer.php'; ?>