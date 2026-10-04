-- ============================================================
-- PINORA NOSE PINS DATABASE INSERT SCRIPT
-- Generated: 2026-10-04 21:15:12
-- ============================================================

START TRANSACTION;

-- 1. Parent Category: Nose Pins
INSERT INTO categories (name, slug, parent_id, sort_order, is_active, created_at, updated_at)
SELECT 'Nose Pins', 'nose-pins', NULL, 1, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'nose-pins');

SET @nose_pins_id = (SELECT id FROM categories WHERE slug = 'nose-pins' LIMIT 1);

-- 2. Subcategories
INSERT INTO categories (name, slug, parent_id, sort_order, is_active, created_at, updated_at)
SELECT 'Daily Wear', 'daily-wear', @nose_pins_id, 1, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'daily-wear');

INSERT INTO categories (name, slug, parent_id, sort_order, is_active, created_at, updated_at)
SELECT 'Floral Designs', 'floral', @nose_pins_id, 2, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'floral');

INSERT INTO categories (name, slug, parent_id, sort_order, is_active, created_at, updated_at)
SELECT 'Studded', 'studded', @nose_pins_id, 3, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'studded');

INSERT INTO categories (name, slug, parent_id, sort_order, is_active, created_at, updated_at)
SELECT 'Premium Heritage', 'premium', @nose_pins_id, 4, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'premium');

-- 3. Select Vendor and Admin User
SET @vendor_id = COALESCE((SELECT id FROM vendors WHERE store_slug = 'pinora' LIMIT 1), (SELECT id FROM vendors ORDER BY id ASC LIMIT 1));
SET @created_by = (SELECT id FROM users ORDER BY id ASC LIMIT 1);

-- 4. Insert / Update Products, Images, Variants & Reviews
-- Product: Floral Gold Nose Pin
INSERT INTO products (vendor_id, created_by, category_id, name, slug, description, short_description, sku, metal_type, purity, weight_grams, making_charges, making_charges_type, stone_type, stone_weight_carats, stone_quality, certification_type, certification_number, base_price, is_featured, is_new_arrival, stock_quantity, status, created_at, updated_at)
VALUES (@vendor_id, @created_by, (SELECT id FROM categories WHERE slug = 'floral' LIMIT 1), 'Floral Gold Nose Pin', 'floral-gold-nose-pin', 'Crafted with pure 22K yellow gold, the Floral Gold Nose Pin celebrates the eternal elegance of nature. Featuring six meticulously cut petals adorned with brilliant American diamonds surrounding a radiant central core. Comes with an ultra-comfortable screw-wire back designed specifically for sensitive daily wear. 100% BIS hallmarked in Rajkot.', 'Exquisite 22K yellow gold floral nose pin with sparkling center stone and precision prong setting.', 'NP-22K-FLR-001', 'gold', '22K', 0.45, 1250, 'fixed', 'CZ / American Diamond', 0.08, 'AAA Grade', 'bis_hallmark', 'BIS-HM-916-8450', 8450, 1, 0, 25, 'active', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), short_description = VALUES(short_description), base_price = VALUES(base_price), is_featured = VALUES(is_featured), is_new_arrival = VALUES(is_new_arrival), status = 'active', updated_at = NOW();

SET @pid = (SELECT id FROM products WHERE slug = 'floral-gold-nose-pin' LIMIT 1);

DELETE FROM product_images WHERE product_id = @pid;
INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order, created_at, updated_at) VALUES (@pid, 'products/nose-pins/pdp-main-1.jpg', 'Floral Gold Nose Pin Plinth View', 1, 0, NOW(), NOW());
INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order, created_at, updated_at) VALUES (@pid, 'products/nose-pins/pdp-thumb-2.jpg', 'Floral Gold Nose Pin Side Profile', 0, 1, NOW(), NOW());
INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order, created_at, updated_at) VALUES (@pid, 'products/nose-pins/pdp-thumb-3.jpg', 'Floral Gold Nose Pin Top Angle', 0, 2, NOW(), NOW());
INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order, created_at, updated_at) VALUES (@pid, 'products/nose-pins/pdp-thumb-4.jpg', 'Floral Gold Nose Pin On Model', 0, 3, NOW(), NOW());
DELETE FROM product_variants WHERE product_id = @pid;
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Small', 'NP-22K-FLR-001-S', 10, 0, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Medium', 'NP-22K-FLR-001-M', 10, 1, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Large', 'NP-22K-FLR-001-L', 10, 2, 1, NOW(), NOW());
INSERT INTO reviews (product_id, user_id, rating, title, body, status, is_verified_purchase, created_at, updated_at)
SELECT @pid, (SELECT id FROM users ORDER BY id ASC LIMIT 1), 5, 'Stunning shine and very comfortable fitting!', 'I was looking for a daily wear 22K gold nose pin from Rajkot. The floral finish is breathtaking and does not hurt the nose piercing even overnight.', 'approved', 1, NOW(), NOW()
ON DUPLICATE KEY UPDATE rating = VALUES(rating), title = VALUES(title), body = VALUES(body), status = 'approved';

