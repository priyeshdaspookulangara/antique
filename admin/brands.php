<?php
$admin_page_title = "Manage Brands & Guilds | Admin Panel";
require_once __DIR__ . '/includes/sidebar.php';

$success_msg = '';
$error_msg = '';

function create_slug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

// Handle Add Brand
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_brand') {
    $brand_name = trim($_POST['brand_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($brand_name)) {
        $error_msg = "Brand name is required.";
    } else {
        $slug = create_slug($brand_name);
        if ($conn) {
            $stmt = mysqli_prepare($conn, "INSERT INTO brands (brand_name, slug, description) VALUES (?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sss", $brand_name, $slug, $description);
                if (mysqli_stmt_execute($stmt)) {
                    $success_msg = "Brand / Guild successfully added!";
                } else {
                    $error_msg = "Error creating brand: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Fetch Brands
$brands = [];
if ($conn) {
    $res = mysqli_query($conn, "
        SELECT b.*, (SELECT COUNT(*) FROM products p WHERE p.brand_id = b.brand_id) as product_count
        FROM brands b
        ORDER BY b.brand_name ASC
    ");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $brands[] = $row;
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold text-dark mb-0">Manage Brands & Guilds</h1>
</div>

<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Add Brand Form -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3 bg-white">
            <h2 class="h5 fw-bold mb-3">Add New Brand / Maker</h2>
            <form action="brands.php" method="POST">
                <input type="hidden" name="action" value="add_brand">
                <div class="mb-3">
                    <label for="brand_name" class="form-label fw-bold small">Brand / Guild Name *</label>
                    <input type="text" name="brand_name" id="brand_name" class="form-control" required placeholder="e.g. Sèvres Porcelain Manufactory">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label fw-bold small">Description / Historical Heritage</label>
                    <textarea name="description" id="description" class="form-control" rows="4" placeholder="Brief history of the maker or guild..."></textarea>
                </div>
                <button type="submit" class="btn btn-dark fw-bold w-100">Add Brand / Guild</button>
            </form>
        </div>
    </div>

    <!-- Brands Table -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0 p-0 bg-white">
            <div class="card-header bg-white py-3">
                <h2 class="h5 mb-0 fw-bold">Existing Brands (<?= count($brands) ?>)</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Brand Name</th>
                            <th>URL Slug</th>
                            <th>Products</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($brands)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No brands defined yet. Use the form to add one.</td></tr>
                        <?php else: ?>
                            <?php foreach ($brands as $b): ?>
                                <tr>
                                    <td>#<?= $b['brand_id'] ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($b['brand_name']) ?></td>
                                    <td><code><?= htmlspecialchars($b['slug']) ?></code></td>
                                    <td><span class="badge bg-secondary"><?= $b['product_count'] ?> item(s)</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
