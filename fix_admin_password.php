<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Admin Password - Customer Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 50px 0; }
        .fix-card { max-width: 700px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card fix-card shadow-lg">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0"><i class="fas fa-wrench me-2"></i>Fix Admin Password</h4>
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
                    echo "<div class='alert alert-info'>Connecting to database...</div>";

                    // Connect to database
                    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    echo "<div class='alert alert-success'>✓ Connected to database</div>";

                    // Generate correct password hash for "123456"
                    $correctHash = password_hash('123456', PASSWORD_DEFAULT);

                    echo "<div class='alert alert-info'>Updating admin password...</div>";

                    // Update admin user password
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
                    $stmt->execute([$correctHash]);

                    $rowsAffected = $stmt->rowCount();

                    if ($rowsAffected > 0) {
                        echo "<div class='alert alert-success'><strong>✓ Admin password fixed successfully!</strong></div>";

                        echo "<h5 class='mt-4'>Login Credentials:</h5>";
                        echo "<ul class='list-group mb-4'>";
                        echo "<li class='list-group-item'>Username: <strong>admin</strong></li>";
                        echo "<li class='list-group-item'>Password: <strong>123456</strong></li>";
                        echo "</ul>";

                        echo "<div class='alert alert-warning'>";
                        echo "<strong>Important:</strong> Please change the default password after first login!";
                        echo "</div>";

                        echo "<a href='index.php' class='btn btn-primary btn-lg'><i class='fas fa-sign-in-alt me-2'></i>Go to Login Page</a>";
                    } else {
                        echo "<div class='alert alert-warning'>No admin user found. You may need to run the full database setup.</div>";
                        echo "<a href='setup_database.php' class='btn btn-primary'>Run Full Setup</a>";
                    }

                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'><strong>Database Error:</strong> " . $e->getMessage() . "</div>";

                    if ($e->getCode() == 1049) {
                        echo "<div class='alert alert-info'>Database doesn't exist yet. Please run the full setup.</div>";
                        echo "<a href='setup_database.php' class='btn btn-primary'>Run Database Setup</a>";
                    }
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'><strong>Error:</strong> " . $e->getMessage() . "</div>";
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
