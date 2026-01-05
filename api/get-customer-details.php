<?php
/**
 * Get Customer Details API
 * Returns customer details for auto-filling challan form
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Require login
requireLogin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Customer ID required']);
    exit;
}

$customerId = (int)$_GET['id'];

try {
    $db = Database::getInstance();

    // Get customer details with state
    $customer = $db->queryOne(
        "SELECT c.id, c.name, c.location, c.monthly_commitment,
                c.installation_date, c.contract_start_date, c.contract_end_date,
                s.name as state_name
         FROM customers c
         LEFT JOIN states s ON c.state_id = s.id
         WHERE c.id = ?",
        [$customerId]
    );

    if (!$customer) {
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
        exit;
    }

    // Get last challan details for this customer
    $lastChallan = $db->queryOne(
        "SELECT delivery_through, material_sending_location, rate
         FROM challans
         WHERE customer_id = ?
         ORDER BY challan_date DESC
         LIMIT 1",
        [$customerId]
    );

    // Get customer product prices
    $productPrices = $db->query(
        "SELECT p.id, p.name, cpp.price
         FROM customer_product_prices cpp
         INNER JOIN products p ON cpp.product_id = p.id
         WHERE cpp.customer_id = ?",
        [$customerId]
    );

    $response = [
        'success' => true,
        'customer' => $customer,
        'lastChallan' => $lastChallan,
        'productPrices' => $productPrices
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
