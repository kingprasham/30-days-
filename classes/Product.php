<?php
/**
 * Product Class
 * Customer Tracking & Billing Management System
 */

class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ========== CATEGORIES ==========

    /**
     * Get all categories
     */
    public function getAllCategories() {
        return $this->db->query(
            "SELECT c.*, COUNT(p.id) as product_count
             FROM categories c
             LEFT JOIN products p ON c.id = p.category_id
             GROUP BY c.id
             ORDER BY c.name"
        );
    }

    /**
     * Get category by ID
     */
    public function getCategoryById($id) {
        return $this->db->queryOne("SELECT * FROM categories WHERE id = ?", [$id]);
    }

    /**
     * Get categories for dropdown
     */
    public function getCategoriesForDropdown() {
        return $this->db->query(
            "SELECT id, name FROM categories WHERE status = 'active' ORDER BY name"
        );
    }

    /**
     * Create category
     */
    public function createCategory($data) {
        $sql = "INSERT INTO categories (name, description, status) VALUES (?, ?, ?)";
        $id = $this->db->insert($sql, [
            trim($data['name']),
            $data['description'] ?? null,
            $data['status'] ?? 'active'
        ]);

        logActivity('create', 'category', $id, ['name' => $data['name']]);
        return $id;
    }

    /**
     * Update category
     */
    public function updateCategory($id, $data) {
        $sql = "UPDATE categories SET name = ?, description = ?, status = ? WHERE id = ?";
        $result = $this->db->execute($sql, [
            trim($data['name']),
            $data['description'] ?? null,
            $data['status'] ?? 'active',
            $id
        ]);

        logActivity('update', 'category', $id);
        return $result;
    }

    /**
     * Delete category
     */
    public function deleteCategory($id) {
        $count = $this->db->getValue(
            "SELECT COUNT(*) FROM products WHERE category_id = ?",
            [$id]
        );

        if ($count > 0) {
            throw new Exception('Cannot delete category with existing products.');
        }

        $category = $this->getCategoryById($id);
        logActivity('delete', 'category', $id, ['name' => $category['name']]);

        return $this->db->execute("DELETE FROM categories WHERE id = ?", [$id]);
    }

    // ========== PRODUCTS ==========

    /**
     * Get all products
     */
    public function getAll($filters = []) {
        $sql = "SELECT p.*, c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.short_name LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY c.name, p.name";

        return $this->db->query($sql, $params);
    }

    /**
     * Get product by ID
     */
    public function getById($id) {
        $sql = "SELECT p.*, c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Get products for dropdown
     */
    public function getForDropdown() {
        return $this->db->query(
            "SELECT p.id, p.name, p.short_name, c.name as category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.status = 'active'
             ORDER BY c.name, p.name"
        );
    }

    /**
     * Get products grouped by category
     */
    public function getGroupedByCategory() {
        $products = $this->db->query(
            "SELECT p.*, c.name as category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.status = 'active'
             ORDER BY c.name, p.name"
        );

        $grouped = [];
        foreach ($products as $product) {
            $catName = $product['category_name'] ?? 'Uncategorized';
            if (!isset($grouped[$catName])) {
                $grouped[$catName] = [];
            }
            $grouped[$catName][] = $product;
        }

        return $grouped;
    }

    /**
     * Create product
     */
    public function create($data) {
        $sql = "INSERT INTO products (category_id, name, short_name, unit, base_price, status)
                VALUES (?, ?, ?, ?, ?, ?)";

        $id = $this->db->insert($sql, [
            $data['category_id'],
            trim($data['name']),
            $data['short_name'] ?? null,
            $data['unit'] ?? 'Pcs',
            $data['base_price'] ?? 0,
            $data['status'] ?? 'active'
        ]);

        logActivity('create', 'product', $id, ['name' => $data['name']]);
        return $id;
    }

    /**
     * Update product
     */
    public function update($id, $data) {
        $sql = "UPDATE products SET
                category_id = ?,
                name = ?,
                short_name = ?,
                unit = ?,
                base_price = ?,
                status = ?
                WHERE id = ?";

        $result = $this->db->execute($sql, [
            $data['category_id'],
            trim($data['name']),
            $data['short_name'] ?? null,
            $data['unit'] ?? 'Pcs',
            $data['base_price'] ?? 0,
            $data['status'] ?? 'active',
            $id
        ]);

        logActivity('update', 'product', $id);
        return $result;
    }

    /**
     * Delete product
     */
    public function delete($id) {
        $count = $this->db->getValue(
            "SELECT COUNT(*) FROM challan_items WHERE product_id = ?",
            [$id]
        );

        if ($count > 0) {
            throw new Exception('Cannot delete product with existing challan entries.');
        }

        $product = $this->getById($id);
        logActivity('delete', 'product', $id, ['name' => $product['name']]);

        return $this->db->execute("DELETE FROM products WHERE id = ?", [$id]);
    }

    /**
     * Get product by name (for Excel import matching)
     */
    public function getByName($name) {
        $normalized = trim(strtolower($name));

        // Try exact match first
        $product = $this->db->queryOne(
            "SELECT * FROM products WHERE LOWER(TRIM(name)) = ?",
            [$normalized]
        );

        if ($product) return $product;

        // Try short name
        $product = $this->db->queryOne(
            "SELECT * FROM products WHERE LOWER(TRIM(short_name)) = ?",
            [$normalized]
        );

        return $product;
    }

    /**
     * Create or get product by name
     */
    public function getOrCreate($name, $categoryId = null) {
        $existing = $this->getByName($name);
        if ($existing) return $existing;

        // Ensure all categories exist
        $this->ensureProductCategories();
        
        // Auto-detect category based on product name
        $detectedCategoryId = $categoryId ?? $this->detectCategory($name);
        
        // Default product prices based on product name patterns
        $basePrice = $this->guessProductPrice($name);

        try {
            // Create new product with guessed price and auto-detected category
            $id = $this->create([
                'name' => $name,
                'category_id' => $detectedCategoryId,
                'base_price' => $basePrice,
                'status' => 'active'
            ]);

            return $this->getById($id);
        } catch (Exception $e) {
            // Log error but don't crash the import
            error_log("Product creation failed for '$name': " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Ensure all product categories exist
     */
    private function ensureProductCategories() {
        static $initialized = false;
        if ($initialized) return;
        
        $categories = [
            ['PVC Film', 'White PVC Film products (A3, A4, A5)'],
            ['Photo Paper', 'GSM Photo Paper products (180, 210, 230, 260 GSM)'],
            ['Blue Film', 'Blue Base Film products (various sizes)'],
            ['General', 'Other products'],
        ];
        
        foreach ($categories as $cat) {
            $existing = $this->db->queryOne("SELECT id FROM categories WHERE name = ?", [$cat[0]]);
            if (!$existing) {
                $this->db->insert(
                    "INSERT INTO categories (name, description, status) VALUES (?, ?, 'active')",
                    [$cat[0], $cat[1]]
                );
            }
        }
        
        $initialized = true;
    }
    
    /**
     * Auto-detect category based on product name
     */
    private function detectCategory($name) {
        $nameLower = strtolower($name);
        
        // PVC Film products
        if (strpos($nameLower, 'pvc') !== false) {
            $cat = $this->db->queryOne("SELECT id FROM categories WHERE name = 'PVC Film'");
            if ($cat) return $cat['id'];
        }
        
        // Blue Film products
        if (strpos($nameLower, 'blue') !== false && strpos($nameLower, 'film') !== false) {
            $cat = $this->db->queryOne("SELECT id FROM categories WHERE name = 'Blue Film'");
            if ($cat) return $cat['id'];
        }
        
        // Photo Paper products (GSM or CC)
        if (preg_match('/(gsm|cc.*photo|photo.*paper)/i', $nameLower)) {
            $cat = $this->db->queryOne("SELECT id FROM categories WHERE name = 'Photo Paper'");
            if ($cat) return $cat['id'];
        }
        
        // Default to General
        $cat = $this->db->queryOne("SELECT id FROM categories WHERE name = 'General'");
        return $cat ? $cat['id'] : 1;
    }

    /**
     * Guess product price based on name patterns
     */
    private function guessProductPrice($name) {
        $name = strtolower($name);

        // Price mapping based on product patterns
        $priceMap = [
            'a3 white pvc' => 12.00,
            'a4 white pvc' => 8.00,
            'a5 white pvc' => 5.00,
            '260 gsm.*a4' => 6.00,
            '260 gsm.*a5' => 4.00,
            '230cc.*a3' => 15.00,
            '230cc.*a4' => 10.00,
            '230cc.*a5' => 7.00,
            '210cc.*a3' => 13.00,
            '210cc.*a4' => 9.00,
            '180.*a3' => 11.00,
            '180.*a4' => 8.00,
            'blue film.*8.*10' => 20.00,
            'blue film.*a3' => 18.00,
            'blue film.*a4' => 15.00,
            'blue film.*13.*17' => 25.00,
            'blue film.*10.*12' => 22.00,
        ];

        foreach ($priceMap as $pattern => $price) {
            if (preg_match('/' . $pattern . '/i', $name)) {
                return $price;
            }
        }

        // Default price for unknown products
        return 10.00;
    }
}
