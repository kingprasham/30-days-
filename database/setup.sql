-- Customer Tracking & Billing Management System
-- Database Setup Script
-- Database: customer_tracker

CREATE DATABASE IF NOT EXISTS customer_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE customer_tracker;

-- =====================================================
-- 1. ROLES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert default roles
INSERT INTO roles (name, permissions) VALUES
('admin', '{"add": true, "edit": true, "delete": true, "view": true, "upload": true, "settings": true}'),
('staff', '{"add": true, "edit": false, "delete": false, "view": true, "upload": true, "settings": false}');

-- =====================================================
-- 2. USERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role_id INT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- Insert default admin user (password: 123456)
INSERT INTO users (username, email, password_hash, full_name, role_id) VALUES
('admin', 'admin@company.com', '$2y$10$FLCqLTqewnSEw7p70P3KBeEkAmkhuNjr9WhGcOuwihDbOQA29a1rK', 'Administrator', 1);

-- =====================================================
-- 3. STATES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS states (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    code VARCHAR(10),
    region ENUM('North', 'South', 'East', 'West', 'Central') DEFAULT 'North',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert Indian states
INSERT INTO states (name, code, region) VALUES
('Andhra Pradesh', 'AP', 'South'),
('Arunachal Pradesh', 'AR', 'East'),
('Assam', 'AS', 'East'),
('Bihar', 'BR', 'East'),
('Chhattisgarh', 'CT', 'Central'),
('Goa', 'GA', 'West'),
('Gujarat', 'GJ', 'West'),
('Haryana', 'HR', 'North'),
('Himachal Pradesh', 'HP', 'North'),
('Jharkhand', 'JH', 'East'),
('Karnataka', 'KA', 'South'),
('Kerala', 'KL', 'South'),
('Madhya Pradesh', 'MP', 'Central'),
('Maharashtra', 'MH', 'West'),
('Manipur', 'MN', 'East'),
('Meghalaya', 'ML', 'East'),
('Mizoram', 'MZ', 'East'),
('Nagaland', 'NL', 'East'),
('Odisha', 'OR', 'East'),
('Punjab', 'PB', 'North'),
('Rajasthan', 'RJ', 'North'),
('Sikkim', 'SK', 'East'),
('Tamil Nadu', 'TN', 'South'),
('Telangana', 'TG', 'South'),
('Tripura', 'TR', 'East'),
('Uttar Pradesh', 'UP', 'North'),
('Uttarakhand', 'UK', 'North'),
('West Bengal', 'WB', 'East'),
('Delhi', 'DL', 'North'),
('Jammu and Kashmir', 'JK', 'North'),
('Ladakh', 'LA', 'North'),
('Puducherry', 'PY', 'South'),
('Chandigarh', 'CH', 'North'),
('Dadra and Nagar Haveli', 'DN', 'West'),
('Daman and Diu', 'DD', 'West'),
('Lakshadweep', 'LD', 'South'),
('Andaman and Nicobar', 'AN', 'South');

-- =====================================================
-- 4. CATEGORIES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert default categories
INSERT INTO categories (name, description) VALUES
('PVC Films', 'White PVC Films in various sizes'),
('GSM Papers', 'GSM Paper products'),
('Photo Papers', 'Photo quality papers in different CC and GSM'),
('Blue Films', 'Blue Films in various sizes');

-- =====================================================
-- 5. PRODUCTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    short_name VARCHAR(50),
    unit VARCHAR(20) DEFAULT 'Pcs',
    base_price DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    UNIQUE KEY unique_product (category_id, name)
) ENGINE=InnoDB;

-- Insert default products based on Excel columns
INSERT INTO products (category_id, name, short_name, unit) VALUES
(1, 'A3 White PVC Film', 'A3 PVC', 'Pcs'),
(1, 'A4 White PVC Film', 'A4 PVC', 'Pcs'),
(1, 'A5 White PVC Film', 'A5 PVC', 'Pcs'),
(2, '260 GSM Paper A4', '260 GSM A4', 'Pcs'),
(2, '260 GSM Paper A5', '260 GSM A5', 'Pcs'),
(3, '230CC Photo Paper A3', '230CC A3', 'Pcs'),
(3, '230CC Photo Paper A4', '230CC A4', 'Pcs'),
(3, '230CC Photo Paper A5', '230CC A5', 'Pcs'),
(3, '210CC Photo Paper A3', '210CC A3', 'Pcs'),
(3, '210CC Photo Paper A4', '210CC A4', 'Pcs'),
(3, '180 GSM Photo Paper A3', '180 GSM A3', 'Pcs'),
(3, '180 GSM Photo Paper A4', '180 GSM A4', 'Pcs'),
(4, 'Blue Film 8x10', 'BF 8x10', 'Pcs'),
(4, 'Blue Film A3', 'BF A3', 'Pcs'),
(4, 'Blue Film A4', 'BF A4', 'Pcs'),
(4, 'Blue Film 13x17', 'BF 13x17', 'Pcs'),
(4, 'Blue Film 10x12', 'BF 10x12', 'Pcs');

-- =====================================================
-- 6. DEALERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS dealers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(200) NOT NULL,
    address TEXT,
    state_id INT,
    location VARCHAR(100),
    pincode VARCHAR(10),
    gst_number VARCHAR(20),
    contact_person VARCHAR(100),
    designation VARCHAR(50),
    mobile VARCHAR(15),
    email VARCHAR(100),
    territory ENUM('North', 'South', 'East', 'West', 'Central', 'Pan India') DEFAULT 'North',
    service_location TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (state_id) REFERENCES states(id)
) ENGINE=InnoDB;

-- =====================================================
-- 7. CUSTOMERS TABLE (Main Entity)
-- =====================================================
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    normalized_name VARCHAR(200),
    state_id INT,
    location VARCHAR(150),
    installation_date DATE,
    monthly_commitment DECIMAL(12,2) DEFAULT 0.00,
    rate DECIMAL(10,2) DEFAULT 0.00,
    target_achieved ENUM('yes', 'no', 'partial') DEFAULT 'no',
    contract_start_date DATE,
    contract_end_date DATE,
    -- Printer Information
    printer_mode VARCHAR(100),
    printer_model VARCHAR(100),
    printer_sr_no VARCHAR(100),
    collect_printer ENUM('yes', 'no') DEFAULT 'no',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (state_id) REFERENCES states(id),
    INDEX idx_normalized_name (normalized_name),
    INDEX idx_state (state_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =====================================================
-- 8. CUSTOMER LOCATIONS (For merged entries)
-- =====================================================
CREATE TABLE IF NOT EXISTS customer_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    location_identifier VARCHAR(50),
    location_name VARCHAR(200),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 9. CUSTOMER-DEALER MAPPING
-- =====================================================
CREATE TABLE IF NOT EXISTS customer_dealers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    dealer_id INT NOT NULL,
    commission_amount DECIMAL(10,2) DEFAULT 0.00,
    assigned_date DATE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (dealer_id) REFERENCES dealers(id),
    UNIQUE KEY unique_customer_dealer (customer_id, dealer_id)
) ENGINE=InnoDB;

-- =====================================================
-- 10. CUSTOMER PRODUCT PRICES (Per customer pricing)
-- =====================================================
CREATE TABLE IF NOT EXISTS customer_product_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    escalation_percent DECIMAL(5,2) DEFAULT 0.00,
    escalation_date DATE,
    effective_from DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    UNIQUE KEY unique_customer_product (customer_id, product_id)
) ENGINE=InnoDB;

-- =====================================================
-- 11. CONTRACTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    contract_number VARCHAR(50),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    renewal_date DATE,
    terms TEXT,
    value DECIMAL(12,2) DEFAULT 0.00,
    status ENUM('active', 'expired', 'renewed', 'terminated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 12. PRICE ESCALATION HISTORY
-- =====================================================
CREATE TABLE IF NOT EXISTS price_escalations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT,
    old_price DECIMAL(10,2),
    new_price DECIMAL(10,2),
    escalation_percent DECIMAL(5,2),
    effective_date DATE,
    created_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- =====================================================
-- 13. UPLOAD BATCHES (Track Excel imports)
-- =====================================================
CREATE TABLE IF NOT EXISTS upload_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255),
    upload_date DATE,
    month_year VARCHAR(20),
    records_count INT DEFAULT 0,
    success_count INT DEFAULT 0,
    error_count INT DEFAULT 0,
    user_id INT,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    error_log TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- =====================================================
-- 14. CHALLANS TABLE (Main billing data)
-- =====================================================
CREATE TABLE IF NOT EXISTS challans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    customer_location_id INT,
    challan_no VARCHAR(50),
    challan_date DATE NOT NULL,
    billed ENUM('yes', 'no') DEFAULT 'no',
    rate DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(12,2) DEFAULT 0.00,
    delivery_through VARCHAR(100),
    remark TEXT,
    material_sending_location VARCHAR(200),
    upload_batch_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_location_id) REFERENCES customer_locations(id),
    FOREIGN KEY (upload_batch_id) REFERENCES upload_batches(id),
    INDEX idx_challan_date (challan_date),
    INDEX idx_customer (customer_id),
    INDEX idx_billed (billed)
) ENGINE=InnoDB;

