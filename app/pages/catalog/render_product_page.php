<?php

function render_product_page(): void
            {
                $id = (int) ($_GET['id'] ?? 0);
                $stmt = db()->prepare("SELECT p.*, c.name AS category_name, s.name AS store_name, s.id AS store_id, s.is_open, (SELECT ROUND(AVG(r.rating),1) FROM reviews r WHERE r.product_id=p.id) AS rating, (SELECT COUNT(*) FROM reviews r WHERE r.product_id=p.id) AS review_count FROM products p JOIN stores s ON s.id=p.store_id LEFT JOIN categories c ON c.id=p.category_id WHERE p.id=? LIMIT 1");
                $stmt->execute([$id]);
                $product = $stmt->fetch();
                if (!$product) {
                    http_response_code(404);
                    render_home();
                    return;
                }
                $stmt = db()->prepare('SELECT r.*, u.name AS buyer_name FROM reviews r JOIN users u ON u.id=r.buyer_id WHERE r.product_id=? ORDER BY r.created_at DESC LIMIT 12');
                $stmt->execute([$id]);
                $reviews = $stmt->fetchAll();
                render_buyer_header($product['name']);
?>
    <section class="container-app py-9"><a href="<?= e(url('store', ['id' => $product['store_id']])) ?>" class="mb-6 inline-flex items-center gap-1 text-xs font-semibold text-slate-400 hover:text-brand-700"><i data-lucide="arrow-left" class="h-3.5 w-3.5"></i>Kembali ke <?= e($product['store_name']) ?></a>
        <div class="grid gap-8 md:grid-cols-2 md:items-start">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white p-2 shadow-sm"><?= product_image($product['image_url'], $product['name']) ?></div>
            <div class="pt-2"><span class="badge bg-brand-50 text-brand-700"><?= e($product['category_name']) ?></span>
                <p class="mt-4 text-xs font-bold uppercase tracking-wider text-brand-700"><?= e($product['store_name']) ?></p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950"><?= e($product['name']) ?></h1>
                <div class="mt-3 flex items-center gap-2 text-sm"><span class="flex items-center gap-1 font-bold text-amber-600"><i data-lucide="star" class="h-4 w-4 fill-amber-400"></i><?= number_format((float) $product['rating'], 1) ?></span><span class="text-slate-400">· <?= (int) $product['review_count'] ?> review</span><span class="text-slate-300">·</span><span class="text-slate-500">Stok <?= (int) $product['stock'] ?></span></div>
                <p class="mt-5 text-3xl font-extrabold text-slate-900"><?= money($product['price']) ?></p>
                <p class="mt-4 leading-7 text-slate-500"><?= nl2br(e($product['description'] ?: 'Menu pilihan dari tenant kantin UIB.')) ?></p>
                <?php if ((int) $product['is_available'] && (int) $product['stock'] > 0 && (int) $product['is_open']): ?><form method="post" action="<?= e(url('cart.add')) ?>" class="mt-7 flex flex-wrap items-center gap-3"><?= csrf_field() ?><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><label class="sr-only" for="quantity">Jumlah</label><input id="quantity" name="quantity" type="number" min="1" max="20" value="1" class="input !w-20"><button class="btn-primary px-6"><i data-lucide="shopping-bag" class="h-4 w-4"></i>Tambah ke keranjang</button></form><?php else: ?><p class="mt-6 rounded-xl bg-rose-50 p-3 text-sm font-semibold text-rose-700">Produk atau toko sedang tidak menerima pesanan.</p><?php endif; ?>
                <div class="mt-6 flex flex-wrap gap-4 border-t border-slate-100 pt-5 text-xs text-slate-500"><span class="flex items-center gap-1.5"><i data-lucide="package-check" class="h-4 w-4 text-brand-600"></i> Pickup di tenant</span><span class="flex items-center gap-1.5"><i data-lucide="shield-check" class="h-4 w-4 text-brand-600"></i> Pesanan tercatat</span></div>
            </div>
        </div>
        <div class="mt-12">
            <div class="mb-5 flex items-end justify-between">
                <div>
                    <h2 class="text-xl font-extrabold">Ulasan pembeli</h2>
                    <p class="mt-1 text-sm text-slate-500">Pengalaman pelanggan untuk menu ini.</p>
                </div><span class="text-sm font-bold text-amber-600">★ <?= number_format((float) $product['rating'], 1) ?>/5</span>
            </div>
            <?php if ($reviews): ?><div class="grid gap-3 md:grid-cols-2"><?php foreach ($reviews as $review): ?><article class="card p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-2.5"><span class="grid h-9 w-9 place-items-center rounded-full bg-slate-100 text-xs font-bold text-slate-600"><?= e(strtoupper(substr($review['buyer_name'], 0, 1))) ?></span>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800"><?= e($review['buyer_name']) ?></p>
                                        <p class="text-[11px] text-slate-400"><?= format_datetime($review['created_at'], 'd M Y') ?></p>
                                    </div>
                                </div><span class="text-sm font-bold text-amber-500">★ <?= (int) $review['rating'] ?>/5</span>
                            </div><?php if ($review['comment']): ?><p class="mt-3 text-sm leading-6 text-slate-600"><?= e($review['comment']) ?></p><?php endif; ?>
                        </article><?php endforeach; ?></div><?php else: ?><div class="card p-8 text-center text-sm text-slate-500">Belum ada review. Jadilah pembeli pertama yang memberikan ulasan setelah pesanan selesai.</div><?php endif; ?>
        </div>
    </section>
<?php render_buyer_footer();
            }
