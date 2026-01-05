<?php
require_once __DIR__ . '/config/config.php';

echo "=== Quick DB Check ===\n";
echo "Categories: " . dbGetValue("SELECT COUNT(*) FROM categories") . "\n";
echo "Products: " . dbGetValue("SELECT COUNT(*) FROM products") . "\n";
echo "Challans: " . dbGetValue("SELECT COUNT(*) FROM challans") . "\n";
echo "Items: " . dbGetValue("SELECT COUNT(*) FROM challan_items") . "\n";
echo "Revenue: " . dbGetValue("SELECT COALESCE(SUM(total_amount),0) FROM challans") . "\n";

echo "\n=== Sample Products ===\n";
$prods = dbQuery("SELECT name, base_price FROM products LIMIT 5");
foreach($prods as $p) echo "{$p['name']}: ₹{$p['base_price']}\n";

echo "\n=== Sample Challans with Revenue ===\n";
$chs = dbQuery("SELECT c.total_amount, cu.name FROM challans c JOIN customers cu ON c.customer_id=cu.id WHERE c.total_amount > 0 LIMIT 5");
foreach($chs as $c) echo "{$c['name']}: ₹{$c['total_amount']}\n";
