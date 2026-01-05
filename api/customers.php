<?php
/**
 * Customer API Endpoints
 * Customer Tracking & Billing Management System
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    requireLogin();
    $customer = new Customer();

    switch ($action) {
        case 'assign_dealer':
            if (!hasPermission('edit')) {
                throw new Exception('Permission denied');
            }

            $customerId = (int)($_POST['customer_id'] ?? 0);
            $dealerId = (int)($_POST['dealer_id'] ?? 0);
            $commission = (float)($_POST['commission'] ?? 0);

            if ($customerId && $dealerId) {
                $customer->assignDealer($customerId, $dealerId, $commission);
                $response['success'] = true;
                $response['message'] = 'Dealer assigned successfully';

                setFlashMessage('success', 'Dealer assigned successfully');
                header('Location: ' . BASE_URL . '/pages/customers/view.php?id=' . $customerId);
                exit;
            }
            break;

        case 'remove_dealer':
            if (!hasPermission('delete')) {
                throw new Exception('Permission denied');
            }

            $customerId = (int)($_GET['customer_id'] ?? 0);
            $dealerId = (int)($_GET['dealer_id'] ?? 0);

            if ($customerId && $dealerId) {
                $customer->removeDealer($customerId, $dealerId);
                $response['success'] = true;
                $response['message'] = 'Dealer removed successfully';

                setFlashMessage('success', 'Dealer removed successfully');
                header('Location: ' . BASE_URL . '/pages/customers/view.php?id=' . $customerId);
                exit;
            }
            break;

        case 'search':
            $query = $_GET['q'] ?? '';
            $filters = ['search' => $query, 'status' => 'active'];
            $results = $customer->getAll($filters);

            $response['success'] = true;
            $response['data'] = array_map(function($c) {
                return [
                    'id' => $c['id'],
                    'name' => $c['name'],
                    'location' => $c['location'] ?? '',
                    'state' => $c['state_name'] ?? ''
                ];
            }, array_slice($results, 0, 10));
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
