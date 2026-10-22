<?php
require_once __DIR__ . '/config/db.php';

$page_title = "Artifact Catalog | 1-of-1 Antique Collection";
$page_desc = "Browse complete index of unique 1-of-1 historical artifacts with advanced filters, category browsing, brand filtering, and price sorting.";

require_once __DIR__ . '/includes/header.php';

// Filter & Pagination Parameters
$search_query   = trim($_GET['q'] ?? '');
$cat_slug       = trim($_GET['category'] ?? '');
$brand_slug     = trim($_GET['brand'] ?? '');
$status_filter  = trim($_GET['status'] ?? '');
$condition_f    = trim($_GET['condition'] ?? '');
$min_price      = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price      = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
$sort_by        = trim($_GET['sort'] ?? 'newest');

$page           = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page       = 6;
$offset         = ($page - 1) * $per_page;

// Build Dynamic WHERE Clause
$where_clauses = [];
$params = [];
$param_types = "";

if (!empty($search_query)) {
    $where_clauses[] = "(p.title LIKE ? OR p.description LIKE ? OR p.era_origin LIKE ?)";
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "sss";
}

if (!empty($cat_slug)) {
    $where_clauses[] = "c.slug = ?";
    $params[] = $cat_slug;
    $param_types .= "s";
}

if (!empty($brand_slug)) {
    $where_clauses[] = "b.slug = ?";
    $params[] = $brand_slug;
    $param_types .= "s";
}

