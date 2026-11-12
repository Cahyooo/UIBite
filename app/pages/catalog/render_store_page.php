<?php

function render_store_page(): void
            {
                $id = (int) ($_GET['id'] ?? 0);
                $stmt = db()->prepare('SELECT s.*, u.name AS seller_name FROM stores s JOIN users u ON u.id = s.seller_id WHERE s.id = ? AND s.is_active = 1 LIMIT 1');
                $stmt->execute([$id]);
                $store = $stmt->fetch();
                if (!$store) {
                    http_response_code(404);
                    render_home();
                    return;
                }
                $stmt = db()->prepare("SELECT p.*, c.name AS category_name, s.name AS store_name, s.id AS store_id, s.is_open, (SELECT ROUND(AVG(r.rating),1) FROM reviews r WHERE r.product_id=p.id) AS rating, (SELECT COUNT(*) FROM reviews r WHERE r.product_id=p.id) AS review_count FROM products p JOIN stores s ON s.id=p.store_id LEFT JOIN categories c ON c.id=p.category_id WHERE p.store_id=? AND p.is_available=1 ORDER BY p.name");
                $stmt->execute([$id]);
                $products = $stmt->fetchAll();
                render_buyer_header($store['name']);
?>
    <section class="border-b border-slate-200 bg-white">
        <div class="container-app py-9"><a href="<?= e(url('home')) ?>" class="mb-5 inline-flex items-center gap-1 text-xs font-semibold text-slate-400 hover:text-brand-700"><i data-lucide="arrow-left" class="h-3.5 w-3.5"></i>Kembali ke marketplace</a>
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                <div class="flex items-start gap-4"><span class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-700"><i data-lucide="store" class="h-7 w-7"></i></span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-brand-600">Tenant kantin UIB</p>
                        <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-950"><?= e($store['name']) ?></h1>
                        <p class="mt-2 flex items-center gap-1.5 text-sm text-slate-500"><i data-lucide="map-pin" class="h-4 w-4"></i><?= e($store['location']) ?></p>
                    </div>
                </div><?= (int) $store['is_open'] ? '<span class="badge bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">● Menerima pesanan</span>' : '<span class="badge bg-rose-50 text-rose-700 ring-1 ring-rose-200">Tutup sementara</span>' ?>
            </div>
            <p class="mt-5 max-w-2xl text-sm leading-6 text-slate-500"><?= e($store['description']) ?></p>
        </div>
    </section>
    <section class="container-app py-9">
        <div class="mb-5">
            <h2 class="text-xl font-extrabold">Menu dari <?= e($store['name']) ?></h2>
            <p class="mt-1 text-sm text-slate-500"><?= count($products) ?> menu tersedia</p>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"><?php foreach ($products as $product) product_card($product); ?></div><?php if (!$products): ?><p class="card p-10 text-center text-sm text-slate-500">Belum ada menu yang tersedia.</p><?php endif; ?>
    </section>
<?php render_buyer_footer();
            }
