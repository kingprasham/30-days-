<?php
/**
 * Database Class - PDO Wrapper
 * Customer Tracking & Billing Management System
 * 
 * Singleton pattern for database connection management
 */

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            
            // Use the environment detection function if available
            $isProduction = function_exists('detectEnvironment') ? detectEnvironment() : true;
            
            if ($isProduction) {
                throw new Exception("Database connection failed. Please contact the administrator.");
            } else {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Get the singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the PDO connection
     */
    public function getConnection() {
        return $this->pdo;
    }

    /**
     * Execute SELECT query and return all results
     */
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute SELECT query and return single row
     */
    public function queryOne($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * Execute INSERT/UPDATE/DELETE
     */
    public function execute($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Insert and return last ID
     */
    public function insert($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    /**
     * Get single value
     */
    public function getValue($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : null;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }

    /**
     * Check if table exists
     */
    public function tableExists($table) {
        $result = $this->queryOne("SHOW TABLES LIKE ?", [$table]);
        return $result !== null;
    }
}