if (!empty($status_filter) && in_array($status_filter, ['Available', 'Reserved', 'Sold'])) {
    $where_clauses[] = "p.stock_status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}

if (!empty($condition_f) && in_array($condition_f, ['Museum Quality', 'Excellent Vintage', 'Patina-Rich / Weathered'])) {
    $where_clauses[] = "p.condition_grade = ?";
    $params[] = $condition_f;
    $param_types .= "s";
}

if ($min_price !== null) {
    $where_clauses[] = "p.price >= ?";
    $params[] = $min_price;
    $param_types .= "d";
}

if ($max_price !== null) {
    $where_clauses[] = "p.price <= ?";
    $params[] = $max_price;
    $param_types .= "d";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Determine Sorting Order
switch ($sort_by) {
    case 'price_asc':
        $order_sql = "ORDER BY p.price ASC, p.product_id DESC";
        break;
    case 'price_desc':
        $order_sql = "ORDER BY p.price DESC, p.product_id DESC";
        break;
    case 'title_asc':
        $order_sql = "ORDER BY p.title ASC";
        break;
    case 'oldest':
        $order_sql = "ORDER BY p.created_at ASC, p.product_id ASC";
        break;
    case 'newest':
    default:
        $order_sql = "ORDER BY p.created_at DESC, p.product_id DESC";
        break;
}

// Fetch categories & brands for sidebar filter
$categories = [];
$brands = [];
if ($conn) {
    $cat_res = mysqli_query($conn, "SELECT category_id, category_name, slug FROM categories ORDER BY category_name ASC");
    if ($cat_res) {
        while ($row = mysqli_fetch_assoc($cat_res)) {
            $categories[] = $row;
        }
    }

    $brand_res = mysqli_query($conn, "SELECT brand_id, brand_name, slug FROM brands ORDER BY brand_name ASC");
    if ($brand_res) {
        while ($row = mysqli_fetch_assoc($brand_res)) {
            $brands[] = $row;
        }
    }
}

// Get Total Count for Pagination
$total_products = 0;
if ($conn) {
    $count_query = "
        SELECT COUNT(*) as total
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        {$where_sql}
    ";

    $stmt_count = mysqli_prepare($conn, $count_query);
    if ($stmt_count) {
        if (!empty($param_types)) {
            mysqli_stmt_bind_param($stmt_count, $param_types, ...$params);
        }
        mysqli_stmt_execute($stmt_count);
        $res_count = mysqli_stmt_get_result($stmt_count);
        if ($res_count && $row_c = mysqli_fetch_assoc($res_count)) {
            $total_products = (int)$row_c['total'];
        }
        mysqli_stmt_close($stmt_count);
    }
}

$total_pages = ceil($total_products / $per_page);

// Fetch Products for Current Page
$products = [];
if ($conn) {
    $query = "
        SELECT p.*, c.category_name, c.slug as category_slug, b.brand_name,
               (SELECT image_path FROM product_media pm WHERE pm.product_id = p.product_id ORDER BY is_primary DESC, media_id ASC LIMIT 1) as primary_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        {$where_sql}
        {$order_sql}
        LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        $p_types = $param_types . "ii";
        $p_params = array_merge($params, [$per_page, $offset]);

        mysqli_stmt_bind_param($stmt, $p_types, ...$p_params);
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

// Helper function to build pagination query strings
function build_filter_url($override_params = []) {
    $current_get = $_GET;
    $merged = array_merge($current_get, $override_params);
    return 'products.php?' . http_build_query($merged);
}
?>

<div class="container my-5">

    <!-- Page Header & Search Bar -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h1 class="h3 header-brand mb-1">Artifact Catalog</h1>
            <p class="text-muted small mb-0">Discover authentic 1-of-1 historical relics, makers, and museum-grade masterworks.</p>
        </div>
        <div class="col-md-6">
            <form action="products.php" method="GET" class="d-flex gap-2">
                <?php if (!empty($cat_slug)): ?><input type="hidden" name="category" value="<?= htmlspecialchars($cat_slug) ?>"><?php endif; ?>
                <?php if (!empty($brand_slug)): ?><input type="hidden" name="brand" value="<?= htmlspecialchars($brand_slug) ?>"><?php endif; ?>
                <?php if (!empty($status_filter)): ?><input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>"><?php endif; ?>
                <input type="text" name="q" class="form-control" placeholder="Search title, era, maker..." value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit" class="btn btn-dark px-4">Search</button>
            </form>
        </div>
    </div>

    <div class="row g-4">

        <!-- Filter Sidebar -->
        <div class="col-lg-3">
            <div class="card p-3 shadow-sm border-0 bg-white">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <h2 class="h5 header-brand mb-0">Refine Results</h2>
                    <a href="products.php" class="btn btn-sm btn-link text-decoration-none text-muted p-0">Reset All</a>
                </div>

                <form action="products.php" method="GET">
                    <?php if (!empty($search_query)): ?><input type="hidden" name="q" value="<?= htmlspecialchars($search_query) ?>"><?php endif; ?>

                    <!-- Category Filter -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-uppercase small text-muted">Category</label>
                        <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= ($cat_slug === $cat['slug']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Brand / Maker Guild Filter -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-uppercase small text-muted">Brand / Guild</label>
                        <select name="brand" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Brands / Makers</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= htmlspecialchars($b['slug']) ?>" <?= ($brand_slug === $b['slug']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b['brand_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-uppercase small text-muted">Price Range ($)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" step="0.01" name="min_price" class="form-control form-control-sm" placeholder="Min $" value="<?= $min_price !== null ? htmlspecialchars($min_price) : '' ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" name="max_price" class="form-control form-control-sm" placeholder="Max $" value="<?= $max_price !== null ? htmlspecialchars($max_price) : '' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Stock Status Filter -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-uppercase small text-muted">1-of-1 Stock Status</label>
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="Available" <?= ($status_filter === 'Available') ? 'selected' : '' ?>>🟢 Available</option>
                            <option value="Reserved" <?= ($status_filter === 'Reserved') ? 'selected' : '' ?>>🟠 Reserved</option>
                            <option value="Sold" <?= ($status_filter === 'Sold') ? 'selected' : '' ?>>🔴 Sold (Private Collections)</option>
                        </select>
                    </div>

                    <!-- Condition Grade Filter -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-uppercase small text-muted">Condition Grade</label>
                        <select name="condition" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Condition Grades</option>
                            <option value="Museum Quality" <?= ($condition_f === 'Museum Quality') ? 'selected' : '' ?>>Museum Quality</option>
                            <option value="Excellent Vintage" <?= ($condition_f === 'Excellent Vintage') ? 'selected' : '' ?>>Excellent Vintage</option>
                            <option value="Patina-Rich / Weathered" <?= ($condition_f === 'Patina-Rich / Weathered') ? 'selected' : '' ?>>Patina-Rich / Weathered</option>
                        </select>
                    </div>

                    <!-- Sort Order -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-uppercase small text-muted">Sort By</label>
                        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="newest" <?= ($sort_by === 'newest') ? 'selected' : '' ?>>Newest Acquisitions</option>
                            <option value="price_asc" <?= ($sort_by === 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_desc" <?= ($sort_by === 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="title_asc" <?= ($sort_by === 'title_asc') ? 'selected' : '' ?>>Title: A to Z</option>
                            <option value="oldest" <?= ($sort_by === 'oldest') ? 'selected' : '' ?>>Oldest First</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-outline-dark btn-sm w-100 fw-bold">Apply Filters</button>
                </form>

            </div>
        </div>

        <!-- Products Listing Grid -->
        <div class="col-lg-9">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small">
                    Showing <strong><?= count($products) ?></strong> of <strong><?= $total_products ?></strong> artifact(s)
                </span>

                <?php if (!empty($search_query) || !empty($cat_slug) || !empty($brand_slug) || !empty($status_filter) || !empty($condition_f) || $min_price !== null || $max_price !== null): ?>
                    <span class="badge bg-light text-dark border">Filters Applied</span>
                <?php endif; ?>
            </div>

            <?php if (empty($products)): ?>
                <div class="alert alert-warning p-5 text-center shadow-sm">
                    <h2 class="h5 fw-bold mb-2">No matching 1-of-1 artifacts found</h2>
                    <p class="text-secondary mb-3">Try adjusting your price range, clearing search terms, or resetting filters.</p>
                    <a href="products.php" class="btn btn-dark btn-sm">Reset All Filters</a>
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

                                    <?php if (!empty($item['brand_name'])): ?>
                                        <p class="text-muted small mb-1"><strong>Maker:</strong> <?= htmlspecialchars($item['brand_name']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['era_origin'])): ?>
                                        <p class="text-muted small mb-2"><strong>Era:</strong> <?= htmlspecialchars($item['era_origin']) ?></p>
                                    <?php endif; ?>

                                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                        <span class="price-badge">$<?= number_format((float)$item['price'], 2) ?></span>
                                        <a href="product-detail.php?slug=<?= urlencode($item['slug']) ?>" class="btn btn-sm btn-outline-dark">View Detail</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination UI -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Catalog Pagination" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <!-- Previous Page -->
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link text-dark" href="<?= build_filter_url(['page' => $page - 1]) ?>">Previous</a>
                            </li>

                            <!-- Page Numbers -->
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                    <a class="page-link <?= ($i === $page) ? 'bg-dark border-dark text-white' : 'text-dark' ?>" href="<?= build_filter_url(['page' => $i]) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Page -->
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link text-dark" href="<?= build_filter_url(['page' => $page + 1]) ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
