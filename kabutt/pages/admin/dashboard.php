<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/admin_header.php';

// Verificar autenticación y rol de admin
$auth = new Auth();

if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header("Location: /kabutt/?page=login");
    exit();
}

require_once __DIR__ . '/../../classes/Order.php';
require_once __DIR__ . '/../../classes/Product.php';
require_once __DIR__ . '/../../classes/User.php';

$orderObj = new Order();
$productObj = new Product();
$userObj = new User();
$baseUrl = '/kabutt/';

// Obtener estadísticas
$stats = [
    'total_products' => $productObj->getTotalProductsCount(),
    'total_users' => $userObj->getTotalUsersCount(),
    'pending_orders' => $orderObj->getOrdersCountByStatus('pendiente'),
    'monthly_sales' => $orderObj->getMonthlySales()
];

// Obtener pedidos recientes
$recentOrders = $orderObj->getAllOrders(['limit' => 5]);
?>

    <div class="dashboard-container">
        <h1>Dashboard</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shoe-prints"></i>
                </div>
                <div class="stat-info">
                    <h3>Productos</h3>
                    <p><?= $stats['total_products'] ?></p>
                    <a href="<?= $baseUrl ?>?page=admin/products">Gestionar</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>Usuarios</h3>
                    <p><?= $stats['total_users'] ?></p>
                    <a href="<?= $baseUrl ?>?page=admin/users">Gestionar</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-info">
                    <h3>Pedidos Pendientes</h3>
                    <p><?= $stats['pending_orders'] ?></p>
                    <a href="<?= $baseUrl ?>?page=admin/orders">Gestionar</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3>Ventas Mensuales</h3>
                    <p>S/ <?= number_format($stats['monthly_sales'], 2) ?></p>
                    <a href="<?= $baseUrl ?>?page=admin/orders">Ver detalles</a>
                </div>
            </div>
        </div>

        <div class="dashboard-sections">
            <section class="recent-orders">
                <h2>Pedidos Recientes</h2>

                <?php if (empty($recentOrders)): ?>
                    <p>No hay pedidos recientes</p>
                <?php else: ?>
                    <table class="orders-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['username']) ?></td>
                                <td>S/ <?= number_format($order['total'], 2) ?></td>
                                <td>
                                    <span class="status-badge <?= $order['status'] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <a href="<?= $baseUrl ?>?page=admin/orders/view&id=<?= $order['id'] ?>" class="btn btn-sm">Ver</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <section class="quick-actions">
                <h2>Acciones Rápidas</h2>
                <div class="actions-grid">
                    <a href="<?= $baseUrl ?>?page=admin/products/add" class="action-card">
                        <i class="fas fa-plus"></i>
                        <span>Añadir Producto</span>
                    </a>

                    <a href="<?= $baseUrl ?>?page=admin/orders" class="action-card">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Gestionar Pedidos</span>
                    </a>

                    <a href="<?= $baseUrl ?>?page=admin/users" class="action-card">
                        <i class="fas fa-user-cog"></i>
                        <span>Gestionar Usuarios</span>
                    </a>

                    <a href="<?= $baseUrl ?>?page=admin/reports" class="action-card">
                        <i class="fas fa-chart-pie"></i>
                        <span>Ver Reportes</span>
                    </a>
                </div>
            </section>
        </div>
    </div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>