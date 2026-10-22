<?php
require_once __DIR__ . '/config/db.php';

$page_title = "Shop All Antique Wall Decor | Patina Decor";
$page_desc = "Browse complete index of unique 1-of-1 historical artifacts, brass wall panels, and authenticated patina antique decor.";

require_once __DIR__ . '/includes/header.php';

// Filter & Pagination Parameters
$search_query   = trim($_GET['q'] ?? '');
$cat_slug       = trim($_GET['category'] ?? ($_GET['c'] ?? ''));
$brand_slug     = trim($_GET['brand'] ?? '');
$status_filter  = trim($_GET['status'] ?? '');
$condition_f    = trim($_GET['condition'] ?? '');
$min_price      = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price      = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
$sort_by        = trim($_GET['sort'] ?? 'newest');

$page           = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page       = 8;
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

// Fetch categories for sidebar filter
$categories = [];
if ($conn) {
    $cat_res = mysqli_query($conn, "SELECT category_id, category_name, slug FROM categories ORDER BY category_name ASC");
    if ($cat_res) {
        while ($row = mysqli_fetch_assoc($cat_res)) {
            $categories[] = $row;
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

function build_filter_url($override_params = []) {
    $current_get = $_GET;
    $merged = array_merge($current_get, $override_params);
    return 'products.php?' . http_build_query($merged);
}

$panel_classes = ['', 'wine', 'pink'];
$relic_symbols = ['ॐ', '❁', '✦', '♨', '❖', '☸', '❀', '✺'];
?>

<div class="wrap my-5">

    <!-- Page Title & Search Header -->
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
        <div>
            <h1 style="font-size:clamp(2.2rem,4vw,3.2rem);">Patina Decor Collection</h1>
            <p style="color:var(--muted);margin-top:6px;">Hand-finished deity panels, antique wall decor, and patina brass relics.</p>
        </div>
        <form action="products.php" method="GET" class="d-flex gap-2">
            <input type="text" name="q" class="form-control" placeholder="Search relics..." value="<?= htmlspecialchars($search_query) ?>" style="border-radius:2px;border:1.5px solid var(--line);">
            <button type="submit" class="btn-brass" style="padding:8px 20px;">Search</button>
        </form>
    </div>

    <div class="row g-4">

        <!-- Filter Sidebar -->
        <div class="col-lg-3">
            <div class="p-4 bg-white shadow-sm" style="border:1px solid var(--line);border-radius:2px;">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <h2 class="h5 mb-0" style="font-family:var(--serif);">Refine Collection</h2>
                    <a href="products.php" class="small text-muted text-decoration-none">Reset</a>
                </div>

                <form action="products.php" method="GET">
                    <?php if (!empty($search_query)): ?><input type="hidden" name="q" value="<?= htmlspecialchars($search_query) ?>"><?php endif; ?>

                    <!-- Category Filter -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Collection / Category</label>
                        <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Collections</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= ($cat_slug === $cat['slug']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Price Range (₹)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min ₹" value="<?= $min_price !== null ? htmlspecialchars($min_price) : '' ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max ₹" value="<?= $max_price !== null ? htmlspecialchars($max_price) : '' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Stock Status -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Availability</label>
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Pieces</option>
                            <option value="Available" <?= ($status_filter === 'Available') ? 'selected' : '' ?>>🟢 1-of-1 Available</option>
                            <option value="Reserved" <?= ($status_filter === 'Reserved') ? 'selected' : '' ?>>🟠 Reserved</option>
                            <option value="Sold" <?= ($status_filter === 'Sold') ? 'selected' : '' ?>>🔴 Sold</option>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted">Sort Order</label>
                        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="newest" <?= ($sort_by === 'newest') ? 'selected' : '' ?>>Newest Arrivals</option>
                            <option value="price_asc" <?= ($sort_by === 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_desc" <?= ($sort_by === 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-dark-theme w-100 fw-bold">Apply Filters</button>
                </form>
            </div>
        </div>

        <!-- Catalog Grid -->
        <div class="col-lg-9">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small">Showing <strong><?= count($products) ?></strong> of <strong><?= $total_products ?></strong> piece(s)</span>
            </div>

            <?php if (empty($products)): ?>
                <div class="p-5 text-center bg-white border rounded">
                    <h2 class="h5 fw-bold mb-2">No matching pieces found</h2>
                    <p class="text-muted mb-3">Try clearing search filters or price ranges.</p>
                    <a href="products.php" class="btn-dark-theme text-decoration-none">View All Pieces</a>
                </div>
            <?php else: ?>
                <div class="grid">
                    <?php foreach ($products as $idx => $prod): ?>
                        <?php
                            $p_class = $panel_classes[$idx % count($panel_classes)];
                            $symbol = $relic_symbols[$idx % count($relic_symbols)];
                        ?>
                        <article class="card-item">
                            <a href="product-detail.php?slug=<?= urlencode($prod['slug']) ?>" class="frame">
                                <div class="panel <?= $p_class ?>">
                                    <div class="relic"><?= $symbol ?></div>
                                    <?php if (!empty($prod['primary_image'])): ?>
                                        <img src="<?= htmlspecialchars($prod['primary_image']) ?>" alt="<?= htmlspecialchars($prod['title']) ?>" loading="lazy" onerror="this.remove()">
                                    <?php endif; ?>
                                </div>
                            </a>
                            <div class="meta">
                                <small><?= htmlspecialchars($prod['category_name'] ?: 'Brass Relic') ?></small>
                                <h3>
                                    <a href="product-detail.php?slug=<?= urlencode($prod['slug']) ?>" class="text-dark text-decoration-none">
                                        <?= htmlspecialchars($prod['title']) ?>
                                    </a>
                                </h3>
                                <div class="row-meta">
                                    <span class="price">₹<?= number_format((float)$prod['price'], 0) ?></span>
                                    <span class="tag <?= $prod['stock_status'] === 'Available' ? 'one' : '' ?>">
                                        <?= $prod['stock_status'] === 'Available' ? '1 left' : htmlspecialchars($prod['stock_status']) ?>
                                    </span>
                                </div>
                                <a href="product-detail.php?slug=<?= urlencode($prod['slug']) ?>" class="add-btn text-decoration-none">Inspect &amp; Order</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav class="mt-5 d-flex justify-content-center">
                        <ul class="pagination">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link text-dark" href="<?= build_filter_url(['page' => $page - 1]) ?>">Prev</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                    <a class="page-link <?= ($i === $page) ? 'bg-dark border-dark text-white' : 'text-dark' ?>" href="<?= build_filter_url(['page' => $i]) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
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
