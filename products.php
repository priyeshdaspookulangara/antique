<?php
require_once __DIR__ . '/config/db.php';

$page_title = "Artifact Catalog | 1-of-1 Antique Collection";
$page_desc = "Browse complete index of unique 1-of-1 historical artifacts, ancient statues, and authenticated antique items.";

require_once __DIR__ . '/includes/header.php';

// Filter parameters
$cat_slug = trim($_GET['category'] ?? '');
$status_filter = trim($_GET['status'] ?? '');

$where_clauses = [];
$params = [];
$param_types = "";

if (!empty($cat_slug)) {
    $where_clauses[] = "c.slug = ?";
    $params[] = $cat_slug;
    $param_types .= "s";
}

if (!empty($status_filter) && in_array($status_filter, ['Available', 'Reserved', 'Sold'])) {
    $where_clauses[] = "p.stock_status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Fetch categories for filter sidebar
$categories = [];
if ($conn) {
    $cat_res = mysqli_query($conn, "SELECT category_id, category_name, slug FROM categories ORDER BY category_name ASC");
    if ($cat_res) {
        while ($row = mysqli_fetch_assoc($cat_res)) {
            $categories[] = $row;
        }
    }
}

// Fetch products matching filters
$products = [];
if ($conn) {
    $query = "
        SELECT p.*, c.category_name, c.slug as category_slug,
               (SELECT image_path FROM product_media pm WHERE pm.product_id = p.product_id ORDER BY is_primary DESC, media_id ASC LIMIT 1) as primary_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        {$where_sql}
        ORDER BY p.created_at DESC
    ";

    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        if (!empty($param_types)) {
            mysqli_stmt_bind_param($stmt, $param_types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $products[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="container my-5">
    <div class="row g-4">

        <!-- Filter Sidebar -->
        <div class="col-lg-3">
            <div class="card p-3 shadow-sm border-0">
                <h2 class="h5 header-brand mb-3 border-bottom pb-2">Filter Catalog</h2>

                <!-- Category Filter -->
                <div class="mb-4">
                    <h3 class="h6 text-uppercase fw-bold text-muted">Category</h3>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-1">
                            <a href="products.php<?= !empty($status_filter) ? '?status=' . urlencode($status_filter) : '' ?>"
                               class="text-decoration-none <?= empty($cat_slug) ? 'fw-bold text-dark' : 'text-secondary' ?>">
                                All Categories
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                            <li class="mb-1">
                                <a href="products.php?category=<?= urlencode($cat['slug']) ?><?= !empty($status_filter) ? '&status=' . urlencode($status_filter) : '' ?>"
                                   class="text-decoration-none <?= ($cat_slug === $cat['slug']) ? 'fw-bold text-primary' : 'text-secondary' ?>">
                                    <?= htmlspecialchars($cat['category_name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Stock Availability Filter -->
                <div>
                    <h3 class="h6 text-uppercase fw-bold text-muted">Stock Status</h3>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-1">
                            <a href="products.php<?= !empty($cat_slug) ? '?category=' . urlencode($cat_slug) : '' ?>"
                               class="text-decoration-none <?= empty($status_filter) ? 'fw-bold text-dark' : 'text-secondary' ?>">
                                All Statuses
                            </a>
                        </li>
                        <li class="mb-1">
                            <a href="products.php?status=Available<?= !empty($cat_slug) ? '&category=' . urlencode($cat_slug) : '' ?>"
                               class="text-decoration-none <?= ($status_filter === 'Available') ? 'fw-bold text-success' : 'text-secondary' ?>">
                                🟢 Available 1-of-1
                            </a>
                        </li>
                        <li class="mb-1">
                            <a href="products.php?status=Reserved<?= !empty($cat_slug) ? '&category=' . urlencode($cat_slug) : '' ?>"
                               class="text-decoration-none <?= ($status_filter === 'Reserved') ? 'fw-bold text-warning' : 'text-secondary' ?>">
                                🟠 Reserved
                            </a>
                        </li>
                        <li class="mb-1">
                            <a href="products.php?status=Sold<?= !empty($cat_slug) ? '&category=' . urlencode($cat_slug) : '' ?>"
                               class="text-decoration-none <?= ($status_filter === 'Sold') ? 'fw-bold text-danger' : 'text-secondary' ?>">
                                🔴 Sold (Private Collections)
                            </a>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 header-brand mb-0">Artifact Catalog</h1>
                <span class="text-muted small">Showing <?= count($products) ?> unique item(s)</span>
            </div>

            <?php if (empty($products)): ?>
                <div class="alert alert-info shadow-sm p-4 text-center">
                    <h4 class="h5">No artifacts found matching your filter criteria.</h4>
                    <p class="mb-0">Try clearing filters or check back later for new arrivals.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($products as $item): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card card-artifact h-100 shadow-sm">
                                <?php
                                $img = !empty($item['primary_image']) ? $item['primary_image'] : 'https://via.placeholder.com/600x400/2c221e/f4e8d1?text=1-of-1+Artifact';
                                ?>
                                <img src="<?= htmlspecialchars($img) ?>" class="card-img-top artifact-img-cover" alt="<?= htmlspecialchars($item['title']) ?>">

                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-secondary text-uppercase fs-7"><?= htmlspecialchars($item['category_name'] ?? 'Relic') ?></span>
                                        <span class="badge <?= $item['stock_status'] === 'Available' ? 'bg-success' : ($item['stock_status'] === 'Reserved' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                            <?= htmlspecialchars($item['stock_status']) ?>
                                        </span>
                                    </div>

                                    <h2 class="h6 card-title text-dark fw-bold mb-2">
                                        <a href="product-detail.php?slug=<?= urlencode($item['slug']) ?>" class="text-dark text-decoration-none">
                                            <?= htmlspecialchars($item['title']) ?>
                                        </a>
                                    </h2>

                                    <?php if (!empty($item['era_origin'])): ?>
                                        <p class="text-muted small mb-2"><strong>Era:</strong> <?= htmlspecialchars($item['era_origin']) ?></p>
                                    <?php endif; ?>

                                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                        <span class="price-badge">$<?= number_format((float)$item['price'], 2) ?></span>
                                        <a href="product-detail.php?slug=<?= urlencode($item['slug']) ?>" class="btn btn-sm btn-outline-dark">View Item</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
