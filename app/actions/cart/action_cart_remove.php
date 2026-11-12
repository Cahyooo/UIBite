<?php

function action_cart_remove(): void
{
    $id = (int) ($_POST['product_id'] ?? 0);
                    unset($_SESSION['cart']['items'][$id]);
                    if (empty($_SESSION['cart']['items'])) cart_clear();
                    flash('success', 'Produk dihapus dari keranjang.');
                    redirect('cart');
}
