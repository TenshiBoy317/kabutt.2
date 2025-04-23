<?php
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/admin_header.php';

// Verificar autenticación y rol de admin
$auth = new Auth();
if (!$auth->isAdmin()) {
    header("Location: /kabutt/?page=login");
    exit();
}

require_once __DIR__ . '/../../../classes/Product.php';

$productObj = new Product();

// Obtener parámetros de paginación
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($currentPage - 1) * $perPage;

// Obtener productos con paginación
$filters = [
    'limit' => $perPage,
    'offset' => $offset
];

// Aplicar filtros si existen
if (!empty($_GET['category'])) {
    $filters['category'] = $_GET['category'];
}

if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

$products = $productObj->getAllProducts($filters);
$totalProducts = $productObj->getTotalProductsCount($filters);
$totalPages = ceil($totalProducts / $perPage);
?>

    <div class="admin-products-container">
        <h1>Gestión de Productos</h1>

        <div class="product-actions">
            <a href="/kabutt/?page=admin/products/add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Añadir Producto
            </a>

            <form method="get" class="search-form">
                <input type="hidden" name="page" value="admin/products">
                <input type="text" name="search" placeholder="Buscar productos..."
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <table class="products-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="7">No se encontraron productos</td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= $product['id'] ?></td>
                        <td>
                            <?php if (!empty($product['images'][0]['image_path'])): ?>
                                <img src="/kabutt/assets/uploads/products/<?= htmlspecialchars($product['images'][0]['image_path']) ?>"
                                     alt="<?= htmlspecialchars($product['name']) ?>" class="product-thumbnail">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td>S/ <?= number_format($product['price'], 2) ?></td>
                        <td><?= ucfirst($product['category']) ?></td>
                        <td>
                            <?php
                            $totalStock = 0;
                            foreach ($product['variants'] as $variant) {
                                $totalStock += $variant['stock'];
                            }
                            echo $totalStock;
                            ?>
                        </td>
                        <td class="actions">
                            <a href="/kabutt/?page=admin/products/edit&id=<?= $product['id'] ?>"
                               class="btn btn-sm btn-edit">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="/kabutt/?page=admin/products/delete&id=<?= $product['id'] ?>"
                               class="btn btn-sm btn-delete"
                               onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                                <i class="fas fa-trash"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => 'admin/products', 'num' => $currentPage - 1])) ?>"
                       class="page-link">&laquo; Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => 'admin/products', 'num' => $i])) ?>"
                       class="page-link <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => 'admin/products', 'num' => $currentPage + 1])) ?>"
                       class="page-link">Siguiente &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>