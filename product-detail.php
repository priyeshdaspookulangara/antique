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
            SELECT p.*, c.category_name, c.slug as category_slug, b.brand_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN brands b ON p.brand_id = b.brand_id
            WHERE p.slug = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "s", $slug);
    } else {
        $stmt = mysqli_prepare($conn, "
            SELECT p.*, c.category_name, c.slug as category_slug, b.brand_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN brands b ON p.brand_id = b.brand_id
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

// Host & Protocol
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$current_url = $protocol . $host . ($_SERVER['REQUEST_URI'] ?? '');

$page_title = !empty($product['meta_title']) ? $product['meta_title'] : ($product ? $product['title'] . " | Brass & Heritage" : "Piece Not Found | Brass & Heritage");
$page_desc = !empty($product['meta_description']) ? $product['meta_description'] : ($product ? mb_substr(strip_tags($product['description'] ?? ''), 0, 150) . '...' : "Handcrafted brass wall decor & 1-of-1 antique relics.");

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap my-5">
    <?php if (!$product): ?>
        <div class="text-center py-5">
            <h1 class="display-5" style="font-family:var(--serif);">Piece Not Found</h1>
            <p class="text-muted">The requested 1-of-1 artifact is unavailable or has been acquired.</p>
            <a href="products.php" class="btn-brass text-decoration-none mt-3 inline-block">Return to Shop</a>
        </div>
    <?php else: ?>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="index.php" class="text-muted text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php" class="text-muted text-decoration-none">Shop</a></li>
                <?php if (!empty($product['category_name'])): ?>
                    <li class="breadcrumb-item"><a href="products.php?category=<?= urlencode($product['category_slug']) ?>" class="text-muted text-decoration-none"><?= htmlspecialchars($product['category_name']) ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['title']) ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <!-- Left Column: Framed Gallery & Inspection Video -->
            <div class="col-lg-6">
                <div class="frame mb-3">
                    <div class="panel">
                        <div class="relic">ॐ</div>
                        <?php if (!empty($media_items)): ?>
                            <img id="primaryGalleryImage" src="<?= htmlspecialchars($media_items[0]['image_path']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Thumbnail Switcher -->
                <?php if (count($media_items) > 1): ?>
                    <div class="d-flex gap-2 flex-wrap mb-4 justify-content-center">
                        <?php foreach ($media_items as $index => $item): ?>
                            <img src="<?= htmlspecialchars($item['image_path']) ?>"
                                 alt="Thumbnail <?= $index + 1 ?>"
                                 class="thumb-img <?= $index === 0 ? 'border-warning' : '' ?>"
                                 style="height:70px;width:70px;object-fit:cover;cursor:pointer;border:2px solid transparent;"
                                 onclick="document.getElementById('primaryGalleryImage').src='<?= htmlspecialchars($item['image_path']) ?>'">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- 360 Walkthrough / Video Player -->
                <?php if (!empty($product['video_path']) && file_exists(__DIR__ . '/' . $product['video_path'])):
                    $vid_ext = strtolower(pathinfo($product['video_path'], PATHINFO_EXTENSION));
                    $vid_mime = ($vid_ext === 'mov') ? 'video/quicktime' : 'video/mp4';
                ?>
                    <div class="mt-4 p-3 bg-white border rounded">
                        <h3 class="h6 fw-bold text-uppercase mb-2" style="font-family:var(--serif);">🎥 Inspection Video</h3>
                        <div class="ratio ratio-16x9">
                            <video controls class="rounded">
                                <source src="<?= htmlspecialchars($product['video_path']) ?>" type="<?= $vid_mime ?>">
                                Browser does not support HTML5 video inspection.
                            </video>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Relic Provenance & Acquisition -->
            <div class="col-lg-6">
                <div class="p-4 p-md-5 bg-white border shadow-sm" style="border-radius:2px;">

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-dark px-3 py-2"><?= htmlspecialchars($product['condition_grade']) ?></span>
                        <span class="tag <?= $product['stock_status'] === 'Available' ? 'one' : '' ?> px-3 py-2">
                            <?= $product['stock_status'] === 'Available' ? '1-of-1 Available' : htmlspecialchars($product['stock_status']) ?>
                        </span>
                    </div>

                    <h1 class="h2 mb-2" style="font-family:var(--serif);font-weight:700;"><?= htmlspecialchars($product['title']) ?></h1>

                    <?php if (!empty($product['brand_name'])): ?>
                        <p class="text-muted small mb-1"><strong>Maker / Guild:</strong> <?= htmlspecialchars($product['brand_name']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($product['era_origin'])): ?>
                        <p class="text-muted small mb-3"><strong>Origin / Era:</strong> <?= htmlspecialchars($product['era_origin']) ?></p>
                    <?php endif; ?>

                    <div class="fs-2 fw-bold mb-4" style="color:var(--oxblood);">
                        ₹<?= number_format((float)$product['price'], 0) ?>
                    </div>

                    <hr>

                    <!-- Specifications -->
                    <div class="mb-4">
                        <h3 class="h6 text-uppercase fw-bold text-muted">Specifications</h3>
                        <ul class="list-unstyled text-secondary mt-2 small lh-lg">
                            <?php if (!empty($product['category_name'])): ?>
                                <li><strong>Category:</strong> <?= htmlspecialchars($product['category_name']) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($product['dimensions'])): ?>
                                <li><strong>Dimensions:</strong> <?= htmlspecialchars($product['dimensions']) ?></li>
                            <?php endif; ?>
                            <li><strong>Authenticity:</strong> Guaranteed 1-of-1 Original Piece</li>
                            <li><strong>Ready to Hang:</strong> Yes (Fittings Attached)</li>
                        </ul>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <h3 class="h6 text-uppercase fw-bold text-muted">Historical Overview &amp; Provenance</h3>
                        <p class="text-secondary lh-lg mt-2" style="font-size:0.95rem;">
                            <?= nl2br(htmlspecialchars($product['description'] ?? 'No detailed provenance provided.')) ?>
                        </p>
                    </div>

                    <!-- Acquisition Button -->
                    <div class="d-grid mt-4">
                        <?php if ($product['stock_status'] === 'Available'): ?>
                            <button type="button" class="btn-brass py-3 fw-bold text-uppercase fs-6 border-0" onclick="alert('Thank you for your interest. Our curator will contact you shortly to complete this 1-of-1 acquisition.')">
                                Acquire This 1-of-1 Piece
                            </button>
                        <?php elseif ($product['stock_status'] === 'Reserved'): ?>
                            <button type="button" class="btn btn-warning py-3 fw-bold text-dark text-uppercase fs-6" disabled>
                                🔒 Currently Reserved
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary py-3 fw-bold text-uppercase fs-6" disabled>
                                🔒 Sold (Private Collection)
                            </button>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
