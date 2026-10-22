<?php
require_once __DIR__ . '/config/db.php';

echo "--- Seeding Patina Decor Database ---\n\n";

if (!$conn) {
    echo "Database connection failed. Ensure MySQL is running or DB credentials are set in environment.\n";
    exit(1);
}

// 1. Create Default Admin User
$admin_email = 'admin@patinadecor.com';
$admin_pass  = 'admin123';
$admin_hash  = password_hash($admin_pass, PASSWORD_DEFAULT);
$admin_name  = 'Lead Curator';

$stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'admin') ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sss", $admin_name, $admin_email, $admin_hash);
    if (mysqli_stmt_execute($stmt)) {
        echo "[SUCCESS] Default Admin account created / updated:\n";
        echo "          Email:    {$admin_email}\n";
        echo "          Password: {$admin_pass}\n";
        echo "          Role:     admin\n\n";
    }
    mysqli_stmt_close($stmt);
}

// 2. Create Default Customer User
$cust_email = 'collector@patinadecor.com';
$cust_pass  = 'customer123';
$cust_hash  = password_hash($cust_pass, PASSWORD_DEFAULT);
$cust_name  = 'Lord Harrington';

$stmt2 = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'customer') ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)");
if ($stmt2) {
    mysqli_stmt_bind_param($stmt2, "sss", $cust_name, $cust_email, $cust_hash);
    if (mysqli_stmt_execute($stmt2)) {
        echo "[SUCCESS] Default Collector account created / updated:\n";
        echo "          Email:    {$cust_email}\n";
        echo "          Password: {$cust_pass}\n";
        echo "          Role:     customer\n\n";
    }
    mysqli_stmt_close($stmt2);
}

// 3. Seed Sample Categories
$categories = [
    ['Framed Deities', 'framed-deities', 'Framed Deities & Wall Shrine Relics', 'Hand-finished brass deity panels framed in velvet.'],
    ['Bells & Lamps', 'bells-lamps', 'Hanging Temple Bells & Antique Lamps', 'Hanging temple bells and brass lamps.'],
    ['Brass Masks', 'brass-masks', 'Authentic Handcrafted Brass Masks', 'Tribal and traditional Kathakali brass masks.'],
    ['Animals & Figures', 'animals-figures', 'Antique Brass Animals & Figures', 'Brass Nandi, peacocks, and shrine figures.']
];

foreach ($categories as $cat) {
    $stmt_c = mysqli_prepare($conn, "INSERT INTO categories (category_name, slug, meta_title, meta_description) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE category_name = VALUES(category_name)");
    if ($stmt_c) {
        mysqli_stmt_bind_param($stmt_c, "ssss", $cat[0], $cat[1], $cat[2], $cat[3]);
        mysqli_stmt_execute($stmt_c);
        mysqli_stmt_close($stmt_c);
    }
}
echo "[SUCCESS] Sample categories seeded.\n";

// 4. Seed Sample Brand / Guild
$brand_name = 'Kerala Craftsmen Guild';
$brand_slug = 'kerala-craftsmen-guild';
$brand_desc = 'Traditional South Indian brass casting guild dating back centuries.';

$stmt_b = mysqli_prepare($conn, "INSERT INTO brands (brand_name, slug, description) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE brand_name = VALUES(brand_name)");
if ($stmt_b) {
    mysqli_stmt_bind_param($stmt_b, "sss", $brand_name, $brand_slug, $brand_desc);
    mysqli_stmt_execute($stmt_b);
    mysqli_stmt_close($stmt_b);
}
echo "[SUCCESS] Sample brand seeded.\n";

echo "\n--- Seeding Complete ---\n";
