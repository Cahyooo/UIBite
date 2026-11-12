<?php

function render_buyer_order_detail(): void
            {
                $buyer = require_role('buyer');
                $id = (int) ($_GET['id'] ?? 0);
                $stmt = db()->prepare('SELECT o.*, s.name AS store_name, s.location, s.phone AS store_phone, p.payment_method, p.payment_status, p.payment_reference, p.paid_at FROM orders o JOIN stores s ON s.id=o.store_id JOIN payments p ON p.order_id=o.id WHERE o.id=? AND o.buyer_id=? LIMIT 1');
                $stmt->execute([$id, $buyer['id']]);
                $order = $stmt->fetch();
                if (!$order) {
                    flash('error', 'Pesanan tidak ditemukan.');
                    redirect('buyer.orders');
                }
                $stmt = db()->prepare('SELECT oi.*, r.id AS review_id, r.rating AS review_rating, r.comment AS review_comment FROM order_items oi LEFT JOIN reviews r ON r.order_item_id=oi.id WHERE oi.order_id=? ORDER BY oi.id');
                $stmt->execute([$id]);
                $items = $stmt->fetchAll();
                $steps = ['pending' => 'Pesanan dibuat', 'confirmed' => 'Dikonfirmasi penjual', 'preparing' => 'Sedang disiapkan', 'ready' => 'Siap diambil', 'completed' => 'Selesai'];
                $stepOrder = array_keys($steps);
                $currentIndex = array_search($order['order_status'], $stepOrder, true);
                render_buyer_header('Detail pesanan');
?>
    <section class="container-app py-9"><a href="<?= e(url('buyer.orders')) ?>" class="mb-5 inline-flex items-center gap-1 text-xs font-semibold text-slate-400 hover:text-brand-700"><i data-lucide="arrow-left" class="h-3.5 w-3.5"></i>Kembali ke pesanan</a>
        <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.15em] text-brand-600">Order details</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight"><?= e($order['order_code']) ?></h1>
                <p class="mt-1 text-sm text-slate-500">Dibuat <?= format_datetime($order['created_at']) ?></p>
            </div><?= status_badge($order['order_status']) ?>
        </div>
        <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
            <div class="space-y-5">
                <div class="card p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="font-extrabold">Status pesanan</h2><span class="text-xs font-semibold text-slate-400">Live status</span>
                    </div><?php if (in_array($order['order_status'], ['cancelled', 'rejected'], true)): ?><div class="mt-4 rounded-xl bg-rose-50 p-4 text-sm text-rose-700">
                            <p class="font-bold">Pesanan <?= e(status_label($order['order_status'])) ?></p>
                            <p class="mt-1 text-xs">Pesanan ini tidak akan diproses lebih lanjut. Stok telah dikembalikan ke tenant.</p>
                        </div><?php else: ?><div class="mt-6 grid grid-cols-5 gap-1"><?php foreach ($stepOrder as $index => $step): $done = $currentIndex !== false && $index <= $currentIndex; ?><div class="h-1.5 rounded-full <?= $done ? 'bg-brand-600' : 'bg-slate-100' ?>"></div><?php endforeach; ?></div>
                        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-5"><?php foreach ($steps as $step => $label): $done = $currentIndex !== false && array_search($step, $stepOrder, true) <= $currentIndex; ?><div class="flex items-start gap-2"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full <?= $done ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-400' ?>"><i data-lucide="<?= $done ? 'check' : 'circle' ?>" class="h-3 w-3"></i></span><span class="text-[11px] leading-4 <?= $done ? 'font-bold text-slate-700' : 'text-slate-400' ?>"><?= e($label) ?></span></div><?php endforeach; ?></div><?php endif; ?>
                </div>
                <?php if ($order['order_status'] === 'ready'): ?><div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                        <div class="flex items-center gap-2 text-emerald-800"><i data-lucide="party-popper" class="h-5 w-5"></i>
                            <h2 class="font-extrabold">Pesanan siap diambil!</h2>
                        </div>
                        <p class="mt-1 text-sm text-emerald-700">Silakan datang ke <?= e($order['store_name']) ?> dan tunjukkan kode pickup berikut.</p>
                        <div class="mt-4 rounded-xl border border-dashed border-emerald-300 bg-white px-4 py-3 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-[.18em] text-emerald-600">Pickup code</p>
                            <p class="mt-1 font-mono text-3xl font-extrabold tracking-[.2em] text-slate-900"><?= e($order['pickup_code']) ?></p>
                        </div>
                    </div><?php endif; ?>
                <div class="card p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="font-extrabold">Item pesanan</h2><span class="text-xs text-slate-400"><?= count($items) ?> jenis</span>
                    </div>
                    <div class="divide-y divide-slate-100"><?php foreach ($items as $item): ?><div class="flex flex-wrap items-center justify-between gap-3 py-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-800"><?= e($item['product_name']) ?></p>
                                    <p class="mt-1 text-xs text-slate-400"><?= (int) $item['quantity'] ?> × <?= money($item['unit_price']) ?></p>
                                </div>
                                <p class="font-extrabold text-slate-900"><?= money($item['line_total']) ?></p>
                            </div><?php if ($order['order_status'] === 'completed'): ?><div class="pb-4"><?php if ($item['review_id']): ?><div class="rounded-lg bg-slate-50 p-3 text-xs"><span class="font-bold text-amber-600">★ <?= (int) $item['review_rating'] ?>/5</span><span class="ml-2 text-slate-500"><?= e($item['review_comment'] ?: 'Review tersimpan') ?></span></div><?php else: ?><form method="post" action="<?= e(url('review.submit')) ?>" class="rounded-xl border border-slate-200 bg-slate-50 p-3"><?= csrf_field() ?><input type="hidden" name="order_item_id" value="<?= (int) $item['id'] ?>">
                                            <div class="flex flex-wrap items-center gap-2"><label class="text-xs font-bold text-slate-600">Beri rating</label><select class="input !w-24 !py-1.5 text-xs" name="rating">
                                                    <option value="5">5 ★</option>
                                                    <option value="4">4 ★</option>
                                                    <option value="3">3 ★</option>
                                                    <option value="2">2 ★</option>
                                                    <option value="1">1 ★</option>
                                                </select><input class="input min-w-0 flex-1 !py-1.5 text-xs" name="comment" placeholder="Bagaimana makanannya?" maxlength="500"><button class="btn-primary !px-3 !py-1.5 text-xs">Kirim</button></div>
                                        </form><?php endif; ?></div><?php endif; ?><?php endforeach; ?></div>
                </div>
            </div>
            <aside class="space-y-4">
                <div class="card p-5">
                    <h2 class="font-extrabold">Ringkasan pembayaran</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between text-slate-500"><span>Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
                        <div class="flex justify-between text-slate-500"><span>Biaya layanan</span><span>Gratis</span></div>
                        <div class="flex justify-between border-t border-dashed border-slate-200 pt-3 text-base font-extrabold"><span>Total</span><span><?= money($order['total']) ?></span></div>
                    </div>
                    <div class="mt-4 rounded-xl bg-slate-50 p-3">
                        <div class="flex items-center justify-between"><span class="text-xs font-semibold text-slate-500">Metode</span><span class="text-xs font-bold text-slate-800"><?= $order['payment_method'] === 'qris_simulation' ? 'QRIS Simulation' : 'Cash on Pickup' ?></span></div>
                        <div class="mt-2 flex items-center justify-between"><span class="text-xs font-semibold text-slate-500">Pembayaran</span><?= payment_status_badge($order['payment_status']) ?></div><?php if ($order['payment_reference']): ?><p class="mt-2 break-all text-[10px] text-slate-400">Ref: <?= e($order['payment_reference']) ?></p><?php endif; ?>
                    </div>
                    <?php if ($order['payment_method'] === 'qris_simulation' && $order['payment_status'] === 'pending' && $order['order_status'] === 'pending'): ?><div class="mt-4 rounded-xl border border-sky-200 bg-sky-50 p-4">
                            <div class="mx-auto grid h-28 w-28 place-items-center rounded-xl border border-sky-100 bg-white text-slate-800">
                                <div class="text-center"><i data-lucide="qr-code" class="mx-auto h-16 w-16"></i><span class="text-[9px] font-bold tracking-widest">UIBITE DEMO</span></div>
                            </div>
                            <p class="mt-3 text-center text-xs leading-5 text-sky-800">Simulasi QRIS untuk keperluan demonstrasi. Tidak terhubung ke bank atau e-wallet.</p>
                            <form method="post" action="<?= e(url('payment.simulate')) ?>" class="mt-3"><?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>"><button class="btn-primary w-full !bg-sky-700 hover:!bg-sky-800">Simulasikan pembayaran sukses</button></form>
                        </div><?php endif; ?>
                    <?php if ($order['order_status'] === 'pending'): ?><form method="post" action="<?= e(url('buyer.order.cancel')) ?>" class="mt-3" onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan ke tenant.')"><?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>"><button class="btn-danger w-full">Batalkan pesanan</button></form><?php endif; ?>
                </div>
                <div class="card p-5">
                    <h2 class="font-extrabold">Lokasi pickup</h2>
                    <p class="mt-2 font-bold text-slate-800"><?= e($order['store_name']) ?></p>
                    <p class="mt-1 flex items-start gap-1.5 text-xs leading-5 text-slate-500"><i data-lucide="map-pin" class="mt-0.5 h-3.5 w-3.5 shrink-0"></i><?= e($order['location']) ?></p><?php if ($order['store_phone']): ?><p class="mt-2 text-xs text-slate-500">Kontak: <?= e($order['store_phone']) ?></p><?php endif; ?>
                </div>
            </aside>
        </div>
    </section>
<?php render_buyer_footer();
            }
