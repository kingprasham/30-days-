<?php
/**
 * Application Configuration
 * Customer Tracking & Billing Management System
 * 
 * This file auto-detects the environment and configures
 * the application for both localhost and production.
 */

// ============================================================
// SESSION MANAGEMENT
// ============================================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Suppress any session errors
    @session_start();
}

// ============================================================
// ENVIRONMENT DETECTION (must be done early)
// ============================================================

/**
 * Detect if we're running on production (GoDaddy) or localhost
 */
function detectEnvironment() {
    static $isProduction = null;
    
    if ($isProduction === null) {
        $isProduction = false;
        
        // Check 1: HTTP Host contains production domain
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (strpos($host, 'mehrgrewal.com') !== false) {
            $isProduction = true;
        }
        
        // Check 2: Server name contains production domain
        elseif (strpos($_SERVER['SERVER_NAME'] ?? '', 'mehrgrewal.com') !== false) {
            $isProduction = true;
        }
        
        // Check 3: GoDaddy-specific home directory exists
        elseif (is_dir('/home/ia8q2bue87d8')) {
            $isProduction = true;
        }
        
        // Check 4: Document root contains GoDaddy path
        elseif (strpos($_SERVER['DOCUMENT_ROOT'] ?? '', '/home/ia8q2bue87d8') !== false) {
            $isProduction = true;
        }
    }
    
    return $isProduction;
}

$isProduction = detectEnvironment();

// ============================================================
// ERROR REPORTING - HARDCODED FIX FOR GODADDY
// ============================================================

// On GoDaddy, error_reporting() return value (262145) leaks to output
// So we NEVER call error_reporting() on production
if ($isProduction) {
    // Production: Only set display_errors to off, never call error_reporting()
    @ini_set('display_errors', '0');
    @ini_set('display_startup_errors', '0');
    // DO NOT call error_reporting() - its return value leaks on GoDaddy
} else {
    // Development: Show errors for debugging
    @ini_set('display_errors', '1');
    @ini_set('display_startup_errors', '1');
    @error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
}

// ============================================================
// TIMEZONE
// ============================================================

date_default_timezone_set('Asia/Kolkata');

// ============================================================
// APPLICATION PATHS
// ============================================================

if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', BASE_PATH . '/config');
if (!defined('CLASSES_PATH')) define('CLASSES_PATH', BASE_PATH . '/classes');
if (!defined('INCLUDES_PATH')) define('INCLUDES_PATH', BASE_PATH . '/includes');
if (!defined('PAGES_PATH')) define('PAGES_PATH', BASE_PATH . '/pages');
if (!defined('UPLOADS_PATH')) define('UPLOADS_PATH', BASE_PATH . '/uploads');
if (!defined('ASSETS_PATH')) define('ASSETS_PATH', BASE_PATH . '/assets');

// ============================================================
// URL PATHS (Auto-detected)
// ============================================================

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

if ($isProduction) {
    // GoDaddy Production URL
    if (!defined('BASE_URL')) define('BASE_URL', $protocol . '://' . $host . '/defaulter');
} else {
    // Local XAMPP Development URL
    if (!defined('BASE_URL')) define('BASE_URL', $protocol . '://' . $host . '/papa/30 days');
}

if (!defined('ASSETS_URL')) define('ASSETS_URL', BASE_URL . '/assets');

// ============================================================
// APPLICATION SETTINGS
// ============================================================

if (!defined('APP_NAME')) define('APP_NAME', 'Customer Tracker');
if (!defined('APP_VERSION')) define('APP_VERSION', '1.0.0');
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', '₹');
if (!defined('DATE_FORMAT')) define('DATE_FORMAT', 'd/m/Y');
if (!defined('DATE_FORMAT_DB')) define('DATE_FORMAT_DB', 'Y-m-d');
if (!defined('DATETIME_FORMAT')) define('DATETIME_FORMAT', 'd/m/Y H:i');

// Pagination
if (!defined('RECORDS_PER_PAGE')) define('RECORDS_PER_PAGE', 25);

// Upload settings
if (!defined('MAX_UPLOAD_SIZE')) define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
if (!defined('ALLOWED_EXTENSIONS')) define('ALLOWED_EXTENSIONS', ['xlsx', 'xls', 'csv']);

