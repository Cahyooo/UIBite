<?php

function action_payment_simulate(): void
{
    $buyer = require_role('buyer');
                    $orderId = (int) ($_POST['order_id'] ?? 0);
                    $pdo = db();
                    $pdo->beginTransaction();
                    try {
                        $stmt = $pdo->prepare('SELECT o.id, o.order_status, o.buyer_id, p.payment_method, p.payment_status FROM orders o JOIN payments p ON p.order_id = o.id WHERE o.id = ? FOR UPDATE');
                        $stmt->execute([$orderId]);
                        $order = $stmt->fetch();
                        if (!$order || (int) $order['buyer_id'] !== (int) $buyer['id']) {
                            throw new RuntimeException('Pesanan tidak ditemukan.');
                        }
                        if ($order['payment_method'] !== 'qris_simulation' || $order['payment_status'] !== 'pending' || $order['order_status'] !== 'pending') {
                            throw new RuntimeException('Pesanan ini tidak membutuhkan simulasi pembayaran.');
                        }
                        $reference = 'DEMO-' . strtoupper(bin2hex(random_bytes(4)));
                        $update = $pdo->prepare('UPDATE payments SET payment_status = \'paid\', payment_reference = ?, paid_at = NOW() WHERE order_id = ?');
                        $update->execute([$reference, $orderId]);
                        $pdo->commit();
                        flash('success', 'Pembayaran simulasi berhasil. Tidak ada uang sungguhan yang diproses.');
                    } catch (Throwable $exception) {
                        if ($pdo->inTransaction()) $pdo->rollBack();
                        throw $exception;
                    }
                    redirect('buyer.order', ['id' => $orderId]);
}