-- Product: Classic Diamond Nose Pin
INSERT INTO products (vendor_id, created_by, category_id, name, slug, description, short_description, sku, metal_type, purity, weight_grams, making_charges, making_charges_type, stone_type, stone_weight_carats, stone_quality, certification_type, certification_number, base_price, is_featured, is_new_arrival, stock_quantity, status, created_at, updated_at)
VALUES (@vendor_id, @created_by, (SELECT id FROM categories WHERE slug = 'studded' LIMIT 1), 'Classic Diamond Nose Pin', 'classic-diamond-nose-pin', 'A masterclass in understated royalty. The Classic Diamond Nose Pin features a round brilliant cut stone held in high-polish 22K gold prongs. The screw mechanism provides unmatched stability.', 'Timeless 22K gold solitaire nose pin with 6-prong brilliant-cut diamond solitaire.', 'NP-22K-DIA-002', 'gold', '22K', 0.52, 1800, 'fixed', 'Solitaire Diamond', 0.1, 'VVS / EF', 'bis_hallmark', 'BIS-HM-916-1290', 12900, 0, 1, 18, 'active', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), short_description = VALUES(short_description), base_price = VALUES(base_price), is_featured = VALUES(is_featured), is_new_arrival = VALUES(is_new_arrival), status = 'active', updated_at = NOW();

SET @pid = (SELECT id FROM products WHERE slug = 'classic-diamond-nose-pin' LIMIT 1);

DELETE FROM product_images WHERE product_id = @pid;
INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order, created_at, updated_at) VALUES (@pid, 'products/nose-pins/prod-2.jpg', 'Classic Diamond Nose Pin', 1, 0, NOW(), NOW());
DELETE FROM product_variants WHERE product_id = @pid;
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Small', 'NP-22K-DIA-002-S', 10, 0, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Medium', 'NP-22K-DIA-002-M', 10, 1, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Large', 'NP-22K-DIA-002-L', 10, 2, 1, NOW(), NOW());
INSERT INTO reviews (product_id, user_id, rating, title, body, status, is_verified_purchase, created_at, updated_at)
SELECT @pid, (SELECT id FROM users ORDER BY id ASC LIMIT 1), 5, 'Pure luxury solitaire', 'Catchy sparkle, exactly like real diamonds. Hallmarking verified on the BIS Care app.', 'approved', 1, NOW(), NOW()
ON DUPLICATE KEY UPDATE rating = VALUES(rating), title = VALUES(title), body = VALUES(body), status = 'approved';

-- Product: Ruby Teardrop Nose Pin
INSERT INTO products (vendor_id, created_by, category_id, name, slug, description, short_description, sku, metal_type, purity, weight_grams, making_charges, making_charges_type, stone_type, stone_weight_carats, stone_quality, certification_type, certification_number, base_price, is_featured, is_new_arrival, stock_quantity, status, created_at, updated_at)
VALUES (@vendor_id, @created_by, (SELECT id FROM categories WHERE slug = 'premium' LIMIT 1), 'Ruby Teardrop Nose Pin', 'ruby-teardrop-nose-pin', 'Designed for grand celebrations and ethnic bridal attire. A deep crimson teardrop gem surrounded by micro-prong diamonds set in solid 22 karat gold.', 'Royal pear-cut crimson ruby enveloped in a diamond halo crafted in 22K gold.', 'NP-22K-RBY-003', 'gold', '22K', 0.58, 1600, 'fixed', 'Crimson Ruby & CZ Halo', 0.15, 'Fine Ruby', 'bis_hallmark', 'BIS-HM-916-1025', 10250, 1, 0, 15, 'active', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), short_description = VALUES(short_description), base_price = VALUES(base_price), is_featured = VALUES(is_featured), is_new_arrival = VALUES(is_new_arrival), status = 'active', updated_at = NOW();

