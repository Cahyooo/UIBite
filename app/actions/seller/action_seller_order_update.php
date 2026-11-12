<?php

function action_seller_order_update(): void
{
    $seller = require_role('seller');
                    $store = get_store_for_seller((int) $seller['id']);
                    $orderId = (int) ($_POST['order_id'] ?? 0);
                    $target = (string) ($_POST['next_status'] ?? '');
                    $pdo = db();
                    $pdo->beginTransaction();
                    try {
                        $stmt = $pdo->prepare('SELECT o.*, p.payment_status, p.payment_method FROM orders o JOIN payments p ON p.order_id = o.id WHERE o.id = ? AND o.store_id = ? FOR UPDATE');
                        $stmt->execute([$orderId, $store['id']]);
                        $order = $stmt->fetch();
                        if (!$order) throw new RuntimeException('Pesanan tidak ditemukan.');
                        $allowed = [
                            'pending' => ['confirmed', 'rejected'],
                            'confirmed' => ['preparing'],
                            'preparing' => ['ready'],
                            'ready' => ['completed'],
                        ];
                        if (!in_array($target, $allowed[$order['order_status']] ?? [], true)) {
                            throw new RuntimeException('Perubahan status tidak diperbolehkan.');
                        }
                        if ($target === 'confirmed' && $order['payment_method'] === 'qris_simulation' && $order['payment_status'] !== 'paid') {
                            throw new RuntimeException('Pembayaran simulasi belum berhasil. Seller hanya bisa menerima pesanan yang sudah dibayar.');
                        }
                        if (in_array($target, ['rejected'], true)) {
                            $itemsStmt = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?');
                            $itemsStmt->execute([$orderId]);
                            $stockBack = $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
                            $movement = $pdo->prepare('INSERT INTO stock_movements (store_id, product_id, actor_user_id, order_id, movement_type, quantity_change, note) VALUES (?, ?, ?, ?, \'return\', ?, ?)');
                            foreach ($itemsStmt->fetchAll() as $item) {
                                $stockBack->execute([$item['quantity'], $item['product_id']]);
                                $movement->execute([$store['id'], $item['product_id'], $seller['id'], $orderId, $item['quantity'], 'Stok dikembalikan karena pesanan ditolak']);
                            }
                            if ($order['payment_status'] === 'paid') {
                                $pdo->prepare("UPDATE payments SET payment_status = 'refunded' WHERE order_id = ?")->execute([$orderId]);
                            }
                        }
                        if ($target === 'completed' && $order['payment_method'] === 'cash_on_pickup') {
                            $pdo->prepare("UPDATE payments SET payment_status = 'paid', paid_at = NOW() WHERE order_id = ?")->execute([$orderId]);
                        }
                        $pdo->prepare('UPDATE orders SET order_status = ?, completed_at = IF(? = \'completed\', NOW(), completed_at) WHERE id = ?')->execute([$target, $target, $orderId]);
                        $pdo->commit();
                        flash('success', 'Status pesanan diperbarui menjadi ' . status_label($target) . '.');
                    } catch (Throwable $exception) {
                        if ($pdo->inTransaction()) $pdo->rollBack();
                        throw $exception;
                    }
                    redirect('seller.orders');
}
