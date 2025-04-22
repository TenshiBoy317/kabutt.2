<?php
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data));
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function formatPrice($price) {
    return 'S/ ' . number_format($price, 2);
}

function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

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
            'active' => false
        ];
    }

    // Page links
    for ($i = 1; $i <= $totalPages; $i++) {
        $links[] = [
            'url' => '?' . http_build_query(array_merge($queryParams, ['page' => $i])),
            'label' => $i,
            'active' => $i === $currentPage
        ];
    }

    // Next link
    if ($currentPage < $totalPages) {
        $links[] = [
            'url' => '?' . http_build_query(array_merge($queryParams, ['page' => $currentPage + 1])),
            'label' => 'Siguiente &raquo;',
            'active' => false
        ];
    }

    return $links;
}
?>