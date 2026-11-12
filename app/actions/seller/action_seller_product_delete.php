<?php

function action_seller_product_delete(): void
{
    $seller = require_role('seller');
                    $store = get_store_for_seller((int) $seller['id']);
                    $id = (int) ($_POST['product_id'] ?? 0);
                    $stmt = db()->prepare('UPDATE products SET is_available = 0 WHERE id = ? AND store_id = ?');
                    $stmt->execute([$id, $store['id']]);
                    flash('success', 'Produk dinonaktifkan dan tidak ditampilkan ke pembeli. Riwayat transaksi tetap aman.');
                    redirect('seller.products');
}
