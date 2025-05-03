<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../classes/Order.php';
require_once __DIR__ . '/../../classes/User.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header("Location: /auth/login.php");
    exit;
}

$baseUrl = '/kabutt/';
$orderObj = new Order();
$userObj = new User();

$user = $userObj->getUserById($_SESSION['user_id']);
$isAdmin = ($user['role'] ?? '') === 'admin';

// Obtener pedidos según el rol
if ($isAdmin) {
    $orders = $orderObj->getAllOrders();
} else {
    $orders = $orderObj->getUserOrders($_SESSION['user_id']);
}
?>

    <div class="orders-page">
        <h1><?= $isAdmin ? 'Todos los Pedidos' : 'Mis Pedidos' ?></h1>

        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <i class="fas fa-box-open"></i>
                <p>No se encontraron pedidos</p>
                <a href="/" class="btn btn-primary">Ir a comprar</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span class="order-id">Pedido #<?= $order['id'] ?></span>
                            <span class="order-date"><?= date('d/m/Y', strtotime($order['created_at'])) ?></span>
                            <span class="order-status <?= strtolower($order['status']) ?>"><?= ucfirst($order['status']) ?></span>
                        </div>

                        <div class="order-body">
                            <div class="order-items-count">
                                <i class="fas fa-box"></i>
                                <?= count($order['items']) ?> producto<?= count($order['items']) !== 1 ? 's' : '' ?>
                            </div>

                            <div class="order-total">
                                Total: S/ <?= number_format($order['total'], 2) ?>
                            </div>
                        </div>

                        <div class="order-footer">
                            <a href="/order/view.php?id=<?= $order['id'] ?>" class="btn btn-outline">
                                Ver detalles
                            </a>

                            <?php if ($isAdmin && $order['status'] === 'pendiente'): ?>
                                <a href="/admin/process_order.php?id=<?= $order['id'] ?>" class="btn btn-primary">
                                    Procesar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>