<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Customer Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 50px 0; }
        .setup-card { max-width: 800px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card setup-card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-database me-2"></i>Database Setup</h4>
            </div>
            <div class="card-body">
                <?php
                error_reporting(E_ALL);
                ini_set('display_errors', 1);

                $host = 'localhost';
                $user = 'root';
                $pass = '';
                $dbname = 'customer_tracker';

                try {
                    echo "<div class='alert alert-info'>Connecting to MySQL server...</div>";

                    // Connect to MySQL (without database)
                    $pdo = new PDO("mysql:host=$host", $user, $pass);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    echo "<div class='alert alert-success'>✓ Connected to MySQL server</div>";

                    // Read SQL file
                    $sqlFile = __DIR__ . '/database/setup.sql';

                    if (!file_exists($sqlFile)) {
                        throw new Exception("SQL file not found at: $sqlFile");
                    }

                    $sql = file_get_contents($sqlFile);

                    echo "<div class='alert alert-info'>Executing SQL setup script...</div>";

                    // Execute SQL statements
                    $pdo->exec($sql);

                    echo "<div class='alert alert-success'><strong>✓ Database setup completed successfully!</strong></div>";

                    echo "<h5 class='mt-4'>Setup Summary:</h5>";
                    echo "<ul class='list-group mb-4'>";
                    echo "<li class='list-group-item'>Database: <strong>$dbname</strong></li>";
                    echo "<li class='list-group-item'>Default Admin Username: <strong>admin</strong></li>";
                    echo "<li class='list-group-item'>Default Admin Password: <strong>123456</strong></li>";
                    echo "<li class='list-group-item'>States: <strong>37 Indian states added</strong></li>";
                    echo "<li class='list-group-item'>Product Categories: <strong>4 categories added</strong></li>";
                    echo "<li class='list-group-item'>Products: <strong>17 products added</strong></li>";
                    echo "</ul>";

                    echo "<div class='alert alert-warning'>";
                    echo "<strong>Important:</strong> Please change the default admin password after first login!";
                    echo "</div>";

                    echo "<a href='index.php' class='btn btn-primary btn-lg'><i class='fas fa-sign-in-alt me-2'></i>Go to Login Page</a>";

                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'><strong>Database Error:</strong> " . $e->getMessage() . "</div>";
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'><strong>Error:</strong> " . $e->getMessage() . "</div>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
