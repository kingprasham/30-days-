<?php
/**
 * Follow-ups API
 * Customer Tracking & Billing Management System
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/FollowUp.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$followUp = new FollowUp();

try {
    switch ($action) {
        case 'create':
            // Create new follow-up
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['customer_id']) || empty($data['title']) || empty($data['follow_up_date'])) {
                throw new Exception('Customer ID, title, and follow-up date are required');
            }

            $result = $followUp->create($data);

            echo json_encode([
                'success' => $result,
                'message' => 'Follow-up created successfully'
            ]);
            break;

        case 'update':
            // Update follow-up
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;

            if (!$id) {
                throw new Exception('Follow-up ID is required');
            }

            unset($data['id']); // Remove id from update data
            $result = $followUp->update($id, $data);

            echo json_encode([
                'success' => $result,
                'message' => 'Follow-up updated successfully'
            ]);
            break;

        case 'complete':
            // Mark follow-up as completed
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $notes = $data['notes'] ?? null;

            if (!$id) {
                throw new Exception('Follow-up ID is required');
            }

            $result = $followUp->markCompleted($id, $notes);

            echo json_encode([
                'success' => $result,
                'message' => 'Follow-up marked as completed'
            ]);
            break;

        case 'cancel':
            // Cancel follow-up
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;

            if (!$id) {
                throw new Exception('Follow-up ID is required');
            }

            $result = $followUp->cancel($id);

            echo json_encode([
                'success' => $result,
                'message' => 'Follow-up cancelled'
            ]);
            break;

        case 'delete':
            // Delete follow-up
            $id = $_POST['id'] ?? 0;

            if (!$id) {
                throw new Exception('Follow-up ID is required');
            }

            $result = $followUp->delete($id);

            echo json_encode([
                'success' => $result,
                'message' => 'Follow-up deleted'
            ]);
            break;

        case 'get':
            // Get follow-up by ID
            $id = $_GET['id'] ?? 0;

            if (!$id) {
                throw new Exception('Follow-up ID is required');
            }

            $data = $followUp->getById($id);

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            break;

        case 'list':
            // Get all follow-ups with filters
            $filters = [
                'status' => $_GET['status'] ?? null,
                'priority' => $_GET['priority'] ?? null,
                'type' => $_GET['type'] ?? null,
                'customer_id' => $_GET['customer_id'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
                'limit' => $_GET['limit'] ?? null
            ];

            // Remove null values
            $filters = array_filter($filters, function($v) {
                return $v !== null && $v !== '';
            });

            $data = $followUp->getAll($filters);

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            break;

        case 'stats':
            // Get follow-up statistics
            $stats = $followUp->getStats();

            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
