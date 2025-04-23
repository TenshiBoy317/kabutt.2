<?php
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/admin_header.php';

// Verificar autenticación y rol de admin
$auth = new Auth();

if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header("Location: /kabutt/?page=login");
    exit();
}

require_once __DIR__ . '/../../../classes/Order.php';

$orderObj = new Order();

// Filtros
$filters = [];
if (isset($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}

if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'];
}

if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'];
}

// Paginación
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$filters['limit'] = $itemsPerPage;
$filters['offset'] = ($currentPage - 1) * $itemsPerPage;

$orders = $orderObj->getAllOrders($filters);
$totalOrders = $orderObj->getOrdersCount($filters);
$totalPages = ceil($totalOrders / $itemsPerPage);
?>

    <div class="admin-orders-container">
        <div class="admin-header">
            <h1>Gestión de Pedidos</h1>

            <div class="filters">
                <form method="get" class="filter-form">
                    <input type="hidden" name="page" value="admin/orders">

                    <select name="status" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" <?= isset($filters['status']) && $filters['status'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="procesando" <?= isset($filters['status']) && $filters['status'] === 'procesando' ? 'selected' : '' ?>>Procesando</option>
                        <option value="enviado" <?= isset($filters['status']) && $filters['status'] === 'enviado' ? 'selected' : '' ?>>Enviado</option>
                        <option value="entregado" <?= isset($filters['status']) && $filters['status'] === 'entregado' ? 'selected' : '' ?>>Entregado</option>
                    </select>

                    <input type="date" name="date_from" value="<?= $filters['date_from'] ?? '' ?>" placeholder="Desde">
                    <input type="date" name="date_to" value="<?= $filters['date_to'] ?? '' ?>" placeholder="Hasta">

                    <button type="submit" class="btn">Filtrar</button>
                </form>
            </div>
        </div>

        <div class="orders-table-container">
            <table class="admin-table">
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
                <?php foreach ($orders as $order): ?>
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
                            <a href="/?page=admin/orders/view&id=<?= $order['id'] ?>" class="btn btn-sm">Ver</a>
                            <a href="/?page=admin/orders/edit&id=<?= $order['id'] ?>" class="btn btn-sm btn-edit">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>" class="page-link">&laquo; Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="page-link <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>" class="page-link">Siguiente &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>