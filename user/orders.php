<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireUserLogin();

$pageTitle = 'My Orders';
$db = getDB();
$userId = $_SESSION['user_id'];

$orders = $db->query("
    SELECT o.*, p.transaction_id, p.status as payment_status
    FROM orders o
    LEFT JOIN payments p ON p.order_id = o.id
    WHERE o.user_id = $userId
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/extra.css">

<main>
    <section class="orders-section">
        <div class="container">

            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>/index.php">Home</a><span>/</span>
                <a href="<?php echo SITE_URL; ?>/user/profile.php">Profile</a><span>/</span>
                <span class="current">My Orders</span>
            </div>

            <h1 style="font-size:var(--fs-2);font-weight:700;color:var(--eerie-black);margin-bottom:30px;">My Orders
            </h1>

            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <ion-icon name="bag-outline"></ion-icon>
                    <h3>No orders yet</h3>
                    <p>You haven't placed any orders. Start shopping!</p>
                    <a href="<?php echo SITE_URL; ?>/products.php" class="btn-primary"
                        style="display:inline-block;width:auto;padding:12px 36px;">Shop Now</a>
                </div>
            <?php else: ?>

                <?php foreach ($orders as $order):
                    $orderItems = $db->query("
              SELECT oi.*, p.name, p.image
              FROM order_items oi
              JOIN products p ON p.id = oi.product_id
              WHERE oi.order_id = {$order['id']}
          ")->fetch_all(MYSQLI_ASSOC);
                    ?>
                    <div class="order-card">

                        <div class="order-card-header">
                            <div>
                                <p class="order-num">
                                    <?php echo htmlspecialchars($order['order_number']); ?>
                                </p>
                                <p class="order-date">
                                    <?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?>
                                </p>
                            </div>
                            <div style="display:flex;gap:10px;align-items:center;">
                                <span class="order-status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                                <a href="<?php echo SITE_URL; ?>/user/invoice.php?id=<?php echo $order['id']; ?>"
                                    style="font-size:var(--fs-9);color:var(--salmon-pink);font-weight:600;">View Invoice →</a>
                            </div>
                        </div>

                        <div class="order-card-body">
                            <?php foreach ($orderItems as $item):
                                $imgSrc = getProductImageUrl($item['image']);
                                ?>
                                <div class="order-item-row">
                                    <div style="display:flex;align-items:center;gap:12px;">
                                        <img src="<?php echo $imgSrc; ?>"
                                            style="width:50px;height:50px;object-fit:cover;border-radius:var(--border-radius-sm);border:1px solid var(--cultured);"
                                            alt="">
                                        <div>
                                            <p class="order-item-name">
                                                <?php echo sanitize($item['name']); ?>
                                            </p>
                                            <p style="font-size:var(--fs-9);color:var(--sonic-silver);">Qty:
                                                <?php echo $item['quantity']; ?> ×
                                                <?php echo formatPrice($item['price']); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <span class="order-item-price">
                                        <?php echo formatPrice($item['total']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>

                            <div class="order-total-row"
                                style="margin-top:12px;padding-top:12px;border-top:1px solid var(--cultured);">
                                <div style="display:flex;gap:24px;align-items:center;">
                                    <span style="font-size:var(--fs-8);color:var(--sonic-silver);">Payment: <strong
                                            style="color:var(--onyx);">
                                            <?php echo strtoupper($order['payment_method']); ?>
                                        </strong></span>
                                    <?php if ($order['transaction_id']): ?>
                                        <span style="font-size:var(--fs-9);color:var(--sonic-silver);">TXN:
                                            <?php echo htmlspecialchars($order['transaction_id']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span style="font-size:var(--fs-5);font-weight:700;color:var(--eerie-black);">Total:
                                        <?php echo formatPrice($order['total_amount']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>