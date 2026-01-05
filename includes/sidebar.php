<?php
/**
 * Sidebar Navigation
 * Customer Tracking & Billing Management System
 */

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

function isActive($page, $dir = null) {
    global $currentPage, $currentDir;
    if ($dir) {
        return $currentDir === $dir ? 'active' : '';
    }
    return $currentPage === $page ? 'active' : '';
}
?>
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-chart-line me-2"></i><?= APP_NAME ?></h4>
        <small>Billing Management System</small>
    </div>

    <div class="sidebar-menu">
        <!-- Main Menu -->
        <div class="menu-header sidebar-text">Main</div>

        <a href="<?= BASE_URL ?>/pages/dashboard.php" class="<?= isActive('dashboard') ?>">
            <i class="fas fa-home"></i>
            <span class="sidebar-text">Dashboard</span>
        </a>

        <!-- Customer Management -->
        <div class="menu-header sidebar-text">Customer Management</div>

        <a href="<?= BASE_URL ?>/pages/customers/list.php" class="<?= isActive('list', 'customers') ?>">
            <i class="fas fa-users"></i>
            <span class="sidebar-text">Customers</span>
        </a>

        <a href="<?= BASE_URL ?>/pages/challans/list.php" class="<?= isActive('list', 'challans') ?>">
            <i class="fas fa-file-invoice"></i>
            <span class="sidebar-text">Challans</span>
        </a>

        <a href="<?= BASE_URL ?>/pages/contracts/list.php" class="<?= isActive('list', 'contracts') ?>">
            <i class="fas fa-file-contract"></i>
            <span class="sidebar-text">Contracts</span>
        </a>

        <!-- Master Data -->
        <div class="menu-header sidebar-text">Master Data</div>

        <a href="<?= BASE_URL ?>/pages/dealers/list.php" class="<?= isActive('list', 'dealers') ?>">
            <i class="fas fa-handshake"></i>
            <span class="sidebar-text">Dealers</span>
        </a>

        <a href="<?= BASE_URL ?>/pages/products/list.php" class="<?= isActive('list', 'products') ?>">
            <i class="fas fa-box"></i>
            <span class="sidebar-text">Products</span>
        </a>

        <a href="<?= BASE_URL ?>/pages/products/categories.php" class="<?= isActive('categories', 'products') ?>">
            <i class="fas fa-tags"></i>
            <span class="sidebar-text">Categories</span>
        </a>

        <!-- Data Import -->
        <div class="menu-header sidebar-text">Data Import</div>

        <a href="<?= BASE_URL ?>/pages/uploads/excel-upload.php" class="<?= isActive('excel-upload', 'uploads') ?>">
            <i class="fas fa-file-excel"></i>
            <span class="sidebar-text">Upload Excel</span>
        </a>

        <a href="<?= BASE_URL ?>/pages/uploads/upload-history.php" class="<?= isActive('upload-history', 'uploads') ?>">
            <i class="fas fa-history"></i>
            <span class="sidebar-text">Upload History</span>
        </a>

        <!-- Reports -->
        <div class="menu-header sidebar-text">Reports</div>

        <a href="<?= BASE_URL ?>/pages/reports/defaulters.php" class="<?= isActive('defaulters', 'reports') ?>">
            <i class="fas fa-exclamation-triangle"></i>
            <span class="sidebar-text">30-Day Defaulters</span>
        </a>

        <a href="<?= BASE_URL ?>/pages/reports/revenue.php" class="<?= isActive('revenue', 'reports') ?>">
            <i class="fas fa-chart-bar"></i>
            <span class="sidebar-text">Revenue Report</span>
        </a>

        <a href="<?= BASE_URL ?>/pages/reports/state-wise.php" class="<?= isActive('state-wise', 'reports') ?>">
            <i class="fas fa-map-marked-alt"></i>
            <span class="sidebar-text">State-wise Report</span>
        </a>

        <a href="<?= BASE_URL ?>/pages/reports/customer-growth.php" class="<?= isActive('customer-growth', 'reports') ?>">
            <i class="fas fa-chart-line"></i>
            <span class="sidebar-text">Customer Growth</span>
        </a>

        <?php if (hasPermission('settings')): ?>
        <!-- Settings (Admin Only) -->
        <div class="menu-header sidebar-text">Settings</div>

        <a href="<?= BASE_URL ?>/pages/settings/users.php" class="<?= isActive('users', 'settings') ?>">
            <i class="fas fa-user-cog"></i>
            <span class="sidebar-text">User Management</span>
        </a>

        <a href="<?= BASE_URL ?>/pages/settings/price-escalation.php" class="<?= isActive('price-escalation', 'settings') ?>">
            <i class="fas fa-percentage"></i>
            <span class="sidebar-text">Price Escalation</span>
        </a>

        <a href="<?= BASE_URL ?>/pages/settings/name-mappings.php" class="<?= isActive('name-mappings', 'settings') ?>">
            <i class="fas fa-spell-check"></i>
            <span class="sidebar-text">Name Corrections</span>
        </a>

        <a href="<?= BASE_URL ?>/admin_reset_db.php" style="color: #dc2626; border-left: 3px solid #dc2626;">
            <i class="fas fa-database"></i>
            <span class="sidebar-text">Reset Database</span>
        </a>
        <?php endif; ?>
    </div>
</nav>
