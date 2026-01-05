<?php
/**
 * Setup Script: Initialize Required Database Data
 * 
 * Run this ONCE on GoDaddy to:
 * 1. Create default category (required for products)
 * 2. Recalculate any existing challan amounts
 * 
 * Access via: https://mehrgrewal.com/defaulter/setup_db.php
 * DELETE THIS FILE AFTER RUNNING!
 */

require_once __DIR__ . '/config/config.php';

echo "<h1>Database Setup Script</h1>";
echo "<pre>";

// Step 1: Create default category if missing
echo "=== Step 1: Checking Categories ===\n";
$cat = dbQueryOne("SELECT id FROM categories WHERE id = 1");
if (!$cat) {
    dbExecute("INSERT INTO categories (id, name, description) VALUES (1, 'General', 'General products')");
    echo "✓ Created default category (ID: 1)\n";
} else {
    echo "✓ Default category already exists\n";
}

// Step 2: Update product prices if products exist
echo "\n=== Step 2: Updating Product Prices ===\n";
$products = dbQuery("SELECT id, name, base_price FROM products");
if (count($products) > 0) {
    $priceMap = [
        'a3 white pvc' => 12.00,
        'a4 white pvc' => 8.00, 
        'a5 white pvc' => 5.00,
        '260 gsm.*a4' => 6.00,
        '260 gsm.*a5' => 4.00,
        '230cc.*a3' => 15.00,
        '230cc.*a4' => 10.00,
        '230cc.*a5' => 7.00,
        '210cc.*a3' => 13.00,
        '210cc.*a4' => 9.00,
        '180.*a3' => 11.00,
        '180.*a4' => 8.00,
        'blue film.*8.*10' => 20.00,
        'blue film.*a3' => 18.00,
        'blue film.*a4' => 15.00,
        'blue film.*13.*17' => 25.00,
        'blue film.*10.*12' => 22.00,
    ];
    
    $updated = 0;
    foreach ($products as $p) {
        $newPrice = 10.00; // default
        foreach ($priceMap as $pattern => $price) {
            if (preg_match('/' . $pattern . '/i', strtolower($p['name']))) {
                $newPrice = $price;
                break;
            }
        }
        if ($p['base_price'] != $newPrice) {
            dbExecute("UPDATE products SET base_price = ? WHERE id = ?", [$newPrice, $p['id']]);
            $updated++;
        }
    }
    echo "✓ Updated $updated product prices\n";
} else {
    echo "No products to update\n";
}

// Step 3: Recalculate challan amounts
echo "\n=== Step 3: Recalculating Challan Amounts ===\n";
$challanCount = dbGetValue("SELECT COUNT(*) FROM challans");
if ($challanCount > 0) {
    // Update challan_items with correct amounts
    dbExecute("
        UPDATE challan_items ci
        JOIN products p ON ci.product_id = p.id
        SET ci.rate = p.base_price,
            ci.amount = ci.quantity * p.base_price
    ");
    
    // Recalculate challan totals
    $challans = dbQuery("SELECT id FROM challans");
    foreach ($challans as $ch) {
        $total = dbGetValue("SELECT COALESCE(SUM(amount), 0) FROM challan_items WHERE challan_id = ?", [$ch['id']]);
        dbExecute("UPDATE challans SET total_amount = ? WHERE id = ?", [$total, $ch['id']]);
    }
    echo "✓ Recalculated amounts for $challanCount challans\n";
} else {
    echo "No challans to recalculate\n";
}

// Summary
echo "\n=== Summary ===\n";
echo "Categories: " . dbGetValue("SELECT COUNT(*) FROM categories") . "\n";
echo "Customers: " . dbGetValue("SELECT COUNT(*) FROM customers") . "\n";
echo "Products: " . dbGetValue("SELECT COUNT(*) FROM products") . "\n";
echo "Challans: " . dbGetValue("SELECT COUNT(*) FROM challans") . "\n";
echo "Total Revenue: ₹" . number_format(dbGetValue("SELECT COALESCE(SUM(total_amount),0) FROM challans"), 2) . "\n";

echo "\n✅ SETUP COMPLETE!\n";
echo "</pre>";
echo "<p><a href='" . BASE_URL . "/pages/dashboard.php'>Go to Dashboard</a></p>";
echo "<p><strong>⚠️ Delete this file after running for security!</strong></p>";
