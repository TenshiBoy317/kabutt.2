<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';


$auth = new Auth();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username)) {
        $errors[] = "El nombre de usuario es requerido";
    }

    if (empty($password)) {
        $errors[] = "La contraseña es requerida";
    }

    if (empty($errors)) {
        $user = $auth->login($username, $password);

        if ($user) {
            // Redirigir según el rol
            if ($user['role'] === 'admin') {
                header("Location: /kabutt/?page=admin/dashboard");
            } else {
                header("Location: /kabutt/");
            }
            exit;
        } else {
            $errors[] = "Usuario o contraseña incorrectos";
        }
    }
}
?>

    <div class="auth-container">
        <div class="auth-form">
            <h2>Iniciar Sesión</h2>

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
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-block">Ingresar</button>
            </form>

            <div class="auth-links">
                <p>¿No tienes una cuenta? <a href="/?page=register">Regístrate aquí</a></p>
                <p><a href="/?page=forgot-password">¿Olvidaste tu contraseña?</a></p>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>