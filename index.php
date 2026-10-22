<?php
require_once __DIR__ . '/config/db.php';

$page_title = "Antique Artifacts | Exclusive 1-of-1 Relics & Masterpieces";
$page_desc = "Explore authentic, rare, museum-quality 1-of-1 historical relics, ancient bronze statues, and antique masterworks.";

require_once __DIR__ . '/includes/header.php';

// Fetch featured products
$featured_products = [];
if ($conn) {
    $query = "
        SELECT p.*, c.category_name,
               (SELECT image_path FROM product_media pm WHERE pm.product_id = p.product_id ORDER BY is_primary DESC, media_id ASC LIMIT 1) as primary_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        ORDER BY p.created_at DESC
        LIMIT 6
    ";
    $res = mysqli_query($conn, $query);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $featured_products[] = $row;
        }
    }
}
?>

<!-- Hero Banner -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold header-brand mb-3">Authentic 1-of-1 Artifacts</h1>
        <p class="lead max-w-700 mx-auto mb-4">Each relic in our repository is guaranteed unique, meticulously inspected for patina and historical provenance.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="products.php" class="btn btn-warning btn-lg px-4 text-dark fw-bold">Explore Catalog</a>
            <a href="about.php" class="btn btn-outline-light btn-lg px-4">Our Provenance</a>
        </div>
    </div>
</section>

<!-- Collection Highlights -->
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="h3 header-brand mb-1">Recent Masterpieces</h2>
            <p class="text-muted mb-0">Newly indexed 1-of-1 acquisitions for private collectors</p>
        </div>
        <a href="products.php" class="btn btn-outline-dark">View Full Collection &rarr;</a>
    </div>

    <?php if (empty($featured_products)): ?>
        <div class="card p-5 text-center shadow-sm">
            <h3 class="h5 text-muted">No artifacts currently loaded in gallery.</h3>
            <p class="text-secondary mb-3">Visit the admin panel to add your first 1-of-1 artifact with media and SEO metadata.</p>
            <div>
                <a href="admin/add_product.php" class="btn btn-primary">Go to Admin Add Product</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($featured_products as $item): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-artifact h-100 shadow-sm">
                        <?php
                        $img = !empty($item['primary_image']) ? $item['primary_image'] : 'https://via.placeholder.com/600x400/2c221e/f4e8d1?text=1-of-1+Artifact';
                        ?>
                        <img src="<?= htmlspecialchars($img) ?>" class="card-img-top artifact-img-cover" alt="<?= htmlspecialchars($item['title']) ?>">

                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-secondary text-uppercase fs-7"><?= htmlspecialchars($item['category_name'] ?? 'Artifact') ?></span>
                                <span class="badge <?= $item['stock_status'] === 'Available' ? 'bg-success' : ($item['stock_status'] === 'Reserved' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                    <?= htmlspecialchars($item['stock_status']) ?>
                                </span>
                            </div>

                            <h3 class="h5 card-title text-dark fw-bold mb-2">
                                <a href="product-detail.php?slug=<?= urlencode($item['slug']) ?>" class="text-dark text-decoration-none">
                                    <?= htmlspecialchars($item['title']) ?>
                                </a>
                            </h3>

                            <?php if (!empty($item['era_origin'])): ?>
                                <p class="text-muted small mb-2"><strong>Era:</strong> <?= htmlspecialchars($item['era_origin']) ?></p>
                            <?php endif; ?>

                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                <span class="price-badge">$<?= number_format((float)$item['price'], 2) ?></span>
                                <a href="product-detail.php?slug=<?= urlencode($item['slug']) ?>" class="btn btn-sm btn-outline-dark">Inspect Relic</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
