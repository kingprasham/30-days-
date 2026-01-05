<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Customer States - Customer Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 50px 0; }
        .fix-card { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="container">
        <div class="fix-card">
            <h2 class="mb-4"><i class="fas fa-map-marked-alt me-2"></i>Update Customer States from Locations</h2>

<?php
require_once __DIR__ . '/config/config.php';

try {
    $db = Database::getInstance();

    // City to state mapping
    $cityStateMap = [
        // Maharashtra
        'mumbai' => 'Maharashtra', 'pune' => 'Maharashtra', 'nagpur' => 'Maharashtra',
        'nashik' => 'Maharashtra', 'thane' => 'Maharashtra', 'aurangabad' => 'Maharashtra',
        'solapur' => 'Maharashtra', 'virar' => 'Maharashtra', 'navi mumbai' => 'Maharashtra',
        'kalyan' => 'Maharashtra', 'vasai' => 'Maharashtra', 'palghar' => 'Maharashtra',
        'badlapur' => 'Maharashtra', 'sangli' => 'Maharashtra', 'palus' => 'Maharashtra',
        'nalasopara' => 'Maharashtra',

        // Delhi NCR
        'delhi' => 'Delhi', 'new delhi' => 'Delhi', 'dwarka' => 'Delhi',
        'gurugram' => 'Haryana', 'gurgaon' => 'Haryana', 'faridabad' => 'Haryana', 'manesar' => 'Haryana',
        'noida' => 'Uttar Pradesh', 'ghaziabad' => 'Uttar Pradesh', 'greater noida' => 'Uttar Pradesh',

        // Uttar Pradesh
        'lucknow' => 'Uttar Pradesh', 'kanpur' => 'Uttar Pradesh', 'agra' => 'Uttar Pradesh',
        'varanasi' => 'Uttar Pradesh', 'meerut' => 'Uttar Pradesh', 'allahabad' => 'Uttar Pradesh',
        'bareilly' => 'Uttar Pradesh', 'aligarh' => 'Uttar Pradesh', 'moradabad' => 'Uttar Pradesh',
        'saharanpur' => 'Uttar Pradesh', 'gorakhpur' => 'Uttar Pradesh', 'amethi' => 'Uttar Pradesh',

        // Karnataka
        'bangalore' => 'Karnataka', 'bengaluru' => 'Karnataka', 'mysore' => 'Karnataka',
        'mangalore' => 'Karnataka', 'hubli' => 'Karnataka', 'belgaum' => 'Karnataka',

        // Gujarat
        'ahmedabad' => 'Gujarat', 'surat' => 'Gujarat', 'vadodara' => 'Gujarat',
        'rajkot' => 'Gujarat', 'bhavnagar' => 'Gujarat', 'jamnagar' => 'Gujarat',

        // Other major cities
        'hyderabad' => 'Telangana', 'chennai' => 'Tamil Nadu', 'kolkata' => 'West Bengal',
        'jaipur' => 'Rajasthan', 'bhopal' => 'Madhya Pradesh', 'patna' => 'Bihar',
    ];

    // Get all customers without states
    $customers = $db->query("SELECT id, name, location FROM customers WHERE state_id IS NULL");

    echo "<p>Found " . count($customers) . " customers without states</p>";
    echo "<table class='table table-sm'>";
    echo "<thead><tr><th>Customer</th><th>Location</th><th>Inferred State</th><th>Status</th></tr></thead><tbody>";

    $updated = 0;
    $notFound = 0;

    foreach ($customers as $customer) {
        $location = strtolower(trim($customer['location']));
        $foundState = null;

        // Try to match location to city
        foreach ($cityStateMap as $city => $stateName) {
            if (stripos($location, $city) !== false) {
                $foundState = $stateName;
                break;
            }
        }

        echo "<tr>";
        echo "<td>" . htmlspecialchars(substr($customer['name'], 0, 50)) . "</td>";
        echo "<td>" . htmlspecialchars($customer['location']) . "</td>";
        echo "<td>" . ($foundState ? htmlspecialchars($foundState) : '<span class="text-muted">Not found</span>') . "</td>";

        if ($foundState) {
            // Get state ID
            $state = $db->queryOne("SELECT id FROM states WHERE LOWER(name) = LOWER(?)", [$foundState]);
            if ($state) {
                $db->execute("UPDATE customers SET state_id = ? WHERE id = ?", [$state['id'], $customer['id']]);
                echo "<td><span class='badge bg-success'>Updated</span></td>";
                $updated++;
            } else {
                echo "<td><span class='badge bg-warning'>State not in DB</span></td>";
                $notFound++;
            }
        } else {
            echo "<td><span class='badge bg-secondary'>Skipped</span></td>";
            $notFound++;
        }

        echo "</tr>";
    }

    echo "</tbody></table>";

    echo "<div class='alert alert-success mt-4'>";
    echo "<h5>Update Complete!</h5>";
    echo "<p><strong>Updated:</strong> $updated customers</p>";
    echo "<p><strong>Not Matched:</strong> $notFound customers</p>";
    echo "</div>";

    echo "<a href='pages/dashboard.php' class='btn btn-primary'>Go to Dashboard</a>";

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}
?>

        </div>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
