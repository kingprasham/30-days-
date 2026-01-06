<?php
/**
 * FollowUp Class
 * Customer Tracking & Billing Management System
 */

class FollowUp {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new follow-up
     */
    public function create($data) {
        $sql = "INSERT INTO follow_ups (
                    customer_id, title, description, follow_up_date, follow_up_time,
                    priority, type, assigned_to, notes, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['customer_id'],
            $data['title'],
            $data['description'] ?? null,
            $data['follow_up_date'],
            $data['follow_up_time'] ?? null,
            $data['priority'] ?? 'medium',
            $data['type'] ?? 'general',
            $data['assigned_to'] ?? null,
            $data['notes'] ?? null,
            $data['created_by'] ?? null
        ];

        return $this->db->execute($sql, $params);
    }

    /**
     * Update follow-up
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        $allowedFields = [
            'title', 'description', 'follow_up_date', 'follow_up_time',
            'priority', 'type', 'assigned_to', 'notes'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE follow_ups SET " . implode(', ', $fields) . " WHERE id = ?";

        return $this->db->execute($sql, $params);
    }

    /**
     * Mark follow-up as completed
     */
    public function markCompleted($id, $notes = null) {
        $sql = "UPDATE follow_ups SET status = 'completed', completed_at = NOW()";
        $params = [];

        if ($notes !== null) {
            $sql .= ", notes = ?";
            $params[] = $notes;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        return $this->db->execute($sql, $params);
    }

    /**
     * Cancel follow-up
     */
    public function cancel($id) {
        return $this->db->execute(
            "UPDATE follow_ups SET status = 'cancelled' WHERE id = ?",
            [$id]
        );
    }

    /**
     * Delete follow-up
     */
    public function delete($id) {
        return $this->db->execute("DELETE FROM follow_ups WHERE id = ?", [$id]);
    }

    /**
     * Get follow-up by ID
     */
    public function getById($id) {
        $sql = "SELECT f.*, c.name as customer_name, c.location, c.contact_person, c.contact_no
                FROM follow_ups f
                JOIN customers c ON f.customer_id = c.id
                WHERE f.id = ?";

        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Get today's follow-ups
     */
    public function getTodays() {
        return $this->db->query("SELECT * FROM v_todays_followups");
    }

    /**
     * Get upcoming follow-ups (next 7 days)
     */
    public function getUpcoming($days = 7) {
        $sql = "SELECT f.*, c.name as customer_name, c.location,
                       DATEDIFF(f.follow_up_date, CURDATE()) as days_until
                FROM follow_ups f
                JOIN customers c ON f.customer_id = c.id
                WHERE f.status = 'pending'
                AND f.follow_up_date > CURDATE()
                AND f.follow_up_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY f.follow_up_date, f.follow_up_time";

        return $this->db->query($sql, [$days]);
    }

    /**
     * Get overdue follow-ups
     */
    public function getOverdue() {
        return $this->db->query("SELECT * FROM v_overdue_followups");
    }

    /**
     * Get follow-ups for a specific customer
     */
    public function getByCustomer($customerId, $includeCompleted = false) {
        $sql = "SELECT f.*, c.name as customer_name
                FROM follow_ups f
                JOIN customers c ON f.customer_id = c.id
                WHERE f.customer_id = ?";

        if (!$includeCompleted) {
            $sql .= " AND f.status = 'pending'";
        }

        $sql .= " ORDER BY f.follow_up_date DESC, f.follow_up_time DESC";

        return $this->db->query($sql, [$customerId]);
    }

    /**
     * Get follow-ups for a date range
     */
    public function getByDateRange($startDate, $endDate, $status = 'pending') {
        $sql = "SELECT f.*, c.name as customer_name, c.location, c.contact_person, c.contact_no
                FROM follow_ups f
                JOIN customers c ON f.customer_id = c.id
                WHERE f.follow_up_date BETWEEN ? AND ?";

        $params = [$startDate, $endDate];

        if ($status) {
            $sql .= " AND f.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY f.follow_up_date, f.follow_up_time";

        return $this->db->query($sql, $params);
    }

    /**
     * Get follow-up statistics
     */
    public function getStats() {
        $result = $this->db->queryOne(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'pending' AND follow_up_date = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN status = 'pending' AND follow_up_date < CURDATE() THEN 1 ELSE 0 END) as overdue,
                SUM(CASE WHEN status = 'pending' AND priority = 'urgent' THEN 1 ELSE 0 END) as urgent,
                SUM(CASE WHEN status = 'pending' AND priority = 'high' THEN 1 ELSE 0 END) as `high_priority`
             FROM follow_ups"
        );

        return [
            'total' => intval($result['total'] ?? 0),
            'pending' => intval($result['pending'] ?? 0),
            'completed' => intval($result['completed'] ?? 0),
            'today' => intval($result['today'] ?? 0),
            'overdue' => intval($result['overdue'] ?? 0),
            'urgent' => intval($result['urgent'] ?? 0),
            'high_priority' => intval($result['high_priority'] ?? 0)
        ];
    }

    /**
     * Get all follow-ups with filters
     */
    public function getAll($filters = []) {
        $sql = "SELECT f.*, c.name as customer_name, c.location, s.name as state_name
                FROM follow_ups f
                JOIN customers c ON f.customer_id = c.id
                LEFT JOIN states s ON c.state_id = s.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND f.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $sql .= " AND f.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND f.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['customer_id'])) {
            $sql .= " AND f.customer_id = ?";
            $params[] = $filters['customer_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND f.follow_up_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND f.follow_up_date <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY f.follow_up_date DESC, f.follow_up_time DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . intval($filters['limit']);
        }

        return $this->db->query($sql, $params);
    }
}
