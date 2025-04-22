<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/helpers.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function register($userData) {
        $errors = $this->validateUserData($userData);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Verificar si el usuario ya existe
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$userData['username'], $userData['email']]);

        if ($stmt->rowCount() > 0) {
            $errors[] = "El nombre de usuario o email ya está en uso";
            return ['success' => false, 'errors' => $errors];
        }

        // Hash de la contraseña
        $hashedPassword = password_hash($userData['password'], PASSWORD_BCRYPT);

        // Insertar nuevo usuario
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, address, phone)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $success = $stmt->execute([
            $userData['username'],
            $userData['email'],
            $hashedPassword,
            $userData['first_name'] ?? '',
            $userData['last_name'] ?? '',
            $userData['address'] ?? '',
            $userData['phone'] ?? ''
        ]);

        if ($success) {
            // Iniciar sesión automáticamente
            $this->login($userData['username'], $userData['password']);
            return ['success' => true];
        }

        return ['success' => false, 'errors' => ['Error al registrar el usuario']];
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Regenerar ID de sesión para prevenir fixation
            session_regenerate_id(true);

            return $user;
        }

        return false;
    }

    public function logout() {
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['role'] === 'admin';
    }

    public function getUser() {
        if (!$this->isLoggedIn()) return null;

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    private function validateUserData($data) {
        $errors = [];

        if (empty($data['username'])) {
            $errors[] = "El nombre de usuario es requerido";
        } elseif (strlen($data['username']) < 4) {
            $errors[] = "El nombre de usuario debe tener al menos 4 caracteres";
        }

        if (empty($data['email'])) {
            $errors[] = "El email es requerido";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email no es válido";
        }

        if (empty($data['password'])) {
            $errors[] = "La contraseña es requerida";
        } elseif (strlen($data['password']) < 6) {
            $errors[] = "La contraseña debe tener al menos 6 caracteres";
        }

        if (empty($data['first_name'])) {
            $errors[] = "El nombre es requerido";
        }

        return $errors;
    }
}
?>