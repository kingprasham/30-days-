<?php
/**
 * Delete Dealer
 * Customer Tracking & Billing Management System
 */

require_once __DIR__ . '/../../config/config.php';
requireAdmin();

$dealer = new Dealer();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid dealer ID');
    redirect(BASE_URL . '/pages/dealers/list.php');
}

try {
    $dealer->delete($id);
    setFlashMessage('success', 'Dealer deleted successfully');
} catch (Exception $e) {
    setFlashMessage('error', $e->getMessage());
}

redirect(BASE_URL . '/pages/dealers/list.php');
