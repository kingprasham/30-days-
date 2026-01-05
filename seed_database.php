<?php
/**
 * Database Seed Script
 * Run ONCE on both localhost and GoDaddy after database reset
 * 
 * This script populates ONLY the states table.
 * Categories are auto-created by Product.php during import.
 * 
 * URL: https://mehrgrewal.com/defaulter/seed_database.php
 * DELETE THIS FILE AFTER RUNNING!
 */

require_once __DIR__ . '/config/config.php';

echo "<h1>Database Seed Script</h1>";
echo "<pre>";

// Populate States (required for state-wise revenue chart)
echo "=== Populating States ===\n";
$stateCount = dbGetValue("SELECT COUNT(*) FROM states");
if ($stateCount > 0) {
    echo "States already exist ($stateCount). Skipping.\n";
} else {
    $states = [
        ['Andhra Pradesh', 'AP', 'South'],
        ['Arunachal Pradesh', 'AR', 'East'],
        ['Assam', 'AS', 'East'],
        ['Bihar', 'BR', 'East'],
        ['Chhattisgarh', 'CG', 'Central'],
        ['Goa', 'GA', 'West'],
        ['Gujarat', 'GJ', 'West'],
        ['Haryana', 'HR', 'North'],
        ['Himachal Pradesh', 'HP', 'North'],
        ['Jharkhand', 'JH', 'East'],
        ['Karnataka', 'KA', 'South'],
        ['Kerala', 'KL', 'South'],
        ['Madhya Pradesh', 'MP', 'Central'],
        ['Maharashtra', 'MH', 'West'],
        ['Manipur', 'MN', 'East'],
        ['Meghalaya', 'ML', 'East'],
        ['Mizoram', 'MZ', 'East'],
        ['Nagaland', 'NL', 'East'],
        ['Odisha', 'OR', 'East'],
        ['Punjab', 'PB', 'North'],
        ['Rajasthan', 'RJ', 'North'],
        ['Sikkim', 'SK', 'East'],
        ['Tamil Nadu', 'TN', 'South'],
        ['Telangana', 'TS', 'South'],
        ['Tripura', 'TR', 'East'],
        ['Uttar Pradesh', 'UP', 'North'],
        ['Uttarakhand', 'UK', 'North'],
        ['West Bengal', 'WB', 'East'],
        ['Delhi', 'DL', 'North'],
        ['Jammu and Kashmir', 'JK', 'North'],
    ];
    
    foreach ($states as $state) {
        try {
            dbExecute("INSERT IGNORE INTO states (name, code, region) VALUES (?, ?, ?)", 
                      [$state[0], $state[1], $state[2]]);
        } catch(Exception $e) {}
    }
    echo "✓ Created " . dbGetValue("SELECT COUNT(*) FROM states") . " states\n";
}

// Note about categories
echo "\n=== Categories ===\n";
echo "Categories are auto-created during Excel import.\n";
echo "(PVC Film, Photo Paper, Blue Film, General)\n";

// Summary
echo "\n=== Summary ===\n";
echo "States: " . dbGetValue("SELECT COUNT(*) FROM states") . "\n";
echo "\n✅ SEED COMPLETE!\n";
echo "</pre>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Delete this file (seed_database.php)</li>";
echo "<li>Go to Excel Upload and import your data</li>";
echo "<li>Check Dashboard - categories will be auto-created</li>";
echo "</ol>";
echo "<p><a href='" . BASE_URL . "/pages/uploads/excel-upload.php'>Go to Excel Upload</a></p>";
