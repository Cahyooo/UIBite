<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once BASE_PATH . '/app/actions.php';

$route = (string) ($_GET['r'] ?? 'home');
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'POST') {
    handle_post($route);
}

try {
    switch ($route) {
        case 'home':
            render_home();
            break;
        case 'store':
            render_store_page();
            break;
        case 'product':
            render_product_page();
            break;
        case 'login':
            if (is_logged_in()) redirect(current_user()['role'] === 'seller' ? 'seller.dashboard' : 'home');
            render_auth_page('login');
            break;
        case 'register':
            if (is_logged_in()) redirect(current_user()['role'] === 'seller' ? 'seller.dashboard' : 'home');
            render_auth_page('register');
            break;
        case 'cart':
            render_cart();
            break;
        case 'checkout':
            render_checkout();
            break;
        case 'buyer.orders':
            require_role('buyer');
            render_buyer_orders();
            break;
        case 'buyer.order':
            require_role('buyer');
            render_buyer_order_detail();
            break;
        case 'seller.dashboard':
            require_role('seller');
            render_seller_dashboard();
            break;
        case 'seller.orders':
            require_role('seller');
            render_seller_orders();
            break;
        case 'seller.products':
            require_role('seller');
            render_seller_products();
            break;
        case 'seller.product-form':
            require_role('seller');
            render_seller_product_form();
            break;
        case 'seller.store':
            require_role('seller');
            render_seller_store();
            break;
        case 'seller.reports':
            require_role('seller');
            render_seller_reports();
            break;
        case 'seller.reports.csv':
            $seller = require_role('seller');
            $store = get_store_for_seller((int) $seller['id']);
            $start = (string) ($_GET['start'] ?? date('Y-m-01'));
            $end = (string) ($_GET['end'] ?? date('Y-m-d'));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) $start = date('Y-m-01');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) $end = date('Y-m-d');
            if ($start > $end) [$start, $end] = [$end, $start];
            $stmt = db()->prepare("SELECT DATE(p.paid_at) AS sale_date, o.order_code, u.name AS buyer_name, o.total, p.payment_method, p.payment_status FROM orders o JOIN users u ON u.id=o.buyer_id JOIN payments p ON p.order_id=o.id WHERE o.store_id=? AND p.payment_status='paid' AND DATE(p.paid_at) BETWEEN ? AND ? ORDER BY p.paid_at DESC");
            $stmt->execute([$store['id'], $start, $end]);
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="uibite-sales-' . $start . '-to-' . $end . '.csv"');
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Tanggal', 'Kode Pesanan', 'Pembeli', 'Total (IDR)', 'Metode Pembayaran', 'Status Pembayaran']);
            while ($row = $stmt->fetch()) fputcsv($output, [$row['sale_date'], $row['order_code'], $row['buyer_name'], $row['total'], $row['payment_method'], $row['payment_status']]);
            fclose($output);
            exit;
        default:
            http_response_code(404);
            render_buyer_header('Halaman tidak ditemukan');
            echo '<section class="container-app py-24 text-center"><p class="text-xs font-bold uppercase tracking-widest text-brand-600">404</p><h1 class="mt-2 text-3xl font-extrabold">Halaman tidak ditemukan</h1><p class="mt-2 text-sm text-slate-500">Halaman yang Anda cari tidak tersedia.</p><a class="btn-primary mt-5" href="' . e(url('home')) . '">Kembali ke beranda</a></section>';
            render_buyer_footer();
            break;
    }
} catch (PDOException $exception) {
    error_log('[UIBite DB] ' . $exception->getMessage());
    http_response_code(500);
    $debug = env('APP_ENV', 'local') === 'local';
?>
    <!doctype html>
    <html lang="id">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Database belum siap · UIBite</title>
        <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    </head>

    <body class="grid min-h-screen place-items-center bg-slate-50 p-5">
        <div class="card w-full max-w-xl p-7">
            <div class="grid h-12 w-12 place-items-center rounded-xl bg-rose-50 text-rose-600"><span class="text-xl">!</span></div>
            <h1 class="mt-4 text-xl font-extrabold">Database UIBite belum siap</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Pastikan MySQL berjalan, database <code class="rounded bg-slate-100 px-1.5 py-0.5">uibite_db</code> sudah di-import dari <code>database/uibite.sql</code>, dan konfigurasi koneksi di file <code>.env</code> sudah benar.</p><?php if ($debug): ?>
                <pre class="mt-4 overflow-auto rounded-xl bg-slate-950 p-3 text-xs text-rose-200"><?= e($exception->getMessage()) ?></pre><?php endif; ?><a href="<?= e(url('home')) ?>" class="btn-primary mt-5">Coba lagi</a>
        </div>
    </body>

    </html><?php
        } catch (Throwable $exception) {
            error_log('[UIBite] ' . $exception->getMessage());
            http_response_code(500);
            echo 'Terjadi kesalahan aplikasi. Periksa konfigurasi atau log PHP.';
        }
