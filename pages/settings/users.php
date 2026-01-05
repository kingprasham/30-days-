<?php
/**
 * User Management Page
 * Admin only - manage system users
 */

$pageTitle = 'User Management';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$user = new User();
$users = $user->getAll();
$errors = [];
$success = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $fullName = sanitize($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'staff';

        if (empty($username) || empty($password)) {
            $errors[] = 'Username and password are required';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        } else {
            try {
                $userId = $user->createWithRole([
                    'username' => $username,
                    'email' => $email,
                    'full_name' => $fullName,
                    'password' => $password,
                    'role' => $role
                ]);
                setFlashMessage('success', 'User created successfully');
                redirect(BASE_URL . '/pages/settings/users.php');
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
    } elseif ($action === 'update_status') {
        $userId = (int)$_POST['user_id'];
        $status = $_POST['status'];
        try {
            $user->updateStatus($userId, $status);
            setFlashMessage('success', 'User status updated');
            redirect(BASE_URL . '/pages/settings/users.php');
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    } elseif ($action === 'reset_password') {
        $userId = (int)$_POST['user_id'];
        $newPassword = $_POST['new_password'] ?? '';

        if (strlen($newPassword) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        } else {
            try {
                $user->resetPassword($userId, $newPassword);
                setFlashMessage('success', 'Password reset successfully');
                redirect(BASE_URL . '/pages/settings/users.php');
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
    }
}

// Refresh users list
$users = $user->getAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">User Management</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-user-plus me-2"></i>Add New User
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
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                        <td><?= htmlspecialchars($u['full_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                        <td>
                            <span class="badge <?= $u['role_name'] === 'admin' ? 'bg-danger' : 'bg-info' ?>">
                                <?= ucfirst($u['role_name']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= getStatusBadgeClass($u['status']) ?>">
                                <?= ucfirst($u['status']) ?>
                            </span>
                        </td>
                        <td><?= $u['last_login'] ? formatDate($u['last_login']) : 'Never' ?></td>
                        <td class="table-actions">
                            <?php if ($u['id'] != getCurrentUser()['id']): ?>
                            <button class="btn btn-sm btn-outline-primary"
                                    onclick="showResetPassword(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username']) ?>')"
                                    title="Reset Password">
                                <i class="fas fa-key"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-<?= $u['status'] === 'active' ? 'warning' : 'success' ?>"
                                    onclick="toggleStatus(<?= $u['id'] ?>, '<?= $u['status'] ?>')"
                                    title="<?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                <i class="fas fa-<?= $u['status'] === 'active' ? 'ban' : 'check' ?>"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="staff">Staff (View & Add only)</option>
                            <option value="admin">Admin (Full Access)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="user_id" id="reset_user_id">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password for <span id="reset_username"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toggle Status Form -->
<form method="POST" id="statusForm" style="display:none;">
    <input type="hidden" name="action" value="update_status">
    <input type="hidden" name="user_id" id="status_user_id">
    <input type="hidden" name="status" id="status_value">
</form>

<script>
function showResetPassword(userId, username) {
    document.getElementById('reset_user_id').value = userId;
    document.getElementById('reset_username').textContent = username;
    new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
}

function toggleStatus(userId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const action = newStatus === 'active' ? 'activate' : 'deactivate';

    if (confirm(`Are you sure you want to ${action} this user?`)) {
        document.getElementById('status_user_id').value = userId;
        document.getElementById('status_value').value = newStatus;
        document.getElementById('statusForm').submit();
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
