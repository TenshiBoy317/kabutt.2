<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';

$auth = new Auth();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userData = [
        'username' => trim($_POST['username']),
        'email' => trim($_POST['email']),
        'password' => trim($_POST['password']),
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name'])
    ];

    $result = $auth->register($userData);

    if ($result['success']) {
        // Iniciar sesión automáticamente después del registro
        $auth->login($userData['username'], $userData['password']);
        header("Location: /");
        exit;
    } else {
        $errors = $result['errors'];
    }
}
?>

    <div class="auth-container">
        <div class="auth-form">
            <h2>Crear Cuenta</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="username">Nombre de Usuario</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="first_name">Nombres</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Apellidos</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>

                <button type="submit" class="btn btn-block">Registrarse</button>
            </form>

            <div class="auth-links">
                <p>¿Ya tienes una cuenta? <a href="/?page=login">Inicia sesión aquí</a></p>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>