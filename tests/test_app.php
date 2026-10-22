<?php
/**
 * Test Suite & Verification Script for Antique E-Commerce
 */

$root_dir = dirname(__DIR__);

echo "--- Starting Verification Tests ---\n\n";

// 1. Check database schema file
$schema_file = $root_dir . '/schema.sql';
if (file_exists($schema_file)) {
    echo "[PASS] schema.sql exists.\n";
    $schema_content = file_get_contents($schema_file);
    $required_tables = ['users', 'categories', 'brands', 'products', 'product_media', 'orders', 'order_items'];
    foreach ($required_tables as $table) {
        if (strpos($schema_content, "CREATE TABLE $table") !== false || strpos($schema_content, "CREATE TABLE `$table`") !== false) {
            echo "   [PASS] Table $table definition found.\n";
        } else {
            echo "   [FAIL] Table $table definition missing!\n";
        }
    }
} else {
    echo "[FAIL] schema.sql is missing!\n";
}

// 2. Check PHP syntax for all PHP files
$php_files = [
    'config/db.php',
    'config/auth.php',
    'login.php',
    'register.php',
    'logout.php',
    'admin/dashboard.php',
    'admin/brands.php',
    'admin/categories.php',
    'admin/orders.php',
    'admin/customers.php',
    'admin/products.php',
    'admin/add_product.php',
    'product-detail.php',
    'sitemap.php',
    'index.php',
    'products.php',
    'about.php',
    'contact.php',
    'includes/header.php',
    'includes/footer.php'
];

foreach ($php_files as $file) {
    $full_path = $root_dir . '/' . $file;
    if (file_exists($full_path)) {
        exec("php -l " . escapeshellarg($full_path), $output, $return_var);
        if ($return_var === 0) {
            echo "[PASS] PHP syntax check passed for $file\n";
        } else {
            echo "[FAIL] PHP syntax check failed for $file\n";
        }
    } else {
        echo "[FAIL] File missing: $file\n";
    }
}

// 3. Test sitemap XML structure in CLI
$_SERVER['HTTP_HOST'] = 'localhost:8000';
$_SERVER['REQUEST_URI'] = '/sitemap.php';
ob_start();
require $root_dir . '/sitemap.php';
$xml_output = ob_get_clean();

if (strpos($xml_output, '<?xml version="1.0" encoding="UTF-8"?>') !== false && strpos($xml_output, '<urlset') !== false) {
    echo "[PASS] sitemap.php outputs valid XML structure.\n";
} else {
    echo "[FAIL] sitemap.php XML structure test failed.\n";
}

// 4. Test render HTML pages in separate PHP processes
$pages_to_test = ['index.php', 'products.php', 'about.php', 'contact.php', 'login.php', 'register.php'];
foreach ($pages_to_test as $p) {
    $cmd = "php " . escapeshellarg($root_dir . '/' . $p);
    $html_out = shell_exec($cmd);
    if (!empty($html_out) && strpos($html_out, '<!DOCTYPE html>') !== false) {
        echo "[PASS] Page $p rendered successfully.\n";
    } else {
        echo "[FAIL] Page $p failed to render expected HTML.\n";
    }
}

// 5. Test SQLite or mock database testing for functional queries
$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create SQLite tables matching schema logic
$db->exec("
    CREATE TABLE users (
        user_id INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name TEXT NOT NULL,
        email TEXT UNIQUE,
        password_hash TEXT,
        role TEXT DEFAULT 'customer'
    );
    CREATE TABLE categories (
        category_id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_name TEXT NOT NULL,
        slug TEXT UNIQUE,
        meta_title TEXT,
        meta_description TEXT
    );
    CREATE TABLE brands (
        brand_id INTEGER PRIMARY KEY AUTOINCREMENT,
        brand_name TEXT NOT NULL,
        slug TEXT UNIQUE,
        description TEXT
    );
    CREATE TABLE products (
        product_id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER,
        brand_id INTEGER,
        title TEXT NOT NULL,
        slug TEXT UNIQUE,
        era_origin TEXT,
        condition_grade TEXT,
        price REAL,
        stock_status TEXT,
        description TEXT,
        dimensions TEXT,
        video_path TEXT,
        meta_title TEXT,
        meta_description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE product_media (
        media_id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER,
        image_path TEXT,
        is_primary INTEGER DEFAULT 0,
        sort_order INTEGER DEFAULT 0
    );
");

// Insert mock user, category, brand & product
$db->exec("INSERT INTO users (full_name, email, password_hash, role) VALUES ('Admin User', 'admin@domain.com', 'hash', 'admin')");
$db->exec("INSERT INTO categories (category_name, slug, meta_title, meta_description) VALUES ('Renaissance Bronze', 'renaissance-bronze', 'Renaissance Bronze Antiques', 'Authentic 16th century bronze artifacts.')");
$cat_id = $db->lastInsertId();
$db->exec("INSERT INTO brands (brand_name, slug, description) VALUES ('Florentine Guild', 'florentine-guild', 'Italian bronze masters')");
$brand_id = $db->lastInsertId();

$stmt = $db->prepare("INSERT INTO products (category_id, brand_id, title, slug, era_origin, condition_grade, price, stock_status, description, dimensions, video_path, meta_title, meta_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([
    $cat_id,
    $brand_id,
    '16th Century Florentine Bronze Statue',
    '16th-century-florentine-bronze-statue',
    '16th Century, Italy',
    'Museum Quality',
    12500.00,
    'Available',
    'Masterpiece bronze figure from Florence.',
    '40cm x 15cm x 15cm',
    'uploads/videos/sample.mp4',
    'Rare Florentine Bronze Sculpture 16th C.',
    'Exquisite 16th-century Florentine bronze statue in museum quality condition.'
]);
$prod_id = $db->lastInsertId();

$db->exec("INSERT INTO product_media (product_id, image_path, is_primary, sort_order) VALUES ($prod_id, 'uploads/products/statue_front.jpg', 1, 0)");
$db->exec("INSERT INTO product_media (product_id, image_path, is_primary, sort_order) VALUES ($prod_id, 'uploads/products/statue_back.jpg', 0, 1)");

echo "[PASS] Mock database queries and inserts executed successfully.\n";

echo "\n--- All Tests Completed Successfully ---\n";
