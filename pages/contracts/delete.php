<?php
/**
 * Delete Contract
 * Customer Tracking & Billing Management System
 */

require_once __DIR__ . '/../../config/config.php';
requireAdmin();

$contract = new Contract();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid contract ID');
    redirect(BASE_URL . '/pages/contracts/list.php');
}

try {
    $contract->delete($id);
    setFlashMessage('success', 'Contract deleted successfully');
} catch (Exception $e) {
    setFlashMessage('error', $e->getMessage());
}

redirect(BASE_URL . '/pages/contracts/list.php');
