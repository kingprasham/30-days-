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
}
