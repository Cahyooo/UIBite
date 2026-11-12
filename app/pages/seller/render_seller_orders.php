<?php

function render_seller_orders(): void
            {
                $seller = require_role('seller');
                $store = get_store_for_seller((int)$seller['id']);
                $status = (string)($_GET['status'] ?? 'all');
                $allowed = ['all', 'pending', 'confirmed', 'preparing', 'ready', 'completed', 'rejected'];
                if (!in_array($status, $allowed, true)) $status = 'all';
                $sql = "SELECT o.*, u.name AS buyer_name, p.payment_method, p.payment_status, (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id=o.id) AS item_count FROM orders o JOIN users u ON u.id=o.buyer_id JOIN payments p ON p.order_id=o.id WHERE o.store_id=?";
                $params = [$store['id']];
                if ($status !== 'all') {
                    $sql .= ' AND o.order_status=?';
                    $params[] = $status;
                }
                $sql .= ' ORDER BY FIELD(o.order_status,\'pending\',\'confirmed\',\'preparing\',\'ready\',\'completed\',\'rejected\'), o.created_at DESC LIMIT 100';
                $stmt = db()->prepare($sql);
                $stmt->execute($params);
                $orders = $stmt->fetchAll();
                render_seller_start('Pesanan', 'seller.orders');
?>
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[.15em] text-brand-600">Order management</p>
        <h2 class="mt-1 text-2xl font-extrabold tracking-tight">Pesanan masuk</h2>
        <p class="mt-1 text-sm text-slate-500">Terima pesanan, siapkan makanan, lalu tandai siap diambil.</p>
    </div>
    <div class="mb-5 flex gap-2 overflow-x-auto pb-1"><?php foreach ($allowed as $filter): ?><a href="<?= e(url('seller.orders', ['status' => $filter])) ?>" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-bold <?= $status === $filter ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200' ?>"><?= $filter === 'all' ? 'Semua' : e(status_label($filter)) ?></a><?php endforeach; ?></div>
    <div class="space-y-3"><?php foreach ($orders as $order): ?><article class="card p-5">
                <div class="flex flex-col justify-between gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-start">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-extrabold text-slate-900"><?= e($order['order_code']) ?></h3><?= status_badge($order['order_status']) ?>
                        </div>
                        <p class="mt-1 text-xs text-slate-500"><?= e($order['buyer_name']) ?> · <?= format_datetime($order['created_at']) ?> · <?= (int)$order['item_count'] ?> jenis menu</p>
                    </div>
                    <div class="sm:text-right">
                        <p class="text-lg font-extrabold text-slate-900"><?= money($order['total']) ?></p>
                        <p class="mt-1 text-[11px] text-slate-400"><?= $order['payment_method'] === 'qris_simulation' ? 'QRIS Simulation' : 'Cash on Pickup' ?> · <?= e(payment_status_label($order['payment_status'])) ?></p>
                    </div>
                </div>
                <?php $itemStmt = db()->prepare('SELECT product_name,quantity,unit_price,line_total FROM order_items WHERE order_id=?');
                                $itemStmt->execute([$order['id']]);
                                $orderItems = $itemStmt->fetchAll(); ?><div class="flex flex-wrap gap-x-5 gap-y-2 py-3"><?php foreach ($orderItems as $item): ?><div class="text-xs"><span class="font-bold text-slate-700"><?= (int)$item['quantity'] ?>× <?= e($item['product_name']) ?></span><span class="ml-1 text-slate-400">· <?= money($item['line_total']) ?></span></div><?php endforeach; ?></div>
                <?php if ($order['customer_note']): ?><div class="mb-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800"><strong>Catatan:</strong> <?= e($order['customer_note']) ?></div><?php endif; ?>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
                    <div class="text-xs text-slate-400">Pickup code <strong class="ml-1 font-mono tracking-widest text-slate-700"><?= e($order['pickup_code']) ?></strong></div>
                    <div class="flex flex-wrap gap-2">
                        <?php if ($order['order_status'] === 'pending'): ?><?php if ($order['payment_method'] !== 'qris_simulation' || $order['payment_status'] === 'paid'): ?><form method="post" action="<?= e(url('seller.order.update')) ?>"><?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>"><input type="hidden" name="next_status" value="confirmed"><button class="btn-primary !px-3 !py-2 text-xs"><i data-lucide="check" class="h-3.5 w-3.5"></i>Terima pesanan</button></form><?php else: ?><span class="rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">Menunggu pembayaran buyer</span><?php endif; ?><form method="post" action="<?= e(url('seller.order.update')) ?>" onsubmit="return confirm('Tolak pesanan dan kembalikan stok?')"><?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>"><input type="hidden" name="next_status" value="rejected"><button class="btn-danger !px-3 !py-2 text-xs">Tolak</button></form>
                <?php elseif ($order['order_status'] === 'confirmed'): ?><form method="post" action="<?= e(url('seller.order.update')) ?>"><?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>"><input type="hidden" name="next_status" value="preparing"><button class="btn-primary !px-3 !py-2 text-xs">Mulai siapkan <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i></button></form>
                <?php elseif ($order['order_status'] === 'preparing'): ?><form method="post" action="<?= e(url('seller.order.update')) ?>"><?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>"><input type="hidden" name="next_status" value="ready"><button class="btn-primary !px-3 !py-2 text-xs"><i data-lucide="bell-ring" class="h-3.5 w-3.5"></i>Tandai siap pickup</button></form>
                <?php elseif ($order['order_status'] === 'ready'): ?><form method="post" action="<?= e(url('seller.order.update')) ?>"><?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>"><input type="hidden" name="next_status" value="completed"><button class="btn-primary !px-3 !py-2 text-xs"><i data-lucide="check-check" class="h-3.5 w-3.5"></i>Selesaikan pesanan</button></form><?php endif; ?></div>
                </div>
            </article><?php endforeach; ?><?php if (!$orders): ?><div class="card p-12 text-center"><i data-lucide="clipboard-list" class="mx-auto h-8 w-8 text-slate-300"></i>
                <h3 class="mt-4 font-bold">Belum ada pesanan</h3>
                <p class="mt-1 text-sm text-slate-500">Pesanan pembeli akan tampil di halaman ini.</p>
            </div><?php endif; ?></div>
<?php render_seller_end();
            }
