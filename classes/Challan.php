<?php
/**
 * Challan Class
 * Customer Tracking & Billing Management System
 */

class Challan {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all challans with filters
     */
    public function getAll($filters = []) {
        $sql = "SELECT ch.*, c.name as customer_name, c.location as customer_location,
                       s.name as state_name,
                       (SELECT SUM(ci.quantity) FROM challan_items ci WHERE ci.challan_id = ch.id) as total_items
                FROM challans ch
                LEFT JOIN customers c ON ch.customer_id = c.id
                LEFT JOIN states s ON c.state_id = s.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['customer_id'])) {
            $sql .= " AND ch.customer_id = ?";
            $params[] = $filters['customer_id'];
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND ch.challan_date >= ?";
            $params[] = formatDateDB($filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND ch.challan_date <= ?";
            $params[] = formatDateDB($filters['to_date']);
        }

        if (!empty($filters['billed'])) {
            $sql .= " AND ch.billed = ?";
            $params[] = $filters['billed'];
        }

        if (!empty($filters['state_id'])) {
            $sql .= " AND c.state_id = ?";
            $params[] = $filters['state_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (ch.challan_no LIKE ? OR c.name LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY ch.challan_date DESC, ch.id DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        return $this->db->query($sql, $params);
    }

    /**
     * Get challan by ID
     */
    public function getById($id) {
        $sql = "SELECT ch.*, c.name as customer_name, c.location as customer_location,
                       s.name as state_name
                FROM challans ch
                LEFT JOIN customers c ON ch.customer_id = c.id
                LEFT JOIN states s ON c.state_id = s.id
                WHERE ch.id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Get challan with items
     */
    public function getWithItems($id) {
        $challan = $this->getById($id);
        if (!$challan) return null;

        $challan['items'] = $this->db->query(
            "SELECT ci.*, p.name as product_name, p.short_name, cat.name as category_name
             FROM challan_items ci
             JOIN products p ON ci.product_id = p.id
             LEFT JOIN categories cat ON p.category_id = cat.id
             WHERE ci.challan_id = ?
             ORDER BY cat.name, p.name",
            [$id]
        );

        return $challan;
    }

    /**
     * Create challan
     */
    public function create($data, $items = []) {
        $this->db->beginTransaction();

        try {
            // Calculate total amount
            $totalAmount = 0;
            foreach ($items as $item) {
                $amount = ($item['quantity'] ?? 0) * ($item['rate'] ?? 0);
                $totalAmount += $amount;
            }

            $sql = "INSERT INTO challans (customer_id, customer_location_id, challan_no,
                    challan_date, billed, rate, total_amount, delivery_through, remark,
                    material_sending_location, upload_batch_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $challanId = $this->db->insert($sql, [
                $data['customer_id'],
                $data['customer_location_id'] ?? null,
                $data['challan_no'] ?? null,
                formatDateDB($data['challan_date']),
                $data['billed'] ?? 'no',
                $data['rate'] ?? 0,
                $totalAmount,
                $data['delivery_through'] ?? null,
                $data['remark'] ?? null,
                $data['material_sending_location'] ?? null,
                $data['upload_batch_id'] ?? null
            ]);

            // Insert items
            foreach ($items as $item) {
                if (empty($item['product_id']) || empty($item['quantity'])) continue;

                $amount = ($item['quantity'] ?? 0) * ($item['rate'] ?? 0);

                $this->db->insert(
                    "INSERT INTO challan_items (challan_id, product_id, quantity, rate, amount)
                     VALUES (?, ?, ?, ?, ?)",
                    [
                        $challanId,
                        $item['product_id'],
                        $item['quantity'],
                        $item['rate'] ?? 0,
                        $amount
                    ]
                );
            }

            $this->db->commit();
            logActivity('create', 'challan', $challanId);
            return $challanId;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Update challan
     */
    public function update($id, $data, $items = []) {
        $this->db->beginTransaction();

        try {
            // Calculate total amount
            $totalAmount = 0;
            foreach ($items as $item) {
                $amount = ($item['quantity'] ?? 0) * ($item['rate'] ?? 0);
                $totalAmount += $amount;
            }

            $sql = "UPDATE challans SET
                    customer_id = ?,
                    customer_location_id = ?,
                    challan_no = ?,
                    challan_date = ?,
                    billed = ?,
                    rate = ?,
                    total_amount = ?,
                    delivery_through = ?,
                    remark = ?,
                    material_sending_location = ?
                    WHERE id = ?";

            $this->db->execute($sql, [
                $data['customer_id'],
                $data['customer_location_id'] ?? null,
                $data['challan_no'] ?? null,
                formatDateDB($data['challan_date']),
                $data['billed'] ?? 'no',
                $data['rate'] ?? 0,
                $totalAmount,
                $data['delivery_through'] ?? null,
                $data['remark'] ?? null,
                $data['material_sending_location'] ?? null,
                $id
            ]);

            // Delete existing items and insert new ones
            $this->db->execute("DELETE FROM challan_items WHERE challan_id = ?", [$id]);

            foreach ($items as $item) {
                if (empty($item['product_id']) || empty($item['quantity'])) continue;

                $amount = ($item['quantity'] ?? 0) * ($item['rate'] ?? 0);

                $this->db->insert(
                    "INSERT INTO challan_items (challan_id, product_id, quantity, rate, amount)
                     VALUES (?, ?, ?, ?, ?)",
                    [
                        $id,
                        $item['product_id'],
                        $item['quantity'],
                        $item['rate'] ?? 0,
                        $amount
                    ]
                );
            }

            $this->db->commit();
            logActivity('update', 'challan', $id);
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Delete challan
     */
    public function delete($id) {
        $challan = $this->getById($id);

        $this->db->execute("DELETE FROM challan_items WHERE challan_id = ?", [$id]);
        $result = $this->db->execute("DELETE FROM challans WHERE id = ?", [$id]);

        logActivity('delete', 'challan', $id, ['challan_no' => $challan['challan_no']]);
        return $result;
    }

    /**
     * Get revenue statistics
     */
    public function getRevenueStats($fromDate = null, $toDate = null) {
        $params = [];
        $dateFilter = "";

        if ($fromDate) {
            $dateFilter .= " AND challan_date >= ?";
            $params[] = formatDateDB($fromDate);
        }
        if ($toDate) {
            $dateFilter .= " AND challan_date <= ?";
            $params[] = formatDateDB($toDate);
        }

        return [
            'total_revenue' => $this->db->getValue(
                "SELECT COALESCE(SUM(total_amount), 0) FROM challans WHERE 1=1 $dateFilter",
                $params
            ),
            'total_challans' => $this->db->getValue(
                "SELECT COUNT(*) FROM challans WHERE 1=1 $dateFilter",
                $params
            ),
            'billed_count' => $this->db->getValue(
                "SELECT COUNT(*) FROM challans WHERE billed = 'yes' $dateFilter",
                $params
            ),
            'unbilled_count' => $this->db->getValue(
                "SELECT COUNT(*) FROM challans WHERE billed = 'no' $dateFilter",
                $params
            )
        ];
    }

    /**
     * Get monthly revenue data for charts
     */
    public function getMonthlyRevenue($months = 12) {
        return $this->db->query(
            "SELECT DATE_FORMAT(challan_date, '%Y-%m') as month,
                    DATE_FORMAT(challan_date, '%b %Y') as month_label,
                    COUNT(*) as challan_count,
                    COALESCE(SUM(total_amount), 0) as revenue
             FROM challans
             WHERE challan_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             GROUP BY DATE_FORMAT(challan_date, '%Y-%m')
             ORDER BY month",
            [$months]
        );
    }

    /**
     * Get state-wise revenue
     */
    public function getStateWiseRevenue($fromDate = null, $toDate = null) {
        $sql = "SELECT s.id, s.name as state_name, s.region,
                       COUNT(DISTINCT c.id) as customer_count,
                       COUNT(ch.id) as challan_count,
                       COALESCE(SUM(ch.total_amount), 0) as revenue
                FROM states s
                LEFT JOIN customers c ON s.id = c.state_id
                LEFT JOIN challans ch ON c.id = ch.customer_id";

        $params = [];

        if ($fromDate || $toDate) {
            $sql .= " AND 1=1";
            if ($fromDate) {
                $sql .= " AND ch.challan_date >= ?";
                $params[] = formatDateDB($fromDate);
            }
            if ($toDate) {
                $sql .= " AND ch.challan_date <= ?";
                $params[] = formatDateDB($toDate);
            }
        }

        $sql .= " GROUP BY s.id ORDER BY revenue DESC";

        return $this->db->query($sql, $params);
    }

    /**
     * Get product-wise sales
     */
    public function getProductWiseSales($fromDate = null, $toDate = null) {
        $sql = "SELECT p.id, p.name as product_name, cat.name as category_name,
                       SUM(ci.quantity) as total_quantity,
                       SUM(ci.amount) as total_amount
                FROM products p
                LEFT JOIN categories cat ON p.category_id = cat.id
                LEFT JOIN challan_items ci ON p.id = ci.product_id
                LEFT JOIN challans ch ON ci.challan_id = ch.id";

        $params = [];

        if ($fromDate || $toDate) {
            $sql .= " WHERE 1=1";
            if ($fromDate) {
                $sql .= " AND ch.challan_date >= ?";
                $params[] = formatDateDB($fromDate);
            }
            if ($toDate) {
                $sql .= " AND ch.challan_date <= ?";
                $params[] = formatDateDB($toDate);
            }
        }

        $sql .= " GROUP BY p.id ORDER BY total_quantity DESC";

        return $this->db->query($sql, $params);
    }

    /**
     * Get top customers by revenue
     */
    public function getTopCustomers($limit = 10, $fromDate = null, $toDate = null) {
        $sql = "SELECT c.id, c.name, c.location, s.name as state_name,
                       COUNT(ch.id) as challan_count,
                       COALESCE(SUM(ch.total_amount), 0) as total_revenue
                FROM customers c
                LEFT JOIN states s ON c.state_id = s.id
                LEFT JOIN challans ch ON c.id = ch.customer_id";

        $params = [];

        if ($fromDate || $toDate) {
            $sql .= " AND 1=1";
            if ($fromDate) {
                $sql .= " AND ch.challan_date >= ?";
                $params[] = formatDateDB($fromDate);
            }
            if ($toDate) {
                $sql .= " AND ch.challan_date <= ?";
                $params[] = formatDateDB($toDate);
            }
        }

        $sql .= " GROUP BY c.id ORDER BY total_revenue DESC LIMIT ?";
        $params[] = $limit;

        return $this->db->query($sql, $params);
    }
}
