<?php
/**
 * Excel Upload Page
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Upload Excel';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$errors = [];
$preview = null;
$importResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'import') {
        // Process import
        if (!empty($_POST['file_path']) && file_exists($_POST['file_path'])) {
            $importer = new ExcelImporter();
            $importResult = $importer->import(
                $_POST['file_path'],
                $_POST['month_year'] ?? null,
                $_SESSION['user_id']
            );

            if ($importResult['success']) {
                setFlashMessage('success', "Import completed! {$importResult['success_count']} records imported successfully.");
                // Clean up temp file
                @unlink($_POST['file_path']);
            } else {
                $errors[] = $importResult['error'] ?? 'Import failed';
            }
        }
    } else {
        // Handle file upload and preview
        if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['excel_file'];

            // Validate file
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
                $errors[] = 'Invalid file type. Please upload .xlsx, .xls, or .csv file';
            } elseif ($file['size'] > MAX_UPLOAD_SIZE) {
                $errors[] = 'File size exceeds limit (' . formatFileSize(MAX_UPLOAD_SIZE) . ')';
            } else {
                // Move to temp location
                $uploadDir = UPLOADS_PATH . '/excel/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $filename = generateUniqueFilename($ext);
                $filepath = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Preview the file
                    $importer = new ExcelImporter();
                    $preview = $importer->preview($filepath);

                    if (!$preview['success']) {
                        $errors[] = $preview['error'];
                        @unlink($filepath);
                    } else {
                        $preview['file_path'] = $filepath;
                        $preview['original_name'] = $file['name'];
                    }
                } else {
                    $errors[] = 'Failed to upload file';
                }
            }
        } elseif (isset($_FILES['excel_file'])) {
            $errors[] = 'Error uploading file: ' . $_FILES['excel_file']['error'];
        }
    }
}
?>

<?php if (!$preview): ?>
<!-- Upload Form -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-excel me-2"></i>Upload Excel File
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="upload-area mb-4" id="uploadArea">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <h5>Drop Excel file here or click to browse</h5>
                        <p class="text-muted mb-0">Supported formats: .xlsx, .xls, .csv (Max: <?= formatFileSize(MAX_UPLOAD_SIZE) ?>)</p>
                        <input type="file" name="excel_file" id="excelFile" accept=".xlsx,.xls,.csv" class="d-none">
                    </div>

                    <div id="fileInfo" class="d-none mb-4">
                        <div class="alert alert-info d-flex align-items-center">
                            <i class="fas fa-file-excel fa-2x me-3"></i>
                            <div>
                                <strong id="fileName"></strong><br>
                                <small id="fileSize" class="text-muted"></small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="clearFile()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Month/Year (Optional)</label>
                            <input type="text" name="month_year" class="form-control"
                                   placeholder="e.g., December 2024" value="<?= date('F Y') ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="previewBtn" disabled>
                        <i class="fas fa-eye me-2"></i>Preview Data
                    </button>
                </form>
            </div>
        </div>

        <!-- Instructions -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Instructions
            </div>
            <div class="card-body">
                <p>Your Excel file should have the following columns:</p>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li>Installation Date</li>
                            <li>Monthly Commitment</li>
                            <li>Rate</li>
                            <li>State</li>
                            <li>Location</li>
                            <li>Customer Name <span class="text-danger">*</span></li>
                            <li>Billed (Yes/No)</li>
                            <li>Challan Date</li>
                            <li>Challan No</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li>Product columns (A3 White PVC Film, etc.)</li>
                            <li>Delivery Thru</li>
                            <li>Remark</li>
                            <li>Material Sending Location</li>
                        </ul>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-muted">
                    <small>* Customer Name is required. The system will automatically detect and normalize duplicate entries.</small>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('excelFile');
const fileInfo = document.getElementById('fileInfo');
const previewBtn = document.getElementById('previewBtn');

uploadArea.addEventListener('click', () => fileInput.click());

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        showFileInfo(e.dataTransfer.files[0]);
    }
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length) {
        showFileInfo(e.target.files[0]);
    }
});

function showFileInfo(file) {
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = formatFileSize(file.size);
    uploadArea.classList.add('d-none');
    fileInfo.classList.remove('d-none');
    previewBtn.disabled = false;
}

function clearFile() {
    fileInput.value = '';
    uploadArea.classList.remove('d-none');
    fileInfo.classList.add('d-none');
    previewBtn.disabled = true;
}

function formatFileSize(bytes) {
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return bytes + ' bytes';
}
</script>

<?php else: ?>
<!-- Preview Section -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-eye me-2"></i>Preview: <?= htmlspecialchars($preview['original_name']) ?></span>
        <span class="badge bg-info"><?= $preview['total_rows'] ?> rows found</span>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Column Mapping Info -->
        <div class="alert alert-info">
            <strong>Detected Columns:</strong>
            <?php
            $mappedCols = array_filter($preview['column_mapping']);
            echo count($mappedCols) . ' standard columns, ' . count($preview['product_columns']) . ' product columns';
            ?>
        </div>

        <!-- Preview Table -->
        <div class="table-responsive mb-4" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-sm table-bordered">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>#</th>
                        <th>Customer Name</th>
                        <th>Normalized</th>
                        <th>State</th>
                        <th>Challan Date</th>
                        <th>Products</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($preview['preview_data'] as $idx => $row): ?>
                    <tr class="<?= !empty($row['normalized']['is_duplicate']) ? 'table-warning' : '' ?>">
                        <td><?= $idx + 1 ?></td>
                        <td><?= htmlspecialchars($row['customer_name'] ?? '') ?></td>
                        <td>
                            <?= htmlspecialchars($row['normalized']['base_name'] ?? '-') ?>
                            <?php if (!empty($row['normalized']['location_id'])): ?>
                            <br><small class="text-muted">(<?= $row['normalized']['location_id'] ?>)</small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['state'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['challan_date'] ?? '-') ?></td>
                        <td>
                            <?php
                            $productCount = count($row['products']);
                            $totalQty = array_sum($row['products']);
                            echo "<span class='badge bg-secondary'>$productCount products ($totalQty qty)</span>";
                            ?>
                        </td>
                        <td>
                            <?php if (!empty($row['normalized']['is_duplicate'])): ?>
                            <span class="badge bg-warning">Duplicate</span>
                            <?php elseif (!empty($row['normalized']['possible_matches'])): ?>
                            <span class="badge bg-info" title="Similar to: <?= $row['normalized']['possible_matches'][0]['corrected_name'] ?>">
                                Similar Found
                            </span>
                            <?php else: ?>
                            <span class="badge bg-success">New</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="import">
            <input type="hidden" name="file_path" value="<?= htmlspecialchars($preview['file_path']) ?>">
            <input type="hidden" name="month_year" value="<?= htmlspecialchars($_POST['month_year'] ?? '') ?>">

            <div class="d-flex justify-content-between">
                <a href="<?= BASE_URL ?>/pages/uploads/excel-upload.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Cancel & Upload Different File
                </a>
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-check me-2"></i>Confirm & Import <?= $preview['total_rows'] ?> Records
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
