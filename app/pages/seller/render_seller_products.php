<?php

function render_seller_products(): void
            {
                $seller = require_role('seller');
                $store = get_store_for_seller((int)$seller['id']);
                $q = trim((string)($_GET['q'] ?? ''));
                $availability = (string)($_GET['availability'] ?? 'all');
                $sql = 'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.store_id=?';
                $params = [$store['id']];
                if ($q !== '') {
                    $sql .= ' AND (p.name LIKE ? OR p.sku LIKE ?)';
                    $params[] = '%' . $q . '%';
                    $params[] = '%' . $q . '%';
                }
                if ($availability === 'active') $sql .= ' AND p.is_available=1';
                elseif ($availability === 'inactive') $sql .= ' AND p.is_available=0';
                $sql .= ' ORDER BY p.created_at DESC';
                $stmt = db()->prepare($sql);
                $stmt->execute($params);
                $products = $stmt->fetchAll();
                render_seller_start('Menu makanan', 'seller.products');
?>
    <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.15em] text-brand-600">Catalog management</p>
            <h2 class="mt-1 text-2xl font-extrabold tracking-tight">Menu makanan</h2>
            <p class="mt-1 text-sm text-slate-500">Kelola produk, harga, ketersediaan, dan stok.</p>
        </div><a class="btn-primary" href="<?= e(url('seller.product-form')) ?>"><i data-lucide="plus" class="h-4 w-4"></i>Tambah menu</a>
    </div>
    <div class="card overflow-hidden">
        <form method="get" action="<?= e(url('seller.products')) ?>" class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row"><input type="hidden" name="r" value="seller.products">
            <div class="relative flex-1"><i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i><input class="input !pl-9" name="q" value="<?= e($q) ?>" placeholder="Cari nama menu atau SKU..."></div><select name="availability" class="input !w-auto">
                <option value="all">Semua status</option>
                <option value="active" <?= $availability === 'active' ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= $availability === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
            </select><button class="btn-secondary">Filter</button>
        </form>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th>Kategori</th>
                        <th>SKU</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($products as $product): ?><tr>
                            <td>
                                <div class="flex min-w-[190px] items-center gap-3">
                                    <div class="h-12 w-14 shrink-0 overflow-hidden rounded-lg"><?= product_image($product['image_url'], $product['name']) ?></div>
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-slate-800"><?= e($product['name']) ?></p>
                                        <p class="mt-0.5 line-clamp-1 text-[10px] text-slate-400"><?= e($product['description'] ?? '') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($product['category_name']) ?></td>
                            <td class="font-mono text-xs text-slate-500"><?= e($product['sku']) ?></td>
                            <td class="font-bold"><?= money($product['price']) ?></td>
                            <td><span class="font-bold <?= (int)$product['stock'] <= 5 ? 'text-amber-700' : 'text-slate-700' ?>"><?= (int)$product['stock'] ?></span></td>
                            <td><?= (int)$product['is_available'] ? '<span class="badge bg-emerald-50 text-emerald-700">Aktif</span>' : '<span class="badge bg-slate-100 text-slate-500">Nonaktif</span>' ?></td>
                            <td>
                                <div class="flex items-center gap-1"><a href="<?= e(url('seller.product-form', ['id' => $product['id']])) ?>" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-brand-50 hover:text-brand-700" title="Edit"><i data-lucide="pencil" class="h-4 w-4"></i></a><?php if ((int)$product['is_available']): ?><form method="post" action="<?= e(url('seller.product.delete')) ?>" onsubmit="return confirm('Nonaktifkan menu ini?')"><?= csrf_field() ?><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><button class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Nonaktifkan"><i data-lucide="archive" class="h-4 w-4"></i></button></form><?php endif; ?></div>
                            </td>
                        </tr><?php endforeach; ?><?php if (!$products): ?><tr>
                            <td colspan="7" class="py-12 text-center text-sm text-slate-400">Belum ada produk yang cocok dengan filter.</td>
                        </tr><?php endif; ?></tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3 text-xs text-slate-400">Menampilkan <?= count($products) ?> menu</div>
    </div>
<?php render_seller_end();
            }
