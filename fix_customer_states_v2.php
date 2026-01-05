<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Customer States V2 - Customer Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 0;
        }
        .fix-card {
            max-width: 1200px;
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
            font-size: 13px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
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
        .table-container {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="fix-card">
            <h2 class="mb-4"><i class="fas fa-map-marked-alt me-2"></i>Fix Customer States from Locations (Enhanced v2)</h2>
            <p class="text-muted">Enhanced version with better Mumbai suburb detection and pattern matching.</p>
            <hr>

<?php
/**
 * Fix Customer States V2 - Enhanced state inference
 */

require_once __DIR__ . '/config/config.php';

try {
    $db = Database::getInstance();

    // Enhanced city to state mapping with Mumbai suburbs
    $cityStateMap = [
        // Maharashtra - Mumbai & Suburbs
        'mumbai' => 'Maharashtra',
        'bombay' => 'Maharashtra',
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

        // Mumbai Suburbs (West)
        'andheri' => 'Maharashtra',
        'bandra' => 'Maharashtra',
        'borivali' => 'Maharashtra',
        'kandivali' => 'Maharashtra',
        'malad' => 'Maharashtra',
        'goregaon' => 'Maharashtra',
        'jogeshwari' => 'Maharashtra',
        'santacruz' => 'Maharashtra',
        'vile parle' => 'Maharashtra',
        'vileparle' => 'Maharashtra',
        'khar' => 'Maharashtra',
        'bhayandar' => 'Maharashtra',
        'bhayander' => 'Maharashtra',
        'dahisar' => 'Maharashtra',
        'mira road' => 'Maharashtra',
        'miraroad' => 'Maharashtra',

        // Mumbai Suburbs (East)
        'ghatkopar' => 'Maharashtra',
        'mulund' => 'Maharashtra',
        'bhandup' => 'Maharashtra',
        'vikhroli' => 'Maharashtra',
        'kanjurmarg' => 'Maharashtra',
        'powai' => 'Maharashtra',
        'chembur' => 'Maharashtra',
        'kurla' => 'Maharashtra',
        'saki naka' => 'Maharashtra',
        'sakinaka' => 'Maharashtra',
        'mankhurd' => 'Maharashtra',
        'govandi' => 'Maharashtra',

        // Mumbai Central & South
        'dadar' => 'Maharashtra',
        'parel' => 'Maharashtra',
        'worli' => 'Maharashtra',
        'mahim' => 'Maharashtra',
        'matunga' => 'Maharashtra',
        'wadala' => 'Maharashtra',
        'sion' => 'Maharashtra',
        'dharavi' => 'Maharashtra',
        'lower parel' => 'Maharashtra',
        'lowerparel' => 'Maharashtra',
        'prabhadevi' => 'Maharashtra',
        'charniroad' => 'Maharashtra',
        'grant road' => 'Maharashtra',
        'grantroad' => 'Maharashtra',
        'mumbai central' => 'Maharashtra',
        'chinchpokli' => 'Maharashtra',
        'antop hill' => 'Maharashtra',
        'gamdevi' => 'Maharashtra',

        // Navi Mumbai
        'vashi' => 'Maharashtra',
        'nerul' => 'Maharashtra',
        'kharghar' => 'Maharashtra',
        'airoli' => 'Maharashtra',
        'sanpada' => 'Maharashtra',
        'kopar khairane' => 'Maharashtra',
        'korakendra' => 'Maharashtra',
        'cbd belapur' => 'Maharashtra',
        'seawoods' => 'Maharashtra',

        // Other Maharashtra Cities
        'bhiwandi' => 'Maharashtra',
        'ulhasnagar' => 'Maharashtra',
        'ambernath' => 'Maharashtra',
        'moshi' => 'Maharashtra',
        'talegaon' => 'Maharashtra',
        'lonavala' => 'Maharashtra',
        'kharadi' => 'Maharashtra',
        'hinjewadi' => 'Maharashtra',
        'wakad' => 'Maharashtra',
        'pimpri' => 'Maharashtra',
        'chinchwad' => 'Maharashtra',
        'aundh' => 'Maharashtra',
        'kothrud' => 'Maharashtra',
        'karve nagar' => 'Maharashtra',
        'deccan' => 'Maharashtra',
        'shivajinagar' => 'Maharashtra',
        'camp' => 'Maharashtra',
        'kalyani nagar' => 'Maharashtra',
        'kondhwa' => 'Maharashtra',
        'hadapsar' => 'Maharashtra',
        'wanowarie' => 'Maharashtra',
        'ambegaon' => 'Maharashtra',
        'satara road' => 'Maharashtra',
        'nagar road' => 'Maharashtra',
        'balewadi' => 'Maharashtra',
        'baner' => 'Maharashtra',
        'warje' => 'Maharashtra',
        'sinhgad' => 'Maharashtra',
        'ratnagiri' => 'Maharashtra',
        'sangli' => 'Maharashtra',
        'ahmednagar' => 'Maharashtra',
        'jalgaon' => 'Maharashtra',
        'dhule' => 'Maharashtra',
        'nandurbar' => 'Maharashtra',
        'beed' => 'Maharashtra',
        'parbhani' => 'Maharashtra',
        'latur' => 'Maharashtra',
        'osmanabad' => 'Maharashtra',
        'jalna' => 'Maharashtra',
        'barshi' => 'Maharashtra',
        'karad' => 'Maharashtra',
        'satara' => 'Maharashtra',
        'miraj' => 'Maharashtra',
        'pandharpur' => 'Maharashtra',
        'indapur' => 'Maharashtra',
        'alibag' => 'Maharashtra',
        'alibagh' => 'Maharashtra',
        'raigad' => 'Maharashtra',
        'pen' => 'Maharashtra',
        'uran' => 'Maharashtra',
        'diva' => 'Maharashtra',
        'titwala' => 'Maharashtra',
        'shilphata' => 'Maharashtra',
        'mumbra' => 'Maharashtra',
        'yerawada' => 'Maharashtra',
        'rasta peth' => 'Maharashtra',
        'narayan peth' => 'Maharashtra',
        'shukrawar peth' => 'Maharashtra',
        'bhosari' => 'Maharashtra',
        'pimple saudagar' => 'Maharashtra',

        // Delhi NCR
        'delhi' => 'Delhi',
        'new delhi' => 'Delhi',
        'dwarka' => 'Delhi',
        'rohini' => 'Delhi',
        'pitampura' => 'Delhi',
        'janakpuri' => 'Delhi',
        'uttam nagar' => 'Delhi',
        'rajinder nagar' => 'Delhi',
        'hauz khas' => 'Delhi',
        'green park' => 'Delhi',
        'saket' => 'Delhi',
        'kailash' => 'Delhi',
        'preet vihar' => 'Delhi',
        'yamuna vihar' => 'Delhi',
        'tilak nagar' => 'Delhi',
        'vipin garden' => 'Delhi',
        'pehladpur' => 'Delhi',
        'karkardooma' => 'Delhi',
        'paschim vihar' => 'Delhi',
        'shalimar bagh' => 'Delhi',
        'shalimarbaug' => 'Delhi',
        'naraina vihar' => 'Delhi',
        'defense colony' => 'Delhi',
        'jangpura' => 'Delhi',
        'greater kailash' => 'Delhi',
        'yusuf sarai' => 'Delhi',

        'gurugram' => 'Haryana',
        'gurgaon' => 'Haryana',
        'manesar' => 'Haryana',
        'faridabad' => 'Haryana',
        'hariyana' => 'Haryana',
        'ambala' => 'Haryana',
        'panchkulla' => 'Haryana',
        'rohtak' => 'Haryana',
        'hissar' => 'Haryana',
        'sonipat' => 'Haryana',
        'panipat' => 'Haryana',
        'sector' => 'Haryana', // Usually Gurgaon/Noida sectors

        'noida' => 'Uttar Pradesh',
        'greater noida' => 'Uttar Pradesh',
        'ghaziabad' => 'Uttar Pradesh',
        'vasundhara' => 'Uttar Pradesh',
        'vaishali' => 'Uttar Pradesh',

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
        'pratap vihar' => 'Uttar Pradesh',
        'bahjoi' => 'Uttar Pradesh',

        // Karnataka
        'bangalore' => 'Karnataka',
        'bengaluru' => 'Karnataka',
        'banglore' => 'Karnataka',
        'bangluru' => 'Karnataka',
        'mysore' => 'Karnataka',
        'mysuru' => 'Karnataka',
        'mangalore' => 'Karnataka',
        'hubli' => 'Karnataka',
        'belgaum' => 'Karnataka',
        'belgavi' => 'Karnataka',
        'mangaluru' => 'Karnataka',
        'koramangala' => 'Karnataka',
        'yelahanka' => 'Karnataka',
        'dasarahalli' => 'Karnataka',

        // Gujarat
        'ahmedabad' => 'Gujarat',
        'surat' => 'Gujarat',
        'vadodara' => 'Gujarat',
        'rajkot' => 'Gujarat',
        'bhavnagar' => 'Gujarat',
        'jamnagar' => 'Gujarat',
        'gandhinagar' => 'Gujarat',
        'anjar' => 'Gujarat',
        'bhuj' => 'Gujarat',
        'gujrat' => 'Gujarat',
        'navsari' => 'Gujarat',
        'valsad' => 'Gujarat',
        'vapi' => 'Gujarat',
        'visnagar' => 'Gujarat',
        'mehsana' => 'Gujarat',
        'deesa' => 'Gujarat',
        'petlad' => 'Gujarat',
        'nadid' => 'Gujarat',
        'morbi' => 'Gujarat',
        'chotila' => 'Gujarat',
        'nakhatrana' => 'Gujarat',
        'savarakundla' => 'Gujarat',
        'padra' => 'Gujarat',
        'karjan' => 'Gujarat',

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
        'barasat' => 'West Bengal',
        'siliguri' => 'West Bengal',

        // Rajasthan
        'jaipur' => 'Rajasthan',
        'jodhpur' => 'Rajasthan',
        'udaipur' => 'Rajasthan',
        'kota' => 'Rajasthan',
        'ajmer' => 'Rajasthan',

        // Telangana
        'hyderabad' => 'Telangana',
        'hydrabad' => 'Telangana',
        'secunderabad' => 'Telangana',
        'warangal' => 'Telangana',
        'gachibowli' => 'Telangana',

        // Andhra Pradesh
        'visakhapatnam' => 'Andhra Pradesh',
        'vijayawada' => 'Andhra Pradesh',
        'guntur' => 'Andhra Pradesh',

        // Kerala
        'thiruvananthapuram' => 'Kerala',
        'kochi' => 'Kerala',
        'kozhikode' => 'Kerala',
        'calicut' => 'Kerala',
        'kerala' => 'Kerala',

        // Madhya Pradesh
        'indore' => 'Madhya Pradesh',
        'bhopal' => 'Madhya Pradesh',
        'jabalpur' => 'Madhya Pradesh',
        'gwalior' => 'Madhya Pradesh',
        'mandsour' => 'Madhya Pradesh',

        // Punjab
        'chandigarh' => 'Chandigarh',
        'ludhiana' => 'Punjab',
        'amritsar' => 'Punjab',
        'jalandhar' => 'Punjab',
        'punjab' => 'Punjab',
        'bathinda' => 'Punjab',
        'mohali' => 'Punjab',
        'rajpura' => 'Punjab',

        // Bihar
        'patna' => 'Bihar',
        'gaya' => 'Bihar',
        'bhagalpur' => 'Bihar',
        'darbhanga' => 'Bihar',
        'nalanda' => 'Bihar',
        'begusarai' => 'Bihar',
        'arrah' => 'Bihar',

        // Odisha
        'bhubaneswar' => 'Odisha',
        'cuttack' => 'Odisha',

        // Assam
        'guwahati' => 'Assam',

        // Goa
        'panaji' => 'Goa',
        'margao' => 'Goa',

        // Jharkhand
        'ranchi' => 'Jharkhand',

        // Uttarakhand
        'dehradun' => 'Uttarakhand',

        // Himachal Pradesh
        'kangra' => 'Himachal Pradesh',

        // Daman
        'daman' => 'Daman and Diu',

        // Arunachal Pradesh
        'akluj' => 'Maharashtra',

        // Jammu & Kashmir
        'barwani' => 'Madhya Pradesh',
        'majalgaon' => 'Maharashtra',
        'aundh' => 'Maharashtra',
        'jawhar' => 'Maharashtra',
        'shahpura' => 'Rajasthan',
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
    echo "<i class='fas fa-info-circle me-2'></i>Analyzing locations with enhanced pattern matching...";
    echo "</div>";
    echo "</div>";
    echo "</div>";

    echo "<h3>Step 2: Inferring states from locations (Enhanced)...</h3>";
    echo "<div class='table-container'>";
    echo "<table class='table table-bordered table-hover'>";
    echo "<thead><tr><th style='width:35%'>Customer Name</th><th style='width:25%'>Location</th><th style='width:20%'>Inferred State</th><th style='width:20%'>Status</th></tr></thead><tbody>";

    $updatedCount = 0;
    $notFoundCount = 0;
    $numericLocationCount = 0;

    foreach ($customers as $customer) {
        $location = trim($customer['location']);
        $locationLower = strtolower($location);
        $foundState = null;

        // Skip numeric-only locations (these are product quantities, not locations)
        if (is_numeric($location) || empty($location)) {
            echo "<tr style='background: #ffe5e5;'>";
            echo "<td>" . htmlspecialchars($customer['name']) . "</td>";
            echo "<td>" . htmlspecialchars($location) . "</td>";
            echo "<td style='color: #999;'>Invalid Data</td>";
            echo "<td style='color: #999;'><i class='fas fa-ban'></i> Skipped (numeric/empty)</td>";
            echo "</tr>";
            $numericLocationCount++;
            continue;
        }

        // Try exact and partial matching
        foreach ($cityStateMap as $city => $stateName) {
            if (stripos($locationLower, $city) !== false) {
                $foundState = $stateName;
                break;
            }
        }

        echo "<tr>";
        echo "<td>" . htmlspecialchars($customer['name']) . "</td>";
        echo "<td>" . htmlspecialchars($location) . "</td>";

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
                echo "<td style='color: orange;'>$foundState (not in DB)</td>";
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
    echo "</div>";

    // Summary
    echo "<br><div class='row'>";
    echo "<div class='col-md-3'>";
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #155724; margin: 0; font-size: 32px;'>$updatedCount</h3>";
    echo "<p style='color: #155724; margin: 5px 0 0 0;'>Successfully Updated</p>";
    echo "</div>";
    echo "</div>";

    echo "<div class='col-md-3'>";
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #856404; margin: 0; font-size: 32px;'>$notFoundCount</h3>";
    echo "<p style='color: #856404; margin: 5px 0 0 0;'>Could Not Update</p>";
    echo "</div>";
    echo "</div>";

    echo "<div class='col-md-3'>";
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #721c24; margin: 0; font-size: 32px;'>$numericLocationCount</h3>";
    echo "<p style='color: #721c24; margin: 5px 0 0 0;'>Invalid/Skipped</p>";
    echo "</div>";
    echo "</div>";

    $totalProcessed = $totalCustomers;
    echo "<div class='col-md-3'>";
    echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #0c5460; margin: 0; font-size: 32px;'>$totalProcessed</h3>";
    echo "<p style='color: #0c5460; margin: 5px 0 0 0;'>Total Processed</p>";
    echo "</div>";
    echo "</div>";
    echo "</div>";

    echo "<br><br>";
    echo "<div class='alert alert-success'>";
    echo "<h4><i class='fas fa-check-circle me-2'></i>State Update Complete!</h4>";
    echo "<p class='mb-2'><strong>Summary:</strong></p>";
    echo "<ul>";
    echo "<li>✅ $updatedCount customers now have correct states</li>";
    echo "<li>⚠️ $numericLocationCount invalid entries (numeric/empty locations) - these are likely data errors</li>";
    echo "<li>❌ " . ($notFoundCount - $numericLocationCount) . " customers with unrecognized locations</li>";
    echo "</ul>";
    echo "<p class='mb-0'><strong>Next:</strong> The State-wise Revenue chart should now display correctly with actual state names.</p>";
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
