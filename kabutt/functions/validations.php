<?php
/**
 * Funciones específicas de validación para la aplicación Kabutt
 *
 * Este archivo contiene validaciones específicas para formularios de productos,
 * pedidos, usuarios y carga de imágenes.
 */

/**
 * Valida los datos de un producto
 * @param array $data Datos del producto a validar
 * @return array Array de mensajes de error (vacío si no hay errores)
 */
function validateProductData($data) {
    $errors = [];
    $validCategories = ['novedades', 'hombres', 'mujeres', 'niños', 'zapatillas'];

    // Validación del nombre
    if (empty($data['name'])) {
        $errors['name'] = "El nombre del producto es requerido";
    } elseif (strlen($data['name']) > 100) {
        $errors['name'] = "El nombre no puede exceder 100 caracteres";
    }

    // Validación del precio
    if (empty($data['price']) || !is_numeric($data['price'])) {
        $errors['price'] = "El precio debe ser un número válido";
    } elseif ($data['price'] <= 0) {
        $errors['price'] = "El precio debe ser mayor que cero";
    }

    // Validación de categoría
    if (empty($data['category']) || !in_array($data['category'], $validCategories)) {
        $errors['category'] = "Seleccione una categoría válida";
    }

    // Validación de variantes
    if (empty($data['variants']) || !is_array($data['variants'])) {
        $errors['variants'] = "Debe agregar al menos una variante";
    } else {
        foreach ($data['variants'] as $index => $variant) {
            if (empty($variant['size'])) {
                $errors["variant_{$index}_size"] = "La talla es requerida";
            }

            if (empty($variant['color'])) {
                $errors["variant_{$index}_color"] = "El color es requerido";
            }

            if (!isset($variant['stock']) || !is_numeric($variant['stock'])) {
                $errors["variant_{$index}_stock"] = "El stock debe ser un número";
            } elseif ($variant['stock'] < 0) {
                $errors["variant_{$index}_stock"] = "El stock no puede ser negativo";
            }
        }
    }

    return $errors;
}

/**
 * Valida los datos de un pedido
 * @param array $data Datos del pedido a validar
 * @return array Array de mensajes de error
 */
function validateOrderData($data) {
    $errors = [];
    $validPaymentMethods = ['tarjeta', 'efectivo', 'yape', 'plin'];

    // Validación de dirección
    if (empty($data['shipping_address'])) {
        $errors['shipping_address'] = "La dirección de envío es requerida";
    } elseif (strlen($data['shipping_address']) > 255) {
        $errors['shipping_address'] = "La dirección no puede exceder 255 caracteres";
    }

    // Validación de teléfono
    if (empty($data['contact_phone'])) {
        $errors['contact_phone'] = "El teléfono de contacto es requerido";
    } elseif (!preg_match('/^[0-9]{9,15}$/', $data['contact_phone'])) {
        $errors['contact_phone'] = "Ingrese un número de teléfono válido";
    }

    // Validación de método de pago
    if (empty($data['payment_method']) || !in_array($data['payment_method'], $validPaymentMethods)) {
        $errors['payment_method'] = "Seleccione un método de pago válido";
    }

    return $errors;
}

/**
 * Valida los datos de actualización de usuario
 * @param array $data Datos del usuario a validar
 * @return array Array de mensajes de error
 */
function validateUserUpdateData($data) {
    $errors = [];

    // Validación de nombre
    if (empty($data['first_name'])) {
        $errors['first_name'] = "El nombre es requerido";
    } elseif (strlen($data['first_name']) > 50) {
        $errors['first_name'] = "El nombre no puede exceder 50 caracteres";
    }

    // Validación de email
    if (empty($data['email'])) {
        $errors['email'] = "El email es requerido";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Ingrese un email válido";
    } elseif (strlen($data['email']) > 100) {
        $errors['email'] = "El email no puede exceder 100 caracteres";
    }

    return $errors;
}

/**
 * Valida un archivo de imagen subido
 * @param array $file Datos del archivo subido ($_FILES['nombre'])
 * @return array Array de mensajes de error
 */
function validateImageUpload($file) {
    $errors = [];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Verificar errores de subida
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => "El archivo excede el tamaño máximo permitido",
            UPLOAD_ERR_FORM_SIZE => "El archivo excede el tamaño máximo especificado",
            UPLOAD_ERR_PARTIAL => "El archivo solo se subió parcialmente",
            UPLOAD_ERR_NO_FILE => "No se seleccionó ningún archivo",
            UPLOAD_ERR_NO_TMP_DIR => "Falta la carpeta temporal",
            UPLOAD_ERR_CANT_WRITE => "No se pudo escribir el archivo en el disco",
            UPLOAD_ERR_EXTENSION => "Una extensión de PHP detuvo la subida del archivo"
        ];

        return [$uploadErrors[$file['error']] ?? ["Error desconocido al subir la imagen"]];
    }

    // Validar tipo de archivo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!in_array($mime, $allowedTypes)) {
        $errors[] = "Solo se permiten imágenes JPEG, PNG o WebP";
    }

    // Validar tamaño
    if ($file['size'] > $maxSize) {
        $errors[] = "La imagen no debe exceder los 5MB";
    }

    // Validar dimensiones (opcional)
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo && ($imageInfo[0] > 2000 || $imageInfo[1] > 2000)) {
        $errors[] = "La imagen no debe exceder 2000px de ancho o alto";
    }

    return $errors;
}