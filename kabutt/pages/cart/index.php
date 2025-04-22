<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/Cart.php';
require_once __DIR__ . '/../../includes/auth.php';

$auth = new Auth();
$cart = new Cart();
$cartItems = $cart->getCartItems();
$subtotal = 0;
?>

    <div class="cart-container">
        <h1>Tu Carrito</h1>

        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <p>Tu carrito está vacío</p>
                <a href="/" class="btn btn-outline">Continuar comprando</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <table>
                    <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Talla</th>
                        <th>Color</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cartItems as $item):
                        $itemTotal = $item['price'] * $item['quantity'];
                        $subtotal += $itemTotal;
                        ?>
                        <tr>
                            <td>
                                <div class="cart-product-info">
                                    <img src="/assets/uploads/<?= $item['image_path'] ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                    <div>
                                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                                        <a href="/?page=product&id=<?= $item['product_id'] ?>">Ver producto</a>
                                    </div>
                                </div>
                            </td>
                            <td><?= $item['size'] ?></td>
                            <td><?= $item['color'] ?></td>
                            <td>S/ <?= number_format($item['price'], 2) ?></td>
                            <td>
                                <form method="post" action="/cart/update" class="update-quantity-form">
                                    <input type="hidden" name="cart_item_id" value="<?= $item['id'] ?>">
                                    <input type="number" name="quantity" min="1" max="<?= $item['max_stock'] ?>" value="<?= $item['quantity'] ?>">
                                    <button type="submit" class="btn btn-sm">Actualizar</button>
                                </form>
                            </td>
                            <td>S/ <?= number_format($itemTotal, 2) ?></td>
                            <td>
                                <form method="post" action="/cart/remove">
                                    <input type="hidden" name="cart_item_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-delete">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-summary">
                <div class="summary-details">
                    <h3>Resumen de Compra</h3>

                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>S/ <?= number_format($subtotal, 2) ?></span>
                    </div>

                    <div class="summary-row">
                        <span>Envío:</span>
                        <span>Gratis</span>
                    </div>

                    <div class="summary-row total">
                        <span>Total:</span>
                        <span>S/ <?= number_format($subtotal, 2) ?></span>
                    </div>

                    <?php if ($auth->isLoggedIn()): ?>
                        <a href="/?page=cart/checkout" class="btn btn-block checkout-btn">Proceder al Pago</a>
                    <?php else: ?>
                        <div class="checkout-login">
                            <p>Para continuar con la compra, inicia sesión o regístrate</p>
                            <div class="auth-buttons">
                                <a href="/?page=login" class="btn">Iniciar Sesión</a>
                                <a href="/?page=register" class="btn btn-outline">Registrarse</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>