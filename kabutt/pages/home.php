<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/Product.php';

$product = new Product();
$baseUrl = '/kabutt/';

try {
    $newProducts = $product->getProducts('novedades', ['limit' => 8]);
    $menProducts = $product->getProducts('hombres', ['limit' => 4]);
    $womenProducts = $product->getProducts('mujeres', ['limit' => 4]);
    $featuredProducts = $product->getProducts('destacados', ['limit' => 2]);
} catch (Exception $e) {
    error_log("Error al obtener productos: " . $e->getMessage());
    $newProducts = $menProducts = $womenProducts = $featuredProducts = [];
}
?>

    <div class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>Calzado de Tendencia</h1>
                <p>Descubre las últimas novedades en calzado para esta temporada</p>
                <a href="<?= $baseUrl ?>?page=products&category=novedades" class="btn">Ver Colección</a>
            </div>
        </div>
    </div>

    <!-- Sección de Novedades -->
    <section class="featured-section">
        <div class="container">
            <h2>Novedades</h2>
            <div class="product-grid">
                <?php foreach ($newProducts as $product): ?>
                    <div class="product-card">
                        <a href="<?= $baseUrl ?>?page=product&id=<?= $product['id'] ?>">
                            <?php if (!empty($product['images'][0]['image_path'])): ?>
                                <img src="<?= $baseUrl ?>assets/uploads/products/<?= $product['images'][0]['image_path'] ?>"
                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                     class="product-image"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="image-placeholder">Imagen no disponible</div>
                            <?php endif; ?>
                            <div class="product-info">
                                <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="product-price">S/ <?= number_format($product['price'], 2) ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Sección de Hombres -->
    <section class="category-section">
        <div class="container">
            <h2>Hombres</h2>
            <div class="product-grid">
                <?php foreach ($menProducts as $product): ?>
                    <div class="product-card">
                        <a href="<?= $baseUrl ?>?page=product&id=<?= $product['id'] ?>">
                            <?php if (!empty($product['images'][0]['image_path'])): ?>
                                <img src="<?= $baseUrl ?>assets/uploads/products/<?= $product['images'][0]['image_path'] ?>"
                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                     class="product-image"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="image-placeholder">Imagen no disponible</div>
                            <?php endif; ?>
                            <div class="product-info">
                                <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="product-price">S/ <?= number_format($product['price'], 2) ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <a href="<?= $baseUrl ?>?page=products&category=hombres" class="btn btn-outline">Ver más</a>
            </div>
        </div>
    </section>

    <!-- Sección de Mujeres -->
    <section class="category-section">
        <div class="container">
            <h2>Mujeres</h2>
            <div class="product-grid">
                <?php foreach ($womenProducts as $product): ?>
                    <div class="product-card">
                        <a href="<?= $baseUrl ?>?page=product&id=<?= $product['id'] ?>">
                            <?php if (!empty($product['images'][0]['image_path'])): ?>
                                <img src="<?= $baseUrl ?>assets/uploads/products/<?= $product['images'][0]['image_path'] ?>"
                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                     class="product-image"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="image-placeholder">Imagen no disponible</div>
                            <?php endif; ?>
                            <div class="product-info">
                                <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="product-price">S/ <?= number_format($product['price'], 2) ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <a href="<?= $baseUrl ?>?page=products&category=mujeres" class="btn btn-outline">Ver más</a>
            </div>
        </div>
    </section>

    <!-- Sección de ofertas -->
    <section class="offers-section">
        <div class="container">
            <h2>Ofertas</h2>
            <div class="offer-tags">
                <span class="offer-tag">12% OFF</span>
                <span class="offer-tag">Air Force 1</span>
                <span class="offer-tag">Edición Limitada</span>
            </div>
        </div>
    </section>
    <style>

        /* Estilos generales para centrado */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Hero section centrada */
        .hero-section {
            text-align: center;
            padding: 4rem 0;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        /* Estilo para secciones de categorías */
        .categories-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
            margin: 3rem 0;
        }

        .category-card {
            width: 100%;
            max-width: 1200px;
            text-align: center;
            position: relative;
            margin-bottom: 2rem;
        }

        .category-card h3 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .category-card h3::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background-color: #000;
        }

        .category-products {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .category-products a {
            flex: 1;
            min-width: 200px;
            max-width: 250px;
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .category-products a:hover {
            transform: translateY(-5px);
        }

        .category-products img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 8px;
        }

        /* Estilo para el botón "Ver Todo" */
        .btn-outline {
            background: transparent;
            border: 2px solid #000;
            color: #000;
            padding: 0.5rem 1.5rem;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            background: #000;
            color: #fff;
        }

        /* Estilo para las novedades */
        .featured-section {
            text-align: center;
            margin: 4rem 0;
        }

        .featured-section h2 {
            font-size: 2rem;
            margin-bottom: 2rem;
            position: relative;
        }

        .featured-section h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: #000;
        }

        .product-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
        }

        .product-card {
            flex: 0 1 calc(25% - 2rem);
            min-width: 250px;
            text-align: center;
        }
        /* Estilos para la fila de categorías */
        .category-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 3rem;
        }

        /* Estilo para el overlay de productos */
        .category-product {
            position: relative;
            display: block;
            overflow: hidden;
            border-radius: 8px;
        }

        .product-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 1rem;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .category-product:hover .product-overlay {
            transform: translateY(0);
        }

        /* Estilo para la sección de ofertas */
        .offers-section {
            text-align: center;
            margin: 4rem 0;
            padding: 2rem 0;
            background: #f5f5f5;
        }

        .offer-tags {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .offer-tag {
            background: #000;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
    </style>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>