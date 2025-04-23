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

$productObj = new Product();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productData = [
        'name' => trim($_POST['name']),
        'description' => trim($_POST['description']),
        'price' => (float)$_POST['price'],
        'category' => $_POST['category'],
        'gender' => $_POST['gender'],
        'variants' => []
    ];

    // Procesar variantes
    foreach ($_POST['variant_size'] as $key => $size) {
        $productData['variants'][] = [
            'size' => $size,
            'color' => $_POST['variant_color'][$key],
            'stock' => (int)$_POST['variant_stock'][$key],
            'sku' => $_POST['variant_sku'][$key] ?? ''
        ];
    }

    // Validar datos
    $errors = validateProductData($productData);

    // Validar imágenes
    $images = [];
    if (isset($_FILES['images'])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $imageErrors = validateImageUpload([
                    'name' => $_FILES['images']['name'][$key],
                    'type' => $_FILES['images']['type'][$key],
                    'tmp_name' => $tmpName,
                    'error' => $_FILES['images']['error'][$key],
                    'size' => $_FILES['images']['size'][$key]
                ]);

                if (!empty($imageErrors)) {
                    $errors = array_merge($errors, $imageErrors);
                } else {
                    $images[] = [
                        'name' => $_FILES['images']['name'][$key],
                        'tmp_name' => $tmpName
                    ];
                }
            }
        }
    }

    if (empty($errors)) {
        $productId = $productObj->addProduct($productData, $images);

        if ($productId) {
            $_SESSION['success_message'] = 'Producto agregado correctamente';
            header("Location: /?page=admin/products/edit&id=$productId");
            exit;
        } else {
            $errors[] = 'Error al agregar el producto';
        }
    }
}
?>

    <div class="form-container">
        <h2>Agregar Nuevo Producto</h2>

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
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="description">Descripción</label>
                <textarea id="description" name="description" rows="5"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Precio (S/)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label for="category">Categoría</label>
                    <select id="category" name="category" required>
                        <option value="">Seleccionar categoría</option>
                        <option value="novedades">Novedades</option>
                        <option value="hombres">Hombres</option>
                        <option value="mujeres">Mujeres</option>
                        <option value="niños">Niños</option>
                        <option value="zapatillas">Zapatillas</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gender">Género</label>
                    <select id="gender" name="gender">
                        <option value="unisex">Unisex</option>
                        <option value="hombre">Hombre</option>
                        <option value="mujer">Mujer</option>
                        <option value="niño">Niño</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Imágenes del Producto</label>
                <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp">
                <small>Seleccione al menos una imagen. La primera será la imagen principal.</small>
            </div>

            <div class="variants-container">
                <h3>Variantes</h3>

                <div class="variant-item">
                    <div class="form-group">
                        <label>Talla</label>
                        <input type="text" name="variant_size[]" required>
                    </div>

                    <div class="form-group">
                        <label>Color</label>
                        <input type="text" name="variant_color[]" required>
                    </div>

                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="variant_stock[]" min="0" required>
                    </div>

                    <div class="form-group">
                        <label>SKU (opcional)</label>
                        <input type="text" name="variant_sku[]">
                    </div>

                    <button type="button" class="btn btn-delete remove-variant">Eliminar</button>
                </div>
            </div>

            <button type="button" class="btn btn-outline add-variant">Añadir Variante</button>

            <div class="form-actions">
                <button type="submit" class="btn">Guardar Producto</button>
                <a href="/?page=admin/products" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Clonar plantilla de variante
            const variantTemplate = document.querySelector('.variant-item').cloneNode(true);

            // Añadir nueva variante
            document.querySelector('.add-variant').addEventListener('click', function() {
                const newVariant = variantTemplate.cloneNode(true);
                const inputs = newVariant.querySelectorAll('input');
                inputs.forEach(input => input.value = '');
                document.querySelector('.variants-container').appendChild(newVariant);
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