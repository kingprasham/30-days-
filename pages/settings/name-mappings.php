<?php
/**
 * Name Corrections/Mappings
 * Admin only - manage customer name variations and typo corrections
 */

$pageTitle = 'Name Corrections';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$db = Database::getInstance();
$errors = [];

// Handle mapping actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $originalName = sanitize($_POST['original_name'] ?? '');
        $correctedName = sanitize($_POST['corrected_name'] ?? '');
        $entityType = $_POST['entity_type'] ?? 'customer';

        if (empty($originalName) || empty($correctedName)) {
            $errors[] = 'Both original and corrected names are required';
        } else {
            try {
                $db->execute(
                    "INSERT INTO name_mappings (original_name, corrected_name, entity_type, created_at)
                     VALUES (?, ?, ?, NOW())",
                    [$originalName, $correctedName, $entityType]
                );
                setFlashMessage('success', 'Name mapping added successfully');
                redirect(BASE_URL . '/pages/settings/name-mappings.php');
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        try {
            $db->execute("DELETE FROM name_mappings WHERE id = ?", [$id]);
            setFlashMessage('success', 'Name mapping deleted');
            redirect(BASE_URL . '/pages/settings/name-mappings.php');
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

// Get all mappings
$mappings = $db->query(
    "SELECT * FROM name_mappings ORDER BY entity_type, created_at DESC"
);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0">Name Corrections</h5>
        <small class="text-muted">Map common typos and variations to correct names</small>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMappingModal">
        <i class="fas fa-plus me-2"></i>Add Name Mapping
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

<div class="alert alert-info">
    <i class="fas fa-lightbulb me-2"></i>
    <strong>How it works:</strong> During Excel import, if a name matches an "Original Name" below,
    it will be automatically corrected to the "Corrected Name". This helps maintain data consistency.
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Entity Type</th>
                        <th>Original Name (Typo)</th>
                        <th>→</th>
                        <th>Corrected Name</th>
                        <th>Added On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mappings)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No name mappings yet. Add one to get started!
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($mappings as $m): ?>
                    <tr>
                        <td>
                            <span class="badge bg-secondary">
                                <?= ucfirst($m['entity_type']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="text-danger">
                                <i class="fas fa-times-circle me-1"></i>
                                <?= htmlspecialchars($m['original_name']) ?>
                            </span>
                        </td>
                        <td><i class="fas fa-arrow-right text-muted"></i></td>
                        <td>
                            <span class="text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                <strong><?= htmlspecialchars($m['corrected_name']) ?></strong>
                            </span>
                        </td>
                        <td><?= formatDate($m['created_at']) ?></td>
                        <td>
                            <form method="POST" style="display:inline;"
                                  onsubmit="return confirm('Delete this mapping?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-question-circle me-2"></i>Examples
    </div>
    <div class="card-body">
        <h6>Common Use Cases:</h6>
        <ul>
            <li><strong>Typos:</strong> "Appollo Hospital" → "Apollo Hospital"</li>
            <li><strong>Abbreviations:</strong> "ABC Diag Centre" → "ABC Diagnostic Centre"</li>
            <li><strong>Variations:</strong> "Mumbai X-ray" → "Mumbai X-Ray & Scan Centre"</li>
            <li><strong>Extra Spaces:</strong> "City  Imaging" → "City Imaging"</li>
        </ul>
    </div>
</div>

<!-- Add Mapping Modal -->
<div class="modal fade" id="addMappingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add Name Mapping</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Entity Type</label>
                        <select name="entity_type" class="form-select">
                            <option value="customer">Customer</option>
                            <option value="dealer">Dealer</option>
                            <option value="product">Product</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Original Name (Typo/Variation) <span class="text-danger">*</span></label>
                        <input type="text" name="original_name" class="form-control"
                               placeholder="e.g., Appollo Hospital" required>
                        <small class="text-muted">The incorrect name that appears in Excel</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Corrected Name <span class="text-danger">*</span></label>
                        <input type="text" name="corrected_name" class="form-control"
                               placeholder="e.g., Apollo Hospital" required>
                        <small class="text-muted">The correct name to use in the database</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Mapping</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
