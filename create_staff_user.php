<?php
/**
 * Create Staff User Script
 * Creates a test staff user for testing permissions
 */

require_once __DIR__ . '/config/config.php';

$user = new User();

try {
    // Create a staff user
    $userId = $user->createWithRole([
        'username' => 'staff',
        'email' => 'staff@example.com',
        'full_name' => 'Staff User',
        'password' => 'staff123',
        'role' => 'staff',
        'status' => 'active'
    ]);

    echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Staff User Created</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 40px 0; }
        .container { max-width: 600px; }
        .success-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class='container'>
        <div class='success-card'>
            <h3 class='text-success'><i class='fas fa-check-circle'></i> Staff User Created Successfully!</h3>
            <hr>
            <div class='alert alert-info'>
                <h5>Login Credentials:</h5>
                <ul class='mb-0'>
                    <li><strong>Username:</strong> staff</li>
                    <li><strong>Password:</strong> staff123</li>
                    <li><strong>Role:</strong> Staff (View & Add only)</li>
                </ul>
            </div>
            <div class='alert alert-warning'>
                <h5>Staff Permissions:</h5>
                <ul class='mb-0'>
                    <li>✓ Can view all pages</li>
                    <li>✓ Can add new records (customers, challans, etc.)</li>
                    <li>✓ Can upload Excel files</li>
                    <li>✗ Cannot edit existing records</li>
                    <li>✗ Cannot delete any records</li>
                    <li>✗ Cannot access Settings pages</li>
                </ul>
            </div>
            <a href='index.php' class='btn btn-primary'><i class='fas fa-sign-in-alt'></i> Go to Login</a>
            <a href='pages/dashboard.php' class='btn btn-outline-secondary'><i class='fas fa-home'></i> Dashboard</a>
        </div>
    </div>
</body>
</html>";

} catch (Exception $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Staff User Exists</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 40px 0; }
        .container { max-width: 600px; }
        .info-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class='container'>
        <div class='info-card'>
            <h3 class='text-info'><i class='fas fa-info-circle'></i> Staff User Already Exists</h3>
            <hr>
            <div class='alert alert-info'>
                <h5>Existing Login Credentials:</h5>
                <ul class='mb-0'>
                    <li><strong>Username:</strong> staff</li>
                    <li><strong>Password:</strong> staff123</li>
                    <li><strong>Role:</strong> Staff (View & Add only)</li>
                </ul>
            </div>
            <a href='index.php' class='btn btn-primary'><i class='fas fa-sign-in-alt'></i> Go to Login</a>
        </div>
    </div>
</body>
</html>";
    } else {
        echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Error Creating Staff User</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 40px 0; }
        .container { max-width: 600px; }
        .error-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class='container'>
        <div class='error-card'>
            <h3 class='text-danger'><i class='fas fa-exclamation-circle'></i> Error</h3>
            <hr>
            <div class='alert alert-danger'>
                <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "
            </div>
        </div>
    </div>
</body>
</html>";
    }
}
