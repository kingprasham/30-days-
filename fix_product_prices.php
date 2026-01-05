<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Product Prices - Customer Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 50px 0; }
        .fix-card { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        table { width: 100%; margin: 20px 0; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #667eea; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="fix-card">
            <h2 class="mb-4"><i class="fas fa-tools me-2"></i>Fix Product Prices & Revenue Calculation</h2>

<?php
/**
 * Fix Product Prices - Set Base Prices for All Products
 */

require_once __DIR__ . '/config/config.php';

try {
    $db = Database::getInstance();

    // Realistic product prices based on market rates for medical imaging supplies
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

    echo "<h3>Updating Product Prices...</h3>";
    echo "<table class='table table-bordered'>";
    echo "<thead><tr><th>Product Name</th><th>Old Price</th><th>New Price</th><th>Status</th></tr></thead><tbody>";

    foreach ($productPrices as $productName => $price) {
        // Get current price
        $current = $db->queryOne("SELECT id, base_price FROM products WHERE name LIKE ?", ["%$productName%"]);

        if ($current) {
            $oldPrice = $current['base_price'];

            // Update price
            $db->execute(
                "UPDATE products SET base_price = ? WHERE id = ?",
                [$price, $current['id']]
            );

            echo "<tr>";
            echo "<td>$productName</td>";
            echo "<td style='color: red;'>₹" . number_format($oldPrice, 2) . "</td>";
            echo "<td style='color: green;'>₹" . number_format($price, 2) . "</td>";
            echo "<td style='color: green;'>✓ Updated</td>";
            echo "</tr>";
        } else {
            echo "<tr>";
            echo "<td>$productName</td>";
            echo "<td colspan='2' style='color: orange;'>Not Found</td>";
            echo "<td>-</td>";
            echo "</tr>";
        }
    }

    echo "</tbody></table>";

    echo "<br><h3>Recalculating Challan Amounts...</h3>";

    // Get all challan items and recalculate
    $items = $db->query("
        SELECT ci.id, ci.challan_id, ci.product_id, ci.quantity, p.base_price, p.name
        FROM challan_items ci
        JOIN products p ON ci.product_id = p.id
    ");

    $updatedItems = 0;
    $totalRevenue = 0;

    foreach ($items as $item) {
        $rate = $item['base_price'];
        $amount = $item['quantity'] * $rate;
        $totalRevenue += $amount;

        $db->execute(
            "UPDATE challan_items SET rate = ?, amount = ? WHERE id = ?",
            [$rate, $amount, $item['id']]
        );

        $updatedItems++;
    }

    echo "<p>✓ Updated $updatedItems challan items</p>";
    echo "<p>Total calculated revenue: <strong>₹" . number_format($totalRevenue, 2) . "</strong></p>";

    // Update challan totals
    echo "<br><h3>Updating Challan Totals...</h3>";

    $challans = $db->query("SELECT id FROM challans");
    $updatedChallans = 0;

    foreach ($challans as $challan) {
        $total = $db->getValue(
            "SELECT COALESCE(SUM(amount), 0) FROM challan_items WHERE challan_id = ?",
            [$challan['id']]
        );

        $db->execute(
            "UPDATE challans SET total_amount = ? WHERE id = ?",
            [$total, $challan['id']]
        );

        $updatedChallans++;
    }

    echo "<p>✓ Updated $updatedChallans challans with correct totals</p>";

    // Show summary
    $finalRevenue = $db->getValue("SELECT SUM(total_amount) FROM challans");
    $challanCount = $db->getValue("SELECT COUNT(*) FROM challans");

    echo "<br><div style='background: #d4edda; padding: 20px; border-radius: 5px;'>";
    echo "<h3 style='color: #155724;'>✓ Success! All prices and amounts updated</h3>";
    echo "<p><strong>Total Challans:</strong> $challanCount</p>";
    echo "<p><strong>Total Revenue:</strong> ₹" . number_format($finalRevenue, 2) . "</p>";
    echo "</div>";

    echo "<br><a href='pages/dashboard.php' class='btn btn-primary btn-lg'>Go to Dashboard</a> ";
    echo "<a href='index.php' class='btn btn-secondary btn-lg'>Back to Login</a>";

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; color: #721c24;'>";
    echo "<strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>

        </div>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
