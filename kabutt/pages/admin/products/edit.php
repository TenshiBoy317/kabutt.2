<?php
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/admin_header.php';

// Verificar autenticación y rol de admin
$auth = new Auth();

if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header("Location: /kabutt/?page=login");
    exit();
}

require_once __DIR__ . '/../../../classes/Product.php';
require_once __DIR__ . '/../../../functions/validations.php';

$baseUrl = '/kabutt/';
$productObj = new Product();
$errors = [];

if (!isset($_GET['id']) || !$product = $productObj->getProductById($_GET['id'])) {
    header("Location: /kabutt/?page=admin/products");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productData = [
        'id' => $product['id'],
        'name' => trim($_POST['name']),
        'description' => trim($_POST['description']),
        'price' => (float)$_POST['price'],
        'category' => $_POST['category'],
        'gender' => $_POST['gender'],
        'variants' => []
    ];

    // Procesar variantes existentes
    if (isset($_POST['existing_variant_id'])) {
        foreach ($_POST['existing_variant_id'] as $key => $id) {
            $productData['variants'][] = [
                'id' => $id,
                'size' => $_POST['existing_variant_size'][$key],
                'color' => $_POST['existing_variant_color'][$key],
                'stock' => (int)$_POST['existing_variant_stock'][$key],
                'sku' => $_POST['existing_variant_sku'][$key] ?? ''
            ];
        }
    }

    // Procesar nuevas variantes
    if (isset($_POST['new_variant_size'])) {
        foreach ($_POST['new_variant_size'] as $key => $size) {
            $productData['variants'][] = [
                'size' => $size,
                'color' => $_POST['new_variant_color'][$key],
                'stock' => (int)$_POST['new_variant_stock'][$key],
                'sku' => $_POST['new_variant_sku'][$key] ?? ''
            ];
        }
    }

    // Validar datos
    $errors = validateProductData($productData);

    // Procesar nuevas imágenes
    $newImages = [];
    if (isset($_FILES['new_images'])) {
        foreach ($_FILES['new_images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['new_images']['error'][$key] === UPLOAD_ERR_OK) {
                $imageErrors = validateImageUpload([
                    'name' => $_FILES['new_images']['name'][$key],
                    'type' => $_FILES['new_images']['type'][$key],
                    'tmp_name' => $tmpName,
                    'error' => $_FILES['new_images']['error'][$key],
                    'size' => $_FILES['new_images']['size'][$key]
                ]);

                if (!empty($imageErrors)) {
                    $errors = array_merge($errors, $imageErrors);
                } else {
                    $newImages[] = [
                        'name' => $_FILES['new_images']['name'][$key],
                        'tmp_name' => $tmpName
                    ];
                }
            }
        }
    }

    // Procesar imágenes principales
    $mainImageId = $_POST['main_image'] ?? null;

    if (empty($errors)) {
        $success = $productObj->updateProduct($product['id'], $productData, $newImages, $mainImageId);

        if ($success) {
            $_SESSION['success_message'] = 'Producto actualizado correctamente';
            header("Location: /kabutt/?page=admin/products/edit&id=" . $product['id']);
            exit;
        } else {
            $errors[] = 'Error al actualizar el producto';
        }
    }
}

