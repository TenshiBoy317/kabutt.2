<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../classes/Order.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header("Location: /auth/login.php");
    exit;
}

$orderId = $_GET['id'] ?? 0;
$orderObj = new Order();
$order = $orderObj->getOrder($orderId, $_SESSION['user_id']);

if (!$order) {
    header("Location: /orders");
    exit;
}
?>

    <div class="order-confirmation">
        <div class="confirmation-header">
            <i class="fas fa-check-circle"></i>
            <h1>¡Pedido Confirmado!</h1>
            <p>Hemos recibido tu pedido correctamente</p>
        </div>

        <div class="order-details">
            <div class="detail-item">
                <span>Número de pedido:</span>
                <strong>#<?= $order['id'] ?></strong>
            </div>

            <div class="detail-item">
                <span>Fecha:</span>
                <strong><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></strong>
            </div>

            <div class="detail-item">
                <span>Total:</span>
                <strong>S/ <?= number_format($order['total'], 2) ?></strong>
            </div>

            <div class="detail-item">
                <span>Método de pago:</span>
                <strong><?= ucfirst($order['payment_method']) ?></strong>
            </div>
        </div>

        <div class="confirmation-actions">
            <a href="/orders/view.php?id=<?= $order['id'] ?>" class="btn btn-outline">
                Ver detalles del pedido
            </a>
            <a href="/" class="btn btn-primary">
                Seguir comprando
            </a>
        </div>
    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>