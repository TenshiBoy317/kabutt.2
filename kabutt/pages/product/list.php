<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/Product.php';

$category = isset($_GET['category']) ? $_GET['category'] : null;
$filters = [
    'gender' => $_GET['gender'] ?? null,
    'min_price' => $_GET['min_price'] ?? null,
    'max_price' => $_GET['max_price'] ?? null,
    'sort' => $_GET['sort'] ?? null,
    'limit' => 12,
    'offset' => isset($_GET['page']) ? ($_GET['page'] - 1) * 12 : 0
];

$product = new Product();
$products = $product->getProducts($category, $filters);
$totalProducts = count($product->getProducts($category, array_diff_key($filters, ['limit'=>0, 'offset'=>0])));
$totalPages = ceil($totalProducts / 12);
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
?>

    <div class="products-header">
        <h1><?= ucfirst($category ?? 'Todos los productos') ?></h1>

        <div class="filters">
            <form method="get" class="filter-form">
                <input type="hidden" name="page" value="products">
                <?php if ($category): ?>
                    <input type="hidden" name="category" value="<?= $category ?>">
                <?php endif; ?>

                <select name="sort" onchange="this.form.submit()">
                    <option value="">Ordenar por</option>
                    <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Más nuevos</option>
                    <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Precio: menor a mayor</option>
                    <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
                </select>

                <select name="gender" onchange="this.form.submit()">
                    <option value="">Todos los géneros</option>
                    <option value="hombre" <?= $filters['gender'] === 'hombre' ? 'selected' : '' ?>>Hombre</option>
                    <option value="mujer" <?= $filters['gender'] === 'mujer' ? 'selected' : '' ?>>Mujer</option>
                    <option value="niño" <?= $filters['gender'] === 'niño' ? 'selected' : '' ?>>Niño</option>
                </select>
            </form>
        </div>
    </div>

    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <a href="/?page=product&id=<?= $product['id'] ?>">
                    <img src="/assets/uploads/<?= $product['images'][0]['image_path'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                    <div class="product-info">
                        <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-price">S/ <?= number_format($product['price'], 2) ?></p>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>