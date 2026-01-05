<?php
/**
 * Customer Class
 * Customer Tracking & Billing Management System
 */

class Customer {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all customers
     */
    public function getAll($filters = []) {
        $sql = "SELECT c.*, s.name as state_name,
                       (SELECT MAX(challan_date) FROM challans WHERE customer_id = c.id) as last_challan_date,
                       (SELECT COUNT(*) FROM challans WHERE customer_id = c.id) as challan_count,
                       (SELECT COALESCE(SUM(total_amount), 0) FROM challans WHERE customer_id = c.id) as total_revenue
                FROM customers c
                LEFT JOIN states s ON c.state_id = s.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['state_id'])) {
            $sql .= " AND c.state_id = ?";
            $params[] = $filters['state_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (c.name LIKE ? OR c.normalized_name LIKE ? OR c.location LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND c.installation_date >= ?";
            $params[] = formatDateDB($filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND c.installation_date <= ?";
            $params[] = formatDateDB($filters['to_date']);
        }

        $sql .= " ORDER BY c.name";

        return $this->db->query($sql, $params);
    }

    /**
     * Get customer by ID
     */
    public function getById($id) {
        $sql = "SELECT c.*, s.name as state_name
                FROM customers c
                LEFT JOIN states s ON c.state_id = s.id
                WHERE c.id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Get customer with full details
     */
    public function getWithDetails($id) {
        $customer = $this->getById($id);
        if (!$customer) return null;

        // Get locations
        $customer['locations'] = $this->db->query(
            "SELECT * FROM customer_locations WHERE customer_id = ? ORDER BY location_identifier",
            [$id]
        );

        // Get assigned dealers
        $customer['dealers'] = $this->db->query(
            "SELECT cd.*, d.company_name, d.contact_person
             FROM customer_dealers cd
             JOIN dealers d ON cd.dealer_id = d.id
             WHERE cd.customer_id = ?",
            [$id]
        );

        // Get product prices
        $customer['product_prices'] = $this->db->query(
            "SELECT cpp.*, p.name as product_name
             FROM customer_product_prices cpp
             JOIN products p ON cpp.product_id = p.id
             WHERE cpp.customer_id = ?",
            [$id]
        );

        // Get recent challans
        $customer['recent_challans'] = $this->db->query(
            "SELECT * FROM challans WHERE customer_id = ? ORDER BY challan_date DESC LIMIT 10",
            [$id]
        );

        // Get contracts
        $customer['contracts'] = $this->db->query(
            "SELECT * FROM contracts WHERE customer_id = ? ORDER BY start_date DESC",
            [$id]
        );

        return $customer;
    }

    /**
     * Get customers for dropdown
     */
    public function getForDropdown() {
        return $this->db->query(
            "SELECT id, name, normalized_name, location FROM customers WHERE status = 'active' ORDER BY name"
        );
    }

    /**
     * Create customer
     */
    public function create($data) {
        $normalizer = new NameNormalizer();
        $normalized = $normalizer->normalize($data['name']);

        $sql = "INSERT INTO customers (name, normalized_name, state_id, location,
                installation_date, monthly_commitment, contract_start_date,
                contract_end_date, status, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $id = $this->db->insert($sql, [
            trim($data['name']),
            $normalized['base_name'],
            $data['state_id'] ?: null,
            $data['location'] ?? null,
            formatDateDB($data['installation_date'] ?? null),
            $data['monthly_commitment'] ?? 0,
            formatDateDB($data['contract_start_date'] ?? null),
            formatDateDB($data['contract_end_date'] ?? null),
            $data['status'] ?? 'active',
            $data['notes'] ?? null
        ]);

        // If there's a location identifier, add to customer_locations
        if (!empty($normalized['location_id'])) {
            $this->addLocation($id, $normalized['location_id'], $data['name']);
        }

        logActivity('create', 'customer', $id, ['name' => $data['name']]);
        return $id;
    }

    /**
     * Update customer
     */
    public function update($id, $data) {
        $normalizer = new NameNormalizer();
        $normalized = $normalizer->normalize($data['name']);

        $sql = "UPDATE customers SET
                name = ?,
                normalized_name = ?,
                state_id = ?,
                location = ?,
                installation_date = ?,
                monthly_commitment = ?,
                contract_start_date = ?,
                contract_end_date = ?,
                status = ?,
                notes = ?
                WHERE id = ?";

        $result = $this->db->execute($sql, [
            trim($data['name']),
            $normalized['base_name'],
            $data['state_id'] ?: null,
            $data['location'] ?? null,
            formatDateDB($data['installation_date'] ?? null),
            $data['monthly_commitment'] ?? 0,
            formatDateDB($data['contract_start_date'] ?? null),
            formatDateDB($data['contract_end_date'] ?? null),
            $data['status'] ?? 'active',
            $data['notes'] ?? null,
            $id
        ]);

        logActivity('update', 'customer', $id);
        return $result;
    }

    /**
     * Delete customer
     */
    public function delete($id) {
        $customer = $this->getById($id);

        // Delete related data (cascades are set up in DB, but being explicit)
        $this->db->execute("DELETE FROM customer_locations WHERE customer_id = ?", [$id]);
        $this->db->execute("DELETE FROM customer_dealers WHERE customer_id = ?", [$id]);
        $this->db->execute("DELETE FROM customer_product_prices WHERE customer_id = ?", [$id]);
        $this->db->execute("DELETE FROM challans WHERE customer_id = ?", [$id]);
        $this->db->execute("DELETE FROM contracts WHERE customer_id = ?", [$id]);

        logActivity('delete', 'customer', $id, ['name' => $customer['name']]);

        return $this->db->execute("DELETE FROM customers WHERE id = ?", [$id]);
    }

    /**
     * Add customer location
     */
    public function addLocation($customerId, $identifier, $name, $address = null) {
        return $this->db->insert(
            "INSERT INTO customer_locations (customer_id, location_identifier, location_name, address)
             VALUES (?, ?, ?, ?)",
            [$customerId, $identifier, $name, $address]
        );
    }

    /**
     * Get customer location
     */
    public function getLocation($customerId, $identifier) {
        return $this->db->queryOne(
            "SELECT * FROM customer_locations WHERE customer_id = ? AND location_identifier = ?",
            [$customerId, $identifier]
        );
    }

    /**
     * Assign dealer to customer
     */
    public function assignDealer($customerId, $dealerId, $commission = 0) {
        // Check if already assigned
        $existing = $this->db->queryOne(
            "SELECT * FROM customer_dealers WHERE customer_id = ? AND dealer_id = ?",
            [$customerId, $dealerId]
        );

        if ($existing) {
            return $this->db->execute(
                "UPDATE customer_dealers SET commission_amount = ?, status = 'active' WHERE id = ?",
                [$commission, $existing['id']]
            );
        }

        return $this->db->insert(
            "INSERT INTO customer_dealers (customer_id, dealer_id, commission_amount, assigned_date)
             VALUES (?, ?, ?, CURDATE())",
            [$customerId, $dealerId, $commission]
        );
    }

    /**
     * Remove dealer from customer
     */
    public function removeDealer($customerId, $dealerId) {
        return $this->db->execute(
            "DELETE FROM customer_dealers WHERE customer_id = ? AND dealer_id = ?",
            [$customerId, $dealerId]
        );
    }

    /**
     * Set product price for customer
     */
    public function setProductPrice($customerId, $productId, $price, $escalationPercent = 0, $escalationDate = null) {
        $existing = $this->db->queryOne(
            "SELECT * FROM customer_product_prices WHERE customer_id = ? AND product_id = ?",
            [$customerId, $productId]
        );

        if ($existing) {
            return $this->db->execute(
                "UPDATE customer_product_prices SET price = ?, escalation_percent = ?, escalation_date = ? WHERE id = ?",
                [$price, $escalationPercent, formatDateDB($escalationDate), $existing['id']]
            );
        }

        return $this->db->insert(
            "INSERT INTO customer_product_prices (customer_id, product_id, price, escalation_percent, escalation_date, effective_from)
             VALUES (?, ?, ?, ?, ?, CURDATE())",
            [$customerId, $productId, $price, $escalationPercent, formatDateDB($escalationDate)]
        );
    }

    /**
     * Find customer by name (with fuzzy matching)
     */
    public function findByName($name) {
        $normalizer = new NameNormalizer();
        $normalized = $normalizer->normalize($name);

        // Try exact normalized match first
        $customer = $this->db->queryOne(
            "SELECT * FROM customers WHERE normalized_name = ?",
            [$normalized['base_name']]
        );

        if ($customer) return $customer;

        // Try fuzzy match
        $suggestions = $normalizer->findSimilar($normalized['base_name'], 'customer');
        if (!empty($suggestions)) {
            return $this->db->queryOne(
                "SELECT * FROM customers WHERE normalized_name = ?",
                [$suggestions[0]['corrected_name']]
            );
        }

        return null;
    }

    /**
     * Get or create customer by name
     */
    public function getOrCreate($name, $stateId = null, $location = null) {
        $existing = $this->findByName($name);
        if ($existing) {
            // Check if we need to add a new location
            $normalizer = new NameNormalizer();
            $normalized = $normalizer->normalize($name);

            if (!empty($normalized['location_id'])) {
                $locExists = $this->getLocation($existing['id'], $normalized['location_id']);
                if (!$locExists) {
                    $this->addLocation($existing['id'], $normalized['location_id'], $name);
                }
            }

            return $existing;
        }

        // Create new customer
        $id = $this->create([
            'name' => $name,
            'state_id' => $stateId,
            'location' => $location,
            'status' => 'active'
        ]);

        return $this->getById($id);
    }

    /**
     * Get 30-day defaulters
     */
    public function getDefaulters($days = 30) {
        $sql = "SELECT c.*, s.name as state_name,
                       MAX(ch.challan_date) as last_challan_date,
                       DATEDIFF(CURDATE(), MAX(ch.challan_date)) as days_inactive,
                       COUNT(ch.id) as total_challans
                FROM customers c
                LEFT JOIN states s ON c.state_id = s.id
                LEFT JOIN challans ch ON c.id = ch.customer_id
                WHERE c.status = 'active'
                GROUP BY c.id
                HAVING days_inactive > ? OR days_inactive IS NULL
                ORDER BY days_inactive DESC";

        return $this->db->query($sql, [$days]);
    }

    /**
     * Get new customers this month
     */
    public function getNewThisMonth() {
        return $this->db->query(
            "SELECT c.*, s.name as state_name
             FROM customers c
             LEFT JOIN states s ON c.state_id = s.id
             WHERE MONTH(c.created_at) = MONTH(CURDATE())
             AND YEAR(c.created_at) = YEAR(CURDATE())
             ORDER BY c.created_at DESC"
        );
    }

    /**
     * Get customer statistics
     */
    public function getStats() {
        return [
            'total' => $this->db->getValue("SELECT COUNT(*) FROM customers"),
            'active' => $this->db->getValue("SELECT COUNT(*) FROM customers WHERE status = 'active'"),
            'new_this_month' => $this->db->getValue(
                "SELECT COUNT(*) FROM customers
                 WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
            ),
            'defaulters' => $this->db->getValue(
                "SELECT COUNT(*) FROM v_defaulters"
            )
        ];
    }
}
