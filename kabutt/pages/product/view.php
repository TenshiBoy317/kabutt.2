<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/Product.php';

$productObj = new Product();
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = $productObj->getProductById($productId);

if (!$product) {
    header("Location: /404.php");
    exit;
}

$variants = $productObj->getProductVariants($productId);
$images = $productObj->getProductImages($productId);
$mainImage = array_filter($images, function($img) { return $img['is_main']; });
$mainImage = reset($mainImage);

// Obtener colores y tallas disponibles
$colors = array_unique(array_column($variants, 'color'));
$sizes = array_unique(array_column($variants, 'size'));
?>

    <div class="product-container">
        <div class="product-gallery">
            <div class="main-image">
                <img src="/assets/uploads/<?= $mainImage['image_path'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>

            <?php if (count($images) > 1): ?>
                <div class="thumbnails">
                    <?php foreach ($images as $image): ?>
                        <img src="/assets/uploads/<?= $image['image_path'] ?>" alt="Miniatura" data-main="<?= $image['is_main'] ? 'true' : 'false' ?>">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="product-details">
            <h1><?= htmlspecialchars($product['name']) ?></h1>

            <div class="product-price">
                S/ <?= number_format($product['price'], 2) ?>
            </div>

            <div class="product-meta">
                <div class="meta-item">
                    <span>Categoría:</span>
                    <strong><?= ucfirst($product['category']) ?></strong>
                </div>

                <?php if ($product['gender'] !== 'unisex'): ?>
                    <div class="meta-item">
                        <span>Género:</span>
                        <strong><?= ucfirst($product['gender']) ?></strong>
                    </div>
                <?php endif; ?>
            </div>

            <form method="post" action="/cart/add" class="add-to-cart-form">
                <input type="hidden" name="product_id" value="<?= $productId ?>">

                <?php if (!empty($colors)): ?>
                    <div class="form-group">
                        <label for="color">Color</label>
                        <select id="color" name="color" required>
                            <option value="">Selecciona un color</option>
                            <?php foreach ($colors as $color): ?>
                                <option value="<?= $color ?>"><?= $color ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (!empty($sizes)): ?>
                    <div class="form-group">
                        <label for="size">Talla</label>
                        <select id="size" name="size" required>
                            <option value="">Selecciona una talla</option>
                            <?php foreach ($sizes as $size): ?>
                                <option value="<?= $size ?>"><?= $size ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="quantity">Cantidad</label>
                    <input type="number" id="quantity" name="quantity" min="1" value="1" max="10">
                </div>

                <button type="submit" class="btn btn-block add-to-cart-btn">
                    Añadir al Carrito
                </button>
            </form>

            <div class="product-description">
                <h3>Descripción</h3>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>

            <div class="product-features">
                <h3>Características</h3>
                <ul>
                    <li>Material: Cuero sintético</li>
                    <li>Plantilla: Amortiguada</li>
                    <li>Suela: Antideslizante</li>
                    <li>Garantía: 3 meses</li>
                </ul>
            </div>
        </div>
    </div>

<?php if (count($product['related_products']) > 0): ?>
    <section class="related-products">
        <h2>Productos Relacionados</h2>

        <div class="product-grid">
            <?php foreach ($product['related_products'] as $related): ?>
                <div class="product-card">
                    <a href="/?page=product&id=<?= $related['id'] ?>">
                        <img src="/assets/uploads/<?= $related['main_image'] ?>" alt="<?= htmlspecialchars($related['name']) ?>" class="product-image">
                        <div class="product-info">
                            <h3 class="product-title"><?= htmlspecialchars($related['name']) ?></h3>
                            <p class="product-price">S/ <?= number_format($related['price'], 2) ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cambiar imagen principal al hacer clic en miniaturas
            const thumbnails = document.querySelectorAll('.thumbnails img');
            const mainImage = document.querySelector('.main-image img');

            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    mainImage.src = this.src;

                    // Actualizar estado activo
                    document.querySelectorAll('.thumbnails img').forEach(img => {
                        img.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });

            // Actualizar tallas disponibles según color seleccionado
            const colorSelect = document.getElementById('color');
            const sizeSelect = document.getElementById('size');

            if (colorSelect && sizeSelect) {
                const variants = <?= json_encode($variants) ?>;

                colorSelect.addEventListener('change', function() {
                    const selectedColor = this.value;
                    const availableSizes = variants
                        .filter(v => v.color === selectedColor)
                        .map(v => v.size);

                    // Actualizar opciones de talla
                    sizeSelect.innerHTML = '<option value="">Selecciona una talla</option>';

                    availableSizes.forEach(size => {
                        const option = document.createElement('option');
                        option.value = size;
                        option.textContent = size;
                        sizeSelect.appendChild(option);
                    });
                });
            }

            // Añadir al carrito
            const addToCartForm = document.querySelector('.add-to-cart-form');
            if (addToCartForm) {
                addToCartForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);

                    fetch('/cart/add', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Producto añadido al carrito');
                                // Actualizar contador del carrito
                                const cartCount = document.querySelector('.cart-count');
                                if (cartCount) {
                                    cartCount.textContent = parseInt(cartCount.textContent) + parseInt(formData.get('quantity'));
                                }
                            } else {
                                alert(data.message || 'Error al añadir al carrito');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error al procesar la solicitud');
                        });
                });
            }
        });
    </script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>