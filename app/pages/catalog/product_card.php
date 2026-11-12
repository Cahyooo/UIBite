<?php

function product_card(array $product): void
            {
?>
    <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition duration-200 hover:-translate-y-1 hover:border-brand-200 hover:shadow-lg hover:shadow-slate-900/5">
        <a href="<?= e(url('product', ['id' => $product['id']])) ?>" class="relative block overflow-hidden bg-slate-100">
            <?= product_image($product['image_url'] ?? null, $product['name']) ?>
            <span class="absolute left-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-[10px] font-bold text-slate-600 shadow-sm"><?= e($product['category_name'] ?? 'Menu') ?></span>
        </a>
        <div class="p-4">
            <div class="mb-1 flex items-center justify-between gap-2"><a href="<?= e(url('store', ['id' => $product['store_id']])) ?>" class="truncate text-[11px] font-semibold text-brand-700 hover:underline"><?= e($product['store_name'] ?? 'Tenant UIB') ?></a><span class="flex shrink-0 items-center gap-1 text-[11px] font-bold text-amber-600"><i data-lucide="star" class="h-3 w-3 fill-amber-400"></i><?= number_format((float) ($product['rating'] ?? 0), 1) ?> <span class="font-medium text-slate-400">(<?= (int) ($product['review_count'] ?? 0) ?>)</span></span></div>
            <a href="<?= e(url('product', ['id' => $product['id']])) ?>" class="line-clamp-1 font-bold text-slate-900 transition group-hover:text-brand-700"><?= e($product['name']) ?></a>
            <div class="mt-3 flex items-center justify-between gap-2">
                <div>
                    <p class="font-extrabold text-slate-900"><?= money($product['price']) ?></p>
                    <p class="mt-0.5 text-[10px] text-slate-400">Stok <?= (int) $product['stock'] ?></p>
                </div>
                <?php if ((int)($product['is_available'] ?? 1) === 1 && (int)($product['stock'] ?? 0) > 0 && (int)($product['is_open'] ?? 1) === 1): ?><form method="post" action="<?= e(url('cart.add')) ?>"><?= csrf_field() ?><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><input type="hidden" name="quantity" value="1"><button class="grid h-9 w-9 place-items-center rounded-xl bg-brand-50 text-brand-700 transition hover:bg-brand-600 hover:text-white" aria-label="Tambah ke keranjang"><i data-lucide="plus" class="h-4 w-4"></i></button></form><?php else: ?><button disabled class="grid h-9 w-9 place-items-center rounded-xl bg-slate-100 text-slate-300" aria-label="Tidak tersedia"><i data-lucide="x" class="h-4 w-4"></i></button><?php endif; ?>
            </div>
        </div>
    </article>
<?php
            }
