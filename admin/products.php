<?php
$admin_page_title = "Artifact Inventory | Admin Panel";
require_once __DIR__ . '/includes/sidebar.php';

// Handle Delete Product
$msg = '';
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = (int)$_GET['id'];
    if ($del_id > 0 && $conn) {
        $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE product_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $del_id);
        if (mysqli_stmt_execute($stmt)) {
            $msg = "Artifact #{$del_id} successfully removed.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch All Products
$products = [];
if ($conn) {
    $res = mysqli_query($conn, "
        SELECT p.*, c.category_name, b.brand_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        ORDER BY p.created_at DESC
    ");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $products[] = $row;
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold text-dark mb-0">Artifact Products Inventory</h1>
    <a href="add_product.php" class="btn btn-warning fw-bold">+ Add New 1-of-1 Artifact</a>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card shadow-sm border-0 bg-white">
    <div class="card-header bg-white py-3">
        <h2 class="h5 mb-0 fw-bold">Inventory List (<?= count($products) ?>)</h2>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Brand / Maker</th>
                    <th>Condition</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No products found in database. Click '+ Add New 1-of-1 Artifact' above.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>#<?= $p['product_id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($p['title']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['category_name'] ?: 'None') ?></span></td>
                            <td><?= htmlspecialchars($p['brand_name'] ?: 'Unattributed') ?></td>
                            <td><span class="badge bg-dark"><?= htmlspecialchars($p['condition_grade']) ?></span></td>
                            <td class="fw-bold">$<?= number_format((float)$p['price'], 2) ?></td>
                            <td>
                                <span class="badge <?= $p['stock_status'] === 'Available' ? 'bg-success' : ($p['stock_status'] === 'Reserved' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                    <?= htmlspecialchars($p['stock_status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="../product-detail.php?slug=<?= urlencode($p['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="products.php?action=delete&id=<?= $p['product_id'] ?>" onclick="return confirm('Delete this artifact permanently?')" class="btn btn-sm btn-outline-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
