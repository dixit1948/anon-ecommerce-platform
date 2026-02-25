<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireUserLogin();

if (!isset($_SESSION['order_success'])) {
    header('Location: ' . SITE_URL . '/user/orders.php');
    exit;
}

$orderData = $_SESSION['order_success'];
unset($_SESSION['order_success']);

$pageTitle = 'Order Placed Successfully!';
$db = getDB();

$orderId = $orderData['order_id'];
$orderItems = $db->query("
    SELECT oi.*, p.name, p.image
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = $orderId
")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/extra.css">

<main>
    <section class="payment-result-section">
        <div class="payment-result-card">

            <div class="result-icon success">✅</div>

            <h2>Order Placed Successfully!</h2>
            <p>Thank you for your purchase. Your order has been received.</p>

            <div class="order-detail-box">
                <p><strong>Order Number:</strong>
                    <?php echo htmlspecialchars($orderData['order_number']); ?>
                </p>
                <p><strong>Transaction ID:</strong>
                    <?php echo htmlspecialchars($orderData['transaction_id']); ?>
                </p>
                <p><strong>Payment Method:</strong>
                    <?php echo strtoupper(htmlspecialchars($orderData['payment_method'])); ?>
                </p>
                <p><strong>Total Amount:</strong>
                    <?php echo formatPrice($orderData['total']); ?>
                </p>
                <?php if ($orderData['payment_method'] === 'cod'): ?>
                    <p style="color:hsl(40,80%,40%);"><strong>💵 Pay on delivery:</strong> Keep the exact amount ready.</p>
                <?php elseif ($orderData['payment_method'] === 'razorpay'): ?>
                    <p style="color:hsl(220,80%,45%);"><strong>🔵 Razorpay Payment Successful</strong> — Transaction
                        verified &amp; confirmed.</p>
                <?php else: ?>
                    <p style="color:hsl(152,51%,40%);"><strong>✓ Payment Successful</strong></p>
                <?php endif; ?>
            </div>

            <!-- Ordered Items -->
            <div style="text-align:left;margin-bottom:20px;">
                <h3 style="font-size:var(--fs-6);font-weight:700;color:var(--eerie-black);margin-bottom:12px;">Items
                    Ordered</h3>
                <?php foreach ($orderItems as $item):
                    $imgSrc = getProductImageUrl($item['image']);
                    ?>
                    <div
                        style="display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px dashed var(--cultured);">
                        <img src="<?php echo $imgSrc; ?>"
                            style="width:45px;height:45px;object-fit:cover;border-radius:var(--border-radius-sm);border:1px solid var(--cultured);"
                            alt="">
                        <div style="flex:1;">
                            <p style="font-size:var(--fs-8);font-weight:600;color:var(--eerie-black);">
                                <?php echo sanitize($item['name']); ?>
                            </p>
                            <p style="font-size:var(--fs-9);color:var(--sonic-silver);">Qty:
                                <?php echo $item['quantity']; ?>
                            </p>
                        </div>
                        <span style="font-size:var(--fs-8);font-weight:600;">
                            <?php echo formatPrice($item['total']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                <a href="<?php echo SITE_URL; ?>/user/orders.php" class="btn-primary"
                    style="width:auto;padding:12px 28px;">Track My Orders</a>
                <a href="<?php echo SITE_URL; ?>/index.php" class="btn-secondary">Continue Shopping</a>
            </div>

            <!-- Print Invoice -->
            <button onclick="window.print()"
                style="margin-top:16px;background:none;border:4px dashed var(--cultured);color:var(--sonic-silver);padding:8px 20px;border-radius:var(--border-radius-sm);cursor:pointer;font-size:var(--fs-8);">
                🖨 Print Invoice
            </button>

        </div>
    </section>
</main>

<style>
    @media print {

        header,
        footer,
        nav,
        button:not(.print-hide),
        .mobile-bottom-navigation {
            display: none !important;
        }

        .payment-result-card {
            box-shadow: none;
            border: 1px solid #ccc;
        }
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>