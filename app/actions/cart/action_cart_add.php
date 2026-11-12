<?php

function action_cart_add(): void
{
    $productId = (int) ($_POST['product_id'] ?? 0);
                    $quantity = max(1, min(20, (int) ($_POST['quantity'] ?? 1)));
                    $stmt = db()->prepare('SELECT p.id, p.store_id, p.stock, p.is_available, s.is_open, p.name FROM products p JOIN stores s ON s.id = p.store_id WHERE p.id = ? LIMIT 1');
                    $stmt->execute([$productId]);
                    $product = $stmt->fetch();
                    if (!$product || !(int) $product['is_available'] || !(int) $product['is_open'] || (int) $product['stock'] < 1) {
                        flash('error', 'Produk sedang tidak tersedia.');
                        redirect('home');
                    }
                    $existingStore = $_SESSION['cart']['store_id'] ?? null;
                    if ($existingStore !== null && (int) $existingStore !== (int) $product['store_id']) {
                        flash('warning', 'Satu checkout hanya untuk satu toko. Selesaikan atau kosongkan cart sebelum memilih toko lain.');
                        redirect('cart');
                    }
                    $_SESSION['cart']['store_id'] = (int) $product['store_id'];
                    $currentQty = (int) ($_SESSION['cart']['items'][$productId] ?? 0);
                    $_SESSION['cart']['items'][$productId] = min((int) $product['stock'], $currentQty + $quantity);
                    flash('success', $product['name'] . ' ditambahkan ke keranjang.');
                    redirect('cart');
}
