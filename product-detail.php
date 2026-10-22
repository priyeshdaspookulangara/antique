<?php
require_once __DIR__ . '/config/db.php';

// Retrieve product by slug or id
$slug = trim($_GET['slug'] ?? '');
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$product = null;
$category = null;
$media_items = [];

if ($conn && (!empty($slug) || $product_id > 0)) {
    if (!empty($slug)) {
        $stmt = mysqli_prepare($conn, "
            SELECT p.*, c.category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE p.slug = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "s", $slug);
    } else {
        $stmt = mysqli_prepare($conn, "
            SELECT p.*, c.category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE p.product_id = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "i", $product_id);
    }

    if ($stmt) {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $product = $row;
        }
        mysqli_stmt_close($stmt);
    }

    if ($product) {
        // Fetch media items
        $media_stmt = mysqli_prepare($conn, "
            SELECT media_id, image_path, is_primary, sort_order
            FROM product_media
            WHERE product_id = ?
            ORDER BY is_primary DESC, sort_order ASC, media_id ASC
        ");
        if ($media_stmt) {
            mysqli_stmt_bind_param($media_stmt, "i", $product['product_id']);
            mysqli_stmt_execute($media_stmt);
            $m_res = mysqli_stmt_get_result($media_stmt);
            if ($m_res) {
                while ($m_row = mysqli_fetch_assoc($m_res)) {
                    $media_items[] = $m_row;
                }
            }
            mysqli_stmt_close($media_stmt);
        }
    }
}

// 404 Header if product not found
if (!$product && !headers_sent()) {
    http_response_code(404);
}

// Determine Host & Protocol for Canonical / OG URLs
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$current_url = $protocol . $host . ($_SERVER['REQUEST_URI'] ?? '');

// Dynamic SEO Head Injection
$page_meta_title = !empty($product['meta_title']) ? $product['meta_title'] : ($product ? $product['title'] . " | Antique Artifacts Collection" : "Artifact Not Found");
$page_meta_desc = !empty($product['meta_description']) ? $product['meta_description'] : ($product ? mb_substr(strip_tags($product['description'] ?? ''), 0, 150) . '...' : "Exclusive 1-of-1 Antique Artifacts.");

// Primary Image for Open Graph
$primary_image = 'assets/placeholder.jpg';
if (!empty($media_items)) {
    $primary_image = $media_items[0]['image_path'];
}
$og_image_url = (strpos($primary_image, 'http') === 0) ? $primary_image : $protocol . $host . '/' . ltrim($primary_image, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Dynamic SEO Meta Tags -->
    <title><?= htmlspecialchars($page_meta_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_meta_desc) ?>">

    <!-- Dynamic Open Graph (OG) Meta Tags -->
    <meta property="og:title" content="<?= htmlspecialchars($page_meta_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_meta_desc) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($og_image_url) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($current_url) ?>">
    <meta property="og:type" content="product">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($page_meta_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($page_meta_desc) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($og_image_url) ?>">

    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #faf8f5; color: #2b2b2b; font-family: 'Georgia', serif; }
        .header-brand { font-family: 'Cinzel', 'Georgia', serif; letter-spacing: 1.5px; }
        .main-gallery-img { max-height: 500px; width: 100%; object-fit: contain; background: #111; border-radius: 6px; }
        .thumb-img { height: 80px; width: 80px; object-fit: cover; border: 2px solid transparent; cursor: pointer; border-radius: 4px; transition: all 0.2s; }
        .thumb-img.active, .thumb-img:hover { border-color: #8c2d19; transform: scale(1.05); }
        .price-tag { font-size: 2rem; color: #8c2d19; font-weight: bold; }
        .badge-museum { background-color: #3d2314; color: #f4e8d1; }
        .badge-vintage { background-color: #1e3d2f; color: #e1f0e6; }
        .badge-patina { background-color: #5a4010; color: #fdf5e6; }
        .stock-available { background-color: #198754; color: #fff; }
        .stock-reserved { background-color: #fd7e14; color: #fff; }
        .stock-sold { background-color: #dc3545; color: #fff; }
        .artifact-spec-box { background: #fff; border: 1px solid #e0d8cf; border-radius: 6px; padding: 20px; }
    </style>
</head>
<body>

<!-- Navbar Header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container">
        <a class="navbar-brand header-brand text-uppercase" href="index.php">🏛️ Museum Artifact Collection</a>
        <a href="admin/add_product.php" class="btn btn-outline-warning btn-sm">Admin Panel</a>
    </div>
</nav>

<div class="container my-5">
    <?php if (!$product): ?>
        <div class="row justify-content-center py-5">
            <div class="col-md-8 text-center">
                <h1 class="display-5 text-muted">Artifact Not Found</h1>
                <p class="lead text-secondary">The requested 1-of-1 historical piece is unavailable or does not exist.</p>
                <a href="index.php" class="btn btn-primary mt-3">Return to Collection</a>
            </div>
        </div>
    <?php else: ?>

        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Home</a></li>
                <?php if (!empty($product['category_name'])): ?>
                    <li class="breadcrumb-item"><a href="sitemap.php" class="text-decoration-none text-muted"><?= htmlspecialchars($product['category_name']) ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['title']) ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <!-- Left Column: Media Gallery & HTML5 Video Inspection -->
            <div class="col-lg-7">

                <!-- Primary Image Box -->
                <div class="position-relative mb-3 text-center">
                    <?php if (!empty($media_items)): ?>
                        <img id="primaryGalleryImage" src="<?= htmlspecialchars($media_items[0]['image_path']) ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="main-gallery-img shadow">
                    <?php else: ?>
                        <div class="main-gallery-img d-flex align-items-center justify-content-center text-muted">No Images Available</div>
                    <?php endif; ?>
                </div>

                <!-- Thumbnails Carousel/Grid -->
                <?php if (count($media_items) > 1): ?>
                    <div class="d-flex gap-2 flex-wrap mb-4 justify-content-center">
                        <?php foreach ($media_items as $index => $item): ?>
                            <img src="<?= htmlspecialchars($item['image_path']) ?>"
                                 alt="Thumbnail <?= $index + 1 ?>"
                                 class="thumb-img <?= $index === 0 ? 'active' : '' ?>"
                                 onclick="switchPrimaryImage(this, '<?= htmlspecialchars($item['image_path']) ?>')">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- 360 Walkthrough / Inspection Video Player -->
                <?php if (!empty($product['video_path']) && file_exists(__DIR__ . '/' . $product['video_path'])): ?>
                    <div class="mt-4 p-3 bg-white border rounded shadow-sm">
                        <h3 class="h6 fw-bold text-uppercase mb-3">🎥 Artifact 360° Walkthrough & Detail Video</h3>
                        <div class="ratio ratio-16x9">
                            <video controls class="rounded">
                                <source src="<?= htmlspecialchars($product['video_path']) ?>" type="video/mp4">
                                Your browser does not support HTML5 video inspection.
                            </video>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Right Column: Artifact Provenance & 1-of-1 Action -->
            <div class="col-lg-5">
                <div class="artifact-spec-box shadow-sm">

                    <!-- Stock & Condition Badges -->
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <?php
                        $badge_class = 'badge-vintage';
                        if ($product['condition_grade'] === 'Museum Quality') $badge_class = 'badge-museum';
                        if ($product['condition_grade'] === 'Patina-Rich / Weathered') $badge_class = 'badge-patina';
                        ?>
                        <span class="badge <?= $badge_class ?> px-3 py-2 fs-6"><?= htmlspecialchars($product['condition_grade']) ?></span>

                        <?php
                        $stock_class = 'stock-available';
                        if ($product['stock_status'] === 'Reserved') $stock_class = 'stock-reserved';
                        if ($product['stock_status'] === 'Sold') $stock_class = 'stock-sold';
                        ?>
                        <span class="badge <?= $stock_class ?> px-3 py-2 fs-6">
                            <?= $product['stock_status'] === 'Available' ? '1-of-1 Available' : htmlspecialchars($product['stock_status']) ?>
                        </span>
                    </div>

                    <h1 class="h2 fw-bold text-dark mb-2"><?= htmlspecialchars($product['title']) ?></h1>

                    <?php if (!empty($product['era_origin'])): ?>
                        <p class="text-muted fs-6 mb-3"><strong>Origin / Era:</strong> <?= htmlspecialchars($product['era_origin']) ?></p>
                    <?php endif; ?>

                    <div class="price-tag mb-4">
                        $<?= number_format((float)$product['price'], 2) ?>
                    </div>

                    <hr>

                    <!-- Details Listing -->
                    <div class="mb-4">
                        <h2 class="h6 fw-bold text-uppercase text-secondary">Artifact Specifications</h2>
                        <ul class="list-unstyled text-secondary mt-2">
                            <?php if (!empty($product['category_name'])): ?>
                                <li class="mb-1"><strong>Category:</strong> <?= htmlspecialchars($product['category_name']) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($product['dimensions'])): ?>
                                <li class="mb-1"><strong>Dimensions:</strong> <?= htmlspecialchars($product['dimensions']) ?></li>
                            <?php endif; ?>
                            <li class="mb-1"><strong>Edition:</strong> Unique 1-of-1 Authentic Piece</li>
                        </ul>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <h2 class="h6 fw-bold text-uppercase text-secondary">Historical Overview & Provenance</h2>
                        <div class="text-secondary lh-lg fs-6 mt-2">
                            <?= nl2br(htmlspecialchars($product['description'] ?? 'No detailed description provided.')) ?>
                        </div>
                    </div>

                    <!-- Action Button Logic (Strict 1-of-1 Purchase/Inquiry) -->
                    <div class="d-grid gap-2 mt-4">
                        <?php if ($product['stock_status'] === 'Available'): ?>
                            <button type="button" class="btn btn-dark btn-lg py-3 fw-bold text-uppercase" onclick="handleInquiry('<?= htmlspecialchars($product['slug']) ?>')">
                                Acquire This 1-of-1 Artifact
                            </button>
                        <?php elseif ($product['stock_status'] === 'Reserved'): ?>
                            <button type="button" class="btn btn-warning btn-lg py-3 fw-bold text-uppercase text-dark" disabled>
                                🔒 Currently Reserved by Collector
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary btn-lg py-3 fw-bold text-uppercase" disabled>
                                🔒 Sold (1-of-1 Private Collection)
                            </button>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function switchPrimaryImage(thumbElement, imageSrc) {
        const primaryImg = document.getElementById('primaryGalleryImage');
        if (primaryImg) {
            primaryImg.src = imageSrc;
        }
        document.querySelectorAll('.thumb-img').forEach(el => el.classList.remove('active'));
        thumbElement.classList.add('active');
    }

    function handleInquiry(slug) {
        alert('Thank you for your interest in this unique 1-of-1 artifact. Our curator will get in touch shortly.');
    }
</script>
</body>
</html>
