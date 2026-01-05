<?php
/**
 * Authentication Handler
 * Customer Tracking & Billing Management System
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Process login request
 */
function processLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return null;
    }

    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        return ['error' => 'Please enter username and password'];
    }

    $user = new User();

    if ($user->authenticate($username, $password)) {
        return ['success' => true];
    }

    return ['error' => 'Invalid username or password'];
}

/**
 * Process logout
 */
function processLogout() {
    $user = new User();
    $user->logout();
    redirect(BASE_URL . '/index.php');
}

/**
 * Check session validity
 */
function checkSession() {
    if (!isLoggedIn()) {
        return false;
    }

    // Optional: Add session timeout check
    $timeout = 3600; // 1 hour
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        $user = new User();
        $user->logout();
        return false;
    }

    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Get current user info
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['role'],
        'permissions' => $_SESSION['permissions']
    ];
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
