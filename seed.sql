-- Patina Decor Sample Database Seed DDL
-- Pre-populated Default Credentials & Taxonomy

-- 1. Default Administrator Account
-- Password for admin@patinadecor.com is: admin123
INSERT INTO users (full_name, email, password_hash, role) VALUES
('Lead Curator', 'admin@patinadecor.com', '$2y$10$f90z6eZz4Wz2G90E0eO5eeF8qK/3Z4M0a8d5f3s1q2e3r4t5y6u7i', 'admin')
ON DUPLICATE KEY UPDATE email = VALUES(email);

-- 2. Default Registered Collector Account
-- Password for collector@patinadecor.com is: customer123
INSERT INTO users (full_name, email, password_hash, role) VALUES
('Lord Harrington', 'collector@patinadecor.com', '$2y$10$f90z6eZz4Wz2G90E0eO5eeF8qK/3Z4M0a8d5f3s1q2e3r4t5y6u7i', 'customer')
ON DUPLICATE KEY UPDATE email = VALUES(email);

-- 3. Sample Categories
INSERT INTO categories (category_name, slug, meta_title, meta_description) VALUES
('Framed Deities', 'framed-deities', 'Framed Deities & Wall Shrine Relics', 'Hand-finished brass deity panels framed in velvet.'),
('Bells & Lamps', 'bells-lamps', 'Hanging Temple Bells & Antique Lamps', 'Hanging temple bells and brass lamps.'),
('Brass Masks', 'brass-masks', 'Authentic Handcrafted Brass Masks', 'Tribal and traditional Kathakali brass masks.'),
('Animals & Figures', 'animals-figures', 'Antique Brass Animals & Figures', 'Brass Nandi, peacocks, and shrine figures.')
ON DUPLICATE KEY UPDATE category_name = VALUES(category_name);

-- 4. Sample Brand / Guild
INSERT INTO brands (brand_name, slug, description) VALUES
('Kerala Craftsmen Guild', 'kerala-craftsmen-guild', 'Traditional South Indian brass casting guild dating back centuries.')
ON DUPLICATE KEY UPDATE brand_name = VALUES(brand_name);
