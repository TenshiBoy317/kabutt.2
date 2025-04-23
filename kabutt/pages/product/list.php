<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/Product.php';

$baseUrl = '/kabutt/';
// Validación y saneamiento de parámetros
$category = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : null;
$filters = [
    'gender' => isset($_GET['gender']) ? htmlspecialchars($_GET['gender']) : null,
    'color' => isset($_GET['color']) ? htmlspecialchars($_GET['color']) : null,
    'size' => isset($_GET['size']) ? htmlspecialchars($_GET['size']) : null,
    'min_price' => isset($_GET['min_price']) ? (float)$_GET['min_price'] : null,
    'max_price' => isset($_GET['max_price']) ? (float)$_GET['max_price'] : null,
    'sort' => isset($_GET['sort']) ? htmlspecialchars($_GET['sort']) : null,
    'limit' => 12,
    'offset' => isset($_GET['page']) ? max(0, ((int)$_GET['page'] - 1) * 12) : 0
];

try {
    $product = new Product();
    $products = $product->getProducts($category, $filters);

    // Obtener opciones de filtros disponibles
    $availableSizes = $product->getAvailableSizes($category);
    $availableColors = $product->getAvailableColors($category);

    // Obtener total de productos sin paginación
    $countFilters = $filters;
    unset($countFilters['limit']);
    unset($countFilters['offset']);
    $totalProducts = count($product->getProducts($category, $countFilters));

    $totalPages = ceil($totalProducts / 12);
    $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

    // Validar que la página actual no exceda el total
    if ($totalPages > 0 && $currentPage > $totalPages) {
        $currentPage = $totalPages;
        $filters['offset'] = ($currentPage - 1) * 12;
        $products = $product->getProducts($category, $filters);
    }
} catch (Exception $e) {
    error_log("Error al obtener productos: " . $e->getMessage());
    $products = [];
    $totalProducts = 0;
    $totalPages = 1;
    $currentPage = 1;
    $availableSizes = [];
    $availableColors = [];
}
?>

    <div class="products-container">
        <!-- Filtros en el lado izquierdo -->
        <div class="filters-sidebar">
            <h2 class="filter-title">Filtros</h2>

            <!-- Filtro por género -->
            <div class="filter-section">
                <h3 class="filter-subtitle">Género</h3>
                <form method="get" class="filter-form">
                    <input type="hidden" name="page" value="products">
                    <?php if ($category): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                    <?php endif; ?>

                    <div class="filter-option">
                        <input type="radio" id="gender_all" name="gender" value="" <?= empty($filters['gender']) ? 'checked' : '' ?>>
                        <label for="gender_all">Todos</label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" id="gender_hombre" name="gender" value="hombre" <?= $filters['gender'] === 'hombre' ? 'checked' : '' ?>>
                        <label for="gender_hombre">Hombre</label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" id="gender_mujer" name="gender" value="mujer" <?= $filters['gender'] === 'mujer' ? 'checked' : '' ?>>
                        <label for="gender_mujer">Mujer</label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" id="gender_nino" name="gender" value="niño" <?= $filters['gender'] === 'niño' ? 'checked' : '' ?>>
                        <label for="gender_nino">Niño</label>
                    </div>
                </form>
            </div>

            <!-- Filtro por precio -->
            <div class="filter-section">
                <h3 class="filter-subtitle">Rango de Precio</h3>
                <form method="get" class="filter-form">
                    <input type="hidden" name="page" value="products">
                    <?php if ($category): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                    <?php endif; ?>

                    <div class="price-range">
                        <input type="range" id="price-range" name="price_range" min="0" max="1000" step="50"
                               value="<?= $filters['max_price'] ?? 500 ?>" onchange="updatePriceValue(this.value)">
                        <div class="price-values">
                            <span>S/ 0</span>
                            <span id="price-value">S/ <?= $filters['max_price'] ?? 500 ?></span>
                        </div>
                        <input type="hidden" name="max_price" id="max_price" value="<?= $filters['max_price'] ?? '' ?>">
                        <button type="submit" class="apply-filter">Aplicar</button>
                    </div>
                </form>
            </div>

            <!-- Filtro por color -->
            <div class="filter-section">
                <h3 class="filter-subtitle">Color</h3>
                <form method="get" class="filter-form">
                    <input type="hidden" name="page" value="products">
                    <?php if ($category): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                    <?php endif; ?>

                    <?php foreach ($availableColors as $color): ?>
                        <div class="filter-option">
                            <input type="checkbox" id="color_<?= htmlspecialchars(strtolower($color)) ?>"
                                   name="color[]" value="<?= htmlspecialchars($color) ?>"
                                <?= in_array($color, (array)$filters['color']) ? 'checked' : '' ?>>
                            <label for="color_<?= htmlspecialchars(strtolower($color)) ?>"><?= htmlspecialchars($color) ?></label>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="apply-filter">Aplicar</button>
                </form>
            </div>

            <!-- Filtro por talla -->
            <div class="filter-section">
                <h3 class="filter-subtitle">Talla</h3>
                <form method="get" class="filter-form">
                    <input type="hidden" name="page" value="products">
                    <?php if ($category): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                    <?php endif; ?>

                    <div class="size-options">
                        <?php foreach ($availableSizes as $size): ?>
                            <button type="submit" name="size" value="<?= htmlspecialchars($size) ?>"
                                    class="size-option <?= $filters['size'] === $size ? 'selected' : '' ?>">
                                <?= htmlspecialchars($size) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Productos en el lado derecho -->
        <div class="products-main">
            <div class="products-header">
                <h1><?= ucfirst($category ?? 'Todos los productos') ?> (<?= $totalProducts ?>)</h1>


            </div>

            <div class="product-grid">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <?php
                        $price = isset($product['price']) && is_numeric($product['price']) ? (float)$product['price'] : 0;
                        $productId = isset($product['id']) ? (int)$product['id'] : 0;
                        $imagePath = !empty($product['images'][0]['image_path']) ? htmlspecialchars($product['images'][0]['image_path']) : 'default-product.jpg';
                        ?>
                        <div class="product-card">
                            <a href="<?= $baseUrl ?>?page=product&id=<?= $productId ?>">
                                <img src="<?= $baseUrl ?>assets/uploads/products/<?= $imagePath ?>" alt="<?= htmlspecialchars($product['name'] ?? 'Producto') ?>" class="product-image">
                                <div class="product-info">
                                    <h3 class="product-title"><?= htmlspecialchars($product['name'] ?? 'Producto sin nombre') ?></h3>
                                    <p class="product-price">S/ <?= number_format($price, 2) ?></p>
                                    <?php if (!empty($product['sizes'])): ?>
                                        <div class="product-sizes">
                                            <?php foreach ($product['sizes'] as $size): ?>
                                                <span class="size-tag"><?= htmlspecialchars($size) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>No se encontraron productos con los filtros seleccionados.</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?<?= htmlspecialchars(http_build_query(array_merge($_GET, ['page' => $currentPage - 1]))) ?>" class="page-link">&laquo; Anterior</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?<?= htmlspecialchars(http_build_query(array_merge($_GET, ['page' => $i]))) ?>" class="page-link <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?<?= htmlspecialchars(http_build_query(array_merge($_GET, ['page' => $currentPage + 1]))) ?>" class="page-link">Siguiente &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function updatePriceValue(value) {
            document.getElementById('price-value').textContent = 'S/ ' + value;
            document.getElementById('max_price').value = value;
        }
    </script>

    <style>
        .products-container {
            display: flex;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .filters-sidebar {
            width: 250px;
            background: #f8f8f8;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .products-main {
            flex: 1;
        }

        .filter-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .filter-subtitle {
            font-size: 1.1rem;
            margin: 15px 0 10px;
            color: #555;
        }

        .filter-option {
            margin: 8px 0;
        }

        .filter-option input[type="checkbox"],
        .filter-option input[type="radio"] {
            margin-right: 8px;
        }

        .price-range {
            margin: 15px 0;
        }

        .price-range input[type="range"] {
            width: 100%;
            margin-bottom: 10px;
        }

        .price-values {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: #666;
        }

        .size-options {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .size-option {
            padding: 5px 10px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .size-option.selected {
            background: #333;
            color: white;
            border-color: #333;
        }

        .apply-filter {
            margin-top: 10px;
            padding: 5px 10px;
            background: #333;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .product-card {
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-info {
            padding: 15px;
        }

        .product-title {
            font-size: 1rem;
            margin: 0 0 5px;
            color: #333;
        }

        .product-price {
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .product-sizes {
            margin-top: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .size-tag {
            font-size: 0.7rem;
            padding: 2px 5px;
            background: #f0f0f0;
            border-radius: 3px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 5px;
        }

        .page-link {
            padding: 5px 10px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
        }

        .page-link.active {
            background: #333;
            color: white;
            border-color: #333;
        }

        .no-products {
            text-align: center;
            padding: 50px;
            font-size: 1.2rem;
            color: #666;
        }
    </style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>