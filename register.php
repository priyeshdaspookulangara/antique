<?php
require_once __DIR__ . '/config/auth.php';

$error = '';
$success = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = isset($_POST['role']) && $_POST['role'] === 'admin' ? 'admin' : 'customer';

    if (empty($full_name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        if ($conn) {
            // Check existing
            $check = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ? LIMIT 1");
            mysqli_stmt_bind_param($check, "s", $email);
            mysqli_stmt_execute($check);
            mysqli_stmt_store_result($check);
            if (mysqli_stmt_num_rows($check) > 0) {
                $error = "An account with this email already exists.";
            } else {
                mysqli_stmt_close($check);

                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ssss", $full_name, $email, $hash, $role);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Account created successfully! You can now <a href='login.php'>sign in</a>.";
                } else {
                    $error = "Registration failed: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            $error = "Database connection unavailable.";
        }
    }
}

$page_title = "Register Account | Antique Artifact Gallery";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm p-4 bg-white">
                <h1 class="h4 header-brand text-center mb-3">Create Collector Account</h1>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success py-2 small"><?= $success ?></div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="mb-3">
                        <label for="full_name" class="form-label fw-bold small">Full Name *</label>
                        <input type="text" name="full_name" id="full_name" class="form-control" required placeholder="Lord / Lady Name">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold small">Email Address *</label>
                        <input type="email" name="email" id="email" class="form-control" required placeholder="collector@domain.com">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold small">Password *</label>
                        <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label fw-bold small">Account Role</label>
                        <select name="role" id="role" class="form-select form-select-sm">
                            <option value="customer" selected>Collector / Customer</option>
                            <option value="admin">Administrator / Curator</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-dark w-100 fw-bold py-2 mb-3">Register Account</button>

                    <div class="text-center small">
                        Already have an account? <a href="login.php" class="text-decoration-none">Sign in</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
