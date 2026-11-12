<?php

function render_home(): void
            {
                $q = trim((string) ($_GET['q'] ?? ''));
                $categoryId = (int) ($_GET['category'] ?? 0);
                $storeId = (int) ($_GET['store'] ?? 0);
                $sort = (string) ($_GET['sort'] ?? 'popular');
                $sortSql = match ($sort) {
                    'price_low' => 'p.price ASC',
                    'price_high' => 'p.price DESC',
                    'name' => 'p.name ASC',
                    default => 'p.created_at DESC',
                };
                $sql = "SELECT p.*, c.name AS category_name, s.name AS store_name, s.id AS store_id, s.is_open,
        (SELECT ROUND(AVG(r.rating), 1) FROM reviews r WHERE r.product_id = p.id) AS rating,
        (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) AS review_count
        FROM products p JOIN stores s ON s.id = p.store_id LEFT JOIN categories c ON c.id = p.category_id
        WHERE p.is_available = 1 AND p.stock > 0 AND s.is_open = 1";
                $params = [];
                if ($q !== '') {
                    $sql .= ' AND (p.name LIKE ? OR p.description LIKE ? OR s.name LIKE ?)';
                    $like = '%' . $q . '%';
                    array_push($params, $like, $like, $like);
                }
                if ($categoryId > 0) {
                    $sql .= ' AND p.category_id = ?';
                    $params[] = $categoryId;
                }
                if ($storeId > 0) {
                    $sql .= ' AND s.id = ?';
                    $params[] = $storeId;
                }
                $sql .= ' ORDER BY ' . $sortSql . ' LIMIT 48';
                $stmt = db()->prepare($sql);
                $stmt->execute($params);
                $products = $stmt->fetchAll();
                $categories = db()->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order, name')->fetchAll();
                $stores = db()->query('SELECT s.*, (SELECT COUNT(*) FROM products p WHERE p.store_id = s.id AND p.is_available = 1) AS product_count FROM stores s WHERE s.is_active = 1 ORDER BY s.name')->fetchAll();
                render_buyer_header('Jelajahi menu');
?>
    <section class="relative overflow-hidden border-b border-slate-200 bg-white">
        <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-brand-100/70 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 left-1/3 h-72 w-72 rounded-full bg-sky-100/70 blur-3xl"></div>
        <div class="container-app relative grid gap-8 py-12 md:grid-cols-[1.1fr_.9fr] md:items-center md:py-16">
            <div>
                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-brand-100 bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Campus food, made simple</div>
                <h1 class="max-w-2xl text-4xl font-extrabold leading-[1.12] tracking-tight text-slate-950 sm:text-5xl">Lapar di kampus? <span class="text-brand-600">Pesan dulu,</span> tinggal ambil.</h1>
                <p class="mt-5 max-w-xl text-base leading-7 text-slate-500">Temukan menu favorit dari tenant kantin UIB, pesan tanpa antre, lalu ambil saat pesanan siap.</p>
                <form method="get" action="<?= e(url('home')) ?>" class="mt-7 flex max-w-xl flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-lg shadow-slate-900/5 sm:flex-row">
                    <input type="hidden" name="r" value="home">
                    <div class="flex flex-1 items-center gap-2 px-2"><i data-lucide="search" class="h-5 w-5 shrink-0 text-slate-400"></i><input name="q" value="<?= e($q) ?>" class="w-full border-0 bg-transparent py-2 text-sm outline-none placeholder:text-slate-400 focus:ring-0" placeholder="Cari nasi goreng, kopi, ayam..."></div><button class="btn-primary !rounded-xl px-5">Cari menu</button>
                </form>
                <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-slate-500"><span class="flex items-center gap-1.5"><i data-lucide="clock-3" class="h-3.5 w-3.5 text-brand-600"></i> Pre-order praktis</span><span class="flex items-center gap-1.5"><i data-lucide="store" class="h-3.5 w-3.5 text-brand-600"></i> Tenant kantin kampus</span><span class="flex items-center gap-1.5"><i data-lucide="shopping-bag" class="h-3.5 w-3.5 text-brand-600"></i> Pickup langsung</span></div>
            </div>
            <div class="relative mx-auto w-full max-w-lg">
                <div class="absolute -inset-3 rounded-[2rem] bg-gradient-to-br from-brand-100 to-sky-100 blur-xl"></div>
                <div class="relative overflow-hidden rounded-[1.8rem] border border-white bg-slate-100 p-3 shadow-2xl shadow-slate-900/10">
                    <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=1000&q=85" alt="Menu makanan" class="h-[260px] w-full rounded-[1.3rem] object-cover sm:h-[310px]" onerror="this.src='<?= e(asset('images/food-placeholder.svg')) ?>'">
                    <div class="absolute bottom-7 left-7 right-7 rounded-2xl border border-white/80 bg-white/95 p-4 shadow-xl backdrop-blur">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-brand-700">Pickup made easy</p>
                                <p class="mt-1 font-bold text-slate-900">Pesanan siap diambil</p>
                            </div><span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><i data-lucide="check-check" class="h-5 w-5"></i></span>
                        </div>
                        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full w-4/5 rounded-full bg-emerald-500"></div>
                        </div>
                        <p class="mt-2 text-[10px] text-slate-500">Pesan · Diproses · Ambil di tenant</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="container-app py-9">
        <div class="mb-5 flex items-end justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.15em] text-brand-600">Find your craving</p>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Jelajahi tenant</h2>
            </div><span class="text-xs font-medium text-slate-400"><?= count($stores) ?> tenant terdaftar</span>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            <?php foreach ($stores as $store): ?>
                <a href="<?= e(url('store', ['id' => $store['id']])) ?>" class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-50 to-sky-100 text-brand-700"><i data-lucide="store" class="h-5 w-5"></i></span><span class="min-w-0 flex-1"><span class="block truncate text-sm font-bold text-slate-800 group-hover:text-brand-700"><?= e($store['name']) ?></span><span class="mt-0.5 block truncate text-[11px] text-slate-400"><?= (int) $store['product_count'] ?> menu · <?= e($store['location']) ?></span></span><i data-lucide="chevron-right" class="h-4 w-4 shrink-0 text-slate-300 group-hover:text-brand-600"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="container-app pb-10">
        <div class="mb-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.15em] text-brand-600">Good food, good mood</p>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900"><?= $q !== '' ? 'Hasil pencarian' : 'Menu pilihan hari ini' ?></h2>
                <p class="mt-1 text-sm text-slate-500"><?= count($products) ?> menu tersedia untuk dipesan</p>
            </div>
            <form method="get" action="<?= e(url('home')) ?>" class="flex flex-wrap gap-2"><input type="hidden" name="r" value="home"><?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?><select name="category" class="input !w-auto !py-2 text-xs" onchange="this.form.submit()">
                    <option value="0">Semua kategori</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?>
                </select><select name="sort" class="input !w-auto !py-2 text-xs" onchange="this.form.submit()">
                    <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Terbaru</option>
                    <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Harga terendah</option>
                    <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Harga tertinggi</option>
                    <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Nama A-Z</option>
                </select></form>
        </div>
        <div class="mb-5 flex gap-2 overflow-x-auto pb-1"><a href="<?= e(url('home')) ?>" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-bold <?= $categoryId === 0 ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200' ?>">Semua menu</a><?php foreach ($categories as $category): ?><a href="<?= e(url('home', ['category' => $category['id']])) ?>" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-bold <?= $categoryId === (int) $category['id'] ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200' ?>"><?= e($category['name']) ?></a><?php endforeach; ?></div>
        <?php if ($products): ?><div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"><?php foreach ($products as $product) product_card($product); ?></div>
        <?php else: ?><div class="card flex flex-col items-center px-6 py-16 text-center"><span class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400"><i data-lucide="search-x" class="h-6 w-6"></i></span>
                <h3 class="mt-4 font-bold text-slate-800">Menu belum ditemukan</h3>
                <p class="mt-1 max-w-sm text-sm text-slate-500">Coba kata kunci atau kategori lain. Menu baru akan muncul di sini saat tenant menambahkannya.</p><a href="<?= e(url('home')) ?>" class="btn-secondary mt-4">Reset filter</a>
            </div><?php endif; ?>
    </section>
<?php
                render_buyer_footer();
            }
