<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireUserLogin();

$db = getDB();
$userId = $_SESSION['user_id'];
$orderId = (int) ($_GET['id'] ?? 0);

$order = $db->query("
    SELECT o.*, p.transaction_id, p.status as pay_status, u.name as user_name, u.email, u.phone
    FROM orders o
    LEFT JOIN payments p ON p.order_id = o.id
    LEFT JOIN users u ON u.id = o.user_id
    WHERE o.id = $orderId AND o.user_id = $userId
")->fetch_assoc();

if (!$order) {
    setFlash('error', 'Order not found.');
    header('Location: ' . SITE_URL . '/user/orders.php');
    exit;
}

$orderItems = $db->query("
    SELECT oi.*, p.name FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = $orderId
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Invoice #' . $order['order_number'];

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/extra.css">

<main>
    <section class="invoice-section">
        <div class="container">

            <div style="display:flex;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
                <a href="<?php echo SITE_URL; ?>/user/orders.php"
                    style="color:var(--salmon-pink);font-weight:600;font-size:var(--fs-7);">← Back to Orders</a>
                <button onclick="window.print()" class="btn-secondary">🖨 Print Invoice</button>
            </div>

            <div class="invoice-box" id="invoicePrint">

                <div class="invoice-header">
                    <div>
                        <img src="<?php echo SITE_URL; ?>/assets/images/logo/logo.svg" width="120" alt="Anon Logo">
                        <p style="color:var(--sonic-silver);font-size:var(--fs-8);margin-top:8px;">419 State 414, New
                            York, USA</p>
                        <p style="color:var(--sonic-silver);font-size:var(--fs-8);">support@anon.com | (607) 936-8058
                        </p>
                    </div>
                    <div class="invoice-meta">
                        <div class="invoice-title">INVOICE</div>
                        <p><strong>
                                <?php echo htmlspecialchars($order['order_number']); ?>
                            </strong></p>
                        <p>Date:
                            <?php echo date('d M Y', strtotime($order['created_at'])); ?>
                        </p>
                        <p>Status: <span class="order-status status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span></p>
                    </div>
                </div>

                <hr style="border:none;border-top:1px solid var(--cultured);margin:20px 0;">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;margin-bottom:20px;">
                    <div>
                        <h4
                            style="font-size:var(--fs-8);font-weight:700;color:var(--eerie-black);margin-bottom:8px;text-transform:uppercase;">
                            Bill To</h4>
                        <p style="font-size:var(--fs-7);color:var(--onyx);">
                            <?php echo sanitize($order['user_name']); ?>
                        </p>
                        <p style="font-size:var(--fs-8);color:var(--sonic-silver);">
                            <?php echo sanitize($order['email']); ?>
                        </p>
                        <p style="font-size:var(--fs-8);color:var(--sonic-silver);">
                            <?php echo sanitize($order['phone'] ?? ''); ?>
                        </p>
                    </div>
                    <div>
                        <h4
                            style="font-size:var(--fs-8);font-weight:700;color:var(--eerie-black);margin-bottom:8px;text-transform:uppercase;">
                            Ship To</h4>
                        <p style="font-size:var(--fs-8);color:var(--sonic-silver);">
                            <?php echo sanitize($order['shipping_address']); ?>
                        </p>
                    </div>
                </div>

                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $i => $item): ?>
                            <tr>
                                <td>
                                    <?php echo $i + 1; ?>
                                </td>
                                <td>
                                    <?php echo sanitize($item['name']); ?>
                                </td>
                                <td>
                                    <?php echo formatPrice($item['price']); ?>
                                </td>
                                <td>
                                    <?php echo $item['quantity']; ?>
                                </td>
                                <td>
                                    <?php echo formatPrice($item['total']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="invoice-total">
                    <div class="summary-row" style="min-width:260px;"><span>Subtotal</span><span>
                            <?php echo formatPrice($order['total_amount'] - $order['shipping_amount'] + $order['discount_amount']); ?>
                        </span></div>
                    <div class="summary-row" style="min-width:260px;"><span>Shipping</span><span>
                            <?php echo formatPrice($order['shipping_amount']); ?>
                        </span></div>
                    <?php if ($order['discount_amount'] > 0): ?>
                        <div class="summary-row" style="min-width:260px;"><span>Discount</span><span
                                style="color:hsl(152,51%,42%);">-
                                <?php echo formatPrice($order['discount_amount']); ?>
                            </span></div>
                    <?php endif; ?>
                    <div class="summary-row total" style="min-width:260px;"><span>Grand Total</span><span>
                            <?php echo formatPrice($order['total_amount']); ?>
                        </span></div>
                </div>

                <hr style="border:none;border-top:1px solid var(--cultured);margin:24px 0 16px;">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                    <div>
                        <p style="font-size:var(--fs-9);color:var(--sonic-silver);"><strong>Payment Method:</strong>
                            <?php echo strtoupper($order['payment_method']); ?>
                        </p>
                        <p style="font-size:var(--fs-9);color:var(--sonic-silver);"><strong>Transaction ID:</strong>
                            <?php echo htmlspecialchars($order['transaction_id'] ?? 'N/A'); ?>
                        </p>
                        <p style="font-size:var(--fs-9);color:var(--sonic-silver);"><strong>Payment Status:</strong>
                            <?php echo ucfirst($order['pay_status'] ?? 'pending'); ?>
                        </p>
                    </div>
                    <div style="text-align:right;">
                        <p style="font-size:var(--fs-9);color:var(--sonic-silver);">Thank you for shopping with
                            <strong>Anon</strong>!</p>
                        <p style="font-size:var(--fs-9);color:var(--sonic-silver);">This is a computer-generated
                            invoice.</p>
                    </div>
                </div>

            </div>

        </div>
    </section>
</main>

<style>
    @media print {

        header,
        footer,
        nav,
        .mobile-bottom-navigation,
        .breadcrumb,
        button {
            display: none !important;
        }

        .invoice-box {
            box-shadow: none !important;
            border: 1px solid #ddd;
        }

        a {
            display: none !important;
        }
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>