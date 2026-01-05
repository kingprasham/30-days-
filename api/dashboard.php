<?php
/**
 * Dashboard API Endpoints
 * Customer Tracking & Billing Management System
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

$action = $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    requireLogin();
    $dashboard = new Dashboard();

    switch ($action) {
        case 'stats':
            $response['success'] = true;
            $response['data'] = $dashboard->getStats();
            break;

        case 'monthly_revenue':
            $months = (int)($_GET['months'] ?? 12);
            $response['success'] = true;
            $response['data'] = $dashboard->getMonthlyRevenueChart($months);
            break;

        case 'state_revenue':
            $response['success'] = true;
            $response['data'] = $dashboard->getStateRevenueChart();
            break;

        case 'top_customers':
            $limit = (int)($_GET['limit'] ?? 10);
            $response['success'] = true;
            $response['data'] = $dashboard->getTopCustomersChart($limit);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
