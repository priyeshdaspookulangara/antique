<?php
require_once __DIR__ . '/../config/db.php';

$errors = [];
$success_message = '';

// Helper function to create slug
function create_slug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

// Ensure upload directories exist
$upload_img_dir = __DIR__ . '/../uploads/products/';
$upload_vid_dir = __DIR__ . '/../uploads/videos/';

if (!file_exists($upload_img_dir)) {
    @mkdir($upload_img_dir, 0755, true);
}
if (!file_exists($upload_vid_dir)) {
    @mkdir($upload_vid_dir, 0755, true);
}

// Fetch categories for dropdown
$categories = [];
if ($conn) {
    $cat_res = mysqli_query($conn, "SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
    if ($cat_res) {
        while ($row = mysqli_fetch_assoc($cat_res)) {
            $categories[] = $row;
        }
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $era_origin = trim($_POST['era_origin'] ?? '');
    $condition_grade = trim($_POST['condition_grade'] ?? 'Excellent Vintage');
    $price = (float)($_POST['price'] ?? 0.00);
    $stock_status = trim($_POST['stock_status'] ?? 'Available');
    $dimensions = trim($_POST['dimensions'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $custom_slug = trim($_POST['slug'] ?? '');

    // Validation
    if (empty($title)) {
        $errors[] = "Title is required.";
    }

    $valid_conditions = ['Museum Quality', 'Excellent Vintage', 'Patina-Rich / Weathered'];
    if (!in_array($condition_grade, $valid_conditions)) {
        $condition_grade = 'Excellent Vintage';
    }

    $valid_stock = ['Available', 'Reserved', 'Sold'];
    if (!in_array($stock_status, $valid_stock)) {
        $stock_status = 'Available';
    }

    // Slug logic
    $slug = !empty($custom_slug) ? create_slug($custom_slug) : create_slug($title);
    if (empty($slug)) {
        $slug = 'artifact-' . time();
    }

    // Handle Optional Video Upload
    $video_path = null;
    if (isset($_FILES['product_video']) && $_FILES['product_video']['error'] === UPLOAD_ERR_OK) {
        $vid_tmp = $_FILES['product_video']['tmp_name'];
        $vid_name = $_FILES['product_video']['name'];
        $vid_ext = strtolower(pathinfo($vid_name, PATHINFO_EXTENSION));
        $allowed_vid_exts = ['mp4', 'mov'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $vid_tmp);
        finfo_close($finfo);

        $allowed_vid_mimes = ['video/mp4', 'video/quicktime'];

        if (in_array($vid_ext, $allowed_vid_exts) && in_array($mime, $allowed_vid_mimes)) {
            $new_vid_name = 'vid_' . uniqid() . '_' . time() . '.' . $vid_ext;
            $target_vid_file = $upload_vid_dir . $new_vid_name;

            if (move_uploaded_file($vid_tmp, $target_vid_file)) {
                $video_path = 'uploads/videos/' . $new_vid_name;
            } else {
                $errors[] = "Failed to upload video file.";
            }
        } else {
            $errors[] = "Invalid video format. Allowed formats: MP4, MOV.";
        }
    }

    // Insert Product if no critical errors so far
    if (empty($errors)) {
        if (!$conn) {
            $errors[] = "Database connection unavailable.";
        } else {
            // Check for duplicate slug
            $slug_check_stmt = mysqli_prepare($conn, "SELECT product_id FROM products WHERE slug = ?");
            mysqli_stmt_bind_param($slug_check_stmt, "s", $slug);
            mysqli_stmt_execute($slug_check_stmt);
            mysqli_stmt_store_result($slug_check_stmt);
            if (mysqli_stmt_num_rows($slug_check_stmt) > 0) {
                $slug = $slug . '-' . time();
            }
            mysqli_stmt_close($slug_check_stmt);

            $stmt = mysqli_prepare($conn, "INSERT INTO products
                (category_id, title, slug, era_origin, condition_grade, price, stock_status, description, dimensions, video_path, meta_title, meta_description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if ($stmt) {
                mysqli_stmt_bind_param(
                    $stmt,
                    "issssdssssss",
                    $category_id,
                    $title,
                    $slug,
                    $era_origin,
                    $condition_grade,
                    $price,
                    $stock_status,
                    $description,
                    $dimensions,
                    $video_path,
                    $meta_title,
                    $meta_description
                );

                if (mysqli_stmt_execute($stmt)) {
                    $product_id = mysqli_insert_id($conn);
                    mysqli_stmt_close($stmt);

                    // Process Multi-Image Uploads
                    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                        $allowed_img_exts = ['jpg', 'jpeg', 'png', 'webp'];
                        $allowed_img_mimes = ['image/jpeg', 'image/png', 'image/webp'];

                        $file_count = count($_FILES['images']['name']);
                        $is_primary = 1; // First image uploaded is set as primary

                        for ($i = 0; $i < $file_count; $i++) {
                            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                                $img_tmp = $_FILES['images']['tmp_name'][$i];
                                $img_name = $_FILES['images']['name'][$i];
                                $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));

                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                $mime = finfo_file($finfo, $img_tmp);
                                finfo_close($finfo);

                                if (in_array($img_ext, $allowed_img_exts) && in_array($mime, $allowed_img_mimes)) {
                                    $new_img_name = 'prod_' . $product_id . '_' . uniqid() . '_' . $i . '.' . $img_ext;
                                    $target_img_file = $upload_img_dir . $new_img_name;

                                    if (move_uploaded_file($img_tmp, $target_img_file)) {
                                        $db_img_path = 'uploads/products/' . $new_img_name;
                                        $sort_order = $i;

                                        $img_stmt = mysqli_prepare($conn, "INSERT INTO product_media (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                                        if ($img_stmt) {
                                            mysqli_stmt_bind_param($img_stmt, "isii", $product_id, $db_img_path, $is_primary, $sort_order);
                                            mysqli_stmt_execute($img_stmt);
                                            mysqli_stmt_close($img_stmt);
                                        }

                                        // Only the very first processed image gets is_primary = 1
                                        $is_primary = 0;
                                    }
                                }
                            }
                        }
                    }

                    $success_message = "Artifact product successfully added! Slug: " . htmlspecialchars($slug);
                } else {
                    $errors[] = "Error executing query: " . mysqli_error($conn);
                }
            } else {
                $errors[] = "Error preparing statement: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add Artifact Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Georgia', serif; }
        .admin-header { background-color: #2c221e; color: #f4e8d1; padding: 20px 0; }
        .card { border-color: #d1c7bd; }
        .btn-primary { background-color: #8c2d19; border-color: #8c2d19; }
        .btn-primary:hover { background-color: #6a2112; border-color: #6a2112; }
    </style>
</head>
<body>

<header class="admin-header mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0">Artifact Repository Admin</h1>
        <a href="../sitemap.php" class="btn btn-outline-light btn-sm" target="_blank">View XML Sitemap</a>
    </div>
</header>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h2 class="h5 mb-0 text-dark">Add New 1-of-1 Artifact Product</h2>
                </div>
                <div class="card-body p-4">

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Errors encountered:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $success_message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="add_product.php" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">

                            <!-- Title & Slug -->
                            <div class="col-md-8">
                                <label for="title" class="form-label fw-bold">Artifact Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required placeholder="e.g., 18th Century Bronze Dynasty Vessel">
                            </div>
                            <div class="col-md-4">
                                <label for="slug" class="form-label fw-bold">URL Slug (Optional)</label>
                                <input type="text" class="form-control" id="slug" name="slug" placeholder="auto-generated-if-blank">
                            </div>

                            <!-- Category & Era -->
                            <div class="col-md-6">
                                <label for="category_id" class="form-label fw-bold">Category</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="era_origin" class="form-label fw-bold">Era / Origin</label>
                                <input type="text" class="form-control" id="era_origin" name="era_origin" placeholder="e.g., 19th Century, France">
                            </div>

                            <!-- Condition, Price, Stock -->
                            <div class="col-md-4">
                                <label for="condition_grade" class="form-label fw-bold">Condition Grade *</label>
                                <select class="form-select" id="condition_grade" name="condition_grade" required>
                                    <option value="Museum Quality">Museum Quality</option>
                                    <option value="Excellent Vintage" selected>Excellent Vintage</option>
                                    <option value="Patina-Rich / Weathered">Patina-Rich / Weathered</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="price" class="form-label fw-bold">Price ($) *</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" required placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label for="stock_status" class="form-label fw-bold">1-of-1 Stock Status *</label>
                                <select class="form-select" id="stock_status" name="stock_status" required>
                                    <option value="Available" selected>Available</option>
                                    <option value="Reserved">Reserved</option>
                                    <option value="Sold">Sold</option>
                                </select>
                            </div>

                            <!-- Dimensions & Description -->
                            <div class="col-md-12">
                                <label for="dimensions" class="form-label fw-bold">Dimensions</label>
                                <input type="text" class="form-control" id="dimensions" name="dimensions" placeholder="e.g., 45cm H x 30cm W x 20cm D">
                            </div>

                            <div class="col-md-12">
                                <label for="description" class="form-label fw-bold">Artifact Description</label>
                                <textarea class="form-control" id="description" name="description" rows="5" placeholder="Detailed historical provenance and aesthetic qualities..."></textarea>
                            </div>

                            <!-- File Uploads -->
                            <div class="col-md-6">
                                <label for="images" class="form-label fw-bold">Product Media Images (Multiple)</label>
                                <input type="file" class="form-control" id="images" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp">
                                <div class="form-text">Upload JPG, PNG, or WebP. First image uploaded becomes primary.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="product_video" class="form-label fw-bold">Inspection Video (Optional)</label>
                                <input type="file" class="form-control" id="product_video" name="product_video" accept=".mp4,.mov">
                                <div class="form-text">MP4 or MOV video file for 360-degree artifact inspection.</div>
                            </div>

                            <!-- SEO Meta Section -->
                            <div class="col-12 mt-4">
                                <h3 class="h6 border-bottom pb-2 fw-bold text-uppercase text-muted">SEO Search Engine Metadata</h3>
                            </div>

                            <div class="col-md-6">
                                <label for="meta_title" class="form-label fw-bold">SEO Meta Title</label>
                                <input type="text" class="form-control" id="meta_title" name="meta_title" maxlength="160" placeholder="Max 160 characters">
                            </div>

                            <div class="col-md-6">
                                <label for="meta_description" class="form-label fw-bold">SEO Meta Description</label>
                                <input type="text" class="form-control" id="meta_description" name="meta_description" maxlength="255" placeholder="Max 255 characters">
                            </div>

                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-primary btn-lg px-5">Publish 1-of-1 Artifact</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
