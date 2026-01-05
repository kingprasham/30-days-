<?php
/**
 * Admin Database Reset Script
 * DANGER: This will delete ALL data from the database
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// Must be admin
requireLogin();
$currentUser = getCurrentUser();
if ($currentUser['role'] !== 'admin') {
    die('Access denied. Admin only.');
}

$db = Database::getInstance();
$success = false;
$message = '';

// Process reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reset'])) {
    $confirmText = $_POST['confirm_text'] ?? '';

    if ($confirmText === 'RESET DATABASE') {
        try {
            // Disable foreign key checks
            $db->execute('SET FOREIGN_KEY_CHECKS = 0');

            // Truncate all tables (keeps structure, removes data)
            $tables = [
                'challan_items',
                'challans',
                'customer_product_prices',
                'customer_dealers',
                'customer_locations',
                'contracts',
                'price_escalations',
                'upload_batches',
                'name_mappings',
                'customers',
                'dealers',
                'products',
                'categories',
                'states',
                'locations'
            ];

            foreach ($tables as $table) {
                try {
                    $db->execute("TRUNCATE TABLE `$table`");
                } catch (Exception $e) {
                    // Table might not exist, continue
                }
            }

            // Reset auto increment for users (keep admin)
            $db->execute("DELETE FROM users WHERE id != 1");
            $db->execute("ALTER TABLE users AUTO_INCREMENT = 2");

            // Re-enable foreign key checks
            $db->execute('SET FOREIGN_KEY_CHECKS = 1');

            $success = true;
            $message = 'Database reset successfully! All data has been cleared.';

        } catch (Exception $e) {
            $success = false;
            $message = 'Error resetting database: ' . $e->getMessage();
        }
    } else {
        $message = 'Confirmation text incorrect. Please type "RESET DATABASE" exactly.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Database - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            min-height: 100vh;
            padding: 50px 0;
        }
        .reset-card {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .danger-icon {
            font-size: 80px;
            color: #dc2626;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .warning-box {
            background: #fee2e2;
            border: 2px solid #dc2626;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .confirm-input {
            font-family: monospace;
            font-weight: bold;
            text-align: center;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="reset-card">
            <div class="text-center mb-4">
                <i class="fas fa-exclamation-triangle danger-icon"></i>
                <h2 class="mt-3" style="color: #dc2626;">DANGER ZONE</h2>
                <h4>Database Reset</h4>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?= $success ? 'success' : 'danger' ?>">
                <i class="fas fa-<?= $success ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <div class="warning-box">
                <h5 class="text-danger"><i class="fas fa-skull-crossbones me-2"></i>WARNING</h5>
                <p class="mb-0"><strong>This action will permanently delete:</strong></p>
                <ul>
                    <li>All customers and their data</li>
                    <li>All challans and transactions</li>
                    <li>All dealers and mappings</li>
                    <li>All products and categories</li>
                    <li>All contracts and price escalations</li>
                    <li>All upload history</li>
                    <li>All states and locations</li>
                </ul>
                <p class="text-danger mb-0"><strong>This action CANNOT be undone!</strong></p>
            </div>

            <form method="POST" id="resetForm">
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        Type <code>RESET DATABASE</code> exactly to confirm:
                    </label>
                    <input type="text" name="confirm_text" class="form-control confirm-input"
                           placeholder="RESET DATABASE" required autocomplete="off">
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="confirm_reset" class="btn btn-danger btn-lg"
                            onclick="return confirm('Are you ABSOLUTELY sure? This will delete ALL data permanently!')">
                        <i class="fas fa-trash-alt me-2"></i>RESET DATABASE NOW
                    </button>
                    <a href="<?= BASE_URL ?>/pages/dashboard.php" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Cancel & Go Back
                    </a>
                </div>
            </form>

            <hr class="my-4">

            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i>What happens after reset?</h6>
                <ul class="mb-0">
                    <li>Database structure remains intact</li>
                    <li>Admin user account is preserved</li>
                    <li>You can upload new Excel data immediately</li>
                    <li>All tables will be empty except users</li>
                </ul>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted mb-0">
                    <i class="fas fa-user-shield me-2"></i>
                    Logged in as: <strong><?= htmlspecialchars($currentUser['username']) ?></strong> (Admin)
                </p>
            </div>
        </div>
    </div>
</body>
</html>
