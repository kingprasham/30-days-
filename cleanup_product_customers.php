<?php
/**
 * Cleanup Script - Remove Product Names from Customers Table
 * Removes entries where product names were incorrectly imported as customer names
 */

require_once __DIR__ . '/config/config.php';

$db = Database::getInstance();

// List of product names that should NOT be customers
$productPatterns = [
    'A3 White PVC Film',
    'A4 White PVC Film',
    'A5 White PVC Film',
    '180 CC A4 Paper',
    '180 GSM',
    '210 CC',
    '230 CC',
    '230 GSM',
    '260 GSM',
    'Blue Base Film',
    'Blue Film'
];

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Cleanup Product Names from Customers</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 40px 0; }
        .container { max-width: 900px; }
        .cleanup-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .log-entry { font-family: monospace; font-size: 13px; padding: 5px; border-left: 3px solid #0d6efd; margin: 5px 0; background: #f8f9fa; }
        .deleted { border-left-color: #dc3545; }
        .kept { border-left-color: #198754; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='cleanup-card'>
            <h3><i class='fas fa-broom'></i> Cleanup Product Names from Customers Table</h3>
            <hr>
";

try {
    // Get all customers
    $customers = $db->query("SELECT id, name, state_id, location FROM customers ORDER BY name");

    $deletedCount = 0;
    $keptCount = 0;
    $deletedIds = [];

    echo "<h5>Scanning " . count($customers) . " customers...</h5>";
    echo "<div class='mt-3'>";

    foreach ($customers as $customer) {
        $shouldDelete = false;
        $reason = '';

        // Check if name matches product patterns
        foreach ($productPatterns as $pattern) {
            if (stripos($customer['name'], $pattern) !== false) {
                $shouldDelete = true;
                $reason = "Contains product keyword: $pattern";
                break;
            }
        }

        // Check regex patterns
        $regexPatterns = [
            '/^(a3|a4|a5)\s+(white\s+)?pvc\s+film/i' => 'PVC Film pattern',
            '/^(180|210|230|260)\s*(cc|gsm)\s+(photo\s+)?paper/i' => 'GSM/CC Paper pattern',
            '/^blue\s+(base\s+)?film/i' => 'Blue Film pattern',
            '/^\d+\s*(cc|gsm)/i' => 'Starts with number + CC/GSM',
        ];

        if (!$shouldDelete) {
            foreach ($regexPatterns as $pattern => $description) {
                if (preg_match($pattern, $customer['name'])) {
                    $shouldDelete = true;
                    $reason = "Matches $description";
                    break;
                }
            }
        }

        // Check if name is purely numeric (clearly wrong)
        if (!$shouldDelete && is_numeric($customer['name'])) {
            $shouldDelete = true;
            $reason = "Purely numeric name";
        }

        // Check if location looks like a number (wrong data)
        if (!$shouldDelete && is_numeric($customer['location']) && strlen($customer['location']) >= 4) {
            $shouldDelete = true;
            $reason = "Numeric location field (likely product code)";
        }

        if ($shouldDelete) {
            // Delete customer and related records
            $db->execute("DELETE FROM challan_items WHERE challan_id IN (SELECT id FROM challans WHERE customer_id = ?)", [$customer['id']]);
            $db->execute("DELETE FROM challans WHERE customer_id = ?", [$customer['id']]);
            $db->execute("DELETE FROM customer_product_prices WHERE customer_id = ?", [$customer['id']]);
            $db->execute("DELETE FROM customer_dealers WHERE customer_id = ?", [$customer['id']]);
            $db->execute("DELETE FROM customer_locations WHERE customer_id = ?", [$customer['id']]);
            $db->execute("DELETE FROM contracts WHERE customer_id = ?", [$customer['id']]);
            $db->execute("DELETE FROM customers WHERE id = ?", [$customer['id']]);

            $deletedIds[] = $customer['id'];
            $deletedCount++;

            echo "<div class='log-entry deleted'>";
            echo "❌ DELETED: <strong>" . htmlspecialchars($customer['name']) . "</strong>";
            echo " (ID: {$customer['id']}, Location: " . htmlspecialchars($customer['location'] ?? 'N/A') . ")";
            echo "<br>&nbsp;&nbsp;&nbsp;Reason: $reason";
            echo "</div>";
        } else {
            $keptCount++;
            if ($keptCount <= 10) { // Show first 10 kept entries as sample
                echo "<div class='log-entry kept'>";
                echo "✓ KEPT: " . htmlspecialchars($customer['name']);
                echo "</div>";
            }
        }
    }

    if ($keptCount > 10) {
        echo "<div class='log-entry kept'>";
        echo "✓ ... and " . ($keptCount - 10) . " more valid customers kept";
        echo "</div>";
    }

    echo "</div>";

    echo "<hr>";
    echo "<div class='alert alert-success'>";
    echo "<h5>✓ Cleanup Complete!</h5>";
    echo "<ul class='mb-0'>";
    echo "<li><strong>Total Customers Scanned:</strong> " . count($customers) . "</li>";
    echo "<li><strong>Product Names Deleted:</strong> $deletedCount</li>";
    echo "<li><strong>Valid Customers Kept:</strong> $keptCount</li>";
    echo "</ul>";
    echo "</div>";

    if ($deletedCount > 0) {
        echo "<div class='alert alert-info'>";
        echo "<strong>Note:</strong> All related records (challans, contracts, prices) for deleted customers were also removed.";
        echo "</div>";
    }

    echo "<a href='pages/customers/list.php' class='btn btn-primary'><i class='fas fa-users'></i> View Customers List</a> ";
    echo "<a href='pages/dashboard.php' class='btn btn-outline-secondary'><i class='fas fa-home'></i> Dashboard</a>";

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "
        </div>
    </div>
</body>
</html>";
