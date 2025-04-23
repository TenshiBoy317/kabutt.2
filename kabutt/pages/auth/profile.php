<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Order.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header("Location: /?page=login");
    exit;
}

$baseUrl = '/kabutt/';
$userObj = new User();
$orderObj = new Order();
$user = $userObj->getUserById($_SESSION['user_id']);
$orders = $orderObj->getUserOrders($_SESSION['user_id']);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updateData = [
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'email' => trim($_POST['email']),
        'address' => trim($_POST['address']),
        'phone' => trim($_POST['phone'])
    ];

    $result = $userObj->updateProfile($_SESSION['user_id'], $updateData);

    if ($result['success']) {
        // Recargar datos del usuario
        $user = $userObj->getUserById($_SESSION['user_id']);
    } else {
        $errors = $result['errors'];
    }
}
?>

    <div class="profile-container">
        <div class="profile-sidebar">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h3><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                <p><?= htmlspecialchars($user['email']) ?></p>
            </div>

            <nav class="profile-nav">
                <a href="<?= $baseUrl?>?page=profile" class="active"><i class="fas fa-user"></i> Mi Perfil</a>
                <a href="<?= $baseUrl?>?page=orders"><i class="fas fa-shopping-bag"></i> Mis Pedidos</a>
                <a href="<?= $baseUrl?>logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </nav>
        </div>

        <div class="profile-content">
            <h2>Mi Perfil</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="profile-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">Nombres</label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Apellidos</label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="address">Dirección</label>
                    <textarea id="address" name="address"><?= htmlspecialchars($user['address']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="phone">Teléfono</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                </div>

                <button type="submit" class="btn">Guardar Cambios</button>
            </form>

            <div class="orders-section">
                <h3>Mis Pedidos Recientes</h3>

                <?php if (empty($orders)): ?>
                    <p>No has realizado ningún pedido todavía.</p>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach (array_slice($orders, 0, 3) as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <span class="order-id">Pedido #<?= $order['id'] ?></span>
                                    <span class="order-date"><?= date('d/m/Y', strtotime($order['created_at'])) ?></span>
                                    <span class="order-status <?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                                </div>

                                <div class="order-products">
                                    <?php foreach (array_slice($order['items'], 0, 2) as $item): ?>
                                        <div class="order-product">
                                            <img src="/assets/uploads/<?= $item['image_path'] ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                            <div class="product-info">
                                                <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                                                <p>Talla: <?= $item['size'] ?> | Color: <?= $item['color'] ?></p>
                                                <p>Cantidad: <?= $item['quantity'] ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <?php if (count($order['items']) > 2): ?>
                                        <div class="more-items">
                                            +<?= count($order['items']) - 2 ?> más productos
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="order-footer">
                                    <span class="order-total">Total: S/ <?= number_format($order['total'], 2) ?></span>
                                    <a href="/?page=order&id=<?= $order['id'] ?>" class="btn btn-outline">Ver Detalles</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <a href="/?page=orders" class="btn btn-outline">Ver Todos los Pedidos</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>