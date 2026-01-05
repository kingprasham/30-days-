<?php
/**
 * Dashboard Class
 * Customer Tracking & Billing Management System
 */

class Dashboard {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all dashboard statistics
     */
    public function getStats() {
        return [
            'customers' => $this->getCustomerStats(),
            'revenue' => $this->getRevenueStats(),
            'challans' => $this->getChallanStats(),
            'dealers' => $this->getDealerStats(),
            'defaulters' => $this->getDefaulterStats()
        ];
    }

    /**
     * Get customer statistics
     */
    public function getCustomerStats() {
        return [
            'total' => $this->db->getValue("SELECT COUNT(*) FROM customers"),
            'active' => $this->db->getValue("SELECT COUNT(*) FROM customers WHERE status = 'active'"),
            'new_this_month' => $this->db->getValue(
                "SELECT COUNT(*) FROM customers
                 WHERE MONTH(created_at) = MONTH(CURDATE())
                 AND YEAR(created_at) = YEAR(CURDATE())"
            ),
            'by_state' => $this->db->query(
                "SELECT s.name, COUNT(c.id) as count
                 FROM states s
                 LEFT JOIN customers c ON s.id = c.state_id AND c.status = 'active'
                 GROUP BY s.id
                 HAVING count > 0
                 ORDER BY count DESC"
            )
        ];
    }

