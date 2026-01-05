<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Customer States - Customer Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 0;
        }
        .fix-card {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .stat-box h3 {
            margin: 0;
            font-size: 36px;
            font-weight: 700;
        }
        .stat-box p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="fix-card">
            <h2 class="mb-4"><i class="fas fa-map-marked-alt me-2"></i>Fix Customer States from Locations</h2>
            <p class="text-muted">This script will infer states from customer locations and update the database.</p>
            <hr>

<?php
/**
 * Fix Customer States - Infer states from Location column
 */

require_once __DIR__ . '/config/config.php';

try {
    $db = Database::getInstance();

    // Comprehensive city to state mapping for India
    $cityStateMap = [
        // Maharashtra
        'mumbai' => 'Maharashtra',
        'pune' => 'Maharashtra',
        'nagpur' => 'Maharashtra',
        'nashik' => 'Maharashtra',
        'thane' => 'Maharashtra',
        'virar' => 'Maharashtra',
        'nalasopara' => 'Maharashtra',
        'nala sopara' => 'Maharashtra',
        'badlapur' => 'Maharashtra',
        'kalyan' => 'Maharashtra',
        'dombivli' => 'Maharashtra',
        'solapur' => 'Maharashtra',
        'aurangabad' => 'Maharashtra',
        'kolhapur' => 'Maharashtra',
        'navi mumbai' => 'Maharashtra',
        'vasai' => 'Maharashtra',
        'panvel' => 'Maharashtra',
        'akola' => 'Maharashtra',

        // Delhi NCR
        'delhi' => 'Delhi',
        'new delhi' => 'Delhi',
        'dwarka' => 'Delhi',
        'rohini' => 'Delhi',
        'gurugram' => 'Haryana',
        'gurgaon' => 'Haryana',
        'manesar' => 'Haryana',
        'noida' => 'Uttar Pradesh',
        'greater noida' => 'Uttar Pradesh',
        'ghaziabad' => 'Uttar Pradesh',
        'faridabad' => 'Haryana',

        // Uttar Pradesh
        'lucknow' => 'Uttar Pradesh',
        'kanpur' => 'Uttar Pradesh',
        'bareilly' => 'Uttar Pradesh',
        'amethi' => 'Uttar Pradesh',
        'agra' => 'Uttar Pradesh',
        'varanasi' => 'Uttar Pradesh',
        'meerut' => 'Uttar Pradesh',
        'allahabad' => 'Uttar Pradesh',
        'prayagraj' => 'Uttar Pradesh',
        'moradabad' => 'Uttar Pradesh',
        'aligarh' => 'Uttar Pradesh',
        'gorakhpur' => 'Uttar Pradesh',

        // Karnataka
        'bangalore' => 'Karnataka',
        'bengaluru' => 'Karnataka',
        'mysore' => 'Karnataka',
        'mysuru' => 'Karnataka',
        'mangalore' => 'Karnataka',
        'hubli' => 'Karnataka',
        'belgaum' => 'Karnataka',

        // Gujarat
        'ahmedabad' => 'Gujarat',
        'surat' => 'Gujarat',
        'vadodara' => 'Gujarat',
        'rajkot' => 'Gujarat',
        'bhavnagar' => 'Gujarat',
        'jamnagar' => 'Gujarat',
        'gandhinagar' => 'Gujarat',

        // Tamil Nadu
        'chennai' => 'Tamil Nadu',
        'coimbatore' => 'Tamil Nadu',
        'madurai' => 'Tamil Nadu',
        'tiruchirappalli' => 'Tamil Nadu',
        'trichy' => 'Tamil Nadu',
        'salem' => 'Tamil Nadu',

        // West Bengal
        'kolkata' => 'West Bengal',
        'howrah' => 'West Bengal',
        'durgapur' => 'West Bengal',
        'asansol' => 'West Bengal',

        // Rajasthan
        'jaipur' => 'Rajasthan',
        'jodhpur' => 'Rajasthan',
        'udaipur' => 'Rajasthan',
        'kota' => 'Rajasthan',
        'ajmer' => 'Rajasthan',

        // Telangana
        'hyderabad' => 'Telangana',
        'secunderabad' => 'Telangana',
        'warangal' => 'Telangana',

        // Andhra Pradesh
        'visakhapatnam' => 'Andhra Pradesh',
        'vijayawada' => 'Andhra Pradesh',
        'guntur' => 'Andhra Pradesh',

        // Kerala
        'thiruvananthapuram' => 'Kerala',
        'kochi' => 'Kerala',
        'kozhikode' => 'Kerala',
        'calicut' => 'Kerala',

        // Madhya Pradesh
        'indore' => 'Madhya Pradesh',
        'bhopal' => 'Madhya Pradesh',
        'jabalpur' => 'Madhya Pradesh',
        'gwalior' => 'Madhya Pradesh',

        // Punjab
        'chandigarh' => 'Chandigarh',
        'ludhiana' => 'Punjab',
        'amritsar' => 'Punjab',
        'jalandhar' => 'Punjab',

        // Bihar
        'patna' => 'Bihar',
        'gaya' => 'Bihar',
        'bhagalpur' => 'Bihar',

        // Odisha
        'bhubaneswar' => 'Odisha',
        'cuttack' => 'Odisha',

        // Assam
        'guwahati' => 'Assam',

        // Goa
        'panaji' => 'Goa',
        'margao' => 'Goa',
    ];

    echo "<h3>Step 1: Finding customers with NULL or empty states...</h3>";

    // Get customers without states
    $customers = $db->query(
        "SELECT id, name, location, state_id
         FROM customers
         WHERE state_id IS NULL OR state_id = 0
         ORDER BY location"
    );

    $totalCustomers = count($customers);

    echo "<div class='row mb-4'>";
    echo "<div class='col-md-4'>";
    echo "<div class='stat-box'>";
    echo "<h3>$totalCustomers</h3>";
    echo "<p>Customers without states</p>";
    echo "</div>";
    echo "</div>";

    if ($totalCustomers === 0) {
        echo "<div class='col-md-8'>";
        echo "<div class='alert alert-success'>";
        echo "<strong>All customers already have states assigned!</strong>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "<a href='pages/dashboard.php' class='btn btn-primary btn-lg'>Go to Dashboard</a>";
        exit;
    }

    echo "<div class='col-md-8'>";
    echo "<div class='alert alert-info'>";
    echo "<i class='fas fa-info-circle me-2'></i>Analyzing locations and mapping to states...";
    echo "</div>";
    echo "</div>";
    echo "</div>";

    echo "<h3>Step 2: Inferring states from locations...</h3>";
    echo "<table class='table table-bordered table-hover'>";
    echo "<thead><tr><th>Customer Name</th><th>Location</th><th>Inferred State</th><th>Status</th></tr></thead><tbody>";

    $updatedCount = 0;
    $notFoundCount = 0;

    foreach ($customers as $customer) {
        $location = strtolower(trim($customer['location']));
        $foundState = null;

        // Try to match city name
        foreach ($cityStateMap as $city => $stateName) {
            if (stripos($location, $city) !== false) {
                $foundState = $stateName;
                break;
            }
        }

        echo "<tr>";
        echo "<td>" . htmlspecialchars($customer['name']) . "</td>";
        echo "<td>" . htmlspecialchars($customer['location']) . "</td>";

        if ($foundState) {
            // Get state ID
            $state = $db->queryOne(
                "SELECT id FROM states WHERE LOWER(name) = LOWER(?)",
                [$foundState]
            );

            if ($state) {
                // Update customer
                $db->execute(
                    "UPDATE customers SET state_id = ? WHERE id = ?",
                    [$state['id'], $customer['id']]
                );

                echo "<td style='color: green; font-weight: 600;'>" . htmlspecialchars($foundState) . "</td>";
                echo "<td style='color: green;'><i class='fas fa-check-circle'></i> Updated</td>";
                $updatedCount++;
            } else {
                echo "<td style='color: orange;'>$foundState (not in database)</td>";
                echo "<td style='color: orange;'><i class='fas fa-exclamation-circle'></i> State not found</td>";
                $notFoundCount++;
            }
        } else {
            echo "<td style='color: red;'>Unknown</td>";
            echo "<td style='color: red;'><i class='fas fa-times-circle'></i> Could not infer</td>";
            $notFoundCount++;
        }

        echo "</tr>";
    }

    echo "</tbody></table>";

    // Summary
    echo "<br><div class='row'>";
    echo "<div class='col-md-4'>";
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #155724; margin: 0; font-size: 32px;'>$updatedCount</h3>";
    echo "<p style='color: #155724; margin: 5px 0 0 0;'>Successfully Updated</p>";
    echo "</div>";
    echo "</div>";

    echo "<div class='col-md-4'>";
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #856404; margin: 0; font-size: 32px;'>$notFoundCount</h3>";
    echo "<p style='color: #856404; margin: 5px 0 0 0;'>Could Not Update</p>";
    echo "</div>";
    echo "</div>";

    $totalProcessed = $totalCustomers;
    echo "<div class='col-md-4'>";
    echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #0c5460; margin: 0; font-size: 32px;'>$totalProcessed</h3>";
    echo "<p style='color: #0c5460; margin: 5px 0 0 0;'>Total Processed</p>";
    echo "</div>";
    echo "</div>";
    echo "</div>";

    echo "<br><br>";
    echo "<div class='alert alert-success'>";
    echo "<h4><i class='fas fa-check-circle me-2'></i>State Update Complete!</h4>";
    echo "<p class='mb-0'>The State-wise Revenue chart should now display correctly with actual state names.</p>";
    echo "</div>";

    echo "<div class='d-grid gap-2 d-md-flex justify-content-md-start'>";
    echo "<a href='pages/dashboard.php' class='btn btn-primary btn-lg'><i class='fas fa-chart-line me-2'></i>View Dashboard</a> ";
    echo "<a href='index.php' class='btn btn-secondary btn-lg'><i class='fas fa-home me-2'></i>Back to Login</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; color: #721c24;'>";
    echo "<h4><i class='fas fa-exclamation-triangle me-2'></i>Error</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

        </div>
    </div>
</body>
</html>
