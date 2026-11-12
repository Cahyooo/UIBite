<?php

function render_cart(): void
            {
                $products = cart_products();
                $subtotal = cart_subtotal($products);
                $storeName = $products[0]['store_name'] ?? '';
                render_buyer_header('Keranjang');
?>
    <section class="container-app py-9">
        <div class="mb-7">
            <p class="text-xs font-bold uppercase tracking-[.15em] text-brand-600">Your selection</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Keranjang belanja</h1>
            <p class="mt-2 text-sm text-slate-500">Periksa kembali menu sebelum membuat pre-order.</p>
        </div>
        <?php if (!$products): ?><div class="card flex flex-col items-center px-6 py-16 text-center"><span class="grid h-16 w-16 place-items-center rounded-2xl bg-brand-50 text-brand-600"><i data-lucide="shopping-bag" class="h-7 w-7"></i></span>
                <h2 class="mt-5 text-lg font-extrabold">Keranjang masih kosong</h2>
                <p class="mt-1 text-sm text-slate-500">Yuk, cari makanan favorit dari tenant kantin UIB.</p><a class="btn-primary mt-5" href="<?= e(url('home')) ?>">Jelajahi menu <i data-lucide="arrow-right" class="h-4 w-4"></i></a>
            </div>
        <?php else: ?>
            <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
                <div class="space-y-4">
                    <div class="card flex items-center gap-3 p-4"><span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-50 text-brand-700"><i data-lucide="store" class="h-5 w-5"></i></span>
                        <div>
                            <p class="text-xs text-slate-400">Pesanan dari satu tenant</p>
                            <p class="font-bold text-slate-800"><?= e($storeName) ?></p>
                        </div><span class="ml-auto badge bg-sky-50 text-sky-700">Pickup</span>
                    </div>
                    <form method="post" action="<?= e(url('cart.update')) ?>" class="card overflow-hidden"><?= csrf_field() ?><div class="divide-y divide-slate-100"><?php foreach ($products as $product): ?><div data-cart-row class="flex gap-4 p-4 sm:p-5">
                                    <div class="h-24 w-24 shrink-0 overflow-hidden rounded-xl"><?= product_image($product['image_url'], $product['name']) ?></div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <p class="text-[11px] font-semibold text-brand-700"><?= e($product['category_name']) ?></p>
                                                <h3 class="mt-0.5 font-bold text-slate-900"><?= e($product['name']) ?></h3>
                                                <p class="mt-1 text-sm font-bold text-slate-700"><?= money($product['price']) ?></p>
                                            </div><button type="button" data-remove-cart-item class="grid h-8 w-8 shrink-0 place-items-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600" aria-label="Hapus"><i data-lucide="trash-2" class="h-4 w-4"></i></button>
                                        </div>
                                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3"><label class="flex items-center gap-2 text-xs text-slate-500">Qty <input type="number" min="0" max="20" name="quantities[<?= (int) $product['id'] ?>]" value="<?= (int) $product['cart_quantity'] ?>" class="input !w-20 !py-1.5 text-center text-sm"></label>
                                            <p class="text-sm font-extrabold text-slate-900"><?= money((float) $product['price'] * (int) $product['cart_quantity']) ?></p>
                                        </div><?php if ((int) $product['stock'] < (int) $product['cart_quantity']): ?><p class="mt-2 text-xs font-medium text-rose-600">Stok tersisa <?= (int) $product['stock'] ?>. Perbarui jumlah sebelum checkout.</p><?php endif; ?>
                                    </div>
                                </div><?php endforeach; ?></div>
                        <div class="flex justify-between border-t border-slate-100 p-4"><a href="<?= e(url('home')) ?>" class="text-sm font-semibold text-brand-700 hover:underline">+ Tambah menu lain</a><button class="text-sm font-bold text-slate-600 hover:text-brand-700" type="submit">Perbarui keranjang</button></div>
                    </form>
                </div>
                <aside class="card h-fit p-5">
                    <h2 class="font-extrabold text-slate-900">Ringkasan pesanan</h2>
                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between text-slate-500"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
                        <div class="flex justify-between text-slate-500"><span>Biaya layanan</span><span class="font-semibold text-emerald-600">Gratis</span></div>
                        <div class="border-t border-dashed border-slate-200 pt-3">
                            <div class="flex justify-between font-extrabold text-slate-900"><span>Total</span><span><?= money($subtotal) ?></span></div>
                        </div>
                    </div>
                    <p class="mt-4 rounded-xl bg-slate-50 p-3 text-xs leading-5 text-slate-500">Pembayaran dilakukan sesuai metode yang dipilih. Makanan diambil langsung di tenant setelah status pesanan menjadi siap.</p><a href="<?= e(url('checkout')) ?>" class="btn-primary mt-5 w-full">Lanjut checkout <i data-lucide="arrow-right" class="h-4 w-4"></i></a>
                </aside>
            </div>
        <?php endif; ?>
    </section>
<?php render_buyer_footer();
            }