    /**
     * Get revenue statistics
     */
    public function getRevenueStats() {
        // This month
        $thisMonthStart = date('Y-m-01');
        $thisMonthEnd = date('Y-m-t');

        // Last month
        $lastMonthStart = date('Y-m-01', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t', strtotime('-1 month'));

        $thisMonthRevenue = $this->db->getValue(
            "SELECT COALESCE(SUM(total_amount), 0) FROM challans
             WHERE challan_date BETWEEN ? AND ?",
            [$thisMonthStart, $thisMonthEnd]
        );

        $lastMonthRevenue = $this->db->getValue(
            "SELECT COALESCE(SUM(total_amount), 0) FROM challans
             WHERE challan_date BETWEEN ? AND ?",
            [$lastMonthStart, $lastMonthEnd]
        );

        // Calculate growth percentage
        $growth = 0;
        if ($lastMonthRevenue > 0) {
            $growth = (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
        }

        return [
            'this_month' => $thisMonthRevenue,
            'last_month' => $lastMonthRevenue,
            'growth_percent' => round($growth, 2),
            'total' => $this->db->getValue("SELECT COALESCE(SUM(total_amount), 0) FROM challans"),
            'average_per_challan' => $this->db->getValue(
                "SELECT COALESCE(AVG(total_amount), 0) FROM challans WHERE total_amount > 0"
            )
        ];
    }

    /**
     * Get challan statistics
     */
    public function getChallanStats() {
        $thisMonthStart = date('Y-m-01');
        $thisMonthEnd = date('Y-m-t');

        return [
            'total' => $this->db->getValue("SELECT COUNT(*) FROM challans"),
            'this_month' => $this->db->getValue(
                "SELECT COUNT(*) FROM challans WHERE challan_date BETWEEN ? AND ?",
                [$thisMonthStart, $thisMonthEnd]
            ),
            'billed' => $this->db->getValue("SELECT COUNT(*) FROM challans WHERE billed = 'yes'"),
            'unbilled' => $this->db->getValue("SELECT COUNT(*) FROM challans WHERE billed = 'no'"),
            'recent' => $this->db->query(
                "SELECT ch.*, c.name as customer_name
                 FROM challans ch
                 JOIN customers c ON ch.customer_id = c.id
                 ORDER BY ch.challan_date DESC, ch.id DESC
                 LIMIT 10"
            )
        ];
    }

    /**
     * Get dealer statistics
     */
    public function getDealerStats() {
        return [
            'total' => $this->db->getValue("SELECT COUNT(*) FROM dealers"),
            'active' => $this->db->getValue("SELECT COUNT(*) FROM dealers WHERE status = 'active'"),
            'total_commission' => $this->db->getValue(
                "SELECT COALESCE(SUM(commission_amount), 0) FROM customer_dealers WHERE status = 'active'"
            ),
            'by_territory' => $this->db->query(
                "SELECT territory, COUNT(*) as count FROM dealers GROUP BY territory"
            )
        ];
    }

    /**
     * Get defaulter statistics
     */
    public function getDefaulterStats() {
        $defaulterDays = defined('DEFAULTER_DAYS') ? DEFAULTER_DAYS : 30;

        $defaulters = $this->db->query(
            "SELECT c.id, c.name, c.location, s.name as state_name,
                    MAX(ch.challan_date) as last_challan_date,
                    DATEDIFF(CURDATE(), MAX(ch.challan_date)) as days_inactive
             FROM customers c
             LEFT JOIN states s ON c.state_id = s.id
             LEFT JOIN challans ch ON c.id = ch.customer_id
             WHERE c.status = 'active'
             GROUP BY c.id
             HAVING days_inactive > ? OR last_challan_date IS NULL
             ORDER BY days_inactive DESC",
            [$defaulterDays]
        );

        return [
            'count' => count($defaulters),
            'list' => array_slice($defaulters, 0, 10)
        ];
    }

    /**
     * Get monthly revenue chart data (fills missing months with zeros)
     */
    public function getMonthlyRevenueChart($months = 12) {
        // Get actual data from database
        $data = $this->db->query(
            "SELECT DATE_FORMAT(challan_date, '%Y-%m') as month,
                    COALESCE(SUM(total_amount), 0) as revenue,
                    COUNT(*) as challans
             FROM challans
             WHERE challan_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             GROUP BY month
             ORDER BY month",
            [$months]
        );

        // Convert to associative array for easy lookup
        $dataMap = [];
        foreach ($data as $row) {
            $dataMap[$row['month']] = $row;
        }

        // Generate all months in the range
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $label = date('M Y', strtotime("-$i months"));

            if (isset($dataMap[$date])) {
                $result[] = [
                    'month' => $date,
                    'label' => $label,
                    'revenue' => $dataMap[$date]['revenue'],
                    'challans' => $dataMap[$date]['challans']
                ];
            } else {
                $result[] = [
                    'month' => $date,
                    'label' => $label,
                    'revenue' => 0,
                    'challans' => 0
                ];
            }
        }

        return $result;
    }

    /**
     * Get state-wise revenue chart data
     */
    public function getStateRevenueChart() {
        return $this->db->query(
            "SELECT COALESCE(s.name, 'Unknown State') as state,
                    COALESCE(s.region, 'Unknown') as region,
                    COALESCE(SUM(ch.total_amount), 0) as revenue,
                    COUNT(DISTINCT c.id) as customers
             FROM customers c
             LEFT JOIN states s ON c.state_id = s.id
             LEFT JOIN challans ch ON c.id = ch.customer_id
             WHERE ch.total_amount > 0
             GROUP BY COALESCE(s.id, 0), COALESCE(s.name, 'Unknown State')
             HAVING revenue > 0
             ORDER BY revenue DESC
             LIMIT 10"
        );
    }

    /**
     * Get top customers chart data
     */
    public function getTopCustomersChart($limit = 10) {
        return $this->db->query(
            "SELECT c.name, COALESCE(SUM(ch.total_amount), 0) as revenue
             FROM customers c
             LEFT JOIN challans ch ON c.id = ch.customer_id
             GROUP BY c.id
             HAVING revenue > 0
             ORDER BY revenue DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get product category distribution
     */
    public function getCategoryDistributionChart() {
        return $this->db->query(
            "SELECT cat.name as category, SUM(ci.quantity) as quantity, SUM(ci.amount) as amount
             FROM categories cat
             LEFT JOIN products p ON cat.id = p.category_id
             LEFT JOIN challan_items ci ON p.id = ci.product_id
             GROUP BY cat.id
             HAVING quantity > 0
             ORDER BY quantity DESC"
        );
    }

    /**
     * Get customer growth chart data
     */
    public function getCustomerGrowthChart($months = 12) {
        // Get actual data
        $data = $this->db->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as new_customers
             FROM customers
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             GROUP BY month
             ORDER BY month",
            [$months]
        );

        // Convert to associative array
        $dataMap = [];
        foreach ($data as $row) {
            $dataMap[$row['month']] = $row['new_customers'];
        }

        // Fill all months with zeros for gaps
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $label = date('M Y', strtotime("-$i months"));

            $result[] = [
                'month' => $date,
                'label' => $label,
                'new_customers' => $dataMap[$date] ?? 0
            ];
        }

        return $result;
    }

    /**
     * Get upcoming contract renewals
     */
    public function getUpcomingRenewals($days = 30) {
        return $this->db->query(
            "SELECT con.*, c.name as customer_name
             FROM contracts con
             JOIN customers c ON con.customer_id = c.id
             WHERE con.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
             AND con.status = 'active'
             ORDER BY con.end_date",
            [$days]
        );
    }

    /**
     * Get payment aging summary for dashboard
     */
    public function getPaymentAgingSummary() {
        $result = $this->db->queryOne(
            "SELECT
                SUM(CASE WHEN billed = 'no' AND DATEDIFF(CURDATE(), challan_date) <= 30 THEN total_amount ELSE 0 END) as aging_0_30,
                SUM(CASE WHEN billed = 'no' AND DATEDIFF(CURDATE(), challan_date) BETWEEN 31 AND 60 THEN total_amount ELSE 0 END) as aging_31_60,
                SUM(CASE WHEN billed = 'no' AND DATEDIFF(CURDATE(), challan_date) BETWEEN 61 AND 90 THEN total_amount ELSE 0 END) as aging_61_90,
                SUM(CASE WHEN billed = 'no' AND DATEDIFF(CURDATE(), challan_date) > 90 THEN total_amount ELSE 0 END) as aging_90_plus,
                SUM(CASE WHEN billed = 'no' THEN total_amount ELSE 0 END) as total_unbilled,
                COUNT(CASE WHEN billed = 'no' THEN 1 END) as unbilled_count,
                COUNT(CASE WHEN billed = 'no' AND DATEDIFF(CURDATE(), challan_date) <= 30 THEN 1 END) as count_0_30,
                COUNT(CASE WHEN billed = 'no' AND DATEDIFF(CURDATE(), challan_date) BETWEEN 31 AND 60 THEN 1 END) as count_31_60,
                COUNT(CASE WHEN billed = 'no' AND DATEDIFF(CURDATE(), challan_date) BETWEEN 61 AND 90 THEN 1 END) as count_61_90,
                COUNT(CASE WHEN billed = 'no' AND DATEDIFF(CURDATE(), challan_date) > 90 THEN 1 END) as count_90_plus
             FROM challans"
        );

        return [
            'aging_0_30' => floatval($result['aging_0_30'] ?? 0),
            'aging_31_60' => floatval($result['aging_31_60'] ?? 0),
            'aging_61_90' => floatval($result['aging_61_90'] ?? 0),
            'aging_90_plus' => floatval($result['aging_90_plus'] ?? 0),
            'total_unbilled' => floatval($result['total_unbilled'] ?? 0),
            'unbilled_count' => intval($result['unbilled_count'] ?? 0),
            'count_0_30' => intval($result['count_0_30'] ?? 0),
            'count_31_60' => intval($result['count_31_60'] ?? 0),
            'count_61_90' => intval($result['count_61_90'] ?? 0),
            'count_90_plus' => intval($result['count_90_plus'] ?? 0)
        ];
    }

    /**
     * Get billing efficiency chart data (billed vs unbilled over months)
     */
    public function getBillingEfficiencyChart($months = 12) {
        $data = $this->db->query(
            "SELECT
                DATE_FORMAT(challan_date, '%Y-%m') as month,
                COUNT(*) as total_challans,
                SUM(CASE WHEN billed = 'yes' THEN 1 ELSE 0 END) as billed_count,
                SUM(CASE WHEN billed = 'no' THEN 1 ELSE 0 END) as unbilled_count,
                COALESCE(SUM(CASE WHEN billed = 'yes' THEN total_amount ELSE 0 END), 0) as billed_amount,
                COALESCE(SUM(CASE WHEN billed = 'no' THEN total_amount ELSE 0 END), 0) as unbilled_amount
             FROM challans
             WHERE challan_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             GROUP BY month
             ORDER BY month",
            [$months]
        );

        // Fill missing months
        $dataMap = [];
        foreach ($data as $row) {
            $dataMap[$row['month']] = $row;
        }

        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $label = date('M Y', strtotime("-$i months"));

            if (isset($dataMap[$date])) {
                $result[] = [
                    'month' => $date,
                    'label' => $label,
                    'total_challans' => intval($dataMap[$date]['total_challans']),
                    'billed_count' => intval($dataMap[$date]['billed_count']),
                    'unbilled_count' => intval($dataMap[$date]['unbilled_count']),
                    'billed_amount' => floatval($dataMap[$date]['billed_amount']),
                    'unbilled_amount' => floatval($dataMap[$date]['unbilled_amount'])
                ];
            } else {
                $result[] = [
                    'month' => $date,
                    'label' => $label,
                    'total_challans' => 0,
                    'billed_count' => 0,
                    'unbilled_count' => 0,
                    'billed_amount' => 0,
                    'unbilled_amount' => 0
                ];
            }
        }

        return $result;
    }

