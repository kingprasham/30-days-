<?php
/**
 * Delete Product
 * Customer Tracking & Billing Management System
 */

require_once __DIR__ . '/../../config/config.php';
requireAdmin();

$product = new Product();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid product ID');
    redirect(BASE_URL . '/pages/products/list.php');
}

try {
    $product->delete($id);
    setFlashMessage('success', 'Product deleted successfully');
} catch (Exception $e) {
    setFlashMessage('error', $e->getMessage());
}

redirect(BASE_URL . '/pages/products/list.php');
