<?php
/**
 * Delete Customer
 * Customer Tracking & Billing Management System
 */

require_once __DIR__ . '/../../config/config.php';
requireAdmin();

$customer = new Customer();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid customer ID');
    redirect(BASE_URL . '/pages/customers/list.php');
}

try {
    $customer->delete($id);
    setFlashMessage('success', 'Customer deleted successfully');
} catch (Exception $e) {
    setFlashMessage('error', $e->getMessage());
}

redirect(BASE_URL . '/pages/customers/list.php');
