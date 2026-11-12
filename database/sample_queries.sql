USE uibite_db;

-- 1. INNER JOIN: daftar pesanan lengkap untuk satu tenant.
SELECT o.order_code, buyer.name AS buyer_name, s.name AS store_name,
       o.order_status, oi.product_name, oi.quantity, oi.line_total
FROM orders o
INNER JOIN users buyer ON buyer.id = o.buyer_id
INNER JOIN stores s ON s.id = o.store_id
INNER JOIN order_items oi ON oi.order_id = o.id
WHERE o.store_id = 1
ORDER BY o.created_at DESC, o.id;

-- 2. LEFT JOIN + aggregate: semua menu termasuk yang belum pernah mendapat review.
SELECT p.id, p.name, COUNT(r.id) AS review_count,
       COALESCE(ROUND(AVG(r.rating), 2), 0) AS average_rating
FROM products p
LEFT JOIN reviews r ON r.product_id = p.id
GROUP BY p.id, p.name
ORDER BY average_rating DESC, review_count DESC;

-- 3. GROUP BY: total unit terjual dan omzet per kategori.
SELECT c.name AS category_name,
       SUM(oi.quantity) AS units_sold,
       SUM(oi.line_total) AS gross_sales
FROM order_items oi
JOIN products p ON p.id = oi.product_id
JOIN categories c ON c.id = p.category_id
JOIN orders o ON o.id = oi.order_id
JOIN payments pay ON pay.order_id = o.id
WHERE pay.payment_status = 'paid'
GROUP BY c.id, c.name
ORDER BY gross_sales DESC;

-- 4. HAVING: kategori dengan total penjualan minimal 2 item.
SELECT c.name AS category_name, SUM(oi.quantity) AS units_sold
FROM order_items oi
JOIN products p ON p.id = oi.product_id
JOIN categories c ON c.id = p.category_id
GROUP BY c.id, c.name
HAVING SUM(oi.quantity) >= 2
ORDER BY units_sold DESC;

-- 5. Subquery: produk yang harganya di atas rata-rata harga di toko masing-masing.
SELECT p.name, p.price, s.name AS store_name
FROM products p
JOIN stores s ON s.id = p.store_id
WHERE p.price > (
    SELECT AVG(p2.price)
    FROM products p2
    WHERE p2.store_id = p.store_id
)
ORDER BY s.name, p.price DESC;

-- 6. Correlated subquery: produk yang belum pernah mendapat review.
SELECT p.id, p.name, s.name AS store_name
FROM products p
JOIN stores s ON s.id = p.store_id
WHERE NOT EXISTS (
    SELECT 1 FROM reviews r WHERE r.product_id = p.id
);

-- 7. VIEW: laporan ringkas pesanan untuk semua tenant.
SELECT order_code, buyer_name, store_name, order_status,
       payment_status, item_count, total, created_at
FROM v_order_summary
ORDER BY created_at DESC;

-- 8. VIEW: produk dengan stok menipis.
SELECT * FROM v_low_stock_products ORDER BY stock ASC, store_name;

-- 9. CTE + window function: ranking produk berdasarkan jumlah unit terjual.
WITH product_sales AS (
    SELECT oi.product_id, oi.product_name, o.store_id,
           SUM(oi.quantity) AS units_sold,
           SUM(oi.line_total) AS sales_value
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN payments p ON p.order_id = o.id
    WHERE p.payment_status = 'paid'
    GROUP BY oi.product_id, oi.product_name, o.store_id
)
SELECT product_name, units_sold, sales_value,
       DENSE_RANK() OVER (PARTITION BY store_id ORDER BY units_sold DESC) AS store_rank
FROM product_sales
ORDER BY store_id, store_rank;

-- 10. Transaction: contoh pola perubahan stok atomik (jalankan pada transaksi percobaan).
-- START TRANSACTION;
-- SELECT id, stock FROM products WHERE id = 1 FOR UPDATE;
-- UPDATE products SET stock = stock - 1 WHERE id = 1 AND stock >= 1;
-- INSERT INTO stock_movements (store_id, product_id, actor_user_id, movement_type, quantity_change, note)
-- VALUES (1, 1, 1, 'sale', -1, 'Contoh uji query');
-- COMMIT;

-- 11. Stored procedure: laporan penjualan harian tenant #1 selama 30 hari.
CALL sp_store_sales_summary(1, CURDATE() - INTERVAL 30 DAY, CURDATE());

-- 12. Trigger test: harus ditolak karena rating di luar rentang 1-5.
-- INSERT INTO reviews (order_item_id, order_id, product_id, buyer_id, rating)
-- VALUES (2001, 1001, 1, 5, 8);
