<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/Product.php';

$productObj = new Product();
$baseUrl = '/kabutt/';
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

// Obtener productos relacionados (con manejo seguro si no existen)
$related_products = $productObj->getRelatedProducts($productId) ?? [];
?>

    <!-- Agrega esto en tu archivo CSS o en el head -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <div class="product-container">
        <!-- Galería de imágenes (izquierda) -->
        <div class="product-gallery">
            <div class="main-image">
                <img src="<?= $baseUrl ?>assets/uploads/products/<?= $mainImage['image_path'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>

            <?php if (count($images) > 1): ?>
                <div class="thumbnails">
                    <?php foreach ($images as $image): ?>
                        <img src="<?= $baseUrl ?>assets/uploads/products/<?= $image['image_path'] ?>" alt="Miniatura" class="<?= $image['is_main'] ? 'active' : '' ?>">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Detalles del producto (derecha) -->
        <div class="product-details">
            <nav class="breadcrumb">
                <a href="#">Novedades</a> > <a href="#"><?= ucfirst($product['category']) ?></a> > <span><?= htmlspecialchars($product['name']) ?></span>
            </nav>

            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>

            <div class="product-categories">
                <span class="category-tag"><?= ucfirst($product['gender']) ?></span>
                <span class="category-tag"><?= ucfirst($product['category']) ?></span>
            </div>

            <div class="price-section">
                S/ <?= number_format($product['price'], 2) ?>
            </div>

            <div class="shipping-info">
                <div>
                    <i class="fas fa-truck"></i>
                    <span>Envío gratis</span>
                </div>
                <div>
                    <i class="fas fa-undo"></i>
                    <span>Devoluciones gratuitas</span>
                </div>
            </div>

            <div class="ratings-section">
                <div class="stars">★★★★★</div>
                <span>(4.8)</span>
                <a href="#reviews" class="review-link">Ver 125 valoraciones</a>
            </div>

            <!-- Selector de colores como en la imagen -->
            <div class="color-selector">
                <h3>Color</h3>
                <div class="color-options">
                    <?php foreach ($colors as $color):
                        // Mapear nombres de colores a clases CSS
                        $colorClass = strtolower($color);
                        if ($colorClass === 'bianco') $colorClass = 'white';
                        if ($colorClass === 'gris') $colorClass = 'gray';
                        if ($colorClass === 'amarillo') $colorClass = 'yellow';
                        if ($colorClass === 'verde') $colorClass = 'green';
                        if ($colorClass === 'azul') $colorClass = 'blue';
                        if ($colorClass === 'negro') $colorClass = 'black';
                        ?>
                        <div class="color-option <?= $colorClass ?>"
                             style="background-color: <?= $colorClass === 'white' ? '#fff' : $colorClass ?>;"
                             data-color="<?= $color ?>"
                             title="<?= $color ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="color" id="selected-color">
            </div>

            <!-- Selector de tallas -->
            <div class="size-selector">

                <div class="size-grid">
                    <?php
                    // Determinar el rango de tallas según la categoría
                    $isKidsCategory = $product['category'] === 'niños';
                    $sizeRange = $isKidsCategory ? range(26, 37) : range(37, 44);

                    // Mostrar todas las tallas en el rango
                    foreach ($sizeRange as $size):
                        $sizeFormatted = 'PR '.number_format($size, $size == intval($size) ? 0 : 1);
                        $variant = array_filter($variants, function($v) use ($size) {
                            return $v['size'] == $size;
                        });
                        $inStock = !empty($variant) && reset($variant)['stock'] > 0;
                        ?>
                        <button type="button"
                                class="size-option <?= !$inStock ? 'out-of-stock' : '' ?>"
                                data-size="<?= $size ?>"
                            <?= !$inStock ? 'disabled' : '' ?>>
                            <?= $sizeFormatted ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="size" id="selected-size">
            </div>

            <!-- Botones de acción -->
            <div class="action-buttons">
                <button type="button" class="add-to-cart-btn">Añadir al carrito</button>
                <button type="button" class="favorite-btn">❤ Favorito</button>
            </div>

            <!-- Descripción del producto -->
            <div class="product-description">
                <h3>Descripción</h3>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>
        </div>
    </div>

