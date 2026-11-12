<?php

declare(strict_types=1);

require_once __DIR__ . '/actions/auth/action_auth_login.php';
require_once __DIR__ . '/actions/auth/action_auth_register.php';
require_once __DIR__ . '/actions/auth/action_auth_logout.php';
require_once __DIR__ . '/actions/cart/action_cart_add.php';
require_once __DIR__ . '/actions/cart/action_cart_update.php';
require_once __DIR__ . '/actions/cart/action_cart_remove.php';
require_once __DIR__ . '/actions/checkout/action_checkout_place.php';
require_once __DIR__ . '/actions/checkout/action_payment_simulate.php';
require_once __DIR__ . '/actions/buyer/action_buyer_order_cancel.php';
require_once __DIR__ . '/actions/buyer/action_review_submit.php';
require_once __DIR__ . '/actions/seller/action_seller_product_save.php';
require_once __DIR__ . '/actions/seller/action_seller_product_delete.php';
require_once __DIR__ . '/actions/seller/action_seller_order_update.php';
require_once __DIR__ . '/actions/seller/action_seller_store_update.php';

/**
 * Dispatch POST requests to a small, single-purpose action handler.
 */
function handle_post(string $route): never
{
    verify_csrf();

    $handlers = [
        'auth.login' => 'action_auth_login',
        'auth.register' => 'action_auth_register',
        'auth.logout' => 'action_auth_logout',
        'cart.add' => 'action_cart_add',
        'cart.update' => 'action_cart_update',
        'cart.remove' => 'action_cart_remove',
        'checkout.place' => 'action_checkout_place',
        'payment.simulate' => 'action_payment_simulate',
        'buyer.order.cancel' => 'action_buyer_order_cancel',
        'review.submit' => 'action_review_submit',
        'seller.product.save' => 'action_seller_product_save',
        'seller.product.delete' => 'action_seller_product_delete',
        'seller.order.update' => 'action_seller_order_update',
        'seller.store.update' => 'action_seller_store_update',
    ];

    try {
        if (!isset($handlers[$route])) {
            http_response_code(404);
            echo 'Aksi tidak ditemukan.';
            exit;
        }

        $handler = $handlers[$route];
        $handler();
        // Every valid action is expected to redirect or terminate.
        redirect('home');
    } catch (Throwable $exception) {
        error_log('[UIBite] ' . $exception->getMessage());
        flash('error', $exception instanceof PDOException
            ? 'Terjadi masalah saat menyimpan data. Periksa koneksi database atau data yang dimasukkan.'
            : $exception->getMessage());

        $fallback = match (true) {
            str_starts_with($route, 'seller.') => 'seller.dashboard',
            str_starts_with($route, 'cart.') => 'cart',
            $route === 'checkout.place' => 'checkout',
            $route === 'auth.register' => 'register',
            $route === 'auth.login' => 'login',
            default => 'home',
        };

        redirect($fallback);
    }
}
