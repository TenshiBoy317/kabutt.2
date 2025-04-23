<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function register($data) {
        $errors = [];

        // Validaciones
        if (empty($data['username'])) {
            $errors[] = "El nombre de usuario es requerido";
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

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Verificar si el usuario ya existe
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$data['username'], $data['email']]);

        if ($stmt->rowCount() > 0) {
            $errors[] = "El nombre de usuario o email ya está en uso";
            return ['success' => false, 'errors' => $errors];
        }

        // Hash de la contraseña
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        // Insertar nuevo usuario
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, address, phone)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $success = $stmt->execute([
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['address'] ?? '',
            $data['phone'] ?? ''
        ]);

        return ['success' => $success, 'errors' => $success ? [] : ['Error al registrar el usuario']];
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateProfile($userId, $data) {
        $errors = [];

        // Validaciones básicas
        if (empty($data['first_name'])) {
            $errors[] = "El nombre es requerido";
        }

        if (empty($data['email'])) {
            $errors[] = "El email es requerido";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email no es válido";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Actualizar datos
        $stmt = $this->db->prepare("
            UPDATE users 
            SET first_name = ?, last_name = ?, email = ?, address = ?, phone = ?
            WHERE id = ?
        ");

        $success = $stmt->execute([
            $data['first_name'],
            $data['last_name'] ?? '',
            $data['email'],
            $data['address'] ?? '',
            $data['phone'] ?? '',
            $userId
        ]);

        return ['success' => $success, 'errors' => $success ? [] : ['Error al actualizar el perfil']];
    }
    /**
     * Obtiene el conteo total de usuarios
     */
    public function getTotalUsersCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }
    public function getAllUsers() {
        $stmt = $this->db->prepare("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateUserRole($userId, $role) {
        $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
        return $stmt->execute([$role, $userId]);
    }
}
?>