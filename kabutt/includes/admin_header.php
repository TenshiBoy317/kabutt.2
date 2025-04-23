<?php
require_once __DIR__ . '/../includes/auth.php';

$baseUrl = '/kabutt/';
$auth = new Auth();
if (!$auth->isAdmin()) {
    header("Location: /?page=login");
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Kabutt - <?= htmlspecialchars($pageTitle ?? 'Panel de Administración') ?></title>
    <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="admin-main">
        <header class="admin-header">
            <div class="container">
                <div class="admin-user">
                    <span><?= htmlspecialchars($_SESSION['username']) ?></span>
                    <a href="<?= $baseUrl ?>/logout.php" class="btn btn-sm btn-outline">Cerrar Sesión</a>
                </div>
            </div>
        </header>

        <main class="container">