<?php
require_once __DIR__ . '/config/auth.php';

$error = '';
$success = '';

if (isset($_GET['error']) && $_GET['error'] === 'admin_required') {
    $error = "Admin access required. Please sign in with an admin account.";
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        if ($conn) {
            $stmt = mysqli_prepare($conn, "SELECT user_id, full_name, email, password_hash, role FROM users WHERE email = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($res && $user = mysqli_fetch_assoc($res)) {
                    if (password_verify($password, $user['password_hash'])) {
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];

                        if ($user['role'] === 'admin') {
                            header("Location: admin/dashboard.php");
                        } else {
                            header("Location: index.php");
                        }
                        exit;
                    } else {
                        $error = "Invalid password.";
                    }
                } else {
                    $error = "No account found with that email address.";
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            $error = "Database connection unavailable.";
        }
    }
}

$page_title = "Sign In | Antique Artifact Gallery";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm p-4 bg-white">
                <h1 class="h4 header-brand text-center mb-3">Sign In</h1>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold small">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" required placeholder="admin@domain.com">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold small">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn btn-dark w-100 fw-bold py-2 mb-3">Sign In</button>

                    <div class="text-center small">
                        Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
