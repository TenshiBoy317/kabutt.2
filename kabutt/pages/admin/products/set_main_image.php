<?php
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../classes/Product.php';

// Verificar autenticación y permisos
$auth = new Auth();
if (!$auth->isAdmin()) {
    header("Location: /kabutt/?page=login");
    exit();
}

// Validar parámetros
if (!isset($_GET['image_id']) || !isset($_GET['product_id'])) {
    header("Location: /kabutt/?page=admin/products&error=Parámetros inválidos");
    exit();
}

$imageId = (int)$_GET['image_id'];
$productId = (int)$_GET['product_id'];

// Validar que los IDs sean positivos
if ($imageId <= 0 || $productId <= 0) {
    header("Location: /kabutt/?page=admin/products&error=IDs inválidos");
    exit();
}

$product = new Product();

try {
    // 1. Verificar que la imagen pertenezca al producto
    $stmt = $product->getDb()->prepare("
        SELECT id FROM product_images 
        WHERE id = :image_id AND product_id = :product_id
    ");
    $stmt->execute([
        ':image_id' => $imageId,
        ':product_id' => $productId
    ]);

    if ($stmt->rowCount() === 0) {
        header("Location: /kabutt/?page=admin/products/edit&id=$productId&error=La imagen no pertenece a este producto");
        exit();
    }

    // 2. Iniciar transacción
    $product->getDb()->beginTransaction();

    // 3. Quitar el estado de principal a todas las imágenes del producto
    $stmt = $product->getDb()->prepare("
        UPDATE product_images 
        SET is_main = 0 
        WHERE product_id = :product_id
    ");
    $stmt->execute([':product_id' => $productId]);

    // 4. Establecer la nueva imagen como principal
    $stmt = $product->getDb()->prepare("
        UPDATE product_images 
        SET is_main = 1 
        WHERE id = :image_id AND product_id = :product_id
    ");
    $stmt->execute([
        ':image_id' => $imageId,
        ':product_id' => $productId
    ]);

    // 5. Confirmar transacción
    $product->getDb()->commit();

    // Redirigir con mensaje de éxito
    header("Location: /kabutt/?page=admin/products/edit&id=$productId&success=Imagen principal actualizada correctamente");
    exit();

} catch (PDOException $e) {
    // Revertir en caso de error
    if ($product->getDb()->inTransaction()) {
        $product->getDb()->rollBack();
    }

    error_log("Error setting main image: " . $e->getMessage());
    header("Location: /kabutt/?page=admin/products/edit&id=$productId&error=Error al actualizar la imagen principal");
    exit();
} catch (Exception $e) {
    error_log("General error setting main image: " . $e->getMessage());
    header("Location: /kabutt/?page=admin/products/edit&id=$productId&error=Ocurrió un error inesperado");
    exit();
}