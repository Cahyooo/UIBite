<?php

function render_seller_reports(): void
            {
                $seller = require_role('seller');
                $store = get_store_for_seller((int)$seller['id']);
                $start = (string)($_GET['start'] ?? date('Y-m-01'));
                $end = (string)($_GET['end'] ?? date('Y-m-d'));
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) $start = date('Y-m-01');
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) $end = date('Y-m-d');
                if ($start > $end) {
                    [$start, $end] = [$end, $start];
                }
                $stmt = db()->prepare("SELECT COUNT(DISTINCT o.id) AS order_count, (SELECT COALESCE(SUM(p2.amount),0) FROM orders o2 JOIN payments p2 ON p2.order_id=o2.id WHERE o2.store_id=? AND p2.payment_status='paid' AND DATE(p2.paid_at) BETWEEN ? AND ?) AS revenue, COALESCE(SUM(oi.quantity),0) AS items_sold FROM orders o LEFT JOIN order_items oi ON oi.order_id=o.id WHERE o.store_id=? AND EXISTS (SELECT 1 FROM payments p3 WHERE p3.order_id=o.id AND p3.payment_status='paid' AND DATE(p3.paid_at) BETWEEN ? AND ?)");
                $stmt->execute([$store['id'], $start, $end, $store['id'], $start, $end]);
                $totals = $stmt->fetch();
                $stmt = db()->prepare("SELECT DATE(p.paid_at) AS sale_date, COUNT(DISTINCT o.id) AS orders_count, SUM(p.amount) AS revenue FROM payments p JOIN orders o ON o.id=p.order_id WHERE o.store_id=? AND p.payment_status='paid' AND DATE(p.paid_at) BETWEEN ? AND ? GROUP BY DATE(p.paid_at) ORDER BY sale_date DESC");
                $stmt->execute([$store['id'], $start, $end]);
                $daily = $stmt->fetchAll();
                $stmt = db()->prepare("SELECT oi.product_name, SUM(oi.quantity) AS qty_sold, SUM(oi.line_total) AS revenue FROM order_items oi JOIN orders o ON o.id=oi.order_id JOIN payments p ON p.order_id=o.id WHERE o.store_id=? AND p.payment_status='paid' AND DATE(p.paid_at) BETWEEN ? AND ? GROUP BY oi.product_id, oi.product_name ORDER BY qty_sold DESC LIMIT 8");
                $stmt->execute([$store['id'], $start, $end]);
                $topProducts = $stmt->fetchAll();
                render_seller_start('Laporan', 'seller.reports');
?>
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[.15em] text-brand-600">Business insights</p>
        <h2 class="mt-1 text-2xl font-extrabold tracking-tight">Laporan penjualan</h2>
        <p class="mt-1 text-sm text-slate-500">Ringkasan performa penjualan dari pembayaran yang berhasil.</p>
    </div>
    <form method="get" action="<?= e(url('seller.reports')) ?>" class="card mb-5 flex flex-col gap-3 p-4 sm:flex-row sm:items-end"><input type="hidden" name="r" value="seller.reports">
        <div class="flex-1"><label class="mb-1.5 block text-[11px] font-bold text-slate-500">Dari tanggal</label><input type="date" name="start" value="<?= e($start) ?>" class="input"></div>
        <div class="flex-1"><label class="mb-1.5 block text-[11px] font-bold text-slate-500">Sampai tanggal</label><input type="date" name="end" value="<?= e($end) ?>" class="input"></div><button class="btn-primary"><i data-lucide="filter" class="h-4 w-4"></i>Terapkan filter</button><a class="btn-secondary" href="<?= e(url('seller.reports.csv', ['start' => $start, 'end' => $end])) ?>"><i data-lucide="download" class="h-4 w-4"></i>CSV</a>
    </form>
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="card p-5">
            <p class="text-xs font-bold text-slate-500">Total pendapatan</p>
            <p class="mt-3 text-2xl font-extrabold"><?= money($totals['revenue']) ?></p>
            <p class="mt-1 text-[11px] text-slate-400">Pembayaran berhasil</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-bold text-slate-500">Pesanan dibayar</p>
            <p class="mt-3 text-2xl font-extrabold"><?= number_format((int)$totals['order_count']) ?></p>
            <p class="mt-1 text-[11px] text-slate-400">Periode laporan</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-bold text-slate-500">Item terjual</p>
            <p class="mt-3 text-2xl font-extrabold"><?= number_format((int)$totals['items_sold']) ?></p>
            <p class="mt-1 text-[11px] text-slate-400">Jumlah kuantitas menu</p>
        </div>
    </div>
    <div class="mt-5 grid gap-5 xl:grid-cols-2">
        <section class="card overflow-hidden">
            <div class="border-b border-slate-100 p-5">
                <h3 class="font-extrabold">Penjualan per hari</h3>
                <p class="mt-1 text-xs text-slate-400">Tanggal <?= e($start) ?> sampai <?= e($end) ?></p>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pesanan</th>
                            <th>Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($daily as $row): ?><tr>
                                <td><?= format_datetime($row['sale_date'], 'd M Y') ?></td>
                                <td><?= (int)$row['orders_count'] ?></td>
                                <td class="font-bold"><?= money($row['revenue']) ?></td>
                            </tr><?php endforeach; ?><?php if (!$daily): ?><tr>
                                <td colspan="3" class="py-8 text-center text-slate-400">Belum ada transaksi pada periode ini.</td>
                            </tr><?php endif; ?></tbody>
                </table>
            </div>
        </section>
        <section class="card overflow-hidden">
            <div class="border-b border-slate-100 p-5">
                <h3 class="font-extrabold">Menu terlaris</h3>
                <p class="mt-1 text-xs text-slate-400">Berdasarkan jumlah item terjual</p>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th>Terjual</th>
                            <th>Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($topProducts as $row): ?><tr>
                                <td class="font-bold text-slate-800"><?= e($row['product_name']) ?></td>
                                <td><?= number_format((int)$row['qty_sold']) ?></td>
                                <td class="font-bold"><?= money($row['revenue']) ?></td>
                            </tr><?php endforeach; ?><?php if (!$topProducts): ?><tr>
                                <td colspan="3" class="py-8 text-center text-slate-400">Belum ada data menu.</td>
                            </tr><?php endif; ?></tbody>
                </table>
            </div>
        </section>
    </div>
<?php render_seller_end();
            }
