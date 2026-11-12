<?php

function action_buyer_order_cancel(): void
{
    $buyer = require_role('buyer');
                    $orderId = (int) ($_POST['order_id'] ?? 0);
                    $pdo = db();
                    $pdo->beginTransaction();
                    try {
                        $stmt = $pdo->prepare('SELECT o.*, p.payment_status FROM orders o JOIN payments p ON p.order_id=o.id WHERE o.id=? AND o.buyer_id=? FOR UPDATE');
                        $stmt->execute([$orderId, $buyer['id']]);
                        $order = $stmt->fetch();
                        if (!$order || $order['order_status'] !== 'pending') {
                            throw new RuntimeException('Pesanan tidak bisa dibatalkan pada status saat ini.');
                        }
                        $itemsStmt = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id=?');
                        $itemsStmt->execute([$orderId]);
                        $stockBack = $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
                        $movement = $pdo->prepare("INSERT INTO stock_movements (store_id, product_id, actor_user_id, order_id, movement_type, quantity_change, note) VALUES (?, ?, ?, ?, 'return', ?, ?)");
                        foreach ($itemsStmt->fetchAll() as $item) {
                            $stockBack->execute([$item['quantity'], $item['product_id']]);
                            $movement->execute([$order['store_id'], $item['product_id'], $buyer['id'], $orderId, $item['quantity'], 'Stok dikembalikan karena pembeli membatalkan pesanan']);
                        }
                        if ($order['payment_status'] === 'paid') {
                            $pdo->prepare("UPDATE payments SET payment_status='refunded' WHERE order_id=?")->execute([$orderId]);
                        }
                        $pdo->prepare("UPDATE orders SET order_status='cancelled', cancelled_at=NOW() WHERE id=?")->execute([$orderId]);
                        $pdo->commit();
                        flash('success', 'Pesanan dibatalkan dan stok dikembalikan ke tenant.');
                    } catch (Throwable $exception) {
                        if ($pdo->inTransaction()) $pdo->rollBack();
                        throw $exception;
                    }
                    redirect('buyer.order', ['id' => $orderId]);
}