SET @pid = (SELECT id FROM products WHERE slug = 'ruby-teardrop-nose-pin' LIMIT 1);

DELETE FROM product_images WHERE product_id = @pid;
INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order, created_at, updated_at) VALUES (@pid, 'products/nose-pins/prod-3.jpg', 'Ruby Teardrop Nose Pin', 1, 0, NOW(), NOW());
DELETE FROM product_variants WHERE product_id = @pid;
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Small', 'NP-22K-RBY-003-S', 10, 0, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Medium', 'NP-22K-RBY-003-M', 10, 1, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Large', 'NP-22K-RBY-003-L', 10, 2, 1, NOW(), NOW());
INSERT INTO reviews (product_id, user_id, rating, title, body, status, is_verified_purchase, created_at, updated_at)
SELECT @pid, (SELECT id FROM users ORDER BY id ASC LIMIT 1), 5, 'Rich color and traditional charm', 'Pairs amazingly with silk sarees and festive lehengas. Received many compliments!', 'approved', 1, NOW(), NOW()
ON DUPLICATE KEY UPDATE rating = VALUES(rating), title = VALUES(title), body = VALUES(body), status = 'approved';

-- Product: Daily Wear Gold Stud
INSERT INTO products (vendor_id, created_by, category_id, name, slug, description, short_description, sku, metal_type, purity, weight_grams, making_charges, making_charges_type, stone_type, stone_weight_carats, stone_quality, certification_type, certification_number, base_price, is_featured, is_new_arrival, stock_quantity, status, created_at, updated_at)
VALUES (@vendor_id, @created_by, (SELECT id FROM categories WHERE slug = 'daily-wear' LIMIT 1), 'Daily Wear Gold Stud', 'daily-wear-gold-stud', 'Crafted for women who adore subtle elegance. Super lightweight, feather-soft wire screw, hypoallergenic finish in 91.6% pure gold.', 'Minimalist flower stud in solid 22K matte-and-gloss gold with center accent.', 'NP-22K-DLY-004', 'gold', '22K', 0.38, 950, 'fixed', 'Accent CZ', 0.02, 'AAA', 'bis_hallmark', 'BIS-HM-916-5800', 5800, 0, 0, 30, 'active', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), short_description = VALUES(short_description), base_price = VALUES(base_price), is_featured = VALUES(is_featured), is_new_arrival = VALUES(is_new_arrival), status = 'active', updated_at = NOW();

SET @pid = (SELECT id FROM products WHERE slug = 'daily-wear-gold-stud' LIMIT 1);

DELETE FROM product_images WHERE product_id = @pid;
INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order, created_at, updated_at) VALUES (@pid, 'products/nose-pins/prod-4.jpg', 'Daily Wear Gold Stud', 1, 0, NOW(), NOW());
DELETE FROM product_variants WHERE product_id = @pid;
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Small', 'NP-22K-DLY-004-S', 10, 0, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Medium', 'NP-22K-DLY-004-M', 10, 1, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Large', 'NP-22K-DLY-004-L', 10, 2, 1, NOW(), NOW());
INSERT INTO reviews (product_id, user_id, rating, title, body, status, is_verified_purchase, created_at, updated_at)
SELECT @pid, (SELECT id FROM users ORDER BY id ASC LIMIT 1), 5, 'Perfect for college and office wear', 'Does not snag on dupattas or towels. The screw is sturdy and comfortable.', 'approved', 1, NOW(), NOW()
ON DUPLICATE KEY UPDATE rating = VALUES(rating), title = VALUES(title), body = VALUES(body), status = 'approved';

-- Product: Petal Bloom Nose Pin
INSERT INTO products (vendor_id, created_by, category_id, name, slug, description, short_description, sku, metal_type, purity, weight_grams, making_charges, making_charges_type, stone_type, stone_weight_carats, stone_quality, certification_type, certification_number, base_price, is_featured, is_new_arrival, stock_quantity, status, created_at, updated_at)
VALUES (@vendor_id, @created_by, (SELECT id FROM categories WHERE slug = 'floral' LIMIT 1), 'Petal Bloom Nose Pin', 'petal-bloom-nose-pin', 'An artistic ode to morning blooms. Five luminous diamond-cut petals reflect light at every turn, secured in a classic Gujarat goldsmith structure.', '5-petal blooming blossom design in pure 22K hallmarked gold with micro-pave stones.', 'NP-22K-PTL-005', 'gold', '22K', 0.48, 1400, 'fixed', 'Zircon Pave', 0.07, 'AAA', 'bis_hallmark', 'BIS-HM-916-9750', 9750, 1, 0, 20, 'active', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), short_description = VALUES(short_description), base_price = VALUES(base_price), is_featured = VALUES(is_featured), is_new_arrival = VALUES(is_new_arrival), status = 'active', updated_at = NOW();

SET @pid = (SELECT id FROM products WHERE slug = 'petal-bloom-nose-pin' LIMIT 1);

DELETE FROM product_images WHERE product_id = @pid;
INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order, created_at, updated_at) VALUES (@pid, 'products/nose-pins/prod-5.jpg', 'Petal Bloom Nose Pin', 1, 0, NOW(), NOW());
DELETE FROM product_variants WHERE product_id = @pid;
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Small', 'NP-22K-PTL-005-S', 10, 0, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Medium', 'NP-22K-PTL-005-M', 10, 1, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Large', 'NP-22K-PTL-005-L', 10, 2, 1, NOW(), NOW());
INSERT INTO reviews (product_id, user_id, rating, title, body, status, is_verified_purchase, created_at, updated_at)
SELECT @pid, (SELECT id FROM users ORDER BY id ASC LIMIT 1), 5, 'Very delicate and elegant', 'The size is just right — neither too small nor too big. Loved the luxury packaging.', 'approved', 1, NOW(), NOW()
ON DUPLICATE KEY UPDATE rating = VALUES(rating), title = VALUES(title), body = VALUES(body), status = 'approved';

-- Product: Emerald Halo Nose Pin
INSERT INTO products (vendor_id, created_by, category_id, name, slug, description, short_description, sku, metal_type, purity, weight_grams, making_charges, making_charges_type, stone_type, stone_weight_carats, stone_quality, certification_type, certification_number, base_price, is_featured, is_new_arrival, stock_quantity, status, created_at, updated_at)
VALUES (@vendor_id, @created_by, (SELECT id FROM categories WHERE slug = 'studded' LIMIT 1), 'Emerald Halo Nose Pin', 'emerald-halo-nose-pin', 'Intense forest emerald brilliance paired with 22K golden warmth. A standout statement piece inspired by royal Rajputana heritage.', 'Vibrant natural emerald gemstone encircled by radiant diamond flower prongs in 22K gold.', 'NP-22K-EMR-006', 'gold', '22K', 0.65, 1900, 'fixed', 'Emerald & CZ Halo', 0.16, 'Fine Emerald', 'bis_hallmark', 'BIS-HM-916-1420', 14200, 0, 1, 12, 'active', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), short_description = VALUES(short_description), base_price = VALUES(base_price), is_featured = VALUES(is_featured), is_new_arrival = VALUES(is_new_arrival), status = 'active', updated_at = NOW();

SET @pid = (SELECT id FROM products WHERE slug = 'emerald-halo-nose-pin' LIMIT 1);

DELETE FROM product_images WHERE product_id = @pid;
INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order, created_at, updated_at) VALUES (@pid, 'products/nose-pins/prod-6.jpg', 'Emerald Halo Nose Pin', 1, 0, NOW(), NOW());
DELETE FROM product_variants WHERE product_id = @pid;
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Small', 'NP-22K-EMR-006-S', 10, 0, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Medium', 'NP-22K-EMR-006-M', 10, 1, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Large', 'NP-22K-EMR-006-L', 10, 2, 1, NOW(), NOW());
INSERT INTO reviews (product_id, user_id, rating, title, body, status, is_verified_purchase, created_at, updated_at)
SELECT @pid, (SELECT id FROM users ORDER BY id ASC LIMIT 1), 5, 'Unique green emerald beauty', 'The emerald color is so vivid! The BIS Hallmark certificate was delivered along with it.', 'approved', 1, NOW(), NOW()
ON DUPLICATE KEY UPDATE rating = VALUES(rating), title = VALUES(title), body = VALUES(body), status = 'approved';

-- Product: Minimal Gold Hoop Pin
INSERT INTO products (vendor_id, created_by, category_id, name, slug, description, short_description, sku, metal_type, purity, weight_grams, making_charges, making_charges_type, stone_type, stone_weight_carats, stone_quality, certification_type, certification_number, base_price, is_featured, is_new_arrival, stock_quantity, status, created_at, updated_at)
VALUES (@vendor_id, @created_by, (SELECT id FROM categories WHERE slug = 'daily-wear' LIMIT 1), 'Minimal Gold Hoop Pin', 'minimal-gold-hoop-pin', 'Modern circular minimalism for nose ring lovers. Snug circular 22K gold hoop wire with a single bezel-set stone for a subtle golden glint.', 'Dainty 22K gold seamless nose ring hoop with single solitaire accent stone.', 'NP-22K-HOP-007', 'gold', '22K', 0.42, 1100, 'fixed', 'Bezel Solitaire', 0.03, 'AAA', 'bis_hallmark', 'BIS-HM-916-6450', 6450, 0, 0, 22, 'active', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), short_description = VALUES(short_description), base_price = VALUES(base_price), is_featured = VALUES(is_featured), is_new_arrival = VALUES(is_new_arrival), status = 'active', updated_at = NOW();

SET @pid = (SELECT id FROM products WHERE slug = 'minimal-gold-hoop-pin' LIMIT 1);

DELETE FROM product_images WHERE product_id = @pid;
INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order, created_at, updated_at) VALUES (@pid, 'products/nose-pins/prod-7.jpg', 'Minimal Gold Hoop Pin', 1, 0, NOW(), NOW());
DELETE FROM product_variants WHERE product_id = @pid;
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Small', 'NP-22K-HOP-007-S', 10, 0, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Medium', 'NP-22K-HOP-007-M', 10, 1, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Large', 'NP-22K-HOP-007-L', 10, 2, 1, NOW(), NOW());
INSERT INTO reviews (product_id, user_id, rating, title, body, status, is_verified_purchase, created_at, updated_at)
SELECT @pid, (SELECT id FROM users ORDER BY id ASC LIMIT 1), 5, 'Comfortable hoop nose pin', 'I usually find hoops difficult to wear, but this mechanism is seamless and painless.', 'approved', 1, NOW(), NOW()
ON DUPLICATE KEY UPDATE rating = VALUES(rating), title = VALUES(title), body = VALUES(body), status = 'approved';

-- Product: Royal Cluster Nose Pin
INSERT INTO products (vendor_id, created_by, category_id, name, slug, description, short_description, sku, metal_type, purity, weight_grams, making_charges, making_charges_type, stone_type, stone_weight_carats, stone_quality, certification_type, certification_number, base_price, is_featured, is_new_arrival, stock_quantity, status, created_at, updated_at)
VALUES (@vendor_id, @created_by, (SELECT id FROM categories WHERE slug = 'premium' LIMIT 1), 'Royal Cluster Nose Pin', 'royal-cluster-nose-pin', 'The pinnacle of festive luxury. Nine brilliant gemstones arranged in a luminous solar cluster setting, designed to catch light from all perspectives.', 'Majestic multi-stone cluster nose pin with 9-stone diamond bloom in 22K gold.', 'NP-22K-CLS-008', 'gold', '22K', 0.78, 2200, 'fixed', '9-Stone Diamond Cluster', 0.22, 'VVS / EF', 'bis_hallmark', 'BIS-HM-916-1750', 17500, 1, 0, 10, 'active', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), short_description = VALUES(short_description), base_price = VALUES(base_price), is_featured = VALUES(is_featured), is_new_arrival = VALUES(is_new_arrival), status = 'active', updated_at = NOW();

SET @pid = (SELECT id FROM products WHERE slug = 'royal-cluster-nose-pin' LIMIT 1);

DELETE FROM product_images WHERE product_id = @pid;
INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order, created_at, updated_at) VALUES (@pid, 'products/nose-pins/prod-8.jpg', 'Royal Cluster Nose Pin', 1, 0, NOW(), NOW());
DELETE FROM product_variants WHERE product_id = @pid;
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Small', 'NP-22K-CLS-008-S', 10, 0, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Medium', 'NP-22K-CLS-008-M', 10, 1, 1, NOW(), NOW());
INSERT INTO product_variants (product_id, name, sku, stock_quantity, sort_order, is_active, created_at, updated_at) VALUES (@pid, 'Large', 'NP-22K-CLS-008-L', 10, 2, 1, NOW(), NOW());
INSERT INTO reviews (product_id, user_id, rating, title, body, status, is_verified_purchase, created_at, updated_at)
SELECT @pid, (SELECT id FROM users ORDER BY id ASC LIMIT 1), 5, 'Absolute royal masterpiece', 'Looks extremely rich. The weight and gold purity are top-notch. High quality!', 'approved', 1, NOW(), NOW()
ON DUPLICATE KEY UPDATE rating = VALUES(rating), title = VALUES(title), body = VALUES(body), status = 'approved';

COMMIT;
