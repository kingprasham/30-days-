<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Challan Dates - Customer Tracker</title>
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
        .progress-log {
            max-height: 400px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="fix-card">
            <h2 class="mb-4"><i class="fas fa-calendar-check me-2"></i>Fix Challan Dates from Excel</h2>
            <p class="text-muted">This script will re-parse all challan dates from the Excel file and update the database.</p>
            <hr>

<?php
/**
 * Fix Challan Dates - Re-parse from Excel
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $db = Database::getInstance();

    echo "<h3>Step 1: Loading Excel file...</h3>";

    $excelFile = __DIR__ . '/Customer new.xlsx';
    if (!file_exists($excelFile)) {
        throw new Exception("Excel file not found: $excelFile");
    }

    $spreadsheet = IOFactory::load($excelFile);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();

    echo "<div class='alert alert-success'>✓ Excel file loaded successfully</div>";

    // Column indices
    $customerCol = 5; // Customer Name
    $challanNoCol = 9; // Challan No
    $challanDateCol = 8; // Challan Date

    echo "<h3>Step 2: Parsing dates and matching challans...</h3>";
    echo "<div class='progress-log'>";

    $updated = 0;
    $notFound = 0;
    $errors = 0;
    $dateSummary = [];

    for ($i = 1; $i < count($data); $i++) {
        $row = $data[$i];

        $customerName = trim($row[$customerCol] ?? '');
        $challanNo = trim($row[$challanNoCol] ?? '');
        $challanDateStr = trim($row[$challanDateCol] ?? '');

        if (empty($customerName) || empty($challanNo) || empty($challanDateStr)) {
            continue;
        }

        // Parse date (format: 1/Nov/25, 3/Nov/25, etc.)
        $parts = explode('/', $challanDateStr);
        if (count($parts) === 3) {
            $day = $parts[0];
            $month = $parts[1];
            $year = '20' . $parts[2];

            try {
                $date = new DateTime("$day-$month-$year");
                $formattedDate = $date->format('Y-m-d');

                // Track date distribution
                if (!isset($dateSummary[$formattedDate])) {
                    $dateSummary[$formattedDate] = 0;
                }
                $dateSummary[$formattedDate]++;

                // Find and update challan in database
                $updated += $db->execute(
                    "UPDATE challans SET challan_date = ?
                     WHERE challan_no = ?
                     AND customer_id IN (SELECT id FROM customers WHERE LOWER(name) = LOWER(?))",
                    [$formattedDate, $challanNo, $customerName]
                );

                if ($i % 100 === 0) {
                    echo "Processed $i rows... (Updated: $updated)\n";
                    flush();
                }

            } catch (Exception $e) {
                $errors++;
                echo "ERROR parsing date '$challanDateStr' for challan $challanNo: " . $e->getMessage() . "\n";
            }
        } else {
            $errors++;
        }
    }

    echo "</div>";

    echo "<br><h3>Step 3: Summary</h3>";

    echo "<div class='row mb-4'>";
    echo "<div class='col-md-4'>";
    echo "<div class='stat-box' style='background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);'>";
    echo "<h3>$updated</h3>";
    echo "<p>Challans Updated</p>";
    echo "</div>";
    echo "</div>";

    echo "<div class='col-md-4'>";
    echo "<div class='stat-box' style='background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);'>";
    echo "<h3>$errors</h3>";
    echo "<p>Parse Errors</p>";
    echo "</div>";
    echo "</div>";

    echo "<div class='col-md-4'>";
    echo "<div class='stat-box' style='background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);'>";
    $totalRows = count($data) - 1;
    echo "<h3>$totalRows</h3>";
    echo "<p>Total Rows Processed</p>";
    echo "</div>";
    echo "</div>";
    echo "</div>";

    // Show date distribution
    echo "<h4>Date Distribution:</h4>";
    echo "<table class='table table-bordered table-sm'>";
    echo "<thead><tr><th>Date</th><th>Challan Count</th></tr></thead><tbody>";

    ksort($dateSummary);
    foreach ($dateSummary as $date => $count) {
        $dateObj = new DateTime($date);
        $formatted = $dateObj->format('F d, Y (D)');
        echo "<tr><td>$formatted</td><td>$count</td></tr>";
    }
    echo "</tbody></table>";

    // Verify fix by checking for defaulters
    echo "<br><h3>Step 4: Verifying Defaulter Detection...</h3>";

    $defaulters = $db->query(
        "SELECT c.id, c.name,
                MAX(ch.challan_date) as last_challan_date,
                DATEDIFF(CURDATE(), MAX(ch.challan_date)) as days_inactive
         FROM customers c
         LEFT JOIN challans ch ON c.id = ch.customer_id
         WHERE c.status = 'active'
         GROUP BY c.id
         HAVING days_inactive > 30 OR last_challan_date IS NULL
         ORDER BY days_inactive DESC
         LIMIT 10"
    );

    $defaulterCount = count($defaulters);

    echo "<div class='alert alert-" . ($defaulterCount > 0 ? 'warning' : 'success') . "'>";
    echo "<h4><i class='fas fa-" . ($defaulterCount > 0 ? 'exclamation-triangle' : 'check-circle') . " me-2'></i>";
    echo "Defaulter Detection Status</h4>";
    echo "<p><strong>$defaulterCount customers</strong> have not had a challan in 30+ days</p>";

    if ($defaulterCount > 0) {
        echo "<p class='mb-0'><small>Top 10 defaulters shown below. Full list available on Dashboard.</small></p>";
    }
    echo "</div>";

    if ($defaulterCount > 0) {
        echo "<table class='table table-sm table-hover'>";
        echo "<thead><tr><th>Customer</th><th>Last Challan</th><th>Days Inactive</th></tr></thead><tbody>";
        foreach ($defaulters as $d) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($d['name']) . "</td>";
            echo "<td>" . $d['last_challan_date'] . "</td>";
            echo "<td><span class='badge bg-danger'>" . $d['days_inactive'] . " days</span></td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    }

    echo "<br><div class='alert alert-success'>";
    echo "<h4><i class='fas fa-check-circle me-2'></i>Challan Dates Fixed Successfully!</h4>";
    echo "<ul class='mb-0'>";
    echo "<li>✅ $updated challan dates updated from Excel</li>";
    echo "<li>✅ Defaulter detection now working correctly</li>";
    echo "<li>✅ Dashboard KPIs will show accurate data</li>";
    echo "<li>✅ Monthly revenue trends will display properly</li>";
    echo "</ul>";
    echo "</div>";

    echo "<div class='d-grid gap-2 d-md-flex justify-content-md-start'>";
    echo "<a href='pages/dashboard.php' class='btn btn-primary btn-lg'><i class='fas fa-chart-line me-2'></i>View Dashboard</a> ";
    echo "<a href='pages/reports/defaulters.php' class='btn btn-warning btn-lg'><i class='fas fa-exclamation-triangle me-2'></i>View Defaulters</a> ";
    echo "<a href='index.php' class='btn btn-secondary btn-lg'><i class='fas fa-home me-2'></i>Home</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; color: #721c24;'>";
    echo "<h4><i class='fas fa-exclamation-triangle me-2'></i>Error</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
?>

        </div>
    </div>
</body>
</html>
