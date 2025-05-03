<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../classes/Cart.php';
require_once __DIR__ . '/../../classes/Order.php';
require_once __DIR__ . '/../../classes/User.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header("Location: /auth/login.php?redirect=checkout");
    exit;
}

$baseUrl = '/kabutt/';
$cartObj = new Cart();
$orderObj = new Order();
$userObj = new User();

// Obtener datos del usuario
$user = $userObj->getUserById($_SESSION['user_id']);

// Obtener el carrito del usuario
$cart = $cartObj->getUserCart($_SESSION['user_id']);

if (empty($cart['items'])) {
    header("Location: /cart");
    exit;
}

// Calcular subtotal
$subtotal = 0;
$cartItems = [];
foreach ($cart['items'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $cartItems[] = $item;
}

// Procesar el pago
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'] ?? '';
    $shippingAddress = $_POST['shipping_address'] ?? '';
    $contactPhone = $_POST['contact_phone'] ?? '';

    // Validaciones
    $errors = [];
    if (empty($paymentMethod)) {
        $errors[] = 'Seleccione un método de pago';
    }
    if (empty($shippingAddress)) {
        $errors[] = 'Ingrese una dirección de envío';
    }
    if (empty($contactPhone)) {
        $errors[] = 'Ingrese un número de contacto';
    }

    if (empty($errors)) {
        // Crear la orden
        $orderId = $orderObj->createOrder([
            'user_id' => $_SESSION['user_id'],
            'total' => $subtotal,
            'payment_method' => $paymentMethod,
            'shipping_address' => $shippingAddress,
            'contact_phone' => $contactPhone
        ], $cart['items']);

        if ($orderId) {
            // Vaciar el carrito
            $cartObj->clearCart($cart['id']);

            // Redirigir a confirmación
            header("Location: /order/confirm.php?id=".$orderId);
            exit;
        } else {
            $errors[] = 'Error al procesar el pedido';
        }
    }
}
?>

    <div class="checkout-container">
        <h1>Finalizar Compra</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="checkout-form">
            <div class="form-section">
                <h2>Información de Envío</h2>

                <div class="form-group">
                    <label for="first_name">Nombres</label>
                    <input type="text" id="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="last_name">Apellidos</label>
                    <input type="text" id="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="shipping_address">Dirección de Envío</label>
                    <textarea id="shipping_address" name="shipping_address" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="contact_phone">Teléfono de Contacto</label>
                    <input type="text" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-section">
                <h2>Método de Pago</h2>

                <div class="payment-methods">
                    <div class="payment-method">
                        <input type="radio" id="payment_card" name="payment_method" value="tarjeta" required>
                        <label for="payment_card">Tarjeta de Crédito/Débito</label>
                    </div>

                    <div class="payment-method">
                        <input type="radio" id="payment_cash" name="payment_method" value="efectivo">
                        <label for="payment_cash">Efectivo al recibir</label>
                    </div>

                    <div class="payment-method">
                        <input type="radio" id="payment_yape" name="payment_method" value="yape">
                        <label for="payment_yape">Yape</label>
                    </div>

                    <div class="payment-method">
                        <input type="radio" id="payment_plin" name="payment_method" value="plin">
                        <label for="payment_plin">Plin</label>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Resumen de Pedido</h2>

                <div class="order-summary-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="order-item">
                            <div class="item-info">
                                <h4><?= htmlspecialchars($item['product_name'] ?? $item['name']) ?></h4>
                                <p>Talla: <?= $item['size'] ?> | Color: <?= $item['color'] ?></p>
                                <p>Cantidad: <?= $item['quantity'] ?></p>
                            </div>
                            <span class="item-price">S/ <?= number_format(($item['price'] * $item['quantity']), 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-total">
                    <span>Total:</span>
                    <span>S/ <?= number_format($subtotal, 2) ?></span>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Confirmar Pedido</button>
            </div>
        </form>
    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>