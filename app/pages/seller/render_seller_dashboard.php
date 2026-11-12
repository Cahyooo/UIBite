<?php

function render_seller_dashboard(): void
            {
                $seller = require_role('seller');
                $store = get_store_for_seller((int) $seller['id']);
                if (!$store) {
                    flash('error', 'Toko belum terhubung ke akun ini.');
                    redirect('home');
                }
                $stmt = db()->prepare("SELECT
        (SELECT COUNT(*) FROM orders o WHERE o.store_id=? AND DATE(o.created_at)=CURDATE()) AS orders_today,
        (SELECT COUNT(*) FROM orders o WHERE o.store_id=? AND o.order_status IN ('pending','confirmed','preparing','ready')) AS active_orders,
        (SELECT COALESCE(SUM(p.amount),0) FROM payments p JOIN orders o ON o.id=p.order_id WHERE o.store_id=? AND p.payment_status='paid' AND DATE(p.paid_at)=CURDATE()) AS revenue_today,
        (SELECT COUNT(*) FROM products p WHERE p.store_id=? AND p.is_available=1) AS active_products,
        (SELECT COUNT(*) FROM products p WHERE p.store_id=? AND p.stock <= 5 AND p.is_available=1) AS low_stock");
                $stmt->execute(array_fill(0, 5, $store['id']));
                $stats = $stmt->fetch();
                $stmt = db()->prepare("SELECT DATE(p.paid_at) AS sale_date, SUM(p.amount) AS revenue FROM payments p JOIN orders o ON o.id=p.order_id WHERE o.store_id=? AND p.payment_status='paid' AND p.paid_at >= CURDATE() - INTERVAL 6 DAY GROUP BY DATE(p.paid_at) ORDER BY sale_date");
                $stmt->execute([$store['id']]);
                $salesRows = $stmt->fetchAll();
                $salesMap = [];
                foreach ($salesRows as $row) $salesMap[$row['sale_date']] = (float) $row['revenue'];
                $chartLabels = [];
                $chartValues = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime('-' . $i . ' days'));
                    $chartLabels[] = date('d M', strtotime($date));
                    $chartValues[] = $salesMap[$date] ?? 0;
                }
                $stmt = db()->prepare("SELECT o.id, o.order_code, o.order_status, o.total, o.created_at, u.name AS buyer_name, p.payment_method, p.payment_status, (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id=o.id) AS item_count FROM orders o JOIN users u ON u.id=o.buyer_id JOIN payments p ON p.order_id=o.id WHERE o.store_id=? ORDER BY o.created_at DESC LIMIT 6");
                $stmt->execute([$store['id']]);
                $recentOrders = $stmt->fetchAll();
                $stmt = db()->prepare('SELECT p.id, p.name, p.stock, p.price, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.store_id=? AND p.is_available=1 ORDER BY p.stock ASC LIMIT 5');
                $stmt->execute([$store['id']]);
                $lowStock = $stmt->fetchAll();
                render_seller_start('Ringkasan', 'seller.dashboard');
?>
    <div class="mb-7 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.15em] text-brand-600">Overview</p>
            <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Halo, <?= e($seller['name']) ?> 👋</h2>
            <p class="mt-1 text-sm text-slate-500">Berikut ringkasan aktivitas <?= e($store['name']) ?>.</p>
        </div><a href="<?= e(url('seller.product-form')) ?>" class="btn-primary"><i data-lucide="plus" class="h-4 w-4"></i>Tambah menu</a>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <?php $cards = [['wallet', 'Pendapatan hari ini', money($stats['revenue_today']), 'Dari pembayaran berhasil', 'text-emerald-600', 'bg-emerald-50'], ['clipboard-list', 'Pesanan hari ini', number_format((int)$stats['orders_today']), 'Pesanan masuk hari ini', 'text-brand-600', 'bg-brand-50'], ['activity', 'Pesanan aktif', number_format((int)$stats['active_orders']), 'Menunggu hingga siap pickup', 'text-violet-600', 'bg-violet-50'], ['package', 'Menu aktif', number_format((int)$stats['active_products']), 'Stok rendah: ' . (int)$stats['low_stock'], 'text-amber-600', 'bg-amber-50']];
                foreach ($cards as [$icon, $label, $value, $desc, $color, $bg]): ?>
            <div class="card p-5">
                <div class="flex items-center justify-between"><span class="text-xs font-bold text-slate-500"><?= e($label) ?></span><span class="grid h-9 w-9 place-items-center rounded-xl <?= $bg ?> <?= $color ?>"><i data-lucide="<?= e($icon) ?>" class="h-[18px] w-[18px]"></i></span></div>
                <p class="mt-4 text-2xl font-extrabold tracking-tight text-slate-950"><?= e($value) ?></p>
                <p class="mt-1 text-[11px] text-slate-400"><?= e($desc) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="mt-6 grid gap-5 xl:grid-cols-[1.45fr_1fr]">
        <section class="card p-5">
            <div class="mb-5 flex items-start justify-between">
                <div>
                    <h3 class="font-extrabold text-slate-900">Tren pendapatan</h3>
                    <p class="mt-1 text-xs text-slate-400">7 hari terakhir · pembayaran berhasil</p>
                </div><span class="badge bg-brand-50 text-brand-700">7 hari</span>
            </div>
            <div class="h-[250px]"><canvas id="salesChart" aria-label="Grafik pendapatan tujuh hari"></canvas></div>
        </section>
        <section class="card p-5">
            <div class="mb-4 flex items-start justify-between">
                <div>
                    <h3 class="font-extrabold text-slate-900">Perlu restock</h3>
                    <p class="mt-1 text-xs text-slate-400">Menu dengan stok 5 atau kurang</p>
                </div><a href="<?= e(url('seller.products')) ?>" class="text-xs font-bold text-brand-700 hover:underline">Lihat semua</a>
            </div>
            <div class="space-y-3"><?php foreach ($lowStock as $item): ?><div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-800"><?= e($item['name']) ?></p>
                            <p class="mt-0.5 text-[11px] text-slate-400"><?= e($item['category_name']) ?> · <?= money($item['price']) ?></p>
                        </div><span class="badge <?= (int)$item['stock'] === 0 ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700' ?>">Stok <?= (int)$item['stock'] ?></span>
                    </div><?php endforeach; ?><?php if (!$lowStock): ?><p class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">Semua stok menu masih aman.</p><?php endif; ?></div>
        </section>
    </div>
    <section class="card mt-5 overflow-hidden">
        <div class="flex items-center justify-between p-5">
            <div>
                <h3 class="font-extrabold text-slate-900">Pesanan terbaru</h3>
                <p class="mt-1 text-xs text-slate-400">Aktivitas terbaru dari pembeli</p>
            </div><a href="<?= e(url('seller.orders')) ?>" class="text-xs font-bold text-brand-700 hover:underline">Semua pesanan</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Pembeli</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody><?php foreach ($recentOrders as $order): ?><tr>
                            <td>
                                <p class="font-bold text-slate-800"><?= e($order['order_code']) ?></p>
                                <p class="mt-0.5 text-[10px] text-slate-400"><?= format_datetime($order['created_at']) ?></p>
                            </td>
                            <td><?= e($order['buyer_name']) ?></td>
                            <td><?= status_badge($order['order_status']) ?></td>
                            <td><span class="text-xs text-slate-500"><?= $order['payment_method'] === 'qris_simulation' ? 'QRIS Demo' : 'Cash' ?></span>
                                <div class="mt-1"><?= payment_status_badge($order['payment_status']) ?></div>
                            </td>
                            <td class="font-bold"><?= money($order['total']) ?></td>
                            <td><a class="text-xs font-bold text-brand-700 hover:underline" href="<?= e(url('seller.orders')) ?>">Kelola</a></td>
                        </tr><?php endforeach; ?><?php if (!$recentOrders): ?><tr>
                            <td colspan="6" class="py-10 text-center text-sm text-slate-400">Belum ada pesanan masuk.</td>
                        </tr><?php endif; ?></tbody>
            </table>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        window.addEventListener('load', () => {
            const el = document.getElementById('salesChart');
            if (!el || !window.Chart) return;
            new Chart(el, {
                type: 'line',
                data: {
                    labels: <?= json_encode($chartLabels) ?>,
                    datasets: [{
                        label: 'Pendapatan',
                        data: <?= json_encode($chartValues) ?>,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,.10)',
                        fill: true,
                        tension: .35,
                        pointRadius: 3,
                        pointBackgroundColor: '#2563eb'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ' Rp' + new Intl.NumberFormat('id-ID').format(ctx.parsed.y)
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (v) => 'Rp' + new Intl.NumberFormat('id-ID', {
                                    notation: 'compact'
                                }).format(v)
                            },
                            grid: {
                                color: '#f1f5f9'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
<?php render_seller_end();
            }
