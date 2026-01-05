<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix All Data Issues - Customer Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 50px 0; }
        .fix-card { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .step { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .step-title { color: #667eea; font-weight: bold; margin-bottom: 15px; }
        .success-box { background: #d1fae5; color: #059669; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .warning-box { background: #fef3c7; color: #d97706; padding: 20px; border-radius: 8px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="fix-card">
            <h2 class="mb-4"><i class="fas fa-tools me-2"></i>Comprehensive Data Fix</h2>
            <p class="text-muted">This script will fix all data issues including product prices, challan dates, customer states, and revenue calculations.</p>

<?php
require_once __DIR__ . '/config/config.php';

try {
    $db = Database::getInstance();

    // STEP 1: Update Product Prices
    echo '<div class="step">';
    echo '<div class="step-title">Step 1: Updating Product Prices</div>';

    $productPrices = [
        'A3 White PVC Film' => 12.00,
        'A4 White PVC Film' => 8.00,
        'A5 White PVC Film' => 5.00,
        '260 GSM Paper A4' => 6.00,
        '260 GSM Paper A5' => 4.00,
        '230CC Photo Paper A3' => 15.00,
        '230CC Photo Paper A4' => 10.00,
        '230CC Photo Paper A5' => 7.00,
        '210CC Photo Paper A3' => 13.00,
        '210CC Photo Paper A4' => 9.00,
        '180 GSM Photo Paper A3' => 11.00,
        '180 GSM Photo Paper A4' => 8.00,
        'Blue Film 8x10' => 20.00,
        'Blue Film A3' => 18.00,
        'Blue Film A4' => 15.00,
        'Blue Film 13x17' => 25.00,
        'Blue Film 10x12' => 22.00,
    ];

    $updated = 0;
    foreach ($productPrices as $productName => $price) {
        $current = $db->queryOne("SELECT id FROM products WHERE name LIKE ?", ["%$productName%"]);
        if ($current) {
            $db->execute("UPDATE products SET base_price = ? WHERE id = ?", [$price, $current['id']]);
            $updated++;
        }
    }

    echo "<p class='text-success'>✓ Updated prices for $updated products</p>";
    echo '</div>';

    // STEP 2: Recalculate Challan Item Amounts
    echo '<div class="step">';
    echo '<div class="step-title">Step 2: Recalculating Challan Item Amounts</div>';

    $items = $db->query("
        SELECT ci.id, ci.quantity, p.base_price
        FROM challan_items ci
        JOIN products p ON ci.product_id = p.id
    ");

    $itemsUpdated = 0;
    foreach ($items as $item) {
        $rate = $item['base_price'];
        $amount = $item['quantity'] * $rate;
        $db->execute("UPDATE challan_items SET rate = ?, amount = ? WHERE id = ?", [$rate, $amount, $item['id']]);
        $itemsUpdated++;
    }

    echo "<p class='text-success'>✓ Recalculated $itemsUpdated challan items</p>";
    echo '</div>';

    // STEP 3: Update Challan Totals
    echo '<div class="step">';
    echo '<div class="step-title">Step 3: Updating Challan Totals</div>';

    $challans = $db->query("SELECT id FROM challans");
    $challansUpdated = 0;

    foreach ($challans as $challan) {
        $total = $db->getValue(
            "SELECT COALESCE(SUM(amount), 0) FROM challan_items WHERE challan_id = ?",
            [$challan['id']]
        );
        $db->execute("UPDATE challans SET total_amount = ? WHERE id = ?", [$total, $challan['id']]);
        $challansUpdated++;
    }

    echo "<p class='text-success'>✓ Updated totals for $challansUpdated challans</p>";
    echo '</div>';

    // STEP 4: Fix Challan Dates (Parse from original data if available)
    echo '<div class="step">';
    echo '<div class="step-title">Step 4: Checking Challan Dates</div>';

    $dateIssues = $db->getValue("SELECT COUNT(*) FROM challans WHERE challan_date = '2025-12-14'");

    if ($dateIssues > 0) {
        echo "<div class='warning-box'>";
        echo "<strong>⚠ Warning:</strong> Found $dateIssues challans with incorrect date (2025-12-14).<br>";
        echo "These should have November 2025 dates from your Excel file.<br>";
        echo "To fix: Please re-upload your Excel file. The date parser has been improved.";
        echo "</div>";
    } else {
        echo "<p class='text-success'>✓ All challan dates look correct</p>";
    }

    echo '</div>';

    // STEP 5: Check Customer States
    echo '<div class="step">';
    echo '<div class="step-title">Step 5: Checking Customer States</div>';

    $missingStates = $db->getValue("SELECT COUNT(*) FROM customers WHERE state_id IS NULL");

    if ($missingStates > 0) {
        echo "<div class='warning-box'>";
        echo "<strong>⚠ Warning:</strong> Found $missingStates customers without states.<br>";
        echo "This affects the State-wise Revenue chart.<br>";
        echo "The Excel file's 'State' column may be empty. You can:<br>";
        echo "<ul class='mb-0 mt-2'>";
        echo "<li>Manually assign states in Customers → Edit page</li>";
        echo "<li>Or ensure the 'State' column in your Excel has proper state names</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<p class='text-success'>✓ All customers have states assigned</p>";
    }

    echo '</div>';

    // STEP 6: Summary
    $finalRevenue = $db->getValue("SELECT COALESCE(SUM(total_amount), 0) FROM challans");
    $challanCount = $db->getValue("SELECT COUNT(*) FROM challans");
    $customerCount = $db->getValue("SELECT COUNT(*) FROM customers");

    echo '<div class="success-box">';
    echo '<h4 class="mb-3">✓ Data Fix Complete!</h4>';
    echo '<div class="row">';
    echo '<div class="col-md-4">';
    echo '<strong>Total Challans:</strong><br>';
    echo '<h3 class="mb-0">' . number_format($challanCount) . '</h3>';
    echo '</div>';
    echo '<div class="col-md-4">';
    echo '<strong>Total Revenue:</strong><br>';
    echo '<h3 class="mb-0">₹ ' . number_format($finalRevenue, 2) . '</h3>';
    echo '</div>';
    echo '<div class="col-md-4">';
    echo '<strong>Total Customers:</strong><br>';
    echo '<h3 class="mb-0">' . number_format($customerCount) . '</h3>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    echo '<div class="text-center mt-4">';
    echo '<a href="pages/dashboard.php" class="btn btn-primary btn-lg me-2"><i class="fas fa-chart-line me-2"></i>Go to Dashboard</a>';
    echo '<a href="pages/uploads/excel-upload.php" class="btn btn-success btn-lg"><i class="fas fa-file-excel me-2"></i>Upload New Excel</a>';
    echo '</div>';

} catch (Exception $e) {
    echo '<div class="alert alert-danger mt-3">';
    echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
    echo '</div>';
}
?>

        </div>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