    /**
     * Get top customers with pending payments
     */
    public function getTopPendingPayments($limit = 10) {
        return $this->db->query(
            "SELECT
                c.id,
                c.name,
                c.location,
                s.name as state_name,
                SUM(CASE WHEN ch.billed = 'no' THEN ch.total_amount ELSE 0 END) as pending_amount,
                COUNT(CASE WHEN ch.billed = 'no' THEN 1 END) as pending_challans,
                MAX(ch.challan_date) as last_challan_date,
                DATEDIFF(CURDATE(), MIN(CASE WHEN ch.billed = 'no' THEN ch.challan_date END)) as oldest_pending_days
             FROM customers c
             LEFT JOIN states s ON c.state_id = s.id
             LEFT JOIN challans ch ON c.id = ch.customer_id
             WHERE c.status = 'active'
             GROUP BY c.id
             HAVING pending_amount > 0
             ORDER BY pending_amount DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get delivery person performance
     */
    public function getDeliveryPerformance() {
        return $this->db->query(
            "SELECT
                COALESCE(delivery_through, 'Not Assigned') as delivery_person,
                COUNT(*) as total_deliveries,
                COUNT(DISTINCT customer_id) as unique_customers,
                SUM(CASE WHEN billed = 'yes' THEN 1 ELSE 0 END) as billed,
                SUM(CASE WHEN billed = 'no' THEN 1 ELSE 0 END) as unbilled,
                ROUND(SUM(CASE WHEN billed = 'yes' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) as billing_rate
             FROM challans
             WHERE challan_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
             AND delivery_through IS NOT NULL
             AND TRIM(delivery_through) != ''
             GROUP BY delivery_through
             ORDER BY total_deliveries DESC
             LIMIT 10"
        );
    }

    /**
     * Get customer payment alerts (overdue > 30 days)
     */
    public function getPaymentAlerts($limit = 10) {
        return $this->db->query(
            "SELECT
                ch.id,
                ch.challan_no,
                ch.challan_date,
                ch.total_amount,
                c.id as customer_id,
                c.name as customer_name,
                c.location,
                s.name as state_name,
                DATEDIFF(CURDATE(), ch.challan_date) as days_overdue
             FROM challans ch
             JOIN customers c ON ch.customer_id = c.id
             LEFT JOIN states s ON c.state_id = s.id
             WHERE ch.billed = 'no'
             AND DATEDIFF(CURDATE(), ch.challan_date) > 30
             ORDER BY ch.challan_date ASC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get monthly collection trend
     */
    public function getMonthlyCollectionTrend($months = 12) {
        $data = $this->db->query(
            "SELECT
                DATE_FORMAT(challan_date, '%Y-%m') as month,
                COALESCE(SUM(total_amount), 0) as total_amount,
                COALESCE(SUM(CASE WHEN billed = 'yes' THEN total_amount ELSE 0 END), 0) as collected,
                COALESCE(SUM(CASE WHEN billed = 'no' THEN total_amount ELSE 0 END), 0) as pending
             FROM challans
             WHERE challan_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             GROUP BY month
             ORDER BY month",
            [$months]
        );

        $dataMap = [];
        foreach ($data as $row) {
            $dataMap[$row['month']] = $row;
        }

        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $label = date('M Y', strtotime("-$i months"));

            if (isset($dataMap[$date])) {
                $result[] = [
                    'month' => $date,
                    'label' => $label,
                    'total_amount' => floatval($dataMap[$date]['total_amount']),
                    'collected' => floatval($dataMap[$date]['collected']),
                    'pending' => floatval($dataMap[$date]['pending'])
                ];
            } else {
                $result[] = [
                    'month' => $date,
                    'label' => $label,
                    'total_amount' => 0,
                    'collected' => 0,
                    'pending' => 0
                ];
            }
        }

        return $result;
    }

    /**
     * Get product-wise sales summary
     */
    public function getProductSalesSummary() {
        return $this->db->query(
            "SELECT
                p.id,
                p.name,
                cat.name as category_name,
                COALESCE(SUM(ci.quantity), 0) as total_quantity,
                COALESCE(SUM(ci.amount), 0) as total_amount,
                COUNT(DISTINCT ch.customer_id) as customer_count
             FROM products p
             LEFT JOIN categories cat ON p.category_id = cat.id
             LEFT JOIN challan_items ci ON p.id = ci.product_id
             LEFT JOIN challans ch ON ci.challan_id = ch.id
             GROUP BY p.id
             HAVING total_quantity > 0
             ORDER BY total_quantity DESC
             LIMIT 15"
        );
    }
}
