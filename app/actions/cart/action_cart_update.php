<?php

function action_cart_update(): void
{
    $quantities = $_POST['quantities'] ?? [];
                    if (is_array($quantities)) {
                        foreach ($quantities as $id => $qty) {
                            $id = (int) $id;
                            $qty = (int) $qty;
                            if ($qty <= 0) {
                                unset($_SESSION['cart']['items'][$id]);
                            } else {
                                $_SESSION['cart']['items'][$id] = min(20, $qty);
                            }
                        }
                    }
                    if (empty($_SESSION['cart']['items'])) cart_clear();
                    flash('success', 'Keranjang diperbarui.');
                    redirect('cart');
}
