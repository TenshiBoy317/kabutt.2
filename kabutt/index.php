<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/functions/helpers.php';

$auth = new Auth();
$page = $_GET['page'] ?? 'home';

// Lista de páginas permitidas
$allowedPages = [
    'home' => '/pages/home.php',
    'product' => '/pages/product/view.php',
    'products' => '/pages/product/list.php',
    'login' => '/pages/auth/login.php',
    'register' => '/pages/auth/register.php',
    'profile' => '/pages/auth/profile.php',
    'cart' => '/pages/cart/index.php',
    'checkout' => '/pages/cart/checkout.php',
    '404' => '/pages/404.php'  // Añadido para manejo explícito de 404
];

// Páginas de admin (requieren autenticación y rol de admin)
if (strpos($page, 'admin/') === 0) {
    if (!$auth->isAdmin()) {
        header("Location: /kabutt/?page=login");
        exit();
    }

    $adminPage = str_replace('admin/', '', $page);
    $adminPages = [
        'dashboard' => '/pages/admin/dashboard.php',
        'products' => '/pages/admin/products/index.php',
        'products/add' => '/pages/admin/products/add.php',
        'products/edit' => '/pages/admin/products/edit.php',
        'products/delete-image' => '/pages/admin/products/delete_image.php', // Nuevo endpoint
        'products/set-main-image' => '/pages/admin/products/set_main_image.php', // Nuevo endpoint
        'users' => '/pages/admin/users/index.php',
        'orders' => '/pages/admin/orders/index.php'
    ];

    if (array_key_exists($adminPage, $adminPages)) {
        $pageLoaded = true;
        require_once __DIR__ . $adminPages[$adminPage];
        exit();
    }
}

// Páginas normales
if (array_key_exists($page, $allowedPages)) {
    $pageLoaded = true;
    require_once __DIR__ . $allowedPages[$page];
} else {
    // Página no encontrada
    header("HTTP/1.0 404 Not Found");
    $pageLoaded = true;
    require_once __DIR__ . '/pages/404.php';
}

// Redundancia para asegurar que se cargue algo
if (!isset($pageLoaded) || !$pageLoaded) {
    header("HTTP/1.0 404 Not Found");
    require_once __DIR__ . '/pages/404.php';
}