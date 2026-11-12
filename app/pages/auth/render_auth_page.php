<?php

function render_auth_page(string $mode): void
            {
                $isRegister = $mode === 'register';
                render_buyer_header($isRegister ? 'Buat akun' : 'Masuk');
?>
    <section class="container-app flex min-h-[calc(100vh-180px)] items-center justify-center py-12">
        <div class="grid w-full max-w-4xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-900/5 md:grid-cols-2">
            <div class="relative hidden flex-col justify-between overflow-hidden bg-slate-950 p-9 text-white md:flex">
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-brand-500/30 blur-3xl"></div>
                <div class="relative">
                    <div class="flex items-center gap-2"><span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-500"><i data-lucide="utensils" class="h-5 w-5"></i></span><span class="text-xl font-extrabold">UIBite</span></div>
                    <h2 class="mt-16 text-3xl font-extrabold leading-tight">Makan enak, <span class="text-blue-400">tanpa antre.</span></h2>
                    <p class="mt-4 text-sm leading-7 text-slate-300">Pesan menu favorit dari tenant kantin kampus dan ambil saat sudah siap.</p>
                </div>
                <div class="relative rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm font-semibold">“Order dulu, ambil kemudian.”</p>
                    <p class="mt-1 text-xs text-slate-400">UIBite · Campus food marketplace</p>
                </div>
            </div>
            <div class="p-7 sm:p-10">
                <div class="mb-8">
                    <p class="text-xs font-bold uppercase tracking-[.16em] text-brand-600"><?= $isRegister ? 'Join UIBite' : 'Welcome back' ?></p>
                    <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950"><?= $isRegister ? 'Buat akun pembeli' : 'Masuk ke UIBite' ?></h1>
                    <p class="mt-2 text-sm text-slate-500"><?= $isRegister ? 'Daftar untuk mulai memesan makanan kantin.' : 'Masukkan email dan password akun Anda.' ?></p>
                </div>
                <form method="post" action="<?= e(url($isRegister ? 'auth.register' : 'auth.login')) ?>" class="space-y-4"><?= csrf_field() ?>
                    <?php if ($isRegister): ?><div><label class="mb-1.5 block text-xs font-bold text-slate-700">Nama lengkap</label><input class="input" name="name" required minlength="2" value="<?= old('name') ?>" placeholder="Nama Anda"></div><?php endif; ?>
                    <div><label class="mb-1.5 block text-xs font-bold text-slate-700">Email</label><input class="input" name="email" type="email" required value="<?= old('email') ?>" placeholder="nama@email.com" autocomplete="email"></div>
                    <?php if ($isRegister): ?><div><label class="mb-1.5 block text-xs font-bold text-slate-700">Nomor HP <span class="font-normal text-slate-400">(opsional)</span></label><input class="input" name="phone" value="<?= old('phone') ?>" placeholder="08xxxxxxxxxx"></div><?php endif; ?>
                    <div><label class="mb-1.5 block text-xs font-bold text-slate-700">Password</label><input class="input" name="password" type="password" required minlength="8" placeholder="Minimal 8 karakter" autocomplete="<?= $isRegister ? 'new-password' : 'current-password' ?>"></div>
                    <?php if ($isRegister): ?><div><label class="mb-1.5 block text-xs font-bold text-slate-700">Konfirmasi password</label><input class="input" name="password_confirmation" type="password" required minlength="8" placeholder="Ulangi password"></div><?php endif; ?>
                    <button class="btn-primary mt-2 w-full !py-3"><?= $isRegister ? 'Buat akun' : 'Masuk' ?><i data-lucide="arrow-right" class="h-4 w-4"></i></button>
                </form>
                <p class="mt-6 text-center text-sm text-slate-500"><?= $isRegister ? 'Sudah punya akun?' : 'Belum punya akun?' ?> <a class="font-bold text-brand-700 hover:underline" href="<?= e(url($isRegister ? 'login' : 'register')) ?>"><?= $isRegister ? 'Masuk' : 'Daftar sekarang' ?></a></p>
                <p class="mt-7 text-center text-[11px] leading-5 text-slate-400">Dengan melanjutkan, Anda menyetujui penggunaan UIBite untuk keperluan pemesanan kantin kampus.</p>
            </div>
        </div>
    </section>
<?php render_buyer_footer();
                clear_old_input();
            }
