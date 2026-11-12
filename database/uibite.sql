-- UIBite database schema + realistic demo seed data
-- Target: MySQL 8.0.16+ (InnoDB, CHECK constraints, views, triggers, stored procedure)

CREATE DATABASE IF NOT EXISTS uibite_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uibite_db;
SET NAMES utf8mb4;
SET time_zone = '+07:00';

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('seller','buyer') NOT NULL DEFAULT 'buyer',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role_active (role, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_id BIGINT UNSIGNED NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    description TEXT NULL,
    location VARCHAR(180) NOT NULL,
    phone VARCHAR(30) NULL,
    is_open TINYINT(1) NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_stores_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_stores_open_active (is_open, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    slug VARCHAR(90) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    store_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    sku VARCHAR(40) NOT NULL,
    name VARCHAR(140) NOT NULL,
    description TEXT NULL,
    price DECIMAL(12,2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    image_url VARCHAR(500) NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_store FOREIGN KEY (store_id) REFERENCES stores(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT uq_products_store_sku UNIQUE (store_id, sku),
    CONSTRAINT chk_products_price CHECK (price > 0),
    CONSTRAINT chk_products_stock CHECK (stock >= 0),
    INDEX idx_products_store_available (store_id, is_available),
    INDEX idx_products_category (category_id),
    INDEX idx_products_name (name),
    INDEX idx_products_stock (stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(32) NOT NULL UNIQUE,
    buyer_id BIGINT UNSIGNED NOT NULL,
    store_id BIGINT UNSIGNED NOT NULL,
    order_status ENUM('pending','confirmed','preparing','ready','completed','cancelled','rejected') NOT NULL DEFAULT 'pending',
    subtotal DECIMAL(12,2) NOT NULL,
    service_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cash_on_pickup','qris_simulation') NOT NULL,
    customer_note VARCHAR(500) NULL,
    pickup_code CHAR(6) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    cancelled_at DATETIME NULL,
    CONSTRAINT fk_orders_buyer FOREIGN KEY (buyer_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_orders_store FOREIGN KEY (store_id) REFERENCES stores(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_orders_subtotal CHECK (subtotal >= 0),
    CONSTRAINT chk_orders_fee CHECK (service_fee >= 0),
    CONSTRAINT chk_orders_total CHECK (total = subtotal + service_fee),
    INDEX idx_orders_store_status_created (store_id, order_status, created_at),
    INDEX idx_orders_buyer_created (buyer_id, created_at),
    INDEX idx_orders_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    product_name VARCHAR(140) NOT NULL COMMENT 'Snapshot nama produk ketika checkout',
    unit_price DECIMAL(12,2) NOT NULL COMMENT 'Snapshot harga ketika checkout',
    quantity SMALLINT UNSIGNED NOT NULL,
    line_total DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_order_items_quantity CHECK (quantity > 0),
    CONSTRAINT chk_order_items_price CHECK (unit_price > 0),
    CONSTRAINT chk_order_items_total CHECK (line_total = unit_price * quantity),
    INDEX idx_order_items_product (product_id),
    INDEX idx_order_items_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL UNIQUE,
    payment_method ENUM('cash_on_pickup','qris_simulation') NOT NULL,
    payment_status ENUM('unpaid','pending','paid','refunded','failed') NOT NULL DEFAULT 'unpaid',
    amount DECIMAL(12,2) NOT NULL,
    payment_reference VARCHAR(80) NULL,
    paid_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_payments_amount CHECK (amount >= 0),
    INDEX idx_payments_status_paid (payment_status, paid_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stock_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    store_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    actor_user_id BIGINT UNSIGNED NULL,
    order_id BIGINT UNSIGNED NULL,
    movement_type ENUM('sale','restock','adjustment','return') NOT NULL,
    quantity_change INT NOT NULL COMMENT 'Negatif untuk stok keluar, positif untuk stok masuk',
    note VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stock_store FOREIGN KEY (store_id) REFERENCES stores(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_stock_product FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_stock_actor FOREIGN KEY (actor_user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_stock_order FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_stock_change CHECK (quantity_change <> 0),
    INDEX idx_stock_product_created (product_id, created_at),
    INDEX idx_stock_store_created (store_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_item_id BIGINT UNSIGNED NOT NULL UNIQUE,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    buyer_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_order_item FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_reviews_order FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_reviews_buyer FOREIGN KEY (buyer_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5),
    INDEX idx_reviews_product_created (product_id, created_at),
    INDEX idx_reviews_buyer (buyer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Views for reporting and query demonstrations.
DROP VIEW IF EXISTS v_low_stock_products;
DROP VIEW IF EXISTS v_daily_sales;
DROP VIEW IF EXISTS v_order_summary;
DROP VIEW IF EXISTS v_product_catalog;

CREATE VIEW v_product_catalog AS
SELECT p.id AS product_id, p.store_id, s.name AS store_name, s.location,
       p.category_id, c.name AS category_name, p.sku, p.name AS product_name,
       p.description, p.price, p.stock, p.image_url, p.is_available,
       COALESCE(ROUND(AVG(r.rating), 1), 0) AS average_rating,
       COUNT(r.id) AS review_count
FROM products p
JOIN stores s ON s.id = p.store_id
LEFT JOIN categories c ON c.id = p.category_id
LEFT JOIN reviews r ON r.product_id = p.id
GROUP BY p.id, p.store_id, s.name, s.location, p.category_id, c.name,
         p.sku, p.name, p.description, p.price, p.stock, p.image_url, p.is_available;

CREATE VIEW v_order_summary AS
SELECT o.id AS order_id, o.order_code, o.buyer_id, u.name AS buyer_name,
       o.store_id, s.name AS store_name, o.order_status, o.subtotal,
       o.service_fee, o.total, o.payment_method, p.payment_status,
       p.payment_reference, p.paid_at, o.pickup_code, o.customer_note,
       o.created_at, o.completed_at,
       COUNT(oi.id) AS item_count, COALESCE(SUM(oi.quantity), 0) AS total_quantity
FROM orders o
JOIN users u ON u.id = o.buyer_id
JOIN stores s ON s.id = o.store_id
LEFT JOIN payments p ON p.order_id = o.id
LEFT JOIN order_items oi ON oi.order_id = o.id
GROUP BY o.id, o.order_code, o.buyer_id, u.name, o.store_id, s.name,
         o.order_status, o.subtotal, o.service_fee, o.total, o.payment_method,
         p.payment_status, p.payment_reference, p.paid_at, o.pickup_code,
         o.customer_note, o.created_at, o.completed_at;

CREATE VIEW v_daily_sales AS
SELECT o.store_id, s.name AS store_name, DATE(p.paid_at) AS sale_date,
       COUNT(DISTINCT o.id) AS order_count, SUM(p.amount) AS gross_revenue
FROM orders o
JOIN stores s ON s.id = o.store_id
JOIN payments p ON p.order_id = o.id
WHERE p.payment_status = 'paid' AND p.paid_at IS NOT NULL
GROUP BY o.store_id, s.name, DATE(p.paid_at);

CREATE VIEW v_low_stock_products AS
SELECT p.id AS product_id, p.store_id, s.name AS store_name, p.sku,
       p.name AS product_name, p.stock, p.is_available
FROM products p JOIN stores s ON s.id = p.store_id
WHERE p.stock <= 5 AND p.is_available = 1;

-- Trigger: only accounts with the seller role may own a tenant.
DROP TRIGGER IF EXISTS trg_stores_require_seller;
DELIMITER //
DELIMITER $$
CREATE TRIGGER trg_stores_require_seller
BEFORE INSERT ON stores
FOR EACH ROW
BEGIN
    DECLARE v_role VARCHAR(20) DEFAULT NULL;
    SELECT role INTO v_role FROM users WHERE id = NEW.seller_id LIMIT 1;
    IF v_role IS NULL OR v_role <> 'seller' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A store owner must have the seller role';
    END IF;
END$$

-- Trigger: an order item must belong to the same tenant as its order.
DROP TRIGGER IF EXISTS trg_order_items_same_store;
DELIMITER //
CREATE TRIGGER trg_order_items_same_store
BEFORE INSERT ON order_items
FOR EACH ROW
BEGIN
    DECLARE v_order_store BIGINT UNSIGNED DEFAULT NULL;
    DECLARE v_product_store BIGINT UNSIGNED DEFAULT NULL;
    SELECT store_id INTO v_order_store FROM orders WHERE id = NEW.order_id LIMIT 1;
    SELECT store_id INTO v_product_store FROM products WHERE id = NEW.product_id LIMIT 1;
    IF v_order_store IS NULL OR v_product_store IS NULL OR v_order_store <> v_product_store THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Order item and order must belong to the same store';
    END IF;
END$$

-- Trigger: stock movements must be recorded against the product's own tenant.
DROP TRIGGER IF EXISTS trg_stock_movement_same_store;
DELIMITER //
CREATE TRIGGER trg_stock_movement_same_store
BEFORE INSERT ON stock_movements
FOR EACH ROW
BEGIN
    DECLARE v_product_store BIGINT UNSIGNED DEFAULT NULL;
    SELECT store_id INTO v_product_store FROM products WHERE id = NEW.product_id LIMIT 1;
    IF v_product_store IS NULL OR v_product_store <> NEW.store_id THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stock movement store must match product store';
    END IF;
END$$

-- Trigger: a completed order must already have a successful payment.
DROP TRIGGER IF EXISTS trg_orders_require_paid_completion;
DELIMITER //
CREATE TRIGGER trg_orders_require_paid_completion
BEFORE UPDATE ON orders
FOR EACH ROW
BEGIN
    DECLARE v_payment_status VARCHAR(20);
    IF NEW.order_status = 'completed' AND OLD.order_status <> 'completed' THEN
        SELECT payment_status INTO v_payment_status
        FROM payments WHERE order_id = OLD.id LIMIT 1;
        IF v_payment_status IS NULL OR v_payment_status <> 'paid' THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Order cannot be completed before payment is paid';
        END IF;
    END IF;
END$$

-- Trigger: reviews must belong to the same buyer/product and a completed order.
DROP TRIGGER IF EXISTS trg_reviews_completed_order;
DELIMITER //
CREATE TRIGGER trg_reviews_completed_order
BEFORE INSERT ON reviews
FOR EACH ROW
BEGIN
    DECLARE v_buyer_id BIGINT UNSIGNED;
    DECLARE v_product_id BIGINT UNSIGNED;
    DECLARE v_order_status VARCHAR(20);
    SELECT o.buyer_id, oi.product_id, o.order_status
      INTO v_buyer_id, v_product_id, v_order_status
    FROM order_items oi JOIN orders o ON o.id = oi.order_id
    WHERE oi.id = NEW.order_item_id AND o.id = NEW.order_id
    LIMIT 1;
    IF v_buyer_id IS NULL OR v_buyer_id <> NEW.buyer_id
       OR v_product_id <> NEW.product_id OR v_order_status <> 'completed' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Review is only allowed for the buyer product item of a completed order';
    END IF;
END$$
DELIMITER ;

-- Stored procedure example: store sales summary for a date range.
DROP PROCEDURE IF EXISTS sp_store_sales_summary;
DELIMITER //
DELIMITER $$
CREATE PROCEDURE sp_store_sales_summary(
    IN p_store_id BIGINT UNSIGNED,
    IN p_start_date DATE,
    IN p_end_date DATE
)
BEGIN
    SELECT sale_date,
           COUNT(*) AS order_count,
           SUM(order_amount) AS total_revenue,
           SUM(items_sold) AS items_sold
    FROM (
        SELECT o.id, DATE(p.paid_at) AS sale_date, p.amount AS order_amount,
               COALESCE(SUM(oi.quantity), 0) AS items_sold
        FROM orders o
        JOIN payments p ON p.order_id = o.id
        LEFT JOIN order_items oi ON oi.order_id = o.id
        WHERE o.store_id = p_store_id
          AND p.payment_status = 'paid'
          AND DATE(p.paid_at) BETWEEN p_start_date AND p_end_date
        GROUP BY o.id, DATE(p.paid_at), p.amount
    ) AS order_level
    GROUP BY sale_date
    ORDER BY sale_date;
END$$
DELIMITER ;

-- Demo accounts. Passwords are hashed using PHP password_hash().
INSERT IGNORE INTO users (id, name, email, phone, password, role) VALUES
(1, 'Rizky Pratama', 'seller.nusantara@uibite.test', '081234560101', '$2y$12$vmQWzC6dON/pdbPVkFtqxeF5tkB4jJ4oeRxxxlE6i0vVVJzi8tb1m.NuSVG2eO', 'seller'),
(2, 'Alya Putri', 'seller.kopicerita@uibite.test', '081234560102', '$2y$12$vmQWzC6dON/pdbPVkFtqxeF5tkB4jJ4oeRxxxlE6i0vVVJzi8tb1m.NuSVG2eO', 'seller'),
(3, 'Bima Saputra', 'seller.geprek@uibite.test', '081234560103', '$2y$12$vmQWzC6dON/pdbPVkFtqxeF5tkB4jJ4oeRxxxlE6i0vVVJzi8tb1m.NuSVG2eO', 'seller'),
(4, 'Citra Maharani', 'seller.bento@uibite.test', '081234560104', '$2y$12$vmQWzC6dON/pdbPVkFtqxeF5tkB4jJ4oeRxxxlE6i0vVVJzi8tb1m.NuSVG2eO', 'seller'),
(5, 'Cahya Demo', 'buyer@uibite.test', '081234560105', '$2y$12$aVGcdwF5zf5bgEJgbR4F7OUEXEPRzWCeBRJYor3FNDIzwFP2cl6Ju', 'buyer'),
(6, 'Nabila Demo', 'nabila@uibite.test', '081234560106', '$2y$12$lMBC8BuLjYVnEU4OaPVqzOS9u3Y6Zg9mi6ymCsY2s246ZEd0AF9Wm', 'buyer');

INSERT IGNORE INTO stores (id, seller_id, name, slug, description, location, phone, is_open, is_active) VALUES
(1, 1, 'Warung Nusantara', 'warung-nusantara', 'Menu rumahan dan masakan khas Indonesia dengan rasa akrab dan porsi mengenyangkan.', 'Kantin Utama · Area A', '081234560101', 1, 1),
(2, 2, 'Kopi & Cerita', 'kopi-dan-cerita', 'Tempat singgah untuk kopi, minuman segar, dan camilan sebelum lanjut kuliah.', 'Kantin Utama · Area B', '081234560102', 1, 1),
(3, 3, 'Ayam Geprek 88', 'ayam-geprek-88', 'Pilihan ayam crispy dan sambal dengan tingkat kepedasan yang bisa disesuaikan.', 'Food Court · Area C', '081234560103', 1, 1),
(4, 4, 'Bento Corner', 'bento-corner', 'Rice bowl dan bento praktis untuk makan siang di sela jadwal perkuliahan.', 'Food Court · Area D', '081234560104', 1, 1);

INSERT IGNORE INTO categories (id, name, slug, description, sort_order, is_active) VALUES
(1, 'Makanan berat', 'makanan-berat', 'Menu utama untuk makan pagi atau siang.', 1, 1),
(2, 'Ayam & seafood', 'ayam-seafood', 'Olahan ayam, ikan, dan seafood.', 2, 1),
(3, 'Mie & pasta', 'mie-pasta', 'Aneka mie dan pasta.', 3, 1),
(4, 'Snack', 'snack', 'Camilan ringan.', 4, 1),
(5, 'Kopi', 'kopi', 'Kopi panas dan dingin.', 5, 1),
(6, 'Minuman', 'minuman', 'Minuman segar non-kopi.', 6, 1),
(7, 'Dessert', 'dessert', 'Hidangan penutup dan manis.', 7, 1),
(8, 'Paket hemat', 'paket-hemat', 'Paket menu hemat.', 8, 1);

INSERT IGNORE INTO products (id, store_id, category_id, sku, name, description, price, stock, image_url, is_available) VALUES
(1,1,1,'WN-001','Nasi Goreng Kampung','Nasi goreng dengan telur, ayam suwir, sayuran, dan kerupuk.',18000,24,'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=900&q=80',1),
(2,1,1,'WN-002','Mie Goreng Jawa','Mie goreng manis gurih dengan telur dan sayuran.',16000,18,'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=900&q=80',1),
(3,1,1,'WN-003','Soto Ayam Lamongan','Soto ayam hangat dengan koya, telur, dan nasi.',20000,15,'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=900&q=80',1),
(4,1,4,'WN-004','Pisang Goreng Crispy','Pisang goreng renyah, cocok untuk teman belajar.',12000,20,'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=900&q=80',1),
(5,1,6,'WN-005','Es Teh Lemon','Teh segar dengan lemon dan es batu.',8000,30,'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=900&q=80',1),
(6,1,8,'WN-006','Paket Nasi Ayam','Nasi, ayam goreng, sambal, dan lalapan.',22000,12,'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=900&q=80',1),
(7,2,5,'KC-001','Es Kopi Susu Aren','Espresso, susu segar, dan gula aren.',18000,25,'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=900&q=80',1),
(8,2,5,'KC-002','Americano Ice','Espresso dengan air dan es, clean serta ringan.',15000,20,'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=900&q=80',1),
(9,2,5,'KC-003','Cafe Latte','Espresso dan steamed milk dengan rasa seimbang.',20000,16,'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=900&q=80',1),
(10,2,6,'KC-004','Matcha Latte','Matcha creamy dengan susu segar.',22000,14,'https://images.unsplash.com/photo-1515823064-d6e0c04616a7?auto=format&fit=crop&w=900&q=80',1),
(11,2,4,'KC-005','Croissant Butter','Croissant berlapis dengan aroma butter.',16000,10,'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=900&q=80',1),
(12,2,7,'KC-006','Brownies Fudge','Brownies cokelat lembut dengan topping choco chip.',14000,9,'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=900&q=80',1),
(13,3,2,'AG-001','Ayam Geprek Original','Ayam crispy geprek dengan sambal bawang level 1.',19000,20,'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=900&q=80',1),
(14,3,2,'AG-002','Ayam Geprek Keju','Ayam crispy dengan sambal dan lelehan keju.',23000,15,'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?auto=format&fit=crop&w=900&q=80',1),
(15,3,8,'AG-003','Paket Geprek + Es Teh','Ayam geprek, nasi, dan es teh dalam satu paket.',24000,18,'https://images.unsplash.com/photo-1562967914-608f82629710?auto=format&fit=crop&w=900&q=80',1),
(16,3,2,'AG-004','Chicken Katsu','Ayam katsu dengan saus khas dan salad.',22000,13,'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=900&q=80',1),
(17,3,6,'AG-005','Es Teh Manis','Teh manis dingin ukuran reguler.',5000,35,'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=900&q=80',1),
(18,3,4,'AG-006','Tahu Crispy','Tahu goreng crispy dengan bumbu tabur.',10000,6,'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?auto=format&fit=crop&w=900&q=80',1),
(19,4,1,'BC-001','Chicken Teriyaki Bowl','Nasi hangat dengan chicken teriyaki dan sayuran.',25000,18,'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=900&q=80',1),
(20,4,1,'BC-002','Beef Yakiniku Bowl','Irisan beef yakiniku dengan nasi dan salad.',30000,12,'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?auto=format&fit=crop&w=900&q=80',1),
(21,4,3,'BC-003','Spicy Chicken Ramen','Ramen kuah pedas dengan chicken dan telur.',28000,10,'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=900&q=80',1),
(22,4,3,'BC-004','Creamy Carbonara','Pasta creamy dengan smoked beef.',27000,8,'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?auto=format&fit=crop&w=900&q=80',1),
(23,4,6,'BC-005','Yuzu Tea','Teh citrus segar dengan aroma yuzu.',14000,16,'https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=900&q=80',1),
(24,4,7,'BC-006','Japanese Pudding','Puding lembut dengan saus caramel.',12000,7,'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=900&q=80',1);

-- Completed and active demo orders so reports and order queues are populated on first login.
INSERT IGNORE INTO orders (id, order_code, buyer_id, store_id, order_status, subtotal, service_fee, total, payment_method, customer_note, pickup_code, created_at, completed_at) VALUES
(1001,'UIB-DEMO-1001',5,1,'completed',36000,0,36000,'cash_on_pickup','Sambal dipisah','A1B2C3',NOW() - INTERVAL 3 DAY,NOW() - INTERVAL 3 DAY),
(1002,'UIB-DEMO-1002',6,1,'pending',26000,0,26000,'qris_simulation','Tidak terlalu pedas','D4E5F6',NOW() - INTERVAL 1 HOUR,NULL),
(1003,'UIB-DEMO-1003',5,2,'completed',33000,0,33000,'qris_simulation',NULL,'G7H8J9',NOW() - INTERVAL 2 DAY,NOW() - INTERVAL 2 DAY),
(1004,'UIB-DEMO-1004',6,3,'completed',34000,0,34000,'cash_on_pickup','Sambal level 2','K1L2M3',NOW() - INTERVAL 1 DAY,NOW() - INTERVAL 1 DAY),
(1005,'UIB-DEMO-1005',5,4,'preparing',25000,0,25000,'cash_on_pickup',NULL,'N4P5Q6',NOW() - INTERVAL 2 HOUR,NULL),
(1006,'UIB-DEMO-1006',6,1,'ready',18000,0,18000,'cash_on_pickup',NULL,'R7S8T9',NOW() - INTERVAL 4 HOUR,NULL);

INSERT IGNORE INTO order_items (id, order_id, product_id, product_name, unit_price, quantity, line_total) VALUES
(2001,1001,1,'Nasi Goreng Kampung',18000,2,36000),
(2002,1002,1,'Nasi Goreng Kampung',18000,1,18000),
(2003,1002,5,'Es Teh Lemon',8000,1,8000),
(2004,1003,7,'Es Kopi Susu Aren',18000,1,18000),
(2005,1003,11,'Croissant Butter',15000,1,15000),
(2006,1004,13,'Ayam Geprek Original',19000,1,19000),
(2007,1004,17,'Es Teh Manis',5000,1,5000),
(2008,1004,18,'Tahu Crispy',10000,1,10000),
(2009,1005,19,'Chicken Teriyaki Bowl',25000,1,25000),
(2010,1006,2,'Mie Goreng Jawa',16000,1,16000),
(2011,1006,5,'Es Teh Lemon',2000,1,2000);

INSERT IGNORE INTO payments (id, order_id, payment_method, payment_status, amount, payment_reference, paid_at) VALUES
(3001,1001,'cash_on_pickup','paid',36000,'CASH-DEMO-1001',NOW() - INTERVAL 3 DAY),
(3002,1002,'qris_simulation','pending',26000,'SIM-DEMO-1002',NULL),
(3003,1003,'qris_simulation','paid',33000,'DEMO-QRIS-1003',NOW() - INTERVAL 2 DAY),
(3004,1004,'cash_on_pickup','paid',34000,'CASH-DEMO-1004',NOW() - INTERVAL 1 DAY),
(3005,1005,'cash_on_pickup','unpaid',25000,NULL,NULL),
(3006,1006,'cash_on_pickup','unpaid',18000,NULL,NULL);

INSERT IGNORE INTO reviews (id, order_item_id, order_id, product_id, buyer_id, rating, comment, created_at) VALUES
(4001,2001,1001,1,5,5,'Porsinya pas dan rasanya enak.','2026-11-07 12:30:00'),
(4002,2004,1003,7,5,4,'Kopinya smooth, tidak terlalu manis.','2026-11-08 10:15:00'),
(4003,2006,1004,13,6,5,'Ayam crispy dan sambalnya mantap.','2026-11-09 12:20:00');

INSERT IGNORE INTO stock_movements (id, store_id, product_id, actor_user_id, order_id, movement_type, quantity_change, note, created_at) VALUES
(5001,1,1,5,1001,'sale',-2,'Penjualan demo',NOW() - INTERVAL 3 DAY),
(5002,1,1,5,1002,'sale',-1,'Reservasi stok pesanan demo',NOW() - INTERVAL 1 HOUR),
(5003,2,7,5,1003,'sale',-1,'Penjualan demo',NOW() - INTERVAL 2 DAY),
(5004,3,13,6,1004,'sale',-1,'Penjualan demo',NOW() - INTERVAL 1 DAY),
(5005,4,19,5,1005,'sale',-1,'Reservasi stok pesanan demo',NOW() - INTERVAL 2 HOUR),
(5006,1,2,6,1006,'sale',-1,'Reservasi stok pesanan demo',NOW() - INTERVAL 4 HOUR);

-- Useful test query: CALL sp_store_sales_summary(1, CURDATE() - INTERVAL 30 DAY, CURDATE());
