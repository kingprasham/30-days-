<?php
/**
 * Header Include
 * Customer Tracking & Billing Management System
 */

// Start output buffering IMMEDIATELY to catch any stray output
ob_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';

// Require login for all pages
requireLogin();

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = $pageTitle ?? APP_NAME;

// CRITICAL: Discard any stray output from config files (fixes 262145 bug)
// and start fresh buffer for HTML
$strayOutput = ob_get_contents();
ob_end_clean();

// Start new clean buffer for actual HTML output
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= APP_NAME ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    <!-- Flatpickr for date picker -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= ASSETS_URL ?>/css/style.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 60px;
            --primary-color: #0d6efd;
            --primary-dark: #0b5ed7;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --sidebar-active: #0d6efd;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            color: white;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            overflow-x: hidden;
            /* Hide scrollbar */
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE/Edge */
        }

        .sidebar::-webkit-scrollbar {
            display: none; /* Chrome/Safari */
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar.collapsed .sidebar-text,
        .sidebar.collapsed small,
        .sidebar.collapsed .menu-header {
            opacity: 0;
            visibility: hidden;
            width: 0;
            overflow: hidden;
        }

        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 18px;
        }

        .sidebar-header small {
            opacity: 0.7;
            font-size: 12px;
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .sidebar-menu .menu-header {
            padding: 10px 20px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.5;
            margin-top: 10px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover {
            background: var(--sidebar-hover);
            color: white;
        }

        .sidebar-menu a.active {
            background: var(--sidebar-active);
            color: white;
            border-left-color: white;
        }

        .sidebar-menu a i {
            width: 20px;
            margin-right: 12px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content.sidebar-collapsed {
            margin-left: 70px;
        }

        /* Top Header */
        .top-header {
            background: white;
            height: var(--header-height);
            padding: 0 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-title {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            text-align: right;
        }

        .user-info .name {
            font-weight: 600;
            font-size: 14px;
            color: #1e293b;
        }

        .user-info .role {
            font-size: 12px;
            color: #64748b;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        /* Content Area */
        .content-wrapper {
            padding: 25px;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
            font-weight: 600;
            border-radius: 12px 12px 0 0 !important;
        }

        .card-body {
            padding: 20px;
        }

        /* Stat Cards */
        .stat-card {
            padding: 20px;
            border-radius: 12px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .stat-card .stat-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 48px;
            opacity: 0.2;
        }

        .stat-card .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-card .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .stat-card.bg-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card.bg-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .stat-card.bg-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-card.bg-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-card.bg-danger { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }
        .stat-card.bg-secondary { background: linear-gradient(135deg, #434343 0%, #000000 100%); }

        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        /* Tables */
        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            background: #f8fafc;
            border-bottom: 2px solid #e9ecef;
        }

        /* Forms */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e9ecef;
            padding: 10px 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .form-label {
            font-weight: 500;
            color: #1e293b;
            margin-bottom: 5px;
        }

        /* Sidebar Toggle */
        .sidebar-toggle {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 18px;
            cursor: pointer;
            color: var(--text-dark);
            transition: all 0.2s;
        }

        .sidebar-toggle:hover {
            background: #f8f9fa;
            transform: scale(1.05);
        }

        /* Responsive */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }

            .sidebar-overlay.active {
                display: block;
            }
        }

        /* Alert Styles */
        .alert {
            border-radius: 10px;
            border: none;
        }

        /* Badge */
        .badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: 500;
        }

        /* DataTables Custom */
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 8px;
            border: 1px solid #e9ecef;
            padding: 6px 12px;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
            border: 1px solid #e9ecef;
            padding: 6px 12px;
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="d-flex align-items-center">
                <button class="sidebar-toggle me-3" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
            </div>

            <div class="user-menu">
                <div class="user-info d-none d-md-block">
                    <div class="name"><?= htmlspecialchars($currentUser['full_name']) ?></div>
                    <div class="role"><?= ucfirst($currentUser['role']) ?></div>
                </div>
                <div class="dropdown">
                    <div class="user-avatar" role="button" data-bs-toggle="dropdown">
                        <?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/pages/profile.php">
                            <i class="fas fa-user me-2"></i>Profile
                        </a></li>
                        <?php if (hasPermission('settings')): ?>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/pages/settings/users.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <?php
            // Display flash messages
            $flash = getFlashMessage();
            if ($flash):
            ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
