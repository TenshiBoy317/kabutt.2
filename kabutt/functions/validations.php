<?php
function validateProductData($data) {
    $errors = [];

    if (empty($data['name'])) {
        $errors[] = "El nombre del producto es requerido";
    }

    if (empty($data['price']) || !is_numeric($data['price']) || $data['price'] <= 0) {
        $errors[] = "El precio debe ser un número positivo";
    }

    if (empty($data['category']) || !in_array($data['category'], ['novedades', 'hombres', 'mujeres', 'niños', 'zapatillas'])) {
        $errors[] = "La categoría seleccionada no es válida";
    }

    if (empty($data['variants']) || !is_array($data['variants'])) {
        $errors[] = "Debe agregar al menos una variante (talla/color)";
    } else {
        foreach ($data['variants'] as $variant) {
            if (empty($variant['size'])) {
                $errors[] = "La talla es requerida para todas las variantes";
                break;
            }

            if (empty($variant['color'])) {
                $errors[] = "El color es requerido para todas las variantes";
                break;
            }

            if (!isset($variant['stock']) || !is_numeric($variant['stock']) || $variant['stock'] < 0) {
                $errors[] = "El stock debe ser un número positivo para todas las variantes";
                break;
            }
        }
    }

    return $errors;
}

function validateOrderData($data) {
    $errors = [];

    if (empty($data['shipping_address'])) {
        $errors[] = "La dirección de envío es requerida";
    }

    if (empty($data['contact_phone'])) {
        $errors[] = "El teléfono de contacto es requerido";
    }

    if (empty($data['payment_method']) || !in_array($data['payment_method'], ['tarjeta', 'efectivo', 'yape', 'plin'])) {
        $errors[] = "El método de pago seleccionado no es válido";
    }

    return $errors;
}

function validateUserUpdateData($data) {
    $errors = [];

    if (empty($data['first_name'])) {
        $errors[] = "El nombre es requerido";
    }

    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El email no es válido";
    }

    return $errors;
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }

    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

    return $data;
}

function validateImageUpload($file) {
    $errors = [];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error al subir la imagen";
        return $errors;
    }

    if (!in_array($file['type'], $allowedTypes)) {
        $errors[] = "Solo se permiten imágenes JPEG, PNG o WebP";
    }

    if ($file['size'] > $maxSize) {
        $errors[] = "La imagen no debe exceder los 5MB";
    }

    return $errors;
}
?>