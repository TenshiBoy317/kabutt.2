<?php
require_once __DIR__ . '/../config/database.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getProducts($category = null, $filters = []) {
        $query = "SELECT p.* FROM products p WHERE 1=1";
        $params = [];

        // Filtro por categoría
        if ($category) {
            $query .= " AND p.category = :category";
            $params[':category'] = $category;
        }

        // Filtro por género
        if (!empty($filters['gender'])) {
            $query .= " AND p.gender = :gender";
            $params[':gender'] = $filters['gender'];
        }

        // Filtro por precio
        if (!empty($filters['min_price'])) {
            $query .= " AND p.price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $query .= " AND p.price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }

        // Ordenamiento
        $order = " ORDER BY p.created_at DESC";
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $order = " ORDER BY p.price ASC";
                    break;
                case 'price_desc':
                    $order = " ORDER BY p.price DESC";
                    break;
                case 'newest':
                    $order = " ORDER BY p.created_at DESC";
                    break;
            }
        }

        $query .= $order;

        // Paginación con parámetros con nombre
        if (!empty($filters['limit'])) {
            $query .= " LIMIT :limit";
            $params[':limit'] = (int)$filters['limit'];

            if (!empty($filters['offset'])) {
                $query .= " OFFSET :offset";
                $params[':offset'] = (int)$filters['offset'];
            }
        }

        $stmt = $this->db->prepare($query);

        // Bind de parámetros con tipos específicos
        foreach ($params as $key => $value) {
            if (strpos($key, ':limit') !== false || strpos($key, ':offset') !== false) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        $stmt->execute();
        $products = $stmt->fetchAll();

        // Obtener imágenes y variantes para cada producto
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
            $product['variants'] = $this->getProductVariants($product['id']);
        }

        return $products;
    }

    public function getProductById($id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch();

        if ($product) {
            $product['images'] = $this->getProductImages($product['id']);
            $product['variants'] = $this->getProductVariants($product['id']);
        }

        return $product;
    }

    public function getProductImages($productId) {
        $stmt = $this->db->prepare("SELECT * FROM product_images WHERE product_id = :product_id ORDER BY is_main DESC");
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getProductVariants($productId) {
        $stmt = $this->db->prepare("SELECT * FROM product_variants WHERE product_id = :product_id AND stock > 0");
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function addProduct($data, $images) {
        // Iniciar transacción
        $this->db->connect()->beginTransaction();

        try {
            // Insertar producto principal
            $stmt = $this->db->prepare("
                INSERT INTO products (name, description, price, category, gender)
                VALUES (:name, :description, :price, :category, :gender)
            ");

            $stmt->bindValue(':name', $data['name']);
            $stmt->bindValue(':description', $data['description']);
            $stmt->bindValue(':price', $data['price']);
            $stmt->bindValue(':category', $data['category']);
            $stmt->bindValue(':gender', $data['gender'] ?? 'unisex');
            $stmt->execute();

            $productId = $this->db->lastInsertId();

            // Insertar variantes
            foreach ($data['variants'] as $variant) {
                $stmt = $this->db->prepare("
                    INSERT INTO product_variants (product_id, size, color, stock, sku)
                    VALUES (:product_id, :size, :color, :stock, :sku)
                ");

                $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
                $stmt->bindValue(':size', $variant['size']);
                $stmt->bindValue(':color', $variant['color']);
                $stmt->bindValue(':stock', $variant['stock'], PDO::PARAM_INT);
                $stmt->bindValue(':sku', $variant['sku'] ?? '');
                $stmt->execute();
            }

            // Subir imágenes
            $mainImageSet = false;
            foreach ($images as $key => $image) {
                $imagePath = $this->uploadImage($image, $productId);

                $isMain = (!$mainImageSet && $key === 0) ? 1 : 0;
                if ($isMain) $mainImageSet = true;

                $stmt = $this->db->prepare("
                    INSERT INTO product_images (product_id, image_path, is_main)
                    VALUES (:product_id, :image_path, :is_main)
                ");

                $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
                $stmt->bindValue(':image_path', $imagePath);
                $stmt->bindValue(':is_main', $isMain, PDO::PARAM_BOOL);
                $stmt->execute();
            }

            // Confirmar transacción
            $this->db->connect()->commit();
            return $productId;

        } catch (Exception $e) {
            // Revertir en caso de error
            $this->db->connect()->rollBack();
            error_log("Error adding product: " . $e->getMessage());
            return false;
        }
    }

    private function uploadImage($image, $productId) {
        $targetDir = __DIR__ . "/../../assets/uploads/products/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileExt = pathinfo($image['name'], PATHINFO_EXTENSION);
        $fileName = "product_{$productId}_" . uniqid() . ".{$fileExt}";
        $targetFile = $targetDir . $fileName;

        // Verificar tipo de imagen
        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array(strtolower($fileExt), $allowedTypes)) {
            throw new Exception("Tipo de archivo no permitido");
        }

        // Mover archivo subido
        if (!move_uploaded_file($image['tmp_name'], $targetFile)) {
            throw new Exception("Error al subir la imagen");
        }

        return "products/" . $fileName;
    }

    public function updateProduct($id, $data, $newImages = []) {
        // Implementación similar a addProduct pero con UPDATE
        // Usando parámetros con nombre como en los otros métodos
    }

    public function deleteProduct($id) {
        try {
            $this->db->connect()->beginTransaction();

            // Eliminar imágenes primero
            $stmt = $this->db->prepare("DELETE FROM product_images WHERE product_id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Eliminar variantes
            $stmt = $this->db->prepare("DELETE FROM product_variants WHERE product_id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Finalmente eliminar el producto
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->db->connect()->commit();
            return true;

        } catch (Exception $e) {
            $this->db->connect()->rollBack();
            error_log("Error deleting product: " . $e->getMessage());
            return false;
        }
    }

    public function getCategories() {
        $stmt = $this->db->prepare("SELECT category FROM products GROUP BY category");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAvailableSizes() {
        $stmt = $this->db->prepare("SELECT size FROM product_variants GROUP BY size ORDER BY size");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAvailableColors() {
        $stmt = $this->db->prepare("SELECT color FROM product_variants GROUP BY color ORDER BY color");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}