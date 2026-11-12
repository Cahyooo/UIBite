<?php

function render_checkout(): void
            {
                $buyer = require_role('buyer');
                $products = cart_products();
                if (!$products) {
                    flash('warning', 'Tambahkan menu ke keranjang terlebih dahulu.');
                    redirect('home');
                }
                $subtotal = cart_subtotal($products);
                $store = $products[0];
                $hasStockIssue = false;
                foreach ($products as $item) if ((int) $item['stock'] < (int) $item['cart_quantity'] || !(int) $item['is_available'] || !(int) $item['is_open']) $hasStockIssue = true;
                render_buyer_header('Checkout');
?>
    <section class="container-app py-9">
        <div class="mb-7">
            <p class="text-xs font-bold uppercase tracking-[.15em] text-brand-600">Almost there</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Checkout</h1>
            <p class="mt-2 text-sm text-slate-500">Konfirmasi pesanan dan pilih metode pembayaran.</p>
        </div>
        <form method="post" action="<?= e(url('checkout.place')) ?>" class="grid gap-6 lg:grid-cols-[1fr_360px]"><?= csrf_field() ?><div class="space-y-5">
                <div class="card p-5">
                    <div class="mb-4 flex items-center gap-2"><i data-lucide="user-round" class="h-5 w-5 text-brand-600"></i>
                        <h2 class="font-extrabold">Informasi pembeli</h2>
                    </div>
                    <p class="text-sm font-semibold text-slate-800"><?= e($buyer['name']) ?></p>
                    <p class="mt-1 text-sm text-slate-500"><?= e($buyer['email']) ?></p>
                    <p class="mt-1 text-sm text-slate-500"><?= e($buyer['phone'] ?? 'Nomor telepon belum diisi') ?></p>
                </div>
                <div class="card p-5">
                    <div class="mb-4 flex items-center gap-2"><i data-lucide="store" class="h-5 w-5 text-brand-600"></i>
                        <h2 class="font-extrabold">Pickup dari tenant</h2>
                    </div>
                    <p class="font-bold text-slate-800"><?= e($store['store_name']) ?></p>
                    <p class="mt-1 text-sm text-slate-500"><?= e($store['location']) ?></p>
                    <div class="mt-4 divide-y divide-slate-100 border-t border-slate-100"><?php foreach ($products as $item): ?><div class="flex items-center justify-between gap-3 py-3 text-sm"><span class="min-w-0 truncate text-slate-600"><?= (int) $item['cart_quantity'] ?>× <?= e($item['name']) ?></span><span class="shrink-0 font-bold text-slate-800"><?= money((float) $item['price'] * (int) $item['cart_quantity']) ?></span></div><?php endforeach; ?></div>
                </div>
                <div class="card p-5">
                    <div class="mb-4 flex items-center gap-2"><i data-lucide="credit-card" class="h-5 w-5 text-brand-600"></i>
                        <h2 class="font-extrabold">Metode pembayaran</h2>
                    </div>
                    <div class="space-y-3"><label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition has-[:checked]:border-brand-400 has-[:checked]:bg-brand-50"><input type="radio" class="mt-1 accent-blue-600" name="payment_method" value="cash_on_pickup" checked><span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-emerald-50 text-emerald-700"><i data-lucide="banknote" class="h-5 w-5"></i></span><span class="flex-1"><span class="block text-sm font-bold text-slate-800">Cash on Pickup</span><span class="mt-1 block text-xs leading-5 text-slate-500">Bayar tunai langsung ke penjual saat mengambil pesanan.</span></span></label><label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition has-[:checked]:border-brand-400 has-[:checked]:bg-brand-50"><input type="radio" class="mt-1 accent-blue-600" name="payment_method" value="qris_simulation"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-sky-50 text-sky-700"><i data-lucide="qr-code" class="h-5 w-5"></i></span><span class="flex-1"><span class="block text-sm font-bold text-slate-800">QRIS Simulation</span><span class="mt-1 block text-xs leading-5 text-slate-500">Pembayaran digital simulasi untuk demo. Tidak ada uang sungguhan yang diproses.</span></span><span class="badge bg-amber-50 text-amber-700">Demo</span></label></div>
                </div>
                <div class="card p-5"><label class="mb-2 block text-sm font-bold text-slate-800" for="customer_note">Catatan untuk penjual <span class="font-normal text-slate-400">(opsional)</span></label><textarea id="customer_note" name="customer_note" rows="3" maxlength="500" class="input" placeholder="Contoh: tidak pedas, saus dipisah..."></textarea></div>
            </div>
            <aside class="card h-fit p-5">
                <h2 class="font-extrabold">Ringkasan pembayaran</h2>
                <div class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between text-slate-500"><span>Subtotal (<?= cart_count() ?> item)</span><span><?= money($subtotal) ?></span></div>
                    <div class="flex justify-between text-slate-500"><span>Biaya layanan</span><span class="font-semibold text-emerald-600">Gratis</span></div>
                    <div class="border-t border-dashed border-slate-200 pt-4">
                        <div class="flex justify-between text-base font-extrabold text-slate-950"><span>Total</span><span><?= money($subtotal) ?></span></div>
                    </div>
                </div><?php if ($hasStockIssue): ?><div class="mt-4 rounded-xl bg-rose-50 p-3 text-xs leading-5 text-rose-700">Ada stok/menu yang berubah. Kembali ke keranjang untuk memeriksa sebelum checkout.</div><?php endif; ?><button class="btn-primary mt-5 w-full" type="submit" <?= $hasStockIssue ? 'disabled' : '' ?>>Buat pesanan <i data-lucide="arrow-right" class="h-4 w-4"></i></button><a href="<?= e(url('cart')) ?>" class="mt-3 block text-center text-xs font-semibold text-slate-500 hover:text-brand-700">Kembali ke keranjang</a>
                <p class="mt-5 text-center text-[10px] leading-5 text-slate-400">Dengan membuat pesanan, stok menu akan direservasi dan pesanan dikirim ke tenant.</p>
            </aside>
        </form>
    </section>
<?php render_buyer_footer();
            }
