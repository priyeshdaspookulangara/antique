<?php
require_once __DIR__ . '/config/db.php';

if (!headers_sent()) {
    header("Content-Type: application/xml; charset=utf-8");
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_url = $protocol . $host;

// Fetch categories
$categories = [];
if ($conn) {
    $cat_query = "SELECT slug FROM categories ORDER BY category_id ASC";
    $cat_res = mysqli_query($conn, $cat_query);
    if ($cat_res) {
        while ($row = mysqli_fetch_assoc($cat_res)) {
            $categories[] = $row;
        }
    }
}

// Fetch active products with stock_status = 'Available'
$products = [];
if ($conn) {
    $prod_query = "SELECT slug, created_at FROM products WHERE stock_status = 'Available' ORDER BY created_at DESC";
    $prod_res = mysqli_query($conn, $prod_query);
    if ($prod_res) {
        while ($row = mysqli_fetch_assoc($prod_res)) {
            $products[] = $row;
        }
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Homepage -->
    <url>
        <loc><?= htmlspecialchars($base_url . '/') ?></loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Category Pages -->
    <?php foreach ($categories as $cat): ?>
    <url>
        <loc><?= htmlspecialchars($base_url . '/products.php?category=' . urlencode($cat['slug'])) ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>

    <!-- Product Detail Pages (Available 1-of-1 Artifacts) -->
    <?php foreach ($products as $prod): ?>
    <url>
        <loc><?= htmlspecialchars($base_url . '/product-detail.php?slug=' . urlencode($prod['slug'])) ?></loc>
        <?php if (!empty($prod['created_at'])): ?>
        <lastmod><?= date('Y-m-d\TH:i:sP', strtotime($prod['created_at'])) ?></lastmod>
        <?php endif; ?>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <?php endforeach; ?>
</urlset>
