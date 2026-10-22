<?php
$admin_page_title = "Manage Categories & SEO | Admin Panel";
require_once __DIR__ . '/includes/sidebar.php';

$success_msg = '';
$error_msg = '';

function create_slug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $category_name = trim($_POST['category_name'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');

    if (empty($category_name)) {
        $error_msg = "Category name is required.";
    } else {
        $slug = create_slug($category_name);
        if ($conn) {
            $stmt = mysqli_prepare($conn, "INSERT INTO categories (category_name, slug, meta_title, meta_description) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssss", $category_name, $slug, $meta_title, $meta_description);
                if (mysqli_stmt_execute($stmt)) {
                    $success_msg = "Category & SEO metadata successfully added!";
                } else {
                    $error_msg = "Error creating category: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Fetch Categories
$categories = [];
if ($conn) {
    $res = mysqli_query($conn, "
        SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.category_id) as product_count
        FROM categories c
        ORDER BY c.category_name ASC
    ");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $categories[] = $row;
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold text-dark mb-0">Manage Categories & SEO Taxonomy</h1>
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
    <!-- Add Category Form -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3 bg-white">
            <h2 class="h5 fw-bold mb-3">Add New Category</h2>
            <form action="categories.php" method="POST">
                <input type="hidden" name="action" value="add_category">
                <div class="mb-3">
                    <label for="category_name" class="form-label fw-bold small">Category Name *</label>
                    <input type="text" name="category_name" id="category_name" class="form-control" required placeholder="e.g. Renaissance Statuary">
                </div>
                <div class="mb-3">
                    <label for="meta_title" class="form-label fw-bold small">SEO Meta Title</label>
                    <input type="text" name="meta_title" id="meta_title" class="form-control" placeholder="Max 160 characters">
                </div>
                <div class="mb-3">
                    <label for="meta_description" class="form-label fw-bold small">SEO Meta Description</label>
                    <textarea name="meta_description" id="meta_description" class="form-control" rows="3" placeholder="Max 255 characters"></textarea>
                </div>
                <button type="submit" class="btn btn-dark fw-bold w-100">Add Category</button>
            </form>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0 p-0 bg-white">
            <div class="card-header bg-white py-3">
                <h2 class="h5 mb-0 fw-bold">Existing Categories (<?= count($categories) ?>)</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Slug</th>
                            <th>SEO Title</th>
                            <th>Products</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No categories defined yet. Use the form to add one.</td></tr>
                        <?php else: ?>
                            <?php foreach ($categories as $c): ?>
                                <tr>
                                    <td>#<?= $c['category_id'] ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($c['category_name']) ?></td>
                                    <td><code><?= htmlspecialchars($c['slug']) ?></code></td>
                                    <td class="small text-muted"><?= htmlspecialchars($c['meta_title'] ?: 'Default') ?></td>
                                    <td><span class="badge bg-secondary"><?= $c['product_count'] ?> item(s)</span></td>
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
