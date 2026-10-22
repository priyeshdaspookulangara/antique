<?php
$admin_page_title = "Manage Customers & Users | Admin Panel";
require_once __DIR__ . '/includes/sidebar.php';

// Fetch Users
$users = [];
if ($conn) {
    $res = mysqli_query($conn, "SELECT user_id, full_name, email, role, created_at FROM users ORDER BY created_at DESC");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $users[] = $row;
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold text-dark mb-0">Registered Customers & Administrators</h1>
</div>

<div class="card shadow-sm border-0 bg-white">
    <div class="card-header bg-white py-3">
        <h2 class="h5 mb-0 fw-bold">User Directory (<?= count($users) ?>)</h2>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>User ID</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Role</th>
                    <th>Registered Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No registered users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>#<?= $u['user_id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?>">
                                    <?= htmlspecialchars($u['role']) ?>
                                </span>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($u['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
