<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/Cart.php';
require_once __DIR__ . '/../../classes/Order.php';
require_once __DIR__ . '/../../includes/auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header("Location: /?page=login");
    exit;
}

$cart = new Cart();
$orderObj = new Order();
$user = $auth->getUser();
$cartItems = $cart->getCartItems();
$subtotal = $cart->getCartTotal();

$errors = [];

if (empty($cartItems)) {
    header("Location: /?page=cart");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingData = [
        'address' => trim($_POST['address']),
        'phone' => trim($_POST['phone'])
    ];

    $paymentMethod = $_POST['payment_method'];

    // Validar datos
    if (empty($shippingData['address'])) {
        $errors[] = "La dirección de envío es requerida";
    }

    if (empty($shippingData['phone'])) {
        $errors[] = "El teléfono de contacto es requerido";
    }

    if (empty($paymentMethod) || !in_array($paymentMethod, ['tarjeta', 'efectivo', 'yape', 'plin'])) {
        $errors[] = "Seleccione un método de pago válido";
    }

    if (empty($errors)) {
        // Crear orden
        $orderId = $orderObj->createOrder($_SESSION['user_id'], $cartItems, $shippingData, $paymentMethod);

        if ($orderId) {
            // Vaciar carrito
            $cart->clearCart();

            // Redirigir a la página de confirmación
            header("Location: /?page=order_confirmation&id=$orderId");
            exit;
        } else {
            $errors[] = "Error al procesar la orden. Por favor, inténtelo de nuevo.";
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
            <div class="form-group">
                <label for="first_name">Nombres</label>
                <input type="text" id="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" readonly>
            </div>

            <div class="form-group">
                <label for="last_name">Apellidos</label>
                <input type="text" id="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" readonly>
            </div>

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
            </div>

            <div class="form-group">
                <label for="address">Dirección de Envío</label>
                <textarea id="address" name="address" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="phone">Teléfono de Contacto</label>
                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
            </div>

            <div class="payment-methods">
                <h2>Método de Pago</h2>

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

            <div class="order-summary">
                <h2>Resumen de Pedido</h2>

                <?php foreach ($cartItems as $item): ?>
                    <div class="order-summary-item">
                        <div>
                            <h4><?= htmlspecialchars($item['name']) ?></h4>
                            <p>Talla: <?= $item['size'] ?> | Color: <?= $item['color'] ?></p>
                            <p>Cantidad: <?= $item['quantity'] ?></p>
                        </div>
                        <span>S/ <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                    </div>
                <?php endforeach; ?>

                <div class="order-summary-total">
                    <span>Total:</span>
                    <span>S/ <?= number_format($subtotal, 2) ?></span>
                </div>

                <button type="submit" class="btn btn-block">Confirmar Pedido</button>
            </div>
        </form>
    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>