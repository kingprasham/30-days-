<?php
/**
 * Contract Class
 * Customer Tracking & Billing Management System
 */

class Contract {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all contracts
     */
    public function getAll($filters = []) {
        $sql = "SELECT con.*, c.name as customer_name, c.location as customer_location,
                       s.name as state_name,
                       DATEDIFF(con.end_date, CURDATE()) as days_remaining
                FROM contracts con
                LEFT JOIN customers c ON con.customer_id = c.id
                LEFT JOIN states s ON c.state_id = s.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['customer_id'])) {
            $sql .= " AND con.customer_id = ?";
            $params[] = $filters['customer_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND con.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['expiring_soon'])) {
            $sql .= " AND con.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                      AND con.status = 'active'";
            $params[] = $filters['expiring_soon'];
        }

        $sql .= " ORDER BY con.end_date";

        return $this->db->query($sql, $params);
    }

    /**
     * Get contract by ID
     */
    public function getById($id) {
        $sql = "SELECT con.*, c.name as customer_name, c.location as customer_location,
                       s.name as state_name
                FROM contracts con
                LEFT JOIN customers c ON con.customer_id = c.id
                LEFT JOIN states s ON c.state_id = s.id
                WHERE con.id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Create contract
     */
    public function create($data) {
        $sql = "INSERT INTO contracts (customer_id, contract_number, start_date, end_date,
                renewal_date, terms, value, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $id = $this->db->insert($sql, [
            $data['customer_id'],
            $data['contract_number'] ?? null,
            formatDateDB($data['start_date']),
            formatDateDB($data['end_date']),
            formatDateDB($data['renewal_date'] ?? null),
            $data['terms'] ?? null,
            $data['value'] ?? 0,
            $data['status'] ?? 'active'
        ]);

        // Update customer contract dates
        $this->db->execute(
            "UPDATE customers SET contract_start_date = ?, contract_end_date = ? WHERE id = ?",
            [formatDateDB($data['start_date']), formatDateDB($data['end_date']), $data['customer_id']]
        );

        logActivity('create', 'contract', $id);
        return $id;
    }

    /**
     * Update contract
     */
    public function update($id, $data) {
        $sql = "UPDATE contracts SET
                customer_id = ?,
                contract_number = ?,
                start_date = ?,
                end_date = ?,
                renewal_date = ?,
                terms = ?,
                value = ?,
                status = ?
                WHERE id = ?";

        $result = $this->db->execute($sql, [
            $data['customer_id'],
            $data['contract_number'] ?? null,
            formatDateDB($data['start_date']),
            formatDateDB($data['end_date']),
            formatDateDB($data['renewal_date'] ?? null),
            $data['terms'] ?? null,
            $data['value'] ?? 0,
            $data['status'] ?? 'active',
            $id
        ]);

        // Update customer contract dates if this is the active contract
        if ($data['status'] === 'active') {
            $this->db->execute(
                "UPDATE customers SET contract_start_date = ?, contract_end_date = ? WHERE id = ?",
                [formatDateDB($data['start_date']), formatDateDB($data['end_date']), $data['customer_id']]
            );
        }

        logActivity('update', 'contract', $id);
        return $result;
    }

    /**
     * Delete contract
     */
    public function delete($id) {
        $contract = $this->getById($id);
        logActivity('delete', 'contract', $id, ['customer' => $contract['customer_name']]);

        return $this->db->execute("DELETE FROM contracts WHERE id = ?", [$id]);
    }

    /**
     * Get expiring contracts
     */
    public function getExpiring($days = 30) {
        return $this->db->query(
            "SELECT con.*, c.name as customer_name, c.location, s.name as state_name,
                    DATEDIFF(con.end_date, CURDATE()) as days_remaining
             FROM contracts con
             JOIN customers c ON con.customer_id = c.id
             LEFT JOIN states s ON c.state_id = s.id
             WHERE con.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
             AND con.status = 'active'
             ORDER BY con.end_date",
            [$days]
        );
    }

    /**
     * Renew contract
     */
    public function renew($id, $newEndDate, $newValue = null) {
        $contract = $this->getById($id);
        if (!$contract) {
            throw new Exception('Contract not found');
        }

        // Mark old contract as renewed
        $this->db->execute(
            "UPDATE contracts SET status = 'renewed' WHERE id = ?",
            [$id]
        );

        // Create new contract
        $newId = $this->create([
            'customer_id' => $contract['customer_id'],
            'contract_number' => $contract['contract_number'] ? $contract['contract_number'] . '-R' : null,
            'start_date' => $contract['end_date'],
            'end_date' => $newEndDate,
            'terms' => $contract['terms'],
            'value' => $newValue ?? $contract['value'],
            'status' => 'active'
        ]);

        logActivity('renew', 'contract', $id, ['new_contract_id' => $newId]);

        return $newId;
    }

    /**
     * Get contract statistics
     */
    public function getStats() {
        return [
            'total' => $this->db->getValue("SELECT COUNT(*) FROM contracts"),
            'active' => $this->db->getValue("SELECT COUNT(*) FROM contracts WHERE status = 'active'"),
            'expiring_30_days' => $this->db->getValue(
                "SELECT COUNT(*) FROM contracts
                 WHERE status = 'active'
                 AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)"
            ),
            'expired' => $this->db->getValue("SELECT COUNT(*) FROM contracts WHERE status = 'expired'"),
            'total_value' => $this->db->getValue(
                "SELECT COALESCE(SUM(value), 0) FROM contracts WHERE status = 'active'"
            )
        ];
    }
}
