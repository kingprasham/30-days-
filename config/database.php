<?php
/**
 * Database Configuration
 * Customer Tracking & Billing Management System
 * 
 * This file auto-detects the environment (localhost vs production)
 * and uses the appropriate database credentials.
 */

// ============================================================
// ENVIRONMENT DETECTION
// ============================================================

/**
 * Detect if we're running on production (GoDaddy) or localhost
 * Multiple detection methods for reliability
 */
function isProductionEnvironment() {
    // Check 1: HTTP Host contains production domain
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, 'mehrgrewal.com') !== false) {
        return true;
    }
    
    // Check 2: Server name contains production domain
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    if (strpos($serverName, 'mehrgrewal.com') !== false) {
        return true;
    }
    
    // Check 3: GoDaddy-specific home directory exists
    if (is_dir('/home/ia8q2bue87d8')) {
        return true;
    }
    
    // Check 4: Document root contains GoDaddy path
    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if (strpos($docRoot, '/home/ia8q2bue87d8') !== false) {
        return true;
    }
    
    return false;
}

// ============================================================
// DATABASE CREDENTIALS
// ============================================================

$isProduction = isProductionEnvironment();

if ($isProduction) {
    // ========== GODADDY PRODUCTION CREDENTIALS ==========
    if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
    if (!defined('DB_NAME')) define('DB_NAME', 'customer_tracker');
    if (!defined('DB_USER')) define('DB_USER', 'acc_admin');
    if (!defined('DB_PASS')) define('DB_PASS', 'Prasham123$');
} else {
    // ========== LOCAL XAMPP DEVELOPMENT CREDENTIALS ==========
    if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
    if (!defined('DB_NAME')) define('DB_NAME', 'customer_tracker');
    if (!defined('DB_USER')) define('DB_USER', 'root');
    if (!defined('DB_PASS')) define('DB_PASS', '');
}

// Common settings for both environments
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// ============================================================
// DATABASE CONNECTION FUNCTIONS
// ============================================================

/**
 * Get PDO Database Connection (Singleton pattern)
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            
            // Show detailed error only in development
            if (!isProductionEnvironment()) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please contact the administrator.");
            }
        }
    }

    return $pdo;
}

/**
 * Execute a query and return all results
 * @param string $sql
 * @param array $params
 * @return array
 */
function dbQuery($sql, $params = []) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Execute a query and return single row
 * @param string $sql
 * @param array $params
 * @return array|null
 */
function dbQueryOne($sql, $params = []) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result !== false ? $result : null;
}

/**
 * Execute an insert/update/delete query
 * @param string $sql
 * @param array $params
 * @return int Last insert ID or affected rows
 */
function dbExecute($sql, $params = []) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if (stripos(trim($sql), 'INSERT') === 0) {
        return $pdo->lastInsertId();
    }
    return $stmt->rowCount();
}

/**
 * Get single value from query
 * @param string $sql
 * @param array $params
 * @return mixed
 */
function dbGetValue($sql, $params = []) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}