// Defaulter settings
if (!defined('DEFAULTER_DAYS')) define('DEFAULTER_DAYS', 30);

// ============================================================
// INCLUDE DATABASE CONFIGURATION
// ============================================================

require_once CONFIG_PATH . '/database.php';

// ============================================================
// AUTOLOAD CLASSES
// ============================================================

spl_autoload_register(function ($class) {
    $file = CLASSES_PATH . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load Composer autoloader if exists
$composerAutoload = BASE_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Helper function to format date for display
 * @param string $date
 * @return string
 */
function formatDate($date) {
    if (empty($date)) return '-';
    $timestamp = strtotime($date);
    return $timestamp ? date(DATE_FORMAT, $timestamp) : '-';
}

/**
 * Helper function to format date for database
 * @param string $date
 * @return string|null
 */
function formatDateDB($date) {
    if (empty($date)) return null;

    // Try different date formats
    $formats = ['d/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y'];
    foreach ($formats as $format) {
        $d = DateTime::createFromFormat($format, $date);
        if ($d !== false) {
            return $d->format('Y-m-d');
        }
    }

    // Try strtotime as fallback
    $timestamp = strtotime($date);
    return $timestamp ? date('Y-m-d', $timestamp) : null;
}

/**
 * Helper function to format currency (improved for Indian numbering)
 * @param float $amount
 * @param bool $short - Use short format (12.5L instead of 12,50,000.00)
 * @return string
 */
function formatCurrency($amount, $short = false) {
    $amount = floatval($amount);

    if ($short) {
        // Short format with Lakh/Crore notation
        if ($amount >= 10000000) { // 1 Crore = 100 Lakhs
            return CURRENCY_SYMBOL . ' ' . number_format($amount / 10000000, 2) . ' Cr';
        } elseif ($amount >= 100000) { // 1 Lakh
            return CURRENCY_SYMBOL . ' ' . number_format($amount / 100000, 2) . ' L';
        } elseif ($amount >= 1000) { // 1 Thousand
            return CURRENCY_SYMBOL . ' ' . number_format($amount / 1000, 2) . ' K';
        }
        return CURRENCY_SYMBOL . ' ' . number_format($amount, 2);
    }

    // Full format with Indian numbering system
    return CURRENCY_SYMBOL . ' ' . number_format($amount, 2, '.', ',');
}

/**
 * Helper function to format number
 * @param float $number
 * @param int $decimals
 * @return string
 */
function formatNumber($number, $decimals = 0) {
    return number_format(floatval($number), $decimals);
}

/**
 * Sanitize input
 * @param string $data
 * @return string
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect helper
 * @param string $url
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Set flash message
 * @param string $type (success, error, warning, info)
 * @param string $message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has permission
 * @param string $permission
 * @return bool
 */
function hasPermission($permission) {
    if (!isLoggedIn()) return false;

    $permissions = $_SESSION['permissions'] ?? [];
    return isset($permissions[$permission]) && $permissions[$permission] === true;
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to continue.');
        redirect(BASE_URL . '/index.php');
    }
}

/**
 * Require admin
 */
function requireAdmin() {
    requireLogin();
    if (!hasPermission('settings')) {
        setFlashMessage('error', 'You do not have permission to access this page.');
        redirect(BASE_URL . '/pages/dashboard.php');
    }
}

/**
 * Check if user can edit
 * @return bool
 */
function canEdit() {
    return hasPermission('edit');
}

/**
 * Check if user can delete
 * @return bool
 */
function canDelete() {
    return hasPermission('delete');
}

/**
 * Check if user can add
 * @return bool
 */
function canAdd() {
    return hasPermission('add');
}

/**
 * Log activity
 * @param string $action
 * @param string $entityType
 * @param int $entityId
 * @param array $details
 */
function logActivity($action, $entityType = null, $entityId = null, $details = []) {
    try {
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        dbExecute(
            "INSERT INTO activity_log (user_id, action, entity_type, entity_id, details, ip_address)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$userId, $action, $entityType, $entityId, json_encode($details), $ip]
        );
    } catch (Exception $e) {
        // Silently fail - don't break the application for logging
        error_log("Activity log error: " . $e->getMessage());
    }
}
