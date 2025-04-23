<?php
$baseUrl = '/kabutt/';
?>

<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2>Kabutt Admin</h2>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="<?= $baseUrl ?>?page=admin/dashboard" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="<?= $baseUrl ?>?page=admin/products" class="<?= strpos($currentPage, 'products/') !== false ? 'active' : '' ?>">
                    <i class="fas fa-shoe-prints"></i> Productos
                </a>
            </li>
            <li>
                <a href="<?= $baseUrl ?>?page=admin/orders" class="<?= strpos($currentPage, 'orders/') !== false ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list"></i> Pedidos
                </a>
            </li>
            <li>
                <a href="<?= $baseUrl ?>?page=admin/users" class="<?= strpos($currentPage, 'users/') !== false ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Usuarios
                </a>
            </li>
        </ul>
    </nav>
</aside>