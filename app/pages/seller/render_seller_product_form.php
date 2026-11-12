<?php

function render_seller_product_form(): void
            {
                $seller = require_role('seller');
                $store = get_store_for_seller((int)$seller['id']);
                $id = (int)($_GET['id'] ?? 0);
                $product = null;
                if ($id > 0) {
                    $stmt = db()->prepare('SELECT * FROM products WHERE id=? AND store_id=?');
                    $stmt->execute([$id, $store['id']]);
                    $product = $stmt->fetch();
                    if (!$product) {
                        flash('error', 'Produk tidak ditemukan.');
                        redirect('seller.products');
                    }
                }
                $categories = db()->query('SELECT id,name FROM categories WHERE is_active=1 ORDER BY sort_order,name')->fetchAll();
                render_seller_start($id ? 'Edit menu' : 'Tambah menu', 'seller.products');
?>
    <div class="mb-6"><a href="<?= e(url('seller.products')) ?>" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-400 hover:text-brand-700"><i data-lucide="arrow-left" class="h-3.5 w-3.5"></i>Kembali ke menu</a>
        <h2 class="mt-3 text-2xl font-extrabold tracking-tight"><?= $id ? 'Edit menu' : 'Tambah menu baru' ?></h2>
        <p class="mt-1 text-sm text-slate-500">Lengkapi informasi produk yang akan ditampilkan kepada pembeli.</p>
    </div>
    <form method="post" action="<?= e(url('seller.product.save')) ?>" class="card max-w-3xl p-5 sm:p-7"><?= csrf_field() ?><input type="hidden" name="product_id" value="<?= $id ?>">
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2"><label class="mb-1.5 block text-xs font-bold text-slate-700">Nama menu *</label><input class="input" name="product_name" required maxlength="120" value="<?= e($product['name'] ?? ($_SESSION['_old']['product_name'] ?? '')) ?>" placeholder="Contoh: Nasi Goreng Spesial"></div>
            <div class="sm:col-span-2"><label class="mb-1.5 block text-xs font-bold text-slate-700">Deskripsi</label><textarea class="input" name="description" rows="3" maxlength="1000" placeholder="Jelaskan isi atau keunikan menu..."><?= e($product['description'] ?? '') ?></textarea></div>
            <div><label class="mb-1.5 block text-xs font-bold text-slate-700">Kategori *</label><select class="input" name="category_id" required>
                    <option value="">Pilih kategori</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= (int)($product['category_id'] ?? 0) === (int)$category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?>
                </select></div>
            <div><label class="mb-1.5 block text-xs font-bold text-slate-700">Harga (Rp) *</label><input class="input" name="price" required value="<?= e($product['price'] ?? ($_SESSION['_old']['price'] ?? '')) ?>" placeholder="15000"></div>
            <div><label class="mb-1.5 block text-xs font-bold text-slate-700">Stok tersedia *</label><input class="input" type="number" min="0" name="stock" required value="<?= e($product['stock'] ?? ($_SESSION['_old']['stock'] ?? '0')) ?>"></div>
            <div><label class="mb-1.5 block text-xs font-bold text-slate-700">URL gambar <span class="font-normal text-slate-400">(opsional)</span></label><input class="input" type="url" name="image_url" value="<?= e($product['image_url'] ?? ($_SESSION['_old']['image_url'] ?? '')) ?>" placeholder="https://...jpg"></div>
            <div class="sm:col-span-2"><label class="flex items-center gap-3 rounded-xl bg-slate-50 p-4"><input type="checkbox" name="is_available" value="1" class="h-4 w-4 accent-blue-600" <?= !isset($product['is_available']) || (int)$product['is_available'] === 1 ? 'checked' : '' ?>><span><span class="block text-sm font-bold text-slate-800">Tampilkan menu ke pembeli</span><span class="mt-0.5 block text-xs text-slate-500">Menu yang tidak aktif tidak akan muncul di marketplace.</span></span></label></div>
        </div>
        <div class="mt-7 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5"><a href="<?= e(url('seller.products')) ?>" class="btn-secondary">Batal</a><button class="btn-primary"><i data-lucide="save" class="h-4 w-4"></i>Simpan menu</button></div>
    </form>
<?php render_seller_end();
                clear_old_input();
            }
