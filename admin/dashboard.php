<?php
$admin_page_title = "Admin Dashboard | Antique Artifact Control";
require_once __DIR__ . '/includes/sidebar.php';

// Fetch key metrics
$total_products = 0;
$available_products = 0;
$sold_products = 0;
$total_categories = 0;
$total_brands = 0;
$total_users = 0;
$total_orders = 0;

if ($conn) {
    if ($res = mysqli_query($conn, "SELECT COUNT(*) FROM products")) {
        $total_products = mysqli_fetch_row($res)[0];
    }
    if ($res = mysqli_query($conn, "SELECT COUNT(*) FROM products WHERE stock_status = 'Available'")) {
        $available_products = mysqli_fetch_row($res)[0];
    }
    if ($res = mysqli_query($conn, "SELECT COUNT(*) FROM products WHERE stock_status = 'Sold'")) {
        $sold_products = mysqli_fetch_row($res)[0];
    }
    if ($res = mysqli_query($conn, "SELECT COUNT(*) FROM categories")) {
        $total_categories = mysqli_fetch_row($res)[0];
    }
    if ($res = mysqli_query($conn, "SELECT COUNT(*) FROM brands")) {
        $total_brands = mysqli_fetch_row($res)[0];
    }
    if ($res = mysqli_query($conn, "SELECT COUNT(*) FROM users")) {
        $total_users = mysqli_fetch_row($res)[0];
    }
    if ($res = mysqli_query($conn, "SELECT COUNT(*) FROM orders")) {
        $total_orders = mysqli_fetch_row($res)[0];
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold text-dark mb-0">Control Panel Overview</h1>
    <a href="add_product.php" class="btn btn-warning fw-bold">+ Add New 1-of-1 Artifact</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-white p-3 shadow-sm border-start border-4 border-primary">
            <div class="text-muted small fw-bold text-uppercase">Total Artifacts</div>
            <div class="fs-2 fw-bold text-dark"><?= $total_products ?></div>
            <small class="text-success fw-bold"><?= $available_products ?> Available | <?= $sold_products ?> Sold</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-white p-3 shadow-sm border-start border-4 border-warning">
            <div class="text-muted small fw-bold text-uppercase">Categories</div>
            <div class="fs-2 fw-bold text-dark"><?= $total_categories ?></div>
            <small class="text-muted">SEO Taxonomy</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-white p-3 shadow-sm border-start border-4 border-info">
            <div class="text-muted small fw-bold text-uppercase">Brands / Guilds</div>
            <div class="fs-2 fw-bold text-dark"><?= $total_brands ?></div>
            <small class="text-muted">Makers & Masters</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-white p-3 shadow-sm border-start border-4 border-success">
            <div class="text-muted small fw-bold text-uppercase">Registered Users</div>
            <div class="fs-2 fw-bold text-dark"><?= $total_users ?></div>
            <small class="text-muted">Collectors & Admins</small>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h2 class="h5 mb-0 fw-bold">Recent 1-of-1 Artifact Acquisitions</h2>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Era / Origin</th>
                        <th>Condition</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($conn) {
                        $res = mysqli_query($conn, "SELECT product_id, title, slug, era_origin, condition_grade, price, stock_status FROM products ORDER BY created_at DESC LIMIT 5");
                        if ($res && mysqli_num_rows($res) > 0) {
                            while ($row = mysqli_fetch_assoc($res)) {
                                echo "<tr>";
                                echo "<td>#{$row['product_id']}</td>";
                                echo "<td class='fw-bold'>" . htmlspecialchars($row['title']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['era_origin'] ?: 'N/A') . "</td>";
                                echo "<td><span class='badge bg-dark'>" . htmlspecialchars($row['condition_grade']) . "</span></td>";
                                echo "<td>$" . number_format((float)$row['price'], 2) . "</td>";
                                $badge = $row['stock_status'] === 'Available' ? 'bg-success' : ($row['stock_status'] === 'Reserved' ? 'bg-warning text-dark' : 'bg-danger');
                                echo "<td><span class='badge {$badge}'>" . htmlspecialchars($row['stock_status']) . "</span></td>";
                                echo "<td class='text-end'><a href='../product-detail.php?slug=" . urlencode($row['slug']) . "' target='_blank' class='btn btn-sm btn-outline-secondary'>View</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center py-4 text-muted'>No products added yet. Click '+ Add New 1-of-1 Artifact' to begin.</td></tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
