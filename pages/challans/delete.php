<?php
/**
 * Delete Challan
 * Customer Tracking & Billing Management System
 */

require_once __DIR__ . '/../../config/config.php';
requireAdmin();

$challan = new Challan();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid challan ID');
    redirect(BASE_URL . '/pages/challans/list.php');
}

try {
    $challan->delete($id);
    setFlashMessage('success', 'Challan deleted successfully');
} catch (Exception $e) {
    setFlashMessage('error', $e->getMessage());
}

redirect(BASE_URL . '/pages/challans/list.php');
