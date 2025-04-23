<?php
require_once __DIR__ . '/../config/database.php';

class Order {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function createOrder($userId, $cartItems, $shippingData, $paymentMethod) {
        $this->db->connect()->beginTransaction();

        try {
            // Calcular total
            $total = 0;
            foreach ($cartItems as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            // Crear orden
            $stmt = $this->db->prepare("
                INSERT INTO orders 
                (user_id, total, status, payment_method, shipping_address, contact_phone)
                VALUES (?, ?, 'pendiente', ?, ?, ?)
            ");

            $stmt->execute([
                $userId,
                $total,
                $paymentMethod,
                $shippingData['address'],
                $shippingData['phone']
            ]);

            $orderId = $this->db->lastInsertId();

            // Añadir items de la orden
            foreach ($cartItems as $item) {
                $stmt = $this->db->prepare("
                    INSERT INTO order_items 
                    (order_id, product_variant_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");

                $stmt->execute([
                    $orderId,
                    $item['product_variant_id'],
                    $item['quantity'],
                    $item['price']
                ]);

                // Actualizar stock
                $stmt = $this->db->prepare("
                    UPDATE product_variants 
                    SET stock = stock - ? 
                    WHERE id = ?
                ");

                $stmt->execute([
                    $item['quantity'],
                    $item['product_variant_id']
                ]);
            }

            $this->db->connect()->commit();
            return $orderId;

        } catch (Exception $e) {
            $this->db->connect()->rollBack();
            error_log("Error creating order: " . $e->getMessage());
            return false;
        }
    }

    public function getOrderById($orderId) {
        $stmt = $this->db->prepare("
            SELECT o.*, u.username, u.email 
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if ($order) {
            $order['items'] = $this->getOrderItems($orderId);
        }

        return $order;
    }

    public function getOrderItems($orderId) {
        $stmt = $this->db->prepare("
            SELECT 
                oi.*,
                p.name as product_name,
                pv.size,
                pv.color,
                pi.image_path
            FROM order_items oi
            JOIN product_variants pv ON oi.product_variant_id = pv.id
            JOIN products p ON pv.product_id = p.id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function getUserOrders($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM orders 
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll();

        foreach ($orders as &$order) {
            $order['items'] = $this->getOrderItems($order['id']);
        }

        return $orders;
    }

    public function getAllOrders($filters = []) {
        $query = "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id WHERE 1=1";
        $params = [];

        // Filtros
        if (!empty($filters['status'])) {
            $query .= " AND o.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND o.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND o.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $query .= " ORDER BY o.created_at DESC";

        // Paginación
        if (!empty($filters['limit'])) {
            $query .= " LIMIT :limit";
            $params[':limit'] = (int)$filters['limit'];

            if (!empty($filters['offset'])) {
                $query .= " OFFSET :offset";
                $params[':offset'] = (int)$filters['offset'];
            }
        }

        $stmt = $this->db->prepare($query);

        // Bind parameters
        foreach ($params as $key => $value) {
            $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $paramType);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateOrderStatus($orderId, $status) {
        $stmt = $this->db->prepare("
            UPDATE orders 
            SET status = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$status, $orderId]);
    }

    /**
     * Obtiene el conteo de pedidos por estado
     * @param string $status Estado del pedido
     * @return int Número de pedidos con ese estado
     */
    public function getOrdersCountByStatus($status) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM orders WHERE status = ?");
        $stmt->execute([$status]);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }

    /**
     * Obtiene las ventas mensuales
     * @return float Total de ventas del último mes
     */
    public function getMonthlySales() {
        $stmt = $this->db->prepare("
            SELECT SUM(total) as total 
            FROM orders 
            WHERE status != 'cancelado' 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        return (float)($result['total'] ?? 0);
    }

    /**
     * Obtiene el total de pedidos
     * @return int Número total de pedidos
     */
    public function getTotalOrdersCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM orders");
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
}
?>