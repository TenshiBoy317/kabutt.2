<?php
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/admin_header.php';

$baseUrl = '/kabutt/';
// Verificar autenticación y rol de admin
$auth = new Auth();

if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header("Location: /kabutt/?page=login");
    exit();
}

require_once __DIR__ . '/../../../classes/User.php';

$userObj = new User();

// Paginación
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$offset = ($currentPage - 1) * $itemsPerPage;

$users = $userObj->getAllUsers($itemsPerPage, $offset);
$totalUsers = $userObj->getTotalUsersCount();
$totalPages = ceil($totalUsers / $itemsPerPage);

// Actualizar rol de usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role'])) {
    if ($userObj->updateUserRole($_POST['user_id'], $_POST['role'])) {
        $_SESSION['success_message'] = 'Rol de usuario actualizado correctamente';
        header("Location: /kabutt/?page=admin/users");
        exit;
    } else {
        $errors[] = 'Error al actualizar el rol del usuario';
    }
}
?>

    <div class="admin-users-container">
        <div class="admin-header">
            <h1>Gestión de Usuarios</h1>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <p><?= $_SESSION['success_message'] ?></p>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <div class="users-table-container">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre de Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <form method="post" class="role-form">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <select name="role" onchange="this.form.submit()">
                                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Usuario</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                </select>
                            </form>
                        </td>
                        <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <a href="<?= $baseUrl?>?page=admin/users/view&id=<?= $user['id'] ?>" class="btn btn-sm">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="<?= $baseUrl?>?page=admin/users&page=<?= $currentPage - 1 ?>" class="page-link">&laquo; Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= $baseUrl?>?page=admin/users&page=<?= $i ?>" class="page-link <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= $baseUrl?>?page=admin/users&page=<?= $currentPage + 1 ?>" class="page-link">Siguiente &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>