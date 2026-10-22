-- Antique Artifacts 1-of-1 E-Commerce Database Schema
-- Engine: InnoDB
-- Charset: utf8mb4

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS product_media;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Categories Table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    meta_title VARCHAR(160) DEFAULT NULL,
    meta_description VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Products Table
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT DEFAULT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    era_origin VARCHAR(100) DEFAULT NULL,
    condition_grade ENUM('Museum Quality', 'Excellent Vintage', 'Patina-Rich / Weathered') NOT NULL DEFAULT 'Excellent Vintage',
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock_status ENUM('Available', 'Reserved', 'Sold') NOT NULL DEFAULT 'Available',
    description TEXT DEFAULT NULL,
    dimensions VARCHAR(100) DEFAULT NULL,
    video_path VARCHAR(255) DEFAULT NULL,
    meta_title VARCHAR(160) DEFAULT NULL,
    meta_description VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id)
        REFERENCES categories(category_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Product Media Table
CREATE TABLE product_media (
    media_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_media_product FOREIGN KEY (product_id)
        REFERENCES products(product_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Orders Table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    order_status ENUM('Pending', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Order Items Table
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id)
        REFERENCES orders(order_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id)
        REFERENCES products(product_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