-- =====================================================
-- 15. CHALLAN ITEMS (Product quantities per challan)
-- =====================================================
CREATE TABLE IF NOT EXISTS challan_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    challan_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 0,
    rate DECIMAL(10,2) DEFAULT 0.00,
    amount DECIMAL(12,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (challan_id) REFERENCES challans(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- =====================================================
-- 16. NAME MAPPINGS (For typo correction)
-- =====================================================
CREATE TABLE IF NOT EXISTS name_mappings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_name VARCHAR(200) NOT NULL,
    corrected_name VARCHAR(200) NOT NULL,
    entity_type ENUM('customer', 'dealer', 'location') DEFAULT 'customer',
    auto_detected TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_original (original_name),
    INDEX idx_entity (entity_type)
) ENGINE=InnoDB;

-- =====================================================
-- 17. ACTIVITY LOG
-- =====================================================
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    details JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- 18. SETTINGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type VARCHAR(20) DEFAULT 'text',
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('company_name', 'Customer Tracker', 'text', 'Company Name'),
('currency_symbol', '₹', 'text', 'Currency Symbol'),
('date_format', 'd/m/Y', 'text', 'Date Format'),
('default_escalation_percent', '5', 'number', 'Default Price Escalation Percentage'),
('defaulter_days', '30', 'number', 'Days to mark as defaulter');

-- =====================================================
-- VIEWS FOR REPORTING
-- =====================================================

-- View: Customer Summary with total revenue
CREATE OR REPLACE VIEW v_customer_summary AS
SELECT
    c.id,
    c.name,
    c.normalized_name,
    s.name as state_name,
    c.location,
    c.installation_date,
    c.monthly_commitment,
    c.status,
    COUNT(DISTINCT ch.id) as total_challans,
    COALESCE(SUM(ch.total_amount), 0) as total_revenue,
    MAX(ch.challan_date) as last_challan_date,
    DATEDIFF(CURDATE(), MAX(ch.challan_date)) as days_since_last_challan
FROM customers c
LEFT JOIN states s ON c.state_id = s.id
LEFT JOIN challans ch ON c.id = ch.customer_id
GROUP BY c.id;

-- View: 30-Day Defaulters
CREATE OR REPLACE VIEW v_defaulters AS
SELECT
    c.*,
    s.name as state_name,
    MAX(ch.challan_date) as last_challan_date,
    DATEDIFF(CURDATE(), MAX(ch.challan_date)) as days_inactive
FROM customers c
LEFT JOIN states s ON c.state_id = s.id
LEFT JOIN challans ch ON c.id = ch.customer_id
WHERE c.status = 'active'
GROUP BY c.id
HAVING days_inactive > 30 OR days_inactive IS NULL;

-- View: Monthly Revenue
CREATE OR REPLACE VIEW v_monthly_revenue AS
SELECT
    DATE_FORMAT(challan_date, '%Y-%m') as month_year,
    DATE_FORMAT(challan_date, '%M %Y') as month_name,
    COUNT(DISTINCT customer_id) as active_customers,
    COUNT(*) as total_challans,
    SUM(total_amount) as total_revenue
FROM challans
GROUP BY DATE_FORMAT(challan_date, '%Y-%m')
ORDER BY month_year DESC;

-- View: State-wise Revenue
CREATE OR REPLACE VIEW v_state_revenue AS
SELECT
    s.id as state_id,
    s.name as state_name,
    s.region,
    COUNT(DISTINCT c.id) as customer_count,
    COUNT(DISTINCT ch.id) as challan_count,
    COALESCE(SUM(ch.total_amount), 0) as total_revenue
FROM states s
LEFT JOIN customers c ON s.id = c.state_id
LEFT JOIN challans ch ON c.id = ch.customer_id
GROUP BY s.id
ORDER BY total_revenue DESC;

-- View: Product-wise Sales
CREATE OR REPLACE VIEW v_product_sales AS
SELECT
    p.id as product_id,
    p.name as product_name,
    cat.name as category_name,
    SUM(ci.quantity) as total_quantity,
    SUM(ci.amount) as total_amount
FROM products p
LEFT JOIN categories cat ON p.category_id = cat.id
LEFT JOIN challan_items ci ON p.id = ci.product_id
GROUP BY p.id
ORDER BY total_quantity DESC;

-- =====================================================
-- PAYMENT TRACKING VIEWS
-- =====================================================

-- View: Unbilled Challans (Pending Payments)
CREATE OR REPLACE VIEW v_unbilled_challans AS
SELECT
    ch.id,
    ch.challan_no,
    ch.challan_date,
    ch.total_amount,
    c.id as customer_id,
    c.name as customer_name,
    c.location,
    s.name as state_name,
    DATEDIFF(CURDATE(), ch.challan_date) as days_pending
FROM challans ch
JOIN customers c ON ch.customer_id = c.id
LEFT JOIN states s ON c.state_id = s.id
WHERE ch.billed = 'no'
ORDER BY ch.challan_date ASC;

-- View: Payment Aging Analysis
CREATE OR REPLACE VIEW v_payment_aging AS
SELECT
    c.id as customer_id,
    c.name as customer_name,
    c.location,
    s.name as state_name,
    SUM(CASE WHEN ch.billed = 'no' AND DATEDIFF(CURDATE(), ch.challan_date) <= 30 THEN ch.total_amount ELSE 0 END) as aging_0_30,
    SUM(CASE WHEN ch.billed = 'no' AND DATEDIFF(CURDATE(), ch.challan_date) BETWEEN 31 AND 60 THEN ch.total_amount ELSE 0 END) as aging_31_60,
    SUM(CASE WHEN ch.billed = 'no' AND DATEDIFF(CURDATE(), ch.challan_date) BETWEEN 61 AND 90 THEN ch.total_amount ELSE 0 END) as aging_61_90,
    SUM(CASE WHEN ch.billed = 'no' AND DATEDIFF(CURDATE(), ch.challan_date) > 90 THEN ch.total_amount ELSE 0 END) as aging_90_plus,
    SUM(CASE WHEN ch.billed = 'no' THEN ch.total_amount ELSE 0 END) as total_unbilled,
    COUNT(CASE WHEN ch.billed = 'no' THEN 1 END) as unbilled_count
FROM customers c
LEFT JOIN states s ON c.state_id = s.id
LEFT JOIN challans ch ON c.id = ch.customer_id
WHERE c.status = 'active'
GROUP BY c.id
HAVING total_unbilled > 0
ORDER BY total_unbilled DESC;

-- View: Customer Activity Summary
CREATE OR REPLACE VIEW v_customer_activity AS
SELECT
    c.id,
    c.name,
    c.location,
    s.name as state_name,
    c.monthly_commitment,
    c.installation_date,
    c.printer_model,
    c.printer_sr_no,
    COUNT(ch.id) as total_challans,
    SUM(CASE WHEN ch.billed = 'yes' THEN 1 ELSE 0 END) as billed_challans,
    SUM(CASE WHEN ch.billed = 'no' THEN 1 ELSE 0 END) as unbilled_challans,
    COALESCE(SUM(ch.total_amount), 0) as total_revenue,
    COALESCE(SUM(CASE WHEN ch.billed = 'yes' THEN ch.total_amount ELSE 0 END), 0) as billed_amount,
    COALESCE(SUM(CASE WHEN ch.billed = 'no' THEN ch.total_amount ELSE 0 END), 0) as unbilled_amount,
    MIN(ch.challan_date) as first_challan_date,
    MAX(ch.challan_date) as last_challan_date,
    DATEDIFF(CURDATE(), MAX(ch.challan_date)) as days_since_last_activity
FROM customers c
LEFT JOIN states s ON c.state_id = s.id
LEFT JOIN challans ch ON c.id = ch.customer_id
WHERE c.status = 'active'
GROUP BY c.id;

-- View: Monthly Billing Summary
CREATE OR REPLACE VIEW v_monthly_billing AS
SELECT
    DATE_FORMAT(ch.challan_date, '%Y-%m') as month_year,
    DATE_FORMAT(ch.challan_date, '%M %Y') as month_name,
    COUNT(*) as total_challans,
    SUM(CASE WHEN ch.billed = 'yes' THEN 1 ELSE 0 END) as billed_count,
    SUM(CASE WHEN ch.billed = 'no' THEN 1 ELSE 0 END) as unbilled_count,
    COALESCE(SUM(ch.total_amount), 0) as total_amount,
    COALESCE(SUM(CASE WHEN ch.billed = 'yes' THEN ch.total_amount ELSE 0 END), 0) as billed_amount,
    COALESCE(SUM(CASE WHEN ch.billed = 'no' THEN ch.total_amount ELSE 0 END), 0) as unbilled_amount,
    ROUND(SUM(CASE WHEN ch.billed = 'yes' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as billing_rate
FROM challans ch
GROUP BY DATE_FORMAT(ch.challan_date, '%Y-%m')
ORDER BY month_year DESC;

-- View: Delivery Person Summary
CREATE OR REPLACE VIEW v_delivery_summary AS
SELECT
    COALESCE(ch.delivery_through, 'Not Specified') as delivery_person,
    COUNT(*) as total_deliveries,
    COUNT(DISTINCT ch.customer_id) as unique_customers,
    SUM(CASE WHEN ch.billed = 'yes' THEN 1 ELSE 0 END) as billed_deliveries,
    SUM(CASE WHEN ch.billed = 'no' THEN 1 ELSE 0 END) as unbilled_deliveries
FROM challans ch
WHERE ch.challan_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
GROUP BY ch.delivery_through
ORDER BY total_deliveries DESC;
