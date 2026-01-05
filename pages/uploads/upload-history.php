<?php
/**
 * Upload History Page
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Upload History';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$importer = new ExcelImporter();
$history = $importer->getUploadHistory(100);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Upload History</h5>
    <a href="<?= BASE_URL ?>/pages/uploads/excel-upload.php" class="btn btn-primary">
        <i class="fas fa-upload me-2"></i>Upload New
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover data-table mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>File</th>
                        <th>Month/Year</th>
                        <th>Total Records</th>
                        <th>Success</th>
                        <th>Errors</th>
                        <th>Uploaded By</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                    <tr>
                        <td><?= formatDate($h['created_at']) ?></td>
                        <td><?= htmlspecialchars($h['original_filename'] ?? $h['filename']) ?></td>
                        <td><?= htmlspecialchars($h['month_year'] ?? '-') ?></td>
                        <td><?= formatNumber($h['records_count']) ?></td>
                        <td><span class="badge bg-success"><?= formatNumber($h['success_count']) ?></span></td>
                        <td>
                            <?php if ($h['error_count'] > 0): ?>
                            <span class="badge bg-danger"><?= formatNumber($h['error_count']) ?></span>
                            <?php else: ?>
                            <span class="badge bg-secondary">0</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($h['uploaded_by'] ?? '-') ?></td>
                        <td>
                            <span class="badge <?= $h['status'] === 'completed' ? 'bg-success' : ($h['status'] === 'failed' ? 'bg-danger' : 'bg-warning') ?>">
                                <?= ucfirst($h['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