// Obtener datos actualizados del producto
$product = $productObj->getProductById($product['id']);
?>

    <div class="form-container">
        <h2>Editar Producto: <?= htmlspecialchars($product['name']) ?></h2>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <p><?= $_SESSION['success_message'] ?></p>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Nombre del Producto</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Descripción</label>
                <textarea id="description" name="description" rows="5"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Precio (S/)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?= $product['price'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="category">Categoría</label>
                    <select id="category" name="category" required>
                        <option value="novedades" <?= $product['category'] === 'novedades' ? 'selected' : '' ?>>Novedades</option>
                        <option value="hombres" <?= $product['category'] === 'hombres' ? 'selected' : '' ?>>Hombres</option>
                        <option value="mujeres" <?= $product['category'] === 'mujeres' ? 'selected' : '' ?>>Mujeres</option>
                        <option value="niños" <?= $product['category'] === 'niños' ? 'selected' : '' ?>>Niños</option>
                        <option value="zapatillas" <?= $product['category'] === 'zapatillas' ? 'selected' : '' ?>>Zapatillas</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gender">Género</label>
                    <select id="gender" name="gender">
                        <option value="unisex" <?= $product['gender'] === 'unisex' ? 'selected' : '' ?>>Unisex</option>
                        <option value="hombre" <?= $product['gender'] === 'hombre' ? 'selected' : '' ?>>Hombre</option>
                        <option value="mujer" <?= $product['gender'] === 'mujer' ? 'selected' : '' ?>>Mujer</option>
                        <option value="niño" <?= $product['gender'] === 'niño' ? 'selected' : '' ?>>Niño</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Imágenes Actuales</label>
                <div class="product-images-grid">
                    <?php foreach ($product['images'] as $image): ?>
                        <div class="image-item <?= $image['is_main'] ? 'main-image' : '' ?>">
                            <img src="<?= $baseUrl ?>/assets/uploads/products/<?= $image['image_path'] ?>" alt="Imagen de producto">
                            <div class="image-actions">
                                <label>
                                    <input type="radio" name="main_image" value="<?= $image['id'] ?>" <?= $image['is_main'] ? 'checked' : '' ?>>
                                    Principal
                                </label>
                                <a href="<?= $baseUrl ?>admin/products/delete_image?id=<?= $image['id'] ?>" class="btn btn-sm btn-delete" onclick="return confirm('¿Eliminar esta imagen?')">Eliminar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Nuevas Imágenes</label>
                <input type="file" name="new_images[]" multiple accept="image/jpeg,image/png,image/webp">
            </div>

            <div class="variants-container">
                <h3>Variantes Existentes</h3>

                <?php foreach ($product['variants'] as $variant): ?>
                    <div class="variant-item">
                        <input type="hidden" name="existing_variant_id[]" value="<?= $variant['id'] ?>">

                        <div class="form-group">
                            <label>Talla</label>
                            <input type="text" name="existing_variant_size[]" value="<?= $variant['size'] ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Color</label>
                            <input type="text" name="existing_variant_color[]" value="<?= $variant['color'] ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number" name="existing_variant_stock[]" min="0" value="<?= $variant['stock'] ?>" required>
                        </div>

                        <div class="form-group">
                            <label>SKU (opcional)</label>
                            <input type="text" name="existing_variant_sku[]" value="<?= $variant['sku'] ?? '' ?>">
                        </div>

                        <a href="<?= $baseUrl ?>admin/products/delete_variant?id=<?= $variant['id'] ?>" class="btn btn-sm btn-delete" onclick="return confirm('¿Eliminar esta variante?')">Eliminar</a>
                    </div>
                <?php endforeach; ?>

                <h3>Nuevas Variantes</h3>

                <div class="new-variants">
                    <div class="variant-item">
                        <div class="form-group">
                            <label>Talla</label>
                            <input type="text" name="new_variant_size[]">
                        </div>

                        <div class="form-group">
                            <label>Color</label>
                            <input type="text" name="new_variant_color[]">
                        </div>

                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number" name="new_variant_stock[]" min="0">
                        </div>

                        <div class="form-group">
                            <label>SKU (opcional)</label>
                            <input type="text" name="new_variant_sku[]">
                        </div>

                        <button type="button" class="btn btn-sm btn-delete remove-variant">Eliminar</button>
                    </div>
                </div>

                <button type="button" class="btn btn-outline add-variant">Añadir Variante</button>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Guardar Cambios</button>
                <a href="<?= $baseUrl ?>?page=admin/products" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Clonar plantilla de nueva variante
            const newVariantTemplate = document.querySelector('.new-variants .variant-item').cloneNode(true);
            document.querySelector('.new-variants').innerHTML = '';

            // Añadir nueva variante
            document.querySelector('.add-variant').addEventListener('click', function() {
                const newVariant = newVariantTemplate.cloneNode(true);
                const inputs = newVariant.querySelectorAll('input');
                inputs.forEach(input => input.value = '');
                document.querySelector('.new-variants').appendChild(newVariant);
            });

            // Eliminar variante
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-variant')) {
                    if (document.querySelectorAll('.variant-item').length > 1) {
                        e.target.closest('.variant-item').remove();
                    } else {
                        alert('Debe haber al menos una variante');
                    }
                }
            });
        });
    </script>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>