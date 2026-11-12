<?php

function render_buyer_orders(): void
            {
                $buyer = require_role('buyer');
                $status = (string) ($_GET['status'] ?? 'all');
                $valid = ['all', 'pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled', 'rejected'];
                if (!in_array($status, $valid, true)) $status = 'all';
                $sql = "SELECT o.*, s.name AS store_name, p.payment_status, p.payment_method, (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id=o.id) AS item_count FROM orders o JOIN stores s ON s.id=o.store_id JOIN payments p ON p.order_id=o.id WHERE o.buyer_id = ?";
                $params = [$buyer['id']];
                if ($status !== 'all') {
                    $sql .= ' AND o.order_status = ?';
                    $params[] = $status;
                }
                $sql .= ' ORDER BY o.created_at DESC LIMIT 60';
                $stmt = db()->prepare($sql);
                $stmt->execute($params);
                $orders = $stmt->fetchAll();
                render_buyer_header('Pesanan saya');
?>
    <section class="container-app py-9">
        <div class="mb-7 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.15em] text-brand-600">Order history</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Pesanan saya</h1>
                <p class="mt-2 text-sm text-slate-500">Pantau proses pesanan dan lihat kode pickup.</p>
            </div><a href="<?= e(url('home')) ?>" class="btn-primary"><i data-lucide="plus" class="h-4 w-4"></i>Pesan lagi</a>
        </div>
        <div class="mb-5 flex gap-2 overflow-x-auto pb-1"><?php foreach ($valid as $filter): ?><a href="<?= e(url('buyer.orders', ['status' => $filter])) ?>" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-bold <?= $status === $filter ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200' ?>"><?= $filter === 'all' ? 'Semua' : e(status_label($filter)) ?></a><?php endforeach; ?></div>
        <?php if ($orders): ?><div class="space-y-3"><?php foreach ($orders as $order): ?><a href="<?= e(url('buyer.order', ['id' => $order['id']])) ?>" class="card group flex flex-col gap-4 p-4 transition hover:border-brand-200 hover:shadow-md sm:flex-row sm:items-center sm:justify-between sm:p-5">
                        <div class="flex items-start gap-3"><span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700"><i data-lucide="receipt" class="h-5 w-5"></i></span>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-extrabold text-slate-900"><?= e($order['order_code']) ?></p><?= status_badge($order['order_status']) ?>
                                </div>
                                <p class="mt-1 text-xs text-slate-500"><?= e($order['store_name']) ?> · <?= (int) $order['item_count'] ?> jenis menu · <?= format_datetime($order['created_at']) ?></p>
                                <p class="mt-2 text-xs text-slate-400"><?= $order['payment_method'] === 'qris_simulation' ? 'QRIS Simulation' : 'Cash on Pickup' ?> · <?= e(payment_status_label($order['payment_status'])) ?></p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-5 sm:justify-end">
                            <div class="text-left sm:text-right">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total</p>
                                <p class="mt-1 text-lg font-extrabold text-slate-900"><?= money($order['total']) ?></p>
                            </div><i data-lucide="chevron-right" class="h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-brand-600"></i>
                        </div>
                    </a><?php endforeach; ?></div><?php else: ?><div class="card p-12 text-center"><i data-lucide="receipt" class="mx-auto h-8 w-8 text-slate-300"></i>
                <h2 class="mt-4 font-bold">Belum ada pesanan</h2>
                <p class="mt-1 text-sm text-slate-500">Pesanan yang dibuat akan muncul di sini.</p><a href="<?= e(url('home')) ?>" class="btn-primary mt-4">Jelajahi menu</a>
            </div><?php endif; ?>
    </section>
<?php render_buyer_footer();
            }
