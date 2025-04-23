<?php
require_once __DIR__ . '/../config/database.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Obtiene todos los productos con información completa
     * @param array $filters Filtros opcionales
     * @return array Lista de productos con sus variantes e imágenes
     */
    public function getAllProducts($filters = []) {
        $query = "SELECT p.* FROM products p WHERE 1=1";
        $params = [];

        // Filtros
        if (!empty($filters['category'])) {
            $query .= " AND p.category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['gender'])) {
            $query .= " AND p.gender = :gender";
            $params[':gender'] = $filters['gender'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
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
        $products = $stmt->fetchAll();

        // Obtener variantes e imágenes para cada producto
        foreach ($products as &$product) {
            $product['variants'] = $this->getProductVariants($product['id']);
            $product['images'] = $this->getProductImages($product['id']);
        }

        return $products;
    }

    /**
     * Obtiene el conteo total de productos con filtros opcionales
     * @param array $filters Filtros a aplicar
     * @return int Total de productos
     */
    public function getTotalProductsCount($filters = []) {
        $query = "SELECT COUNT(*) as total FROM products WHERE 1=1";
        $params = [];

        if (!empty($filters['category'])) {
            $query .= " AND category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['gender'])) {
            $query .= " AND gender = :gender";
            $params[':gender'] = $filters['gender'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (name LIKE :search OR description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return (int)$result['total'];
    }

    /**
     * Obtiene productos con información de stock
     */
    public function getProductsWithStock($limit = null, $offset = null) {
        $query = "SELECT p.*, SUM(pv.stock) as total_stock 
                 FROM products p
                 LEFT JOIN product_variants pv ON p.id = pv.product_id
                 GROUP BY p.id";

        if ($limit !== null) {
            $query .= " LIMIT :limit";
            if ($offset !== null) {
                $query .= " OFFSET :offset";
            }
        }

        $stmt = $this->db->prepare($query);

        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll();
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
        $stmt = $this->db->prepare("SELECT * FROM product_variants WHERE product_id = :product_id");
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function addProduct($data, $images) {
        $conn = $this->db->connect();
        $conn->beginTransaction();
        $transactionActive = true;

        try {
            // Insertar producto principal
            $stmt = $conn->prepare("
                INSERT INTO products (name, description, price, category, gender)
                VALUES (:name, :description, :price, :category, :gender)
            ");

            $stmt->execute([
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':price' => $data['price'],
                ':category' => $data['category'],
                ':gender' => $data['gender'] ?? 'unisex'
            ]);

            $productId = $conn->lastInsertId();

            // Insertar variantes
            foreach ($data['variants'] as $variant) {
                $stmt = $conn->prepare("
                    INSERT INTO product_variants (product_id, size, color, stock, sku)
                    VALUES (:product_id, :size, :color, :stock, :sku)
                ");

                $stmt->execute([
                    ':product_id' => $productId,
                    ':size' => $variant['size'],
                    ':color' => $variant['color'],
                    ':stock' => (int)$variant['stock'],
                    ':sku' => $variant['sku'] ?? ''
                ]);
            }

            // Subir imágenes
            $mainImageSet = false;
            foreach ($images as $key => $image) {
                $imagePath = $this->uploadImage($image, $productId);

                $isMain = (!$mainImageSet && $key === 0) ? 1 : 0;
                if ($isMain) $mainImageSet = true;

                $stmt = $conn->prepare("
                    INSERT INTO product_images (product_id, image_path, is_main)
                    VALUES (:product_id, :image_path, :is_main)
                ");

                $stmt->execute([
                    ':product_id' => $productId,
                    ':image_path' => $imagePath,
                    ':is_main' => $isMain
                ]);
            }

            $conn->commit();
            $transactionActive = false;
            return $productId;

        } catch (Exception $e) {
            if ($transactionActive && $conn->inTransaction()) {
                try {
                    $conn->rollBack();
                } catch (PDOException $rollbackEx) {
                    error_log("Error al hacer rollback: " . $rollbackEx->getMessage());
                }
            }
            error_log("Error adding product: " . $e->getMessage());
            return false;
        }
    }

    public function updateProduct($id, $data, $newImages = [], $mainImageId = null) {
        $conn = $this->db->connect();
        $conn->beginTransaction();
        $transactionActive = true;

        try {
            // 1. Actualizar información básica del producto
            $stmt = $conn->prepare("
                UPDATE products 
                SET name = :name, description = :description, price = :price, 
                    category = :category, gender = :gender, updated_at = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                ':id' => $id,
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':price' => $data['price'],
                ':category' => $data['category'],
                ':gender' => $data['gender'] ?? 'unisex'
            ]);

            // 2. Manejar variantes
            $this->updateProductVariants($conn, $id, $data['variants']);

            // 3. Manejar imágenes
            $this->updateProductImages($conn, $id, $newImages, $mainImageId);

            $conn->commit();
            $transactionActive = false;
            return true;

        } catch (Exception $e) {
            if ($transactionActive && $conn->inTransaction()) {
                try {
                    $conn->rollBack();
                } catch (PDOException $rollbackEx) {
                    error_log("Error al hacer rollback: " . $rollbackEx->getMessage());
                }
            }
            error_log("Error updating product: " . $e->getMessage());
            return false;
        }
    }

    private function updateProductVariants($conn, $productId, $variants) {
        // 1. Eliminar variantes existentes
        $stmt = $conn->prepare("DELETE FROM product_variants WHERE product_id = :id");
        $stmt->execute([':id' => $productId]);

        // 2. Insertar nuevas variantes
        foreach ($variants as $variant) {
            $stmt = $conn->prepare("
                INSERT INTO product_variants (product_id, size, color, stock, sku)
                VALUES (:product_id, :size, :color, :stock, :sku)
            ");

            $stmt->execute([
                ':product_id' => $productId,
                ':size' => $variant['size'],
                ':color' => $variant['color'],
                ':stock' => (int)$variant['stock'],
                ':sku' => $variant['sku'] ?? ''
            ]);
        }
    }

    private function updateProductImages($conn, $productId, $newImages, $mainImageId = null) {
        // 1. Manejar nueva imagen principal si se especifica
        if ($mainImageId) {
            // Resetear todas las imágenes a no principales
            $stmt = $conn->prepare("
                UPDATE product_images 
                SET is_main = 0 
                WHERE product_id = :product_id
            ");
            $stmt->execute([':product_id' => $productId]);

            // Establecer la nueva imagen principal
            $stmt = $conn->prepare("
                UPDATE product_images 
                SET is_main = 1 
                WHERE id = :id AND product_id = :product_id
            ");
            $stmt->execute([
                ':id' => $mainImageId,
                ':product_id' => $productId
            ]);
        }

        // 2. Subir nuevas imágenes
        foreach ($newImages as $image) {
            $imagePath = $this->uploadImage($image, $productId);

            $stmt = $conn->prepare("
                INSERT INTO product_images (product_id, image_path, is_main)
                VALUES (:product_id, :image_path, 0)
            ");
            $stmt->execute([
                ':product_id' => $productId,
                ':image_path' => $imagePath
            ]);
        }
    }

    public function deleteProduct($id) {
        $conn = $this->db->connect();
        $conn->beginTransaction();
        $transactionActive = true;

        try {
            // Eliminar imágenes primero
            $stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = :id");
            $stmt->execute([':id' => $id]);

            // Eliminar variantes
            $stmt = $conn->prepare("DELETE FROM product_variants WHERE product_id = :id");
            $stmt->execute([':id' => $id]);

            // Finalmente eliminar el producto
            $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $conn->commit();
            $transactionActive = false;
            return true;

        } catch (Exception $e) {
            if ($transactionActive && $conn->inTransaction()) {
                try {
                    $conn->rollBack();
                } catch (PDOException $rollbackEx) {
                    error_log("Error al hacer rollback: " . $rollbackEx->getMessage());
                }
            }
            error_log("Error deleting product: " . $e->getMessage());
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

    /**
     * Elimina una imagen de producto
     * @param int $imageId ID de la imagen a eliminar
     * @return bool True si se eliminó correctamente, false si falló
     */
    public function deleteImage($imageId) {
        try {
            // 1. Obtener información de la imagen
            $stmt = $this->db->prepare("SELECT * FROM product_images WHERE id = :id");
            $stmt->execute([':id' => $imageId]);
            $image = $stmt->fetch();

            if (!$image) {
                return false;
            }

            // 2. Eliminar el archivo físico
            $filePath = __DIR__ . '/../../assets/uploads/' . $image['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // 3. Eliminar el registro de la base de datos
            $stmt = $this->db->prepare("DELETE FROM product_images WHERE id = :id");
            $stmt->execute([':id' => $imageId]);

            // 4. Si era la imagen principal, asignar una nueva
            if ($image['is_main']) {
                $this->setNewMainImage($image['product_id']);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error deleting image: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Establece una nueva imagen principal para un producto
     * @param int $productId ID del producto
     */
    private function setNewMainImage($productId) {
        try {
            // Buscar la primera imagen disponible
            $stmt = $this->db->prepare("SELECT id FROM product_images WHERE product_id = :product_id LIMIT 1");
            $stmt->execute([':product_id' => $productId]);
            $newMainImage = $stmt->fetch();

            if ($newMainImage) {
                $stmt = $this->db->prepare("UPDATE product_images SET is_main = 1 WHERE id = :id");
                $stmt->execute([':id' => $newMainImage['id']]);
            }
        } catch (Exception $e) {
            error_log("Error setting new main image: " . $e->getMessage());
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