<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($page_title)) {
    $page_title = "Antique Artifacts | Unique 1-of-1 Gallery";
}
if (!isset($page_desc)) {
    $page_desc = "Curated repository of authentic, rare, one-of-a-kind historical artifacts.";
}

$is_user_logged = isset($_SESSION['user_id']);
$user_name = $_SESSION['full_name'] ?? '';
$is_admin_user = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_desc) ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Style -->
    <style>
        body { background-color: #faf8f5; color: #2b2b2b; font-family: 'Georgia', serif; display: flex; flex-direction: column; min-height: 100vh; }
        .content-wrapper { flex: 1; }
        .header-brand { font-family: 'Cinzel', 'Georgia', serif; letter-spacing: 1.5px; }
        .hero-section { background: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.65)), url('https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=1200&q=80') center/cover no-repeat; color: #f4e8d1; padding: 100px 0; }
        .card-artifact { border: 1px solid #e0d8cf; transition: transform 0.2s, box-shadow 0.2s; background: #fff; }
        .card-artifact:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .artifact-img-cover { height: 260px; object-fit: cover; width: 100%; background: #2c221e; }
        .price-badge { color: #8c2d19; font-weight: bold; font-size: 1.25rem; }
        footer { background-color: #1f1815; color: #b8ac9e; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3 border-bottom border-secondary">
    <div class="container">
        <a class="navbar-brand header-brand text-uppercase" href="index.php">🏛️ Museum Artifacts</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto ms-lg-4">
                <li class="nav-item"><a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : '' ?>" href="products.php">Artifact Catalog</a></li>
                <li class="nav-item"><a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : '' ?>" href="about.php">About Gallery</a></li>
                <li class="nav-item"><a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : '' ?>" href="contact.php">Collector Contact</a></li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if ($is_user_logged): ?>
                    <span class="text-light small me-2">Welcome, <strong><?= htmlspecialchars($user_name) ?></strong></span>
                    <?php if ($is_admin_user): ?>
                        <a href="admin/dashboard.php" class="btn btn-warning btn-sm fw-bold">Admin Panel</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">Sign Out</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light btn-sm">Sign In</a>
                    <a href="register.php" class="btn btn-warning btn-sm text-dark fw-bold">Register</a>
                <?php endif; ?>
                <a href="sitemap.php" class="btn btn-outline-secondary btn-sm text-light ms-1" target="_blank">XML Sitemap</a>
            </div>
        </div>
    </div>
</nav>

<div class="content-wrapper">
