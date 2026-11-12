<?php

function render_seller_start(string $title, string $active): void
    {
        $user = current_user();
        $nav = [
            'seller.dashboard' => ['layout-dashboard', 'Ringkasan'],
            'seller.orders' => ['clipboard-list', 'Pesanan'],
            'seller.products' => ['utensils', 'Menu makanan'],
            'seller.reports' => ['chart-no-axes-combined', 'Laporan'],
            'seller.store' => ['store', 'Pengaturan toko'],
        ];
?>
    <!doctype html>
    <html lang="id">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> · UIBite Seller</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
        <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js" defer></script>
    </head>

    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <div class="min-h-screen lg:flex">
            <aside id="seller-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-[260px] -translate-x-full flex-col bg-slate-950 px-4 pb-5 pt-6 text-white transition-transform lg:static lg:translate-x-0">
                <a href="<?= e(url('seller.dashboard')) ?>" class="mb-9 flex items-center gap-2.5 px-2">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-500"><i data-lucide="utensils" class="h-5 w-5"></i></span>
                    <span class="text-xl font-extrabold tracking-tight">UI<span class="text-blue-400">Bite</span><span class="block text-[10px] font-bold uppercase tracking-[.2em] text-slate-500">Seller workspace</span></span>
                </a>
                <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-[.18em] text-slate-500">Workspace</p>
                <nav class="space-y-1">
                    <?php foreach ($nav as $route => [$icon, $label]): ?>
                        <a class="sidebar-link <?= $active === $route ? 'active' : '' ?>" href="<?= e(url($route)) ?>"><i data-lucide="<?= e($icon) ?>" class="h-[18px] w-[18px]"></i><?= e($label) ?></a>
                    <?php endforeach; ?>
                </nav>
                <div class="mt-auto rounded-xl border border-white/10 bg-white/5 p-3.5">
                    <div class="mb-2 flex items-center gap-2 text-xs font-bold text-white"><i data-lucide="graduation-cap" class="h-4 w-4 text-blue-400"></i> UIBite Campus</div>
                    <p class="text-xs leading-relaxed text-slate-400">Kelola menu dan pesanan tenant kantin kampus dalam satu tempat.</p>
                </div>
                <a href="<?= e(url('home')) ?>" class="sidebar-link mt-3"><i data-lucide="external-link" class="h-[18px] w-[18px]"></i>Lihat marketplace</a>
                <form class="mt-1" method="post" action="<?= e(url('auth.logout')) ?>"><?= csrf_field() ?><button type="submit" class="sidebar-link w-full text-left"><i data-lucide="log-out" class="h-[18px] w-[18px]"></i>Keluar</button></form>
            </aside>
            <div id="seller-sidebar-overlay" class="fixed inset-0 z-40 hidden bg-slate-950/40 lg:hidden" onclick="toggleSellerSidebar(false)"></div>
            <div class="min-w-0 flex-1">
                <header class="sticky top-0 z-30 flex h-[68px] items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-7">
                    <div class="flex items-center gap-3"><button onclick="toggleSellerSidebar(true)" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-600 lg:hidden"><i data-lucide="menu" class="h-4 w-4"></i></button>
                        <div>
                            <p class="text-xs font-medium text-slate-400">Seller workspace</p>
                            <h1 class="text-base font-bold text-slate-800"><?= e($title) ?></h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-3"><a href="<?= e(url('home')) ?>" class="hidden text-xs font-semibold text-slate-500 hover:text-brand-700 sm:inline-flex">Marketplace <i data-lucide="arrow-up-right" class="ml-1 h-3.5 w-3.5"></i></a><span class="grid h-9 w-9 place-items-center rounded-full bg-brand-50 text-sm font-bold text-brand-700"><?= e(strtoupper(substr($user['name'] ?? 'S', 0, 1))) ?></span></div>
                </header>
                <?php render_flashes(); ?>
                <main class="p-4 sm:p-7">
                <?php
            }
