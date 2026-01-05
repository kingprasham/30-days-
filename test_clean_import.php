<?php
require_once __DIR__ . '/config/config.php';

echo "=== Clearing ALL data ===\n";
dbExecute("SET FOREIGN_KEY_CHECKS = 0");
dbExecute("TRUNCATE TABLE challan_items");
dbExecute("TRUNCATE TABLE challans");
dbExecute("TRUNCATE TABLE products");
dbExecute("TRUNCATE TABLE customers");
dbExecute("TRUNCATE TABLE categories");
dbExecute("TRUNCATE TABLE upload_batches");
dbExecute("SET FOREIGN_KEY_CHECKS = 1");
echo "All tables cleared (including categories)\n";

echo "\n=== Verifying categories is EMPTY ===\n";
$catCount = dbGetValue("SELECT COUNT(*) FROM categories");
echo "Categories: $catCount (should be 0)\n";

echo "\n=== Running import (should auto-create category) ===\n";
$importer = new ExcelImporter();
$result = $importer->import(__DIR__ . '/Customer new.xlsx', date('F Y'), 1);
echo "Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
echo "Rows: " . ($result['total_rows'] ?? 0) . "\n";
echo "Success: " . ($result['success_count'] ?? 0) . "\n";
echo "Errors: " . ($result['error_count'] ?? 0) . "\n";

echo "\n=== Final Check ===\n";
echo "Categories: " . dbGetValue("SELECT COUNT(*) FROM categories") . " (should be 1+)\n";
echo "Products: " . dbGetValue("SELECT COUNT(*) FROM products") . "\n";
echo "Challans: " . dbGetValue("SELECT COUNT(*) FROM challans") . "\n";
echo "Items: " . dbGetValue("SELECT COUNT(*) FROM challan_items") . "\n";
echo "Revenue: ₹" . number_format(dbGetValue("SELECT COALESCE(SUM(total_amount),0) FROM challans"), 2) . "\n";
