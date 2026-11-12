<?php

function action_checkout_place(): void
{
    $buyer = require_role('buyer');
                    $cart = cart_items();
                    if (!$cart) {
                        flash('error', 'Keranjang masih kosong.');
                        redirect('home');
                    }
                    $storeId = (int) ($_SESSION['cart']['store_id'] ?? 0);
                    $method = (string) ($_POST['payment_method'] ?? 'cash_on_pickup');
                    $note = trim((string) ($_POST['customer_note'] ?? ''));
                    if (!in_array($method, ['cash_on_pickup', 'qris_simulation'], true)) {
                        flash('error', 'Metode pembayaran tidak valid.');
                        redirect('checkout');
                    }

                    $pdo = db();
                    $pdo->beginTransaction();
                    try {
                        $ids = array_values(array_map('intval', array_keys($cart)));
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $params = array_merge([$storeId], $ids);
                        $lock = $pdo->prepare("SELECT p.*, s.is_open FROM products p JOIN stores s ON s.id = p.store_id WHERE p.store_id = ? AND p.id IN ($placeholders) FOR UPDATE");
                        $lock->execute($params);
                        $products = $lock->fetchAll();
                        if (count($products) !== count($ids)) {
                            throw new RuntimeException('Ada produk di keranjang yang sudah tidak tersedia.');
                        }
                        $subtotal = 0.0;
                        foreach ($products as $product) {
                            $qty = (int) ($cart[$product['id']] ?? 0);
                            if ($qty < 1 || !(int) $product['is_available'] || !(int) $product['is_open']) {
                                throw new RuntimeException('Ada produk atau toko yang sedang tidak menerima pesanan.');
                            }
                            if ((int) $product['stock'] < $qty) {
                                throw new RuntimeException('Stok ' . $product['name'] . ' tidak mencukupi. Silakan perbarui keranjang.');
                            }
                            $subtotal += (float) $product['price'] * $qty;
                        }
                        $orderCode = order_code();
                        $pickupCode = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
                        $orderInsert = $pdo->prepare('INSERT INTO orders (order_code, buyer_id, store_id, order_status, subtotal, service_fee, total, payment_method, customer_note, pickup_code) VALUES (?, ?, ?, \'pending\', ?, 0, ?, ?, ?, ?)');
                        $orderInsert->execute([$orderCode, $buyer['id'], $storeId, $subtotal, $subtotal, $method, $note ?: null, $pickupCode]);
                        $orderId = (int) $pdo->lastInsertId();
                        $itemInsert = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total) VALUES (?, ?, ?, ?, ?, ?)');
                        $stockUpdate = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
                        $movement = $pdo->prepare('INSERT INTO stock_movements (store_id, product_id, actor_user_id, order_id, movement_type, quantity_change, note) VALUES (?, ?, ?, ?, \'sale\', ?, ?)');
                        foreach ($products as $product) {
                            $qty = (int) $cart[$product['id']];
                            $lineTotal = (float) $product['price'] * $qty;
                            $itemInsert->execute([$orderId, $product['id'], $product['name'], $product['price'], $qty, $lineTotal]);
                            $stockUpdate->execute([$qty, $product['id'], $qty]);
                            if ($stockUpdate->rowCount() !== 1) {
                                throw new RuntimeException('Stok berubah saat checkout. Silakan coba lagi.');
                            }
                            $movement->execute([$storeId, $product['id'], $buyer['id'], $orderId, -$qty, 'Stok dipesan melalui checkout']);
                        }
                        $paymentStatus = $method === 'qris_simulation' ? 'pending' : 'unpaid';
                        $paymentRef = $method === 'qris_simulation' ? 'SIM-' . strtoupper(bin2hex(random_bytes(4))) : null;
                        $paymentInsert = $pdo->prepare('INSERT INTO payments (order_id, payment_method, payment_status, amount, payment_reference) VALUES (?, ?, ?, ?, ?)');
                        $paymentInsert->execute([$orderId, $method, $paymentStatus, $subtotal, $paymentRef]);
                        $pdo->commit();
                        cart_clear();
                        flash('success', 'Pesanan berhasil dibuat. Kode pickup: ' . $pickupCode);
                        redirect('buyer.order', ['id' => $orderId]);
                    } catch (Throwable $exception) {
                        if ($pdo->inTransaction()) $pdo->rollBack();
                        throw $exception;
                    }
}
