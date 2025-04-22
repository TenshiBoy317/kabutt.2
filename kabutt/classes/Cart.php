<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

class Cart {
    private $db;
    private $auth;

    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth();
    }

    private function getCartId() {
        if ($this->auth->isLoggedIn()) {
            // Usuario autenticado - obtener carrito por user_id
            $userId = $_SESSION['user_id'];
            $stmt = $this->db->prepare("SELECT id FROM carts WHERE user_id = ?");
            $stmt->execute([$userId]);
            $cart = $stmt->fetch();

            if ($cart) {
                return $cart['id'];
            } else {
                // Crear nuevo carrito
                $stmt = $this->db->prepare("INSERT INTO carts (user_id) VALUES (?)");
                $stmt->execute([$userId]);
                return $this->db->lastInsertId();
            }
        } else {
            // Usuario invitado - usar session_id
            if (!isset($_SESSION['cart_id'])) {
                $sessionId = session_id();
                $stmt = $this->db->prepare("INSERT INTO carts (session_id) VALUES (?)");
                $stmt->execute([$sessionId]);
                $_SESSION['cart_id'] = $this->db->lastInsertId();
            }
            return $_SESSION['cart_id'];
        }
    }

    public function addItem($productVariantId, $quantity) {
        $cartId = $this->getCartId();

        // Verificar si el item ya está en el carrito
        $stmt = $this->db->prepare("
            SELECT id, quantity FROM cart_items 
            WHERE cart_id = ? AND product_variant_id = ?
        ");
        $stmt->execute([$cartId, $productVariantId]);
        $existingItem = $stmt->fetch();

        if ($existingItem) {
            // Actualizar cantidad
            $newQuantity = $existingItem['quantity'] + $quantity;
            return $this->updateItem($existingItem['id'], $newQuantity);
        } else {
            // Añadir nuevo item
            $stmt = $this->db->prepare("
                INSERT INTO cart_items (cart_id, product_variant_id, quantity)
                VALUES (?, ?, ?)
            ");
            return $stmt->execute([$cartId, $productVariantId, $quantity]);
        }
    }

    public function updateItem($itemId, $quantity) {
        // Verificar stock antes de actualizar
        $stmt = $this->db->prepare("
            SELECT pv.stock 
            FROM cart_items ci
            JOIN product_variants pv ON ci.product_variant_id = pv.id
            WHERE ci.id = ?
        ");
        $stmt->execute([$itemId]);
        $stock = $stmt->fetchColumn();

        if ($quantity <= 0) {
            return $this->removeItem($itemId);
        } elseif ($quantity > $stock) {
            return false; // No hay suficiente stock
        }

        $stmt = $this->db->prepare("
            UPDATE cart_items 
            SET quantity = ?
            WHERE id = ?
        ");
        return $stmt->execute([$quantity, $itemId]);
    }

    public function removeItem($itemId) {
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE id = ?");
        return $stmt->execute([$itemId]);
    }

    public function getCartItems() {
        $cartId = $this->getCartId();

        $stmt = $this->db->prepare("
            SELECT 
                ci.id,
                ci.quantity,
                p.name,
                p.price,
                pv.size,
                pv.color,
                pv.stock as max_stock,
                pi.image_path
            FROM cart_items ci
            JOIN product_variants pv ON ci.product_variant_id = pv.id
            JOIN products p ON pv.product_id = p.id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
            WHERE ci.cart_id = ?
        ");
        $stmt->execute([$cartId]);
        return $stmt->fetchAll();
    }

    public function getCartTotal() {
        $items = $this->getCartItems();
        $total = 0;

        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }

    public function clearCart() {
        $cartId = $this->getCartId();
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        return $stmt->execute([$cartId]);
    }

    public function mergeGuestCartWithUser($userId) {
        if (isset($_SESSION['cart_id'])) {
            $guestCartId = $_SESSION['cart_id'];

            // Verificar si el usuario ya tiene un carrito
            $stmt = $this->db->prepare("SELECT id FROM carts WHERE user_id = ?");
            $stmt->execute([$userId]);
            $userCart = $stmt->fetch();

            if ($userCart) {
                $userCartId = $userCart['id'];

                // Mover items del carrito de invitado al carrito del usuario
                $stmt = $this->db->prepare("
                    UPDATE cart_items 
                    SET cart_id = ? 
                    WHERE cart_id = ?
                ");
                $stmt->execute([$userCartId, $guestCartId]);
            } else {
                // Asignar el carrito de invitado al usuario
                $stmt = $this->db->prepare("
                    UPDATE carts 
                    SET user_id = ?, session_id = NULL 
                    WHERE id = ?
                ");
                $stmt->execute([$userId, $guestCartId]);
            }

            unset($_SESSION['cart_id']);
        }
    }
}
?>