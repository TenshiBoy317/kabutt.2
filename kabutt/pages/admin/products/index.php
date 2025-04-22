<?php
require_once __DIR__ . '/../../../includes/admin_header.php';
require_once __DIR__ . '/../../../classes/Product.php';

$productObj = new Product();
$products = $productObj->getAllProducts();
?>

    <div class="admin-products-container">
        <div class="admin-header">
            <h1>Gestión de Productos</h1>
            <a href="/?page=admin/products/add" class="btn">Añadir Producto</a>
        </div>

        <div class="products-table-container">
            <table class="admin-table">
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
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= $product['id'] ?></td>
                        <td>
                            <?php if (!empty($product['main_image'])): ?>
                                <img src="/assets/uploads/<?= $product['main_image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-thumbnail">
                            <?php else: ?>
                                <div class="no-image">Sin imagen</div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td>S/ <?= number_format($product['price'], 2) ?></td>
                        <td><?= ucfirst($product['category']) ?></td>
                        <td><?= $product['total_stock'] ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="/?page=admin/products/edit&id=<?= $product['id'] ?>" class="btn btn-sm btn-edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="post" action="/admin/products/delete" class="delete-form">
                                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-delete" onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>