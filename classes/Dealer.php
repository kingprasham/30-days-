<?php
/**
 * Dealer Class
 * Customer Tracking & Billing Management System
 */

class Dealer {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all dealers
     */
    public function getAll($filters = []) {
        $sql = "SELECT d.*, s.name as state_name
                FROM dealers d
                LEFT JOIN states s ON d.state_id = s.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND d.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['state_id'])) {
            $sql .= " AND d.state_id = ?";
            $params[] = $filters['state_id'];
        }

        if (!empty($filters['territory'])) {
            $sql .= " AND d.territory = ?";
            $params[] = $filters['territory'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (d.company_name LIKE ? OR d.contact_person LIKE ? OR d.mobile LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY d.company_name";

        return $this->db->query($sql, $params);
    }

    /**
     * Get dealer by ID
     */
    public function getById($id) {
        $sql = "SELECT d.*, s.name as state_name
                FROM dealers d
                LEFT JOIN states s ON d.state_id = s.id
                WHERE d.id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Get dealers for dropdown
     */
    public function getForDropdown() {
        return $this->db->query(
            "SELECT id, company_name, contact_person FROM dealers WHERE status = 'active' ORDER BY company_name"
        );
    }

    /**
     * Create new dealer
     */
    public function create($data) {
        $sql = "INSERT INTO dealers (company_name, address, state_id, location, pincode,
                gst_number, contact_person, designation, mobile, email, territory,
                service_location, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $id = $this->db->insert($sql, [
            trim($data['company_name']),
            $data['address'] ?? null,
            $data['state_id'] ?: null,
            $data['location'] ?? null,
            $data['pincode'] ?? null,
            $data['gst_number'] ?? null,
            $data['contact_person'] ?? null,
            $data['designation'] ?? null,
            $data['mobile'] ?? null,
            $data['email'] ?? null,
            $data['territory'] ?? 'North',
            $data['service_location'] ?? null,
            $data['status'] ?? 'active'
        ]);

        logActivity('create', 'dealer', $id, ['name' => $data['company_name']]);
        return $id;
    }

    /**
     * Update dealer
     */
    public function update($id, $data) {
        $sql = "UPDATE dealers SET
                company_name = ?,
                address = ?,
                state_id = ?,
                location = ?,
                pincode = ?,
                gst_number = ?,
                contact_person = ?,
                designation = ?,
                mobile = ?,
                email = ?,
                territory = ?,
                service_location = ?,
                status = ?
                WHERE id = ?";

        $result = $this->db->execute($sql, [
            trim($data['company_name']),
            $data['address'] ?? null,
            $data['state_id'] ?: null,
            $data['location'] ?? null,
            $data['pincode'] ?? null,
            $data['gst_number'] ?? null,
            $data['contact_person'] ?? null,
            $data['designation'] ?? null,
            $data['mobile'] ?? null,
            $data['email'] ?? null,
            $data['territory'] ?? 'North',
            $data['service_location'] ?? null,
            $data['status'] ?? 'active',
            $id
        ]);

        logActivity('update', 'dealer', $id);
        return $result;
    }

    /**
     * Delete dealer
     */
    public function delete($id) {
        // Check if dealer is assigned to customers
        $count = $this->db->getValue(
            "SELECT COUNT(*) FROM customer_dealers WHERE dealer_id = ?",
            [$id]
        );

        if ($count > 0) {
            throw new Exception('Cannot delete dealer. They are assigned to customers.');
        }

        $dealer = $this->getById($id);
        logActivity('delete', 'dealer', $id, ['name' => $dealer['company_name']]);

        return $this->db->execute("DELETE FROM dealers WHERE id = ?", [$id]);
    }

    /**
     * Get dealer statistics
     */
    public function getStats() {
        return [
            'total' => $this->db->getValue("SELECT COUNT(*) FROM dealers"),
            'active' => $this->db->getValue("SELECT COUNT(*) FROM dealers WHERE status = 'active'"),
            'by_territory' => $this->db->query(
                "SELECT territory, COUNT(*) as count FROM dealers GROUP BY territory"
            )
        ];
    }

    /**
     * Get dealers with customer count
     */
    public function getWithCustomerCount() {
        return $this->db->query(
            "SELECT d.*, s.name as state_name,
                    COUNT(DISTINCT cd.customer_id) as customer_count,
                    COALESCE(SUM(cd.commission_amount), 0) as total_commission
             FROM dealers d
             LEFT JOIN states s ON d.state_id = s.id
             LEFT JOIN customer_dealers cd ON d.id = cd.dealer_id AND cd.status = 'active'
             GROUP BY d.id
             ORDER BY d.company_name"
        );
    }
}