<?php if (!empty($related_products)): ?>
    <section class="related-products">
        <h2>Productos Relacionados</h2>
        <div class="product-grid">
            <?php foreach ($related_products as $related): ?>
                <div class="product-card">
                    <a href="<?= $baseUrl ?>?page=product&id=<?= $related['id'] ?>">
                        <img src="<?= $baseUrl ?>assets/uploads/products/<?= $related['main_image'] ?>" alt="<?= htmlspecialchars($related['name']) ?>" class="product-image">
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

    <style>
        /* Estilos generales */
        .product-container {
            display: flex;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #333;
            gap: 40px;
        }

        /* Galería de imágenes (lado izquierdo) */
        .product-gallery {
            flex: 1;
            max-width: 500px;
        }

        .main-image img {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .thumbnails {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .thumbnails img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #ddd;
        }

        .thumbnails img.active,
        .thumbnails img:hover {
            border-color: #000;
        }

        /* Detalles del producto (lado derecho) */
        .product-details {
            flex: 1;
            padding: 0 20px;
        }

        .breadcrumb {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .breadcrumb a {
            color: #0066cc;
            text-decoration: none;
        }

        .product-title {
            font-size: 28px;
            font-weight: 700;
            margin: 10px 0 15px;
        }

        .product-categories {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .category-tag {
            background-color: #f0f0f0;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
        }

        .price-section {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
        }

        .shipping-info {
            display: flex;
            gap: 20px;
            margin: 20px 0;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .shipping-info div {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .shipping-info i {
            color: #0066cc;
        }

        .ratings-section {
            margin: 20px 0;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .stars {
            color: #ffc107;
            font-size: 18px;
        }

        .review-link {
            color: #0066cc;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            margin-top: 5px;
        }

        /* Selector de colores */
        .color-selector {
            margin: 20px 0;
        }

        .color-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .color-option {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            position: relative;
        }

        .color-option.selected {
            border-color: #000;
        }

        .color-option.selected::after {
            content: '✓';
            color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 14px;
            text-shadow: 0 0 2px #000;
        }

        /* Selector de tallas */
        .size-selector {
            margin: 25px 0;
        }

        .size-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .size-guide-link a {
            color: #0066cc;
            font-size: 14px;
            text-decoration: none;
        }

        .size-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin-top: 15px;
        }

        .size-option {
            padding: 10px;
            border: 1px solid #ddd;
            background: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
            position: relative;
        }

        .size-option:hover:not(.out-of-stock) {
            border-color: #000;
        }

        .size-option.selected {
            background-color: #000;
            color: white;
            border-color: #000;
        }

        .size-option.out-of-stock {
            color: #999;
            background-color: #f5f5f5;
            border-color: #ddd;
            cursor: not-allowed;
            text-decoration: line-through;
            position: relative;
        }

        .size-option.out-of-stock::after {
            position: absolute;
            bottom: -18px;
            left: 0;
            right: 0;
            font-size: 10px;
            color: #d82c2c;
            text-decoration: none;
        }

        .size-option:hover {
            border-color: #000;
        }

        .size-option.selected {
            background-color: #000;
            color: white;
            border-color: #000;
        }

        /* Botones de acción */
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin: 30px 0;
        }

        .add-to-cart-btn {
            padding: 15px;
            background-color: #000;
            color: white;
            border: none;
            border-radius: 30px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            margin-bottom: 2px;
        }

        .add-to-cart-btn:hover {
            background-color: #333;
        }

        .favorite-btn {
            padding: 15px;
            background: white;
            color: black;
            border: 1px solid #ccc;
            border-radius: 30px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.2s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 2px;
        }

        .favorite-btn:hover {
            background-color: #f5f5f5;
        }

        .favorite-btn.active {
            color: black;
            background-color: #f5f5f5;
            border-color: #ccc;
        }
        .favorite-btn i {
            font-size: 16px;
        }

        /* Productos relacionados */
        .related-products {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #eee;
        }

        .related-products h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .product-card {
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .product-info {
            padding: 15px;
        }

        .product-title {
            font-size: 16px;
            margin: 0 0 10px;
        }

        .product-price {
            font-weight: bold;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cambiar imagen principal al hacer clic en miniaturas
            const thumbnails = document.querySelectorAll('.thumbnails img');
            const mainImage = document.querySelector('.main-image img');

            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    mainImage.src = this.src;
                    thumbnails.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Selección de colores
            const colorOptions = document.querySelectorAll('.color-option');
            colorOptions.forEach(option => {
                option.addEventListener('click', function() {
                    colorOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    document.getElementById('selected-color').value = this.dataset.color;
                });
            });

            // Selección de tallas
            const sizeOptions = document.querySelectorAll('.size-option');
            sizeOptions.forEach(option => {
                option.addEventListener('click', function() {
                    sizeOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    document.getElementById('selected-size').value = this.dataset.size;
                });
            });
// Selección de tallas (solo si no están agotadas)
            document.querySelectorAll('.size-option:not(.out-of-stock)').forEach(option => {
                option.addEventListener('click', function() {
                    document.querySelectorAll('.size-option').forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    this.classList.add('selected');
                    document.getElementById('selected-size').value = this.dataset.size;
                });
            });
            // Botón favorito
            const favoriteBtn = document.querySelector('.favorite-btn');
            const favoriteIcon = favoriteBtn.querySelector('i');

            favoriteBtn.addEventListener('click', function() {
                this.classList.toggle('active');
                if (this.classList.contains('active')) {
                    favoriteIcon.classList.remove('far');
                    favoriteIcon.classList.add('fas');
                } else {
                    favoriteIcon.classList.remove('fas');
                    favoriteIcon.classList.add('far');
                }
            });

            // Añadir al carrito
            const addToCartBtn = document.querySelector('.add-to-cart-btn');
            addToCartBtn.addEventListener('click', function() {
                const color = document.getElementById('selected-color').value;
                const size = document.getElementById('selected-size').value;

                if (!color) {
                    alert('Por favor selecciona un color');
                    return;
                }

                if (!size) {
                    alert('Por favor selecciona una talla');
                    return;
                }

                const formData = new FormData();
                formData.append('product_id', <?= $productId ?>);
                formData.append('color', color);
                formData.append('size', size);
                formData.append('quantity', 1); // Cantidad fija por ahora

                fetch('<?= $baseUrl ?>../cart/add', {
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
                                cartCount.textContent = parseInt(cartCount.textContent) + 1;
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
        });
    </script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>