<?php
$admin_page_title = "Add 1-of-1 Artifact | Admin Panel";
require_once __DIR__ . '/includes/sidebar.php';

$errors = [];
$success_message = '';

// Sticky input variables
$title = '';
$custom_slug = '';
$category_id = null;
$brand_id = null;
$era_origin = '';
$condition_grade = 'Excellent Vintage';
$price = '';
$stock_status = 'Available';
$is_featured = 0;
$dimensions = '';
$description = '';
$meta_title = '';
$meta_description = '';

// Fetch active server upload limits for display
$max_upload_size_str = ini_get('upload_max_filesize');
$max_post_size_str   = ini_get('post_max_size');

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

// Fetch categories & brands for dropdowns
$categories = [];
$brands = [];
if ($conn) {
    $cat_res = mysqli_query($conn, "SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
    if ($cat_res) {
        while ($row = mysqli_fetch_assoc($cat_res)) {
            $categories[] = $row;
        }
    }

    $brand_res = mysqli_query($conn, "SELECT brand_id, brand_name FROM brands ORDER BY brand_name ASC");
    if ($brand_res) {
        while ($row = mysqli_fetch_assoc($brand_res)) {
            $brands[] = $row;
        }
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
        $errors[] = "The total post payload exceeded the server's post_max_size limit ({$max_post_size_str}). Please select smaller image/video files.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $brand_id = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
        $era_origin = trim($_POST['era_origin'] ?? '');
        $condition_grade = trim($_POST['condition_grade'] ?? 'Excellent Vintage');
        $price = trim($_POST['price'] ?? '');
        $stock_status = trim($_POST['stock_status'] ?? 'Available');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $dimensions = trim($_POST['dimensions'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $meta_title = trim($_POST['meta_title'] ?? '');
        $meta_description = trim($_POST['meta_description'] ?? '');
        $custom_slug = trim($_POST['slug'] ?? '');

        if (empty($title)) {
            $errors[] = "Title is required. Please type an artifact title into the field.";
        }

        $valid_conditions = ['Museum Quality', 'Excellent Vintage', 'Patina-Rich / Weathered'];
        if (!in_array($condition_grade, $valid_conditions)) {
            $condition_grade = 'Excellent Vintage';
        }

        $valid_stock = ['Available', 'Reserved', 'Sold'];
        if (!in_array($stock_status, $valid_stock)) {
            $stock_status = 'Available';
        }

        $price_num = (float)$price;

        $slug = !empty($custom_slug) ? create_slug($custom_slug) : create_slug($title);
        if (empty($slug)) {
            $slug = 'artifact-' . time();
        }

        // Video Upload Handler
        $video_path = null;
        if (isset($_FILES['product_video']) && $_FILES['product_video']['error'] !== UPLOAD_ERR_NO_FILE) {
            $vid_error = $_FILES['product_video']['error'];
            if ($vid_error === UPLOAD_ERR_INI_SIZE || $vid_error === UPLOAD_ERR_FORM_SIZE) {
                $errors[] = "The video file exceeds the maximum allowed upload_max_filesize limit ({$max_upload_size_str}).";
            } elseif ($vid_error === UPLOAD_ERR_OK) {
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
                        $errors[] = "Failed to move video file.";
                    }
                } else {
                    $errors[] = "Invalid video format. Allowed formats: MP4, MOV.";
                }
            } else {
                $errors[] = "Video upload failed with code: " . $vid_error;
            }
        }

        // Image Upload Errors Check
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['error'] as $i_idx => $i_err) {
                if ($i_err === UPLOAD_ERR_INI_SIZE || $i_err === UPLOAD_ERR_FORM_SIZE) {
                    $errors[] = "Image #" . ($i_idx + 1) . " (" . htmlspecialchars($_FILES['images']['name'][$i_idx]) . ") exceeds server limit ({$max_upload_size_str}).";
                }
            }
        }

        if (empty($errors)) {
            if (!$conn) {
                $errors[] = "Database connection unavailable.";
            } else {
                // Unique slug check
                $slug_check_stmt = mysqli_prepare($conn, "SELECT product_id FROM products WHERE slug = ?");
                mysqli_stmt_bind_param($slug_check_stmt, "s", $slug);
                mysqli_stmt_execute($slug_check_stmt);
                mysqli_stmt_store_result($slug_check_stmt);
                if (mysqli_stmt_num_rows($slug_check_stmt) > 0) {
                    $slug = $slug . '-' . time();
                }
                mysqli_stmt_close($slug_check_stmt);

                // Handle NULL foreign keys safely
                $cat_sql = ($category_id !== null) ? (int)$category_id : "NULL";
                $brand_sql = ($brand_id !== null) ? (int)$brand_id : "NULL";

                $insert_query = "INSERT INTO products
                    (category_id, brand_id, title, slug, era_origin, condition_grade, price, stock_status, is_featured, description, dimensions, video_path, meta_title, meta_description)
                    VALUES ({$cat_sql}, {$brand_sql}, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = mysqli_prepare($conn, $insert_query);

                if ($stmt) {
                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssssdsisssss",
                        $title,
                        $slug,
                        $era_origin,
                        $condition_grade,
                        $price_num,
                        $stock_status,
                        $is_featured,
                        $description,
                        $dimensions,
                        $video_path,
                        $meta_title,
                        $meta_description
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        $product_id = mysqli_insert_id($conn);
                        mysqli_stmt_close($stmt);

                        // Multi-Image Upload Handler
                        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                            $allowed_img_exts = ['jpg', 'jpeg', 'png', 'webp'];
                            $allowed_img_mimes = ['image/jpeg', 'image/png', 'image/webp'];

                            $file_count = count($_FILES['images']['name']);
                            $is_primary = 1;

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

                                            $is_primary = 0;
                                        }
                                    }
                                }
                            }
                        }

                        $success_message = "1-of-1 Artifact published successfully! Slug: " . htmlspecialchars($slug);
                        // Reset sticky inputs on success
                        $title = $custom_slug = $era_origin = $price = $dimensions = $description = $meta_title = $meta_description = '';
                        $category_id = $brand_id = null;
                        $condition_grade = 'Excellent Vintage';
                        $stock_status = 'Available';
                        $is_featured = 0;
                    } else {
                        $errors[] = "Execution error: " . mysqli_error($conn);
                    }
                } else {
                    $errors[] = "Statement prepare error: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold text-dark mb-0">Publish New 1-of-1 Artifact</h1>
    <a href="products.php" class="btn btn-outline-secondary btn-sm">View Inventory</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Errors:</strong>
        <ul class="mb-0 mt-1">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= $success_message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card shadow-sm border-0 p-4 bg-white">
    <form action="add_product.php" method="POST" enctype="multipart/form-data">
        <div class="row g-3">

            <div class="col-md-8">
                <label for="title" class="form-label fw-bold small">Artifact Title *</label>
                <input type="text" class="form-control" id="title" name="title" required value="<?= htmlspecialchars($title) ?>" placeholder="e.g. 18th Century Bronze Dynasty Vessel">
            </div>

            <div class="col-md-4">
                <label for="slug" class="form-label fw-bold small">URL Slug (Optional)</label>
                <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($custom_slug) ?>" placeholder="auto-generated-if-blank">
            </div>

            <div class="col-md-4">
                <label for="category_id" class="form-label fw-bold small">Category</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>" <?= ($category_id === (int)$cat['category_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="brand_id" class="form-label fw-bold small">Brand / Maker Guild</label>
                <select class="form-select" id="brand_id" name="brand_id">
                    <option value="">-- Select Brand / Maker --</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= $b['brand_id'] ?>" <?= ($brand_id === (int)$b['brand_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['brand_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="era_origin" class="form-label fw-bold small">Era / Origin</label>
                <input type="text" class="form-control" id="era_origin" name="era_origin" value="<?= htmlspecialchars($era_origin) ?>" placeholder="e.g. 19th Century, France">
            </div>

            <div class="col-md-4">
                <label for="condition_grade" class="form-label fw-bold small">Condition Grade *</label>
                <select class="form-select" id="condition_grade" name="condition_grade" required>
                    <option value="Museum Quality" <?= ($condition_grade === 'Museum Quality') ? 'selected' : '' ?>>Museum Quality</option>
                    <option value="Excellent Vintage" <?= ($condition_grade === 'Excellent Vintage') ? 'selected' : '' ?>>Excellent Vintage</option>
                    <option value="Patina-Rich / Weathered" <?= ($condition_grade === 'Patina-Rich / Weathered') ? 'selected' : '' ?>>Patina-Rich / Weathered</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="price" class="form-label fw-bold small">Price ($) *</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" required value="<?= htmlspecialchars($price) ?>" placeholder="0.00">
            </div>

            <div class="col-md-4">
                <label for="stock_status" class="form-label fw-bold small">1-of-1 Stock Status *</label>
                <select class="form-select" id="stock_status" name="stock_status" required>
                    <option value="Available" <?= ($stock_status === 'Available') ? 'selected' : '' ?>>Available</option>
                    <option value="Reserved" <?= ($stock_status === 'Reserved') ? 'selected' : '' ?>>Reserved</option>
                    <option value="Sold" <?= ($stock_status === 'Sold') ? 'selected' : '' ?>>Sold</option>
                </select>
            </div>

            <!-- Featured Checkbox -->
            <div class="col-md-12">
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" <?= $is_featured ? 'checked' : '' ?>>
                    <label class="form-check-label fw-bold" for="is_featured">⭐ Set as Featured (Display in Homepage Hero Sliding Banner)</label>
                </div>
            </div>

            <div class="col-md-12">
                <label for="dimensions" class="form-label fw-bold small">Dimensions</label>
                <input type="text" class="form-control" id="dimensions" name="dimensions" value="<?= htmlspecialchars($dimensions) ?>" placeholder="e.g. 45cm H x 30cm W x 20cm D">
            </div>

            <div class="col-md-12">
                <label for="description" class="form-label fw-bold small">Description & Provenance</label>
                <textarea class="form-control" id="description" name="description" rows="4" placeholder="Detailed historical overview..."><?= htmlspecialchars($description) ?></textarea>
            </div>

            <div class="col-md-6">
                <label for="images" class="form-label fw-bold small">Multi-Image Gallery Upload</label>
                <input type="file" class="form-control" id="images" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp">
                <div class="form-text small">Max file size limit per upload: <strong><?= htmlspecialchars($max_upload_size_str) ?></strong>. First image is primary.</div>
            </div>

            <div class="col-md-6">
                <label for="product_video" class="form-label fw-bold small">Walkthrough Inspection Video (Optional)</label>
                <input type="file" class="form-control" id="product_video" name="product_video" accept=".mp4,.mov">
                <div class="form-text small">MP4 or MOV video file (Max file size: <strong><?= htmlspecialchars($max_upload_size_str) ?></strong>).</div>
            </div>

            <div class="col-12 mt-4"><h3 class="h6 border-bottom pb-2 fw-bold text-uppercase text-muted">SEO Search Engine Metadata</h3></div>

            <div class="col-md-6">
                <label for="meta_title" class="form-label fw-bold small">SEO Meta Title</label>
                <input type="text" class="form-control" id="meta_title" name="meta_title" maxlength="160" value="<?= htmlspecialchars($meta_title) ?>">
            </div>

            <div class="col-md-6">
                <label for="meta_description" class="form-label fw-bold small">SEO Meta Description</label>
                <input type="text" class="form-control" id="meta_description" name="meta_description" maxlength="255" value="<?= htmlspecialchars($meta_description) ?>">
            </div>

            <div class="col-12 text-end mt-4">
                <button type="submit" class="btn btn-dark btn-lg px-5 fw-bold">Publish 1-of-1 Artifact</button>
            </div>

        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
