<?php
require_once __DIR__ . '/../../config/auth.php';
require_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($admin_page_title) ? htmlspecialchars($admin_page_title) : "Admin Control Panel" ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f6f9; }
        .wrapper { display: flex; width: 100%; align-items: stretch; min-height: 100vh; }
        #sidebar { min-width: 260px; max-width: 260px; background: #1e242b; color: #b8c7ce; transition: all 0.3s; }
        #sidebar .sidebar-header { padding: 20px; background: #181c22; font-family: 'Georgia', serif; border-bottom: 1px solid #2d353f; }
        #sidebar ul.components { padding: 20px 0; }
        #sidebar ul li a { padding: 12px 20px; font-size: 0.95rem; display: block; color: #a2b0b9; text-decoration: none; border-left: 3px solid transparent; }
        #sidebar ul li a:hover, #sidebar ul li a.active { color: #fff; background: #262e37; border-left-color: #d97706; }
        #content { width: 100%; padding: 25px; min-height: 100vh; }
        .stat-card { border-radius: 8px; border: none; }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h3 class="h5 mb-0 text-white">🏛️ Admin Panel</h3>
            <small class="text-muted">1-of-1 Artifact Control</small>
        </div>

        <ul class="list-unstyled components">
            <li>
                <a href="dashboard.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>">📊 Dashboard</a>
            </li>
            <li>
                <a href="add_product.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'add_product.php') ? 'active' : '' ?>">➕ Add Product</a>
            </li>
            <li>
                <a href="products.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : '' ?>">📦 Products Inventory</a>
            </li>
            <li>
                <a href="brands.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'brands.php') ? 'active' : '' ?>">🏷️ Brands & Guilds</a>
            </li>
            <li>
                <a href="categories.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'categories.php') ? 'active' : '' ?>">📁 Categories & SEO</a>
            </li>
            <li>
                <a href="orders.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'orders.php') ? 'active' : '' ?>">🛒 Customer Orders</a>
            </li>
            <li>
                <a href="customers.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'customers.php') ? 'active' : '' ?>">👥 Registered Users</a>
            </li>
            <hr class="border-secondary my-3 mx-3">
            <li>
                <a href="../index.php" target="_blank">🌐 View Storefront</a>
            </li>
            <li>
                <a href="../sitemap.php" target="_blank">🗺️ XML Sitemap</a>
            </li>
            <li>
                <a href="../logout.php" class="text-danger">🚪 Logout (<?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?>)</a>
            </li>
        </ul>
    </nav>

    <!-- Page Content -->
    <div id="content">
