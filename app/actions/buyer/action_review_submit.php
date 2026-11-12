<?php

function action_review_submit(): void
{
    $buyer = require_role('buyer');
                    $orderItemId = (int) ($_POST['order_item_id'] ?? 0);
                    $rating = (int) ($_POST['rating'] ?? 5);
                    $comment = trim((string) ($_POST['comment'] ?? ''));
                    if ($rating < 1 || $rating > 5) {
                        flash('error', 'Rating harus antara 1 sampai 5.');
                        redirect('buyer.orders');
                    }
                    $stmt = db()->prepare("SELECT oi.id, oi.product_id, o.id AS order_id FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE oi.id = ? AND o.buyer_id = ? AND o.order_status = 'completed' LIMIT 1");
                    $stmt->execute([$orderItemId, $buyer['id']]);
                    $item = $stmt->fetch();
                    if (!$item) {
                        flash('error', 'Review hanya dapat diberikan untuk produk dari pesanan yang selesai.');
                        redirect('buyer.orders');
                    }
                    $stmt = db()->prepare('INSERT INTO reviews (order_item_id, order_id, product_id, buyer_id, rating, comment) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), updated_at = NOW()');
                    $stmt->execute([$orderItemId, $item['order_id'], $item['product_id'], $buyer['id'], $rating, $comment ?: null]);
                    flash('success', 'Terima kasih! Review berhasil disimpan.');
                    redirect('buyer.order', ['id' => $item['order_id']]);
}
