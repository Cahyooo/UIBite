<?php

function render_buyer_header(string $title): void
{
    $user = current_user();
?>
    <!doctype html>
    <html lang="id">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#ffffff">
        <title><?= e($title) ?> | <?= e(env('APP_NAME', 'UIBite')) ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
        <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js" defer></script>
    </head>

    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur">
            <div class="container-app flex h-[72px] items-center justify-between gap-4">
                <a href="<?= e(url('home')) ?>" class="flex shrink-0 items-center gap-2.5">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-600 text-white shadow-sm shadow-brand-600/20"><i data-lucide="utensils" class="h-5 w-5"></i></span>
                    <span class="text-xl font-extrabold tracking-tight text-slate-900">UI<span class="text-brand-600">Bite</span><span class="ml-1 hidden text-[10px] font-bold uppercase tracking-[.18em] text-slate-400 sm:inline">Campus food</span></span>
                </a>
                <nav class="hidden items-center gap-7 text-sm font-semibold text-slate-600 md:flex">
                    <a class="transition hover:text-brand-700" href="<?= e(url('home')) ?>">Jelajahi</a>
                    <?php if ($user && $user['role'] === 'buyer'): ?><a class="transition hover:text-brand-700" href="<?= e(url('buyer.orders')) ?>">Pesanan saya</a><?php endif; ?>
                    <?php if ($user && $user['role'] === 'seller'): ?><a class="transition hover:text-brand-700" href="<?= e(url('seller.dashboard')) ?>">Dashboard penjual</a><?php endif; ?>
                </nav>
                <div class="flex items-center gap-2.5">
                    <a href="<?= e(url('cart')) ?>" class="relative grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-700 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700" aria-label="Keranjang">
                        <i data-lucide="shopping-bag" class="h-[18px] w-[18px]"></i>
                        <?php if (cart_count() > 0): ?><span class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-brand-600 px-1 text-[10px] font-bold text-white"><?= cart_count() ?></span><?php endif; ?>
                    </a>
                    <?php if ($user): ?>
                        <div class="hidden items-center gap-2 sm:flex"><span class="grid h-9 w-9 place-items-center rounded-full bg-slate-100 text-sm font-bold text-slate-600"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></span><span class="max-w-32 truncate text-sm font-semibold text-slate-700"><?= e($user['name']) ?></span></div>
                        <form method="post" action="<?= e(url('auth.logout')) ?>"><?= csrf_field() ?><button class="btn-secondary hidden !px-3 !py-2 text-xs sm:inline-flex" type="submit">Keluar</button></form>
                    <?php else: ?>
                        <a href="<?= e(url('login')) ?>" class="hidden px-2 py-2 text-sm font-bold text-slate-700 hover:text-brand-700 sm:inline-flex">Masuk</a>
                        <a href="<?= e(url('register')) ?>" class="btn-primary !rounded-xl !px-3.5 !py-2.5 text-sm">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>
        <?php render_flashes(); ?>
        <main>
        <?php
    }
