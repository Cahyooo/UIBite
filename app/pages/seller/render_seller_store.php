<?php

function render_seller_store(): void
            {
                $seller = require_role('seller');
                $store = get_store_for_seller((int)$seller['id']);
                render_seller_start('Pengaturan toko', 'seller.store');
?>
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[.15em] text-brand-600">Tenant profile</p>
        <h2 class="mt-1 text-2xl font-extrabold tracking-tight">Pengaturan toko</h2>
        <p class="mt-1 text-sm text-slate-500">Informasi ini akan ditampilkan pada marketplace UIBite.</p>
    </div>
    <form method="post" action="<?= e(url('seller.store.update')) ?>" class="card max-w-3xl p-5 sm:p-7"><?= csrf_field() ?><div class="mb-6 flex items-center gap-4 rounded-2xl bg-slate-50 p-4"><span class="grid h-14 w-14 place-items-center rounded-2xl bg-brand-100 text-brand-700"><i data-lucide="store" class="h-7 w-7"></i></span>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-brand-700">Tenant UIB</p>
                <h3 class="text-lg font-extrabold text-slate-900"><?= e($store['name']) ?></h3>
                <p class="text-xs text-slate-500">Seller: <?= e($seller['name']) ?></p>
            </div>
        </div>
        <div class="space-y-5">
            <div><label class="mb-1.5 block text-xs font-bold text-slate-700">Nama toko *</label><input class="input" name="store_name" value="<?= e($store['name']) ?>" required maxlength="100"></div>
            <div><label class="mb-1.5 block text-xs font-bold text-slate-700">Deskripsi toko</label><textarea class="input" name="description" rows="4" maxlength="1000"><?= e($store['description']) ?></textarea></div>
            <div><label class="mb-1.5 block text-xs font-bold text-slate-700">Lokasi tenant *</label><input class="input" name="location" value="<?= e($store['location']) ?>" required maxlength="180" placeholder="Contoh: Kantin utama, lantai 1"></div>
            <div><label class="mb-1.5 block text-xs font-bold text-slate-700">Nomor kontak</label><input class="input" name="phone" value="<?= e($store['phone']) ?>" maxlength="30"></div><label class="flex items-center gap-3 rounded-xl bg-slate-50 p-4"><input type="checkbox" name="is_open" value="1" class="h-4 w-4 accent-blue-600" <?= (int)$store['is_open'] ? 'checked' : '' ?>><span><span class="block text-sm font-bold text-slate-800">Toko sedang buka</span><span class="mt-0.5 block text-xs text-slate-500">Jika dimatikan, pembeli tetap bisa melihat toko tetapi tidak dapat membuat pesanan baru.</span></span></label>
        </div>
        <div class="mt-7 flex justify-end border-t border-slate-100 pt-5"><button class="btn-primary"><i data-lucide="save" class="h-4 w-4"></i>Simpan perubahan</button></div>
    </form>
<?php render_seller_end();
            }
