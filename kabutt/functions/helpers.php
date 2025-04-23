<?php
/**
 * Funciones de ayuda para la aplicación Kabutt
 *
 * Este archivo contiene funciones utilitarias para sanitización, redirección,
 * formato de datos y otras operaciones comunes.
 */

/**
 * Sanitiza una entrada de datos para prevenir XSS y otros ataques
 * @param mixed $data Los datos a sanitizar (puede ser string o array)
 * @return mixed Los datos sanitizados
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Redirige a una URL específica y termina la ejecución del script
 * @param string $url URL a la que redirigir
 */
function redirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    }
    // Fallback con JavaScript si los headers ya fueron enviados
    echo "<script>window.location.href='$url';</script>";
    exit;
}

/**
 * Verifica si la solicitud actual es una petición AJAX
 * @return bool True si es AJAX, false en caso contrario
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Formatea un precio como moneda peruana (Soles)
 * @param float $price El precio a formatear
 * @return string Precio formateado con símbolo de soles y 2 decimales
 */
function formatPrice($price) {
    return 'S/ ' . number_format((float)$price, 2, '.', ',');
}

/**
 * Obtiene la URL actual completa
 * @return string URL completa con protocolo, host y URI
 */
function getCurrentUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ||
        $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Genera y almacena un token CSRF si no existe
 * @return string Token CSRF generado
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        if (function_exists('random_bytes')) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        } else {
            throw new RuntimeException('No se encontró un generador de números aleatorios seguro');
        }
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida un token CSRF contra el almacenado en sesión
 * @param string $token Token a validar
 * @return bool True si el token es válido, false en caso contrario
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Genera enlaces de paginación
 * @param int $totalItems Total de ítems a paginar
 * @param int $itemsPerPage Ítems por página
 * @param int $currentPage Página actual
 * @param array $queryParams Parámetros adicionales para mantener en la URL
 * @return array Array con los enlaces de paginación
 */
function getPaginationLinks($totalItems, $itemsPerPage, $currentPage, $queryParams = []) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $links = [];

    if ($totalPages <= 1) {
        return $links;
    }

    // Previous link
    if ($currentPage > 1) {
        $links[] = [
            'url' => '?' . http_build_query(array_merge($queryParams, ['page' => $currentPage - 1])),
            'label' => '&laquo; Anterior',
            'active' => false,
            'disabled' => false
        ];
    }

    // Page links (mostrar solo páginas cercanas a la actual)
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);

    if ($startPage > 1) {
        $links[] = [
            'url' => '?' . http_build_query(array_merge($queryParams, ['page' => 1])),
            'label' => '1',
            'active' => false,
            'disabled' => false
        ];
        if ($startPage > 2) {
            $links[] = [
                'label' => '...',
                'disabled' => true
            ];
        }
    }

    for ($i = $startPage; $i <= $endPage; $i++) {
        $links[] = [
            'url' => '?' . http_build_query(array_merge($queryParams, ['page' => $i])),
            'label' => $i,
            'active' => $i === $currentPage,
            'disabled' => false
        ];
    }

    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $links[] = [
                'label' => '...',
                'disabled' => true
            ];
        }
        $links[] = [
            'url' => '?' . http_build_query(array_merge($queryParams, ['page' => $totalPages])),
            'label' => $totalPages,
            'active' => false,
            'disabled' => false
        ];
    }

    // Next link
    if ($currentPage < $totalPages) {
        $links[] = [
            'url' => '?' . http_build_query(array_merge($queryParams, ['page' => $currentPage + 1])),
            'label' => 'Siguiente &raquo;',
            'active' => false,
            'disabled' => false
        ];
    }

    return $links;
}

/**
 * Valida un campo de formulario según su tipo
 * @param string $field Nombre del campo
 * @param string $type Tipo de validación (email, text, number, etc.)
 * @return array ['value' => valor sanitizado, 'error' => mensaje de error]
 */
function validateFormField($field, $type = 'text') {
    $value = $_POST[$field] ?? '';
    $error = '';

    $value = sanitizeInput($value);

    // Validaciones específicas
    switch ($type) {
        case 'email':
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $error = "El email no es válido";
            }
            break;

        case 'number':
            if (!is_numeric($value)) {
                $error = "Debe ser un número válido";
            }
            break;

        case 'required':
            if (empty($value)) {
                $error = "Este campo es obligatorio";
            }
            break;

        case 'password':
            if (strlen($value) < 6) {
                $error = "La contraseña debe tener al menos 6 caracteres";
            }
            break;
    }

    return ['value' => $value, 'error' => $error];
}