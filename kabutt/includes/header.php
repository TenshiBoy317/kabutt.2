<?php
$auth = new Auth();
// Mejor detección de página activa para el sistema de routing
$currentPage = $_GET['page'] ?? 'home';
$baseUrl = '/kabutt/'; // Cambia esto si tu app está en un subdirectorio ej. '/kabutt/'
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kabutt - <?= htmlspecialchars($pageTitle ?? 'E-commerce de Calzado') ?></title>
    <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header class="header">
    <div class="container">
        <a href="<?= $baseUrl ?>" class="logo">KABUTT</a>

        <nav class="nav">
            <a href="<?= $baseUrl ?>?page=home" class="nav-link <?= $currentPage === 'home' ? 'active' : '' ?>">Inicio</a>
            <a href="<?= $baseUrl ?>?page=products&category=novedades" class="nav-link <?= strpos($currentPage, 'products') !== false && ($_GET['category'] ?? '') === 'novedades' ? 'active' : '' ?>">Novedades</a>
            <a href="<?= $baseUrl ?>?page=products&category=hombres" class="nav-link <?= strpos($currentPage, 'products') !== false && ($_GET['category'] ?? '') === 'hombres' ? 'active' : '' ?>">Hombres</a>
            <a href="<?= $baseUrl ?>?page=products&category=mujeres" class="nav-link <?= strpos($currentPage, 'products') !== false && ($_GET['category'] ?? '') === 'mujeres' ? 'active' : '' ?>">Mujeres</a>
            <a href="<?= $baseUrl ?>?page=products&category=niños" class="nav-link <?= strpos($currentPage, 'products') !== false && ($_GET['category'] ?? '') === 'niños' ? 'active' : '' ?>">Niños</a>
            <a href="<?= $baseUrl ?>?page=products&category=zapatillas" class="nav-link <?= strpos($currentPage, 'products') !== false && ($_GET['category'] ?? '') === 'zapatillas' ? 'active' : '' ?>">Zapatillas</a>

            <?php if ($auth->isLoggedIn()): ?>
                <a href="<?= $baseUrl ?>?page=profile" class="nav-link <?= $currentPage === 'profile' ? 'active' : '' ?>"><i class="fas fa-user"></i></a>
                <a href="<?= $baseUrl ?>?page=cart" class="nav-link <?= $currentPage === 'cart' ? 'active' : '' ?>"><i class="fas fa-shopping-cart"></i></a>
                <a href="<?= $baseUrl ?>logout.php" class="nav-link">Cerrar Sesión</a>
            <?php else: ?>
                <a href="<?= $baseUrl ?>?page=login" class="nav-link <?= $currentPage === 'login' ? 'active' : '' ?>">Login</a>
                <a href="<?= $baseUrl ?>?page=register" class="nav-link <?= $currentPage === 'register' ? 'active' : '' ?>">Registro</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="main-content">