<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/helpers.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Registra un nuevo usuario en el sistema
     * @param array $userData Datos del usuario a registrar
     * @return array Resultado de la operación
     */
    public function register($userData) {
        $errors = $this->validateUserData($userData);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Verificar si el usuario ya existe
        if ($this->userExists($userData['username'], $userData['email'])) {
            return ['success' => false, 'errors' => ["El nombre de usuario o email ya está en uso"]];
        }

        // Hash de la contraseña
        $hashedPassword = password_hash($userData['password'], PASSWORD_BCRYPT);

        // Insertar nuevo usuario
        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, first_name, last_name, address, phone)
                VALUES (:username, :email, :password, :first_name, :last_name, :address, :phone)
            ");

            $stmt->execute([
                ':username' => $userData['username'],
                ':email' => $userData['email'],
                ':password' => $hashedPassword,
                ':first_name' => $userData['first_name'] ?? '',
                ':last_name' => $userData['last_name'] ?? '',
                ':address' => $userData['address'] ?? '',
                ':phone' => $userData['phone'] ?? ''
            ]);

            // Iniciar sesión automáticamente
            return $this->login($userData['username'], $userData['password']);

        } catch (PDOException $e) {
            error_log("Error al registrar usuario: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error al registrar el usuario']];
        }
    }

    /**
     * Verifica si un usuario ya existe
     * @param string $username Nombre de usuario
     * @param string $email Email del usuario
     * @return bool True si el usuario existe, false si no
     */
    private function userExists($username, $email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username, ':email' => $email]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Inicia sesión con un usuario
     * @param string $username Nombre de usuario
     * @param string $password Contraseña
     * @return array|false Datos del usuario si es exitoso, false si falla
     */
    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $this->setUserSession($user);
                return ['success' => true, 'user' => $user];
            }

            return ['success' => false, 'errors' => ['Usuario o contraseña incorrectos']];

        } catch (PDOException $e) {
            error_log("Error al iniciar sesión: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error al iniciar sesión']];
        }
    }

    /**
     * Establece los datos de sesión del usuario
     * @param array $user Datos del usuario
     */
    private function setUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_data'] = $user;

        // Regenerar ID de sesión para prevenir fixation
        session_regenerate_id(true);
    }

    /**
     * Cierra la sesión actual
     */
    public function logout() {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Verifica si hay un usuario autenticado
     * @return bool True si está autenticado, false si no
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Verifica si el usuario actual es administrador
     * @return bool True si es admin, false si no
     */
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['role'] === 'admin';
    }

    /**
     * Obtiene los datos del usuario actual
     * @return array|null Datos del usuario o null si no está autenticado
     */
    public function getUser() {
        if (!$this->isLoggedIn()) return null;

        // Usar datos de sesión si están disponibles
        if (isset($_SESSION['user_data'])) {
            return $_SESSION['user_data'];
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $_SESSION['user_id']]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Error al obtener usuario: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Valida los datos de registro de usuario
     * @param array $data Datos a validar
     * @return array Errores de validación
     */
    private function validateUserData($data) {
        $errors = [];

        if (empty($data['username'])) {
            $errors['username'] = "El nombre de usuario es requerido";
        } elseif (strlen($data['username']) < 4) {
            $errors['username'] = "El nombre de usuario debe tener al menos 4 caracteres";
        } elseif (strlen($data['username']) > 50) {
            $errors['username'] = "El nombre de usuario no puede exceder 50 caracteres";
        }

        if (empty($data['email'])) {
            $errors['email'] = "El email es requerido";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "El email no es válido";
        } elseif (strlen($data['email']) > 100) {
            $errors['email'] = "El email no puede exceder 100 caracteres";
        }

        if (empty($data['password'])) {
            $errors['password'] = "La contraseña es requerida";
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = "La contraseña debe tener al menos 6 caracteres";
        }

        if (empty($data['first_name'])) {
            $errors['first_name'] = "El nombre es requerido";
        } elseif (strlen($data['first_name']) > 50) {
            $errors['first_name'] = "El nombre no puede exceder 50 caracteres";
        }

        return $errors;
    }
}

// Procesamiento del formulario de login (si es necesario en este archivo)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $auth = new Auth();
    $errors = [];

    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username)) {
        $errors['username'] = "El nombre de usuario es requerido";
    }

    if (empty($password)) {
        $errors['password'] = "La contraseña es requerida";
    }

    if (empty($errors)) {
        $result = $auth->login($username, $password);

        if ($result['success']) {
            // Redirigir según el rol
            $redirectUrl = $result['user']['role'] === 'admin'
                ? '/kabutt/?page=admin/dashboard'
                : '/kabutt/';

            header("Location: $redirectUrl");
            exit();
        } else {
            $errors['login'] = $result['errors'][0];
        }
    }
}