<?php
/**
 * User Class - Authentication & User Management
 * Customer Tracking & Billing Management System
 */

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        $sql = "SELECT u.*, r.name as role_name, r.permissions
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.username = ? AND u.status = 'active'";

        $user = $this->db->queryOne($sql, [$username]);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $this->db->execute(
                "UPDATE users SET last_login = NOW() WHERE id = ?",
                [$user['id']]
            );

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role_name'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['permissions'] = json_decode($user['permissions'], true);

            logActivity('login', 'user', $user['id']);
            return true;
        }

        return false;
    }

    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            logActivity('logout', 'user', $_SESSION['user_id']);
        }

        session_unset();
        session_destroy();
        return true;
    }

    /**
     * Get all users
     */
    public function getAll() {
        $sql = "SELECT u.*, r.name as role_name
                FROM users u
                JOIN roles r ON u.role_id = r.id
                ORDER BY u.created_at DESC";
        return $this->db->query($sql);
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        $sql = "SELECT u.*, r.name as role_name, r.permissions
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Create new user
     */
    public function create($data) {
        // Check if username exists
        $exists = $this->db->getValue(
            "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?",
            [$data['username'], $data['email']]
        );

        if ($exists > 0) {
            throw new Exception('Username or email already exists');
        }

        $sql = "INSERT INTO users (username, email, password_hash, full_name, role_id, status)
                VALUES (?, ?, ?, ?, ?, ?)";

        $id = $this->db->insert($sql, [
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['full_name'] ?? $data['username'],
            $data['role_id'],
            $data['status'] ?? 'active'
        ]);

        logActivity('create', 'user', $id, ['username' => $data['username']]);
        return $id;
    }

    /**
     * Update user
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $params[] = $data['email'];
        }
        if (isset($data['full_name'])) {
            $fields[] = "full_name = ?";
            $params[] = $data['full_name'];
        }
        if (isset($data['role_id'])) {
            $fields[] = "role_id = ?";
            $params[] = $data['role_id'];
        }
        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }
        if (!empty($data['password'])) {
            $fields[] = "password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) return false;

        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";

        $result = $this->db->execute($sql, $params);
        logActivity('update', 'user', $id);
        return $result;
    }

    /**
     * Delete user
     */
    public function delete($id) {
        // Don't allow deleting the last admin
        $adminCount = $this->db->getValue(
            "SELECT COUNT(*) FROM users WHERE role_id = 1 AND status = 'active'"
        );

        $user = $this->getById($id);
        if ($user['role_id'] == 1 && $adminCount <= 1) {
            throw new Exception('Cannot delete the last admin user');
        }

        logActivity('delete', 'user', $id, ['username' => $user['username']]);
        return $this->db->execute("DELETE FROM users WHERE id = ?", [$id]);
    }

    /**
     * Change password
     */
    public function changePassword($id, $currentPassword, $newPassword) {
        $user = $this->getById($id);

        if (!password_verify($currentPassword, $user['password_hash'])) {
            throw new Exception('Current password is incorrect');
        }

        return $this->db->execute(
            "UPDATE users SET password_hash = ? WHERE id = ?",
            [password_hash($newPassword, PASSWORD_DEFAULT), $id]
        );
    }

    /**
     * Get all roles
     */
    public function getRoles() {
        return $this->db->query("SELECT * FROM roles ORDER BY id");
    }

    /**
     * Get role by ID
     */
    public function getRoleById($id) {
        return $this->db->queryOne("SELECT * FROM roles WHERE id = ?", [$id]);
    }

    /**
     * Update user status
     */
    public function updateStatus($id, $status) {
        return $this->db->execute(
            "UPDATE users SET status = ? WHERE id = ?",
            [$status, $id]
        );
    }

    /**
     * Reset user password (admin function)
     */
    public function resetPassword($id, $newPassword) {
        return $this->db->execute(
            "UPDATE users SET password_hash = ? WHERE id = ?",
            [password_hash($newPassword, PASSWORD_DEFAULT), $id]
        );
    }

    /**
     * Create user with role name instead of role_id
     */
    public function createWithRole($data) {
        // Get role ID from role name
        $role = $this->db->queryOne("SELECT id FROM roles WHERE name = ?", [$data['role'] ?? 'staff']);
        $roleId = $role['id'] ?? 2; // Default to staff role (ID 2)

        return $this->create([
            'username' => $data['username'],
            'email' => $data['email'] ?? '',
            'full_name' => $data['full_name'] ?? $data['username'],
            'password' => $data['password'],
            'role_id' => $roleId,
            'status' => $data['status'] ?? 'active'
        ]);
    }
}
