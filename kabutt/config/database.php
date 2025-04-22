<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'kabutt';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
            die("Error de conexión a la base de datos. Por favor, inténtelo más tarde.");
        }

        return $this->conn;
    }

    public function prepare($sql) {
        return $this->connect()->prepare($sql);
    }

    public function lastInsertId() {
        return $this->connect()->lastInsertId();
    }
}
?>