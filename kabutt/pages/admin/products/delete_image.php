<?php
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../classes/Product.php';

$auth = new Auth();
if (!$auth->isAdmin()) {
    header("Location: /kabutt/?page=login");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /kabutt/?page=admin/products");
    exit();
}

$imageId = (int)$_GET['id'];
$product = new Product();

if ($product->deleteImage($imageId)) {
    // Redirigir con mensaje de éxito
    header("Location: /kabutt/?page=admin/products/edit&id=" . $_GET['product_id'] . "&success=Imagen eliminada correctamente");
} else {
    // Redirigir con mensaje de error
    header("Location: /kabutt/?page=admin/products/edit&id=" . $_GET['product_id'] . "&error=No se pudo eliminar la imagen");
}
exit();