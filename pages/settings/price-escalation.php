<?php
/**
 * Price Escalation Management
 * Admin only - manage product price increases per customer
 */

$pageTitle = 'Price Escalation';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$db = Database::getInstance();
$errors = [];

// Handle escalation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = (int)$_POST['customer_id'];
    $productId = (int)$_POST['product_id'];
    $escalationPercent = (float)$_POST['escalation_percent'];
    $effectiveDate = $_POST['effective_date'];

    if (empty($customerId) || empty($productId) || empty($escalationPercent)) {
        $errors[] = 'All fields are required';
    } else {
        try {
            // Get current price
            $current = $db->queryOne(
                "SELECT price FROM customer_product_prices
                 WHERE customer_id = ? AND product_id = ?",
                [$customerId, $productId]
            );

            $oldPrice = $current['price'] ?? 0;
            $newPrice = $oldPrice * (1 + ($escalationPercent / 100));

            // Update price
            if ($current) {
                $db->execute(
                    "UPDATE customer_product_prices
                     SET price = ?, escalation_percent = ?, escalation_date = ?
                     WHERE customer_id = ? AND product_id = ?",
                    [$newPrice, $escalationPercent, $effectiveDate, $customerId, $productId]
                );
            } else {
                $db->execute(
                    "INSERT INTO customer_product_prices
                     (customer_id, product_id, price, escalation_percent, escalation_date)
                     VALUES (?, ?, ?, ?, ?)",
                    [$customerId, $productId, $newPrice, $escalationPercent, $effectiveDate]
                );
            }

            // Log escalation
            $db->execute(
                "INSERT INTO price_escalations
                 (customer_id, product_id, old_price, new_price, escalation_percent, effective_date, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$customerId, $productId, $oldPrice, $newPrice, $escalationPercent, $effectiveDate, getCurrentUser()['id']]
            );

            setFlashMessage('success', 'Price escalation applied successfully');
            redirect(BASE_URL . '/pages/settings/price-escalation.php');
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

// Get escalation history
$history = $db->query(
    "SELECT pe.*, c.name as customer_name, p.name as product_name,
            u.username as created_by_name
     FROM price_escalations pe
     JOIN customers c ON pe.customer_id = c.id
     JOIN products p ON pe.product_id = p.id
     LEFT JOIN users u ON pe.created_by = u.id
     ORDER BY pe.created_at DESC
     LIMIT 100"
);

$customers = $db->query("SELECT id, name FROM customers WHERE status = 'active' ORDER BY name");
$products = $db->query("SELECT id, name FROM products WHERE status = 'active' ORDER BY name");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Price Escalation Management</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#escalationModal">
        <i class="fas fa-percentage me-2"></i>Apply Price Escalation
    </button>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-history me-2"></i>Escalation History
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover data-table mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Old Price</th>
                        <th>New Price</th>
                        <th>Escalation %</th>
                        <th>Effective Date</th>
                        <th>Applied By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                    <tr>
                        <td><?= formatDate($h['created_at']) ?></td>
                        <td><?= htmlspecialchars($h['customer_name']) ?></td>
                        <td><?= htmlspecialchars($h['product_name']) ?></td>
                        <td><?= formatCurrency($h['old_price']) ?></td>
                        <td><?= formatCurrency($h['new_price']) ?></td>
                        <td>
                            <span class="badge bg-warning">
                                +<?= number_format($h['escalation_percent'], 2) ?>%
                            </span>
                        </td>
                        <td><?= formatDate($h['effective_date']) ?></td>
                        <td><?= htmlspecialchars($h['created_by_name']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Escalation Modal -->
<div class="modal fade" id="escalationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Apply Price Escalation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select select2" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select select2" required>
                            <option value="">Select Product</option>
                            <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Escalation Percentage <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="escalation_percent" class="form-control"
                                   step="0.1" min="0" max="100" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">e.g., Enter 10 for 10% increase</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Effective Date</label>
                        <input type="text" name="effective_date" class="form-control datepicker"
                               value="<?= date('d/m/Y') ?>" required>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> This will increase the current price by the specified percentage.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Escalation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
