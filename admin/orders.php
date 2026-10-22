<?php
$admin_page_title = "Manage Customer Orders | Admin Panel";
require_once __DIR__ . '/includes/sidebar.php';

// Handle Order Status Update
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $status   = trim($_POST['status'] ?? 'Pending');

    if ($order_id > 0 && in_array($status, ['Pending', 'Completed', 'Cancelled'])) {
        if ($conn) {
            $stmt = mysqli_prepare($conn, "UPDATE orders SET order_status = ? WHERE order_id = ?");
            mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
            if (mysqli_stmt_execute($stmt)) {
                $msg = "Order #{$order_id} status updated to {$status}.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Fetch Orders
$orders = [];
if ($conn) {
    $res = mysqli_query($conn, "
        SELECT o.*, u.full_name as user_name
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        ORDER BY o.created_at DESC
    ");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $orders[] = $row;
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold text-dark mb-0">Customer Orders & Transactions</h1>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card shadow-sm border-0 bg-white">
    <div class="card-header bg-white py-3">
        <h2 class="h5 mb-0 fw-bold">Order History (<?= count($orders) ?>)</h2>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Total Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end">Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No customer orders recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>#<?= $o['order_id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($o['customer_name']) ?></td>
                            <td><?= htmlspecialchars($o['customer_email']) ?></td>
                            <td class="fw-bold text-primary">$<?= number_format((float)$o['total_amount'], 2) ?></td>
                            <td class="small text-muted"><?= htmlspecialchars($o['created_at']) ?></td>
                            <td>
                                <span class="badge <?= $o['order_status'] === 'Completed' ? 'bg-success' : ($o['order_status'] === 'Pending' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                    <?= htmlspecialchars($o['order_status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <form action="orders.php" method="POST" class="d-inline-flex gap-1">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="Pending" <?= $o['order_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="Completed" <?= $o['order_status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="Cancelled" <?= $o['order_status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-dark">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
