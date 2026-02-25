<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' – Anon Admin' : 'Admin Panel – Anon'; ?>
    </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/extra.css">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --admin-bg: hsl(220, 25%, 96%);
            --admin-sidebar: hsl(220, 35%, 13%);
            --admin-sidebar2: hsl(220, 30%, 19%);
            --admin-accent: hsl(353, 100%, 60%);
            --admin-white: #ffffff;
            --admin-text: hsl(220, 25%, 20%);
            --admin-muted: hsl(220, 12%, 55%);
            --admin-border: hsl(220, 20%, 88%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--admin-bg);
            color: var(--admin-text);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .admin-sidebar {
            width: 250px;
            background: var(--admin-sidebar);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            overflow-y: auto;
        }

        .sidebar-logo {
            padding: 24px 22px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid hsla(0, 0%, 100%, 0.08);
        }

        .sidebar-logo .logo-icon {
            background: var(--admin-accent);
            color: white;
            font-size: 20px;
            font-weight: 800;
            padding: 6px 12px;
            border-radius: 8px;
            letter-spacing: 1px;
        }

        .sidebar-logo .logo-text {
            color: white;
            font-weight: 700;
            font-size: 16px;
        }

        .sidebar-logo .logo-sub {
            color: hsla(0, 0%, 100%, 0.45);
            font-size: 11px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 0;
        }

        .sidebar-section {
            padding: 10px 18px 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: hsla(0, 0%, 100%, 0.3);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 22px;
            color: hsla(0, 0%, 100%, 0.65);
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
            position: relative;
        }

        .sidebar-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--admin-accent);
            opacity: 0;
            transition: opacity 0.2s;
            border-radius: 0 3px 3px 0;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            color: white;
            background: hsla(0, 0%, 100%, 0.07);
        }

        .sidebar-link.active::before {
            opacity: 1;
        }

        .sidebar-link ion-icon {
            font-size: 18px;
        }

        .sidebar-bottom {
            padding: 16px 0;
            border-top: 1px solid hsla(0, 0%, 100%, 0.08);
        }

        /* Top Bar */
        .admin-topbar {
            position: fixed;
            top: 0;
            left: 250px;
            right: 0;
            height: 64px;
            background: var(--admin-white);
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            z-index: 99;
            box-shadow: 0 1px 6px hsla(0, 0%, 0%, 0.05);
        }

        .topbar-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--admin-text);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-badge {
            background: hsl(353, 100%, 96%);
            color: var(--admin-accent);
            font-size: 13px;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 20px;
        }

        .topbar-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--admin-accent);
            color: white;
            font-weight: 700;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Page content */
        .admin-wrapper {
            margin-left: 250px;
            padding-top: 64px;
            min-height: 100vh;
            flex: 1;
        }

        .admin-main {
            padding: 28px;
        }

        .admin-page-header {
            margin-bottom: 28px;
        }

        .admin-page-header h1 {
            font-size: 24px;
            font-weight: 800;
            color: var(--admin-text);
        }

        .admin-page-header p {
            color: var(--admin-muted);
            font-size: 14px;
            margin-top: 4px;
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--admin-white);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 12px hsla(0, 0%, 0%, 0.06);
            display: flex;
            gap: 14px;
            align-items: center;
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 500;
            color: var(--admin-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 22px;
            font-weight: 800;
            color: var(--admin-text);
            margin-top: 2px;
        }

        /* Cards */
        .admin-card {
            background: var(--admin-white);
            border-radius: 12px;
            box-shadow: 0 2px 12px hsla(0, 0%, 0%, 0.06);
            overflow: hidden;
        }

        .admin-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 22px;
            border-bottom: 1px solid var(--admin-border);
        }

        .admin-card-header h2 {
            font-size: 16px;
            font-weight: 700;
            color: var(--admin-text);
        }

        .admin-card>*:not(.admin-card-header) {
            padding: 16px 22px;
        }

        .admin-link {
            color: var(--admin-accent);
            font-size: 13px;
            font-weight: 600;
        }

        .admin-two-col {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 20px;
        }

        /* Table */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th {
            background: var(--admin-bg);
            padding: 11px 16px;
            font-size: 12px;
            font-weight: 600;
            color: var(--admin-muted);
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .admin-table td {
            padding: 13px 16px;
            font-size: 14px;
            color: var(--admin-text);
            border-bottom: 1px solid var(--admin-border);
            vertical-align: middle;
        }

        .admin-table tr:last-child td {
            border-bottom: none;
        }

        .admin-table tr:hover td {
            background: hsl(220, 25%, 98%);
        }

        /* Buttons */
        .admin-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }

        .admin-btn-primary {
            background: var(--admin-accent);
            color: white;
        }

        .admin-btn-primary:hover {
            background: hsl(353, 100%, 50%);
        }

        .admin-btn-success {
            background: hsl(152, 51%, 50%);
            color: white;
        }

        .admin-btn-danger {
            background: hsl(0, 75%, 58%);
            color: white;
        }

        .admin-btn-secondary {
            background: var(--admin-bg);
            color: var(--admin-text);
            border: 1px solid var(--admin-border);
        }

        /* Form inline */
        .admin-form .form-group {
            margin-bottom: 16px;
        }

        .admin-form label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--admin-text);
            margin-bottom: 6px;
        }

        .admin-form input,
        .admin-form select,
        .admin-form textarea {
            width: 100%;
            padding: 9px 13px;
            border: 1.5px solid var(--admin-border);
            border-radius: 8px;
            font-size: 14px;
            color: var(--admin-text);
            background: white;
            font-family: 'Inter', sans-serif;
        }

        .admin-form input:focus,
        .admin-form select:focus,
        .admin-form textarea:focus {
            outline: none;
            border-color: var(--admin-accent);
        }

        .admin-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .admin-form-grid .full {
            grid-column: 1 / -1;
        }

        @media (max-width: 900px) {
            .admin-sidebar {
                display: none;
            }

            .admin-wrapper {
                margin-left: 0;
            }

            .admin-topbar {
                left: 0;
            }

            .admin-two-col {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">A</div>
            <div>
                <p class="logo-text">Anon Admin</p>
                <p class="logo-sub">Management Panel</p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <span class="sidebar-section">Main</span>
            <a href="<?php echo SITE_URL; ?>/admin/dashboard.php"
                class="sidebar-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <ion-icon name="grid-outline"></ion-icon> Dashboard
            </a>

            <span class="sidebar-section">Catalog</span>
            <a href="<?php echo SITE_URL; ?>/admin/products.php"
                class="sidebar-link <?php echo $currentPage === 'products.php' ? 'active' : ''; ?>">
                <ion-icon name="cube-outline"></ion-icon> Products
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/categories.php"
                class="sidebar-link <?php echo $currentPage === 'categories.php' ? 'active' : ''; ?>">
                <ion-icon name="list-outline"></ion-icon> Categories
            </a>

            <span class="sidebar-section">Sales</span>
            <a href="<?php echo SITE_URL; ?>/admin/orders.php"
                class="sidebar-link <?php echo $currentPage === 'orders.php' ? 'active' : ''; ?>">
                <ion-icon name="bag-outline"></ion-icon> Orders
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/coupons.php"
                class="sidebar-link <?php echo $currentPage === 'coupons.php' ? 'active' : ''; ?>">
                <ion-icon name="pricetag-outline"></ion-icon> Coupons
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/reports.php"
                class="sidebar-link <?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>">
                <ion-icon name="bar-chart-outline"></ion-icon> Reports
            </a>

            <span class="sidebar-section">Users</span>
            <a href="<?php echo SITE_URL; ?>/admin/users.php"
                class="sidebar-link <?php echo $currentPage === 'users.php' ? 'active' : ''; ?>">
                <ion-icon name="people-outline"></ion-icon> Customers
            </a>
        </nav>

        <div class="sidebar-bottom">
            <a href="<?php echo SITE_URL; ?>/index.php" class="sidebar-link" target="_blank">
                <ion-icon name="open-outline"></ion-icon> View Store
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="sidebar-link">
                <ion-icon name="log-out-outline"></ion-icon> Logout
            </a>
        </div>
    </aside>

    <!-- Top Bar -->
    <div class="admin-wrapper">
        <header class="admin-topbar">
            <h1 class="topbar-title">
                <?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?>
            </h1>
            <div class="topbar-right">
                <span class="topbar-badge">Administrator</span>
                <div class="topbar-avatar">
                    <?php echo strtoupper($_SESSION['admin_name'][0]); ?>
                </div>
            </div>
        </header>