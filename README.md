# UIBite — Campus Canteen Pre-Order System

UIBite adalah aplikasi web untuk pemesanan makanan kantin kampus dengan dua role:

- **Buyer / Pembeli:** jelajahi tenant, cari dan filter menu, cart, checkout, pembayaran cash atau QRIS simulasi, pelacakan pesanan, dan review setelah pesanan selesai.
- **Seller / Penjual:** dashboard ringkasan, pengelolaan menu dan stok, pengelolaan pesanan, pengaturan toko, dan laporan penjualan.

Aplikasi dibangun menggunakan **PHP Native + MySQL + JavaScript (Vanilla) + Tailwind CSS CLI**. Tidak menggunakan framework PHP maupun React.

> Pembayaran QRIS hanya simulasi untuk kebutuhan demonstrasi. Tidak terhubung ke QRIS asli, bank, atau e-wallet dan tidak memproses uang sungguhan.

## 1. Kebutuhan sistem

- PHP 8.1 atau lebih baru.
- MySQL 8.0.16+ atau MariaDB 10.6+ (disarankan MySQL 8 untuk kompatibilitas CHECK, trigger, procedure, dan window function pada file query demo).
- PHP extension `pdo_mysql` aktif.
- Node.js 20+ dan npm untuk build Tailwind CSS.
- Browser modern.

## 2. Struktur folder

```text
UIBite/
├── app/
│   ├── actions.php          # Handler POST, transaksi checkout, status order
│   ├── bootstrap.php        # Session, timezone, bootstrap aplikasi
│   ├── db.php               # Koneksi PDO
│   ├── helpers.php          # Helper auth, CSRF, URL, cart, format
│   └── pages.php            # Halaman Buyer dan Seller
├── config/database.php      # Konfigurasi PDO dari .env
├── database/
│   ├── uibite.sql           # Schema, indexes, constraints, views, triggers, procedure, seed
│   └── sample_queries.sql   # Contoh query untuk presentasi database
├── public/
│   ├── assets/css/app.css   # Output Tailwind CLI (dibangun dari src/css/app.css)
│   ├── assets/js/app.js     # Interaksi browser
│   ├── assets/images/       # Placeholder gambar lokal
│   └── index.php            # Front controller
├── src/css/app.css          # Input Tailwind CSS v4
├── storage/uploads/         # Disiapkan untuk kebutuhan upload di pengembangan berikutnya
├── .env.example
├── package.json
└── README.md
```

## 3. Instalasi database

1. Jalankan MySQL melalui XAMPP / Laragon / MySQL Server.
2. Buat database dengan mengimpor file `database/uibite.sql` melalui phpMyAdmin atau MySQL client. File ini akan membuat database `uibite_db` jika belum ada.
3. Pastikan import selesai tanpa error. File SQL juga membuat tabel, foreign key, CHECK constraints, indexes, views, 5 triggers, stored procedure, dan data demo.

Contoh melalui terminal:

```bash
mysql -u root -p < database/uibite.sql
```

Jika menggunakan XAMPP dengan password root kosong, cukup:

```bash
mysql -u root < database/uibite.sql
```

## 4. Konfigurasi PHP

Salin `.env.example` menjadi `.env`, lalu sesuaikan koneksi database:

```ini
APP_NAME="UIBite"
APP_URL=""
APP_ENV="local"
DB_HOST="127.0.0.1"
DB_PORT="3306"
DB_NAME="uibite_db"
DB_USER="root"
DB_PASS=""
```

- `APP_URL` boleh dikosongkan jika aplikasi dijalankan dari PHP built-in server dengan document root `public`.
- Jika menggunakan XAMPP dan folder proyek berada di `htdocs/UIBite`, gunakan `APP_URL="/UIBite/public"`.
- Pastikan extension `pdo_mysql` aktif di `php.ini`.

## 5. Install dan build Tailwind CSS CLI

Tailwind CSS v4 CLI dipakai secara lokal, bukan Play CDN.

```bash
npm install
npm run build
```

Untuk development sambil mengawasi perubahan file PHP/JS/CSS:

```bash
npm run dev
```

Biarkan `npm run dev` berjalan di terminal pertama. Jalankan PHP server di terminal kedua. File hasil build ditulis ke `public/assets/css/app.css`.

## 6. Menjalankan aplikasi

Dari root folder UIBite:

```bash
php -S localhost:8000 -t public
```

Buka browser ke:

```text
http://localhost:8000
```

Alternatif XAMPP: letakkan folder di `htdocs`, import SQL, atur `APP_URL` di `.env`, lalu buka `http://localhost/UIBite/public`.

## 7. Akun demo

Semua akun demo menggunakan password yang tercantum di tabel ini. Akun seller dibuat untuk empat tenant.

| Role | Email | Password | Tenant |
|---|---|---|---|
| Seller | `seller.nusantara@uibite.test` | `seller123` | Warung Nusantara |
| Seller | `seller.kopicerita@uibite.test` | `seller123` | Kopi & Cerita |
| Seller | `seller.geprek@uibite.test` | `seller123` | Ayam Geprek 88 |
| Seller | `seller.bento@uibite.test` | `seller123` | Bento Corner |
| Buyer | `buyer@uibite.test` | `buyer123` | Pembeli demo |
| Buyer | `nabila@uibite.test` | `nabila123` | Pembeli demo |

Pembeli baru juga dapat melakukan registrasi dari halaman daftar. Registrasi publik selalu membuat role `buyer`; akun seller disediakan melalui seed data.

## 8. Alur demo yang disarankan untuk expo

### Demo A — Buyer checkout cash

1. Login sebagai `buyer@uibite.test`.
2. Buka marketplace, cari menu, coba filter kategori atau tenant.
3. Tambahkan beberapa menu dari **toko yang sama** ke cart. Sistem membatasi satu checkout untuk satu tenant agar alur pickup jelas.
4. Checkout dan pilih **Cash on Pickup**.
5. Logout atau buka browser lain, lalu login sebagai seller tenant terkait.
6. Terima pesanan → mulai siapkan → tandai siap pickup.
7. Login kembali sebagai buyer, tampilkan kode pickup.
8. Seller menyelesaikan pesanan. Untuk cash, pembayaran ditandai paid saat pesanan diselesaikan.

### Demo B — QRIS simulation

1. Login sebagai buyer dan checkout dengan **QRIS Simulation**.
2. Buka detail pesanan, klik **Simulasikan pembayaran sukses**.
3. Status pembayaran berubah menjadi paid dan penjual dapat menerima pesanan.
4. Tegaskan kepada dosen bahwa fitur ini hanya simulasi lokal tanpa payment gateway.

### Demo C — Seller dashboard dan laporan

1. Login sebagai seller.
2. Tunjukkan statistik hari ini, tren pendapatan, stok rendah, dan pesanan terbaru.
3. Tambah atau edit menu, ubah stok, nonaktifkan menu.
4. Buka laporan, ubah rentang tanggal, dan export CSV.

## 9. Aturan bisnis utama

- Satu seller memiliki tepat satu toko melalui relasi `stores.seller_id UNIQUE`.
- Satu toko dapat memiliki banyak produk.
- Satu cart/checkout hanya boleh berisi produk dari satu toko.
- Checkout menggunakan PDO transaction dan `SELECT ... FOR UPDATE` untuk memeriksa stok sebelum stok dikurangi.
- Harga dan nama produk disimpan sebagai snapshot di `order_items`, supaya riwayat transaksi tidak berubah saat seller mengubah menu.
- Stok dikurangi saat pesanan dibuat. Jika pesanan dibatalkan atau ditolak, stok dikembalikan dan dicatat di `stock_movements`.
- QRIS simulasi berstatus pending sampai buyer menekan tombol simulasi sukses. Seller tidak dapat menerima pesanan digital yang belum dibayar.
- Cash on Pickup berstatus unpaid sampai seller menyelesaikan pesanan.
- Review hanya dapat diberikan pembeli untuk item dari pesanan yang completed; satu item pesanan hanya punya satu review.
- Produk dinonaktifkan (soft hide) agar data transaksi lama tetap mengacu ke produk yang sama.

## 10. Rancangan database

| Tabel | Fungsi |
|---|---|
| `users` | Akun buyer dan seller, role, dan data login |
| `stores` | Profil tenant; satu seller satu toko |
| `categories` | Kategori makanan/minuman |
| `products` | Menu, harga, stok, dan ketersediaan |
| `orders` | Header transaksi, pembeli, toko, status, total, pickup code |
| `order_items` | Detail produk per pesanan dan snapshot harga/nama |
| `payments` | Metode dan status pembayaran |
| `stock_movements` | Audit mutasi stok masuk/keluar |
| `reviews` | Rating dan komentar untuk item pesanan selesai |

ERD ringkas:

```text
users (seller) 1 ───── 1 stores 1 ───── N products N ───── 1 categories
users (buyer)  1 ───── N orders N ───── 1 stores
orders         1 ───── N order_items N ───── 1 products
orders         1 ───── 1 payments
products       1 ───── N stock_movements
orders         1 ───── N stock_movements
order_items    1 ───── 0..1 reviews
users (buyer)  1 ───── N reviews
```

## 11. Implementasi database untuk penilaian UAS

- **JOIN:** data pesanan buyer, tenant, detail item, dan pembayaran.
- **LEFT JOIN:** daftar produk sekalipun belum memiliki review.
- **Aggregate:** `COUNT`, `SUM`, `AVG`, dan `ROUND` untuk laporan serta rating.
- **GROUP BY + HAVING:** ringkasan penjualan kategori dan pencarian kategori terlaris.
- **Subquery / correlated subquery:** produk di atas rata-rata harga toko dan produk tanpa review.
- **CTE + window function:** ranking produk per tenant (`DENSE_RANK`).
- **VIEW:** `v_product_catalog`, `v_order_summary`, `v_daily_sales`, `v_low_stock_products`.
- **TRIGGER:** validasi pemilik toko harus seller, konsistensi tenant pada order item dan stock movement, pencegahan order selesai sebelum pembayaran paid, serta validasi review.
- **Stored procedure:** `sp_store_sales_summary` untuk ringkasan penjualan berdasarkan tenant dan periode.
- **Transaction:** checkout, reservasi stok, pembayaran simulasi, dan pengembalian stok.

Buka `database/sample_queries.sql` untuk contoh query yang dapat langsung dicoba saat persiapan presentasi.

## 12. Keamanan dan validasi yang sudah diterapkan

- PDO prepared statements dan native prepares.
- `password_hash()` / `password_verify()` untuk password.
- Session regeneration setelah login.
- Role checks untuk halaman dan aksi seller/buyer.
- CSRF token pada form POST.
- Output escaping menggunakan `htmlspecialchars()`.
- Validasi server-side untuk input penting.
- Foreign key, UNIQUE, CHECK, dan index di database.
- Transaksi database untuk proses stok dan order.

## 13. Catatan pengembangan

- Gambar menu demo menggunakan URL gambar eksternal. Ganti URL gambar dengan foto menu tenant asli bila tersedia; fallback placeholder lokal tetap disediakan.
- Belum ada integrasi payment gateway sungguhan, notifikasi WhatsApp/email, QR scanner, atau admin platform terpisah.
- Tidak ada role admin. Pengelolaan tenant dilakukan oleh akun seller masing-masing dan data seed.
- Untuk penggunaan produksi, tambahkan HTTPS, konfigurasi error/logging produksi, rate limiting login, backup database, kebijakan privasi, dan integrasi payment gateway resmi.


## Struktur kode (versi clean)

Kode halaman dan aksi sudah dipisah berdasarkan tanggung jawab supaya lebih mudah dicari dan dirawat.

```text
app/
├── actions.php                 # Dispatcher POST, bukan tempat logic panjang
├── actions/
│   ├── auth/                   # Login, register, logout
│   ├── cart/                   # Tambah, ubah, hapus keranjang
│   ├── checkout/               # Checkout dan simulasi pembayaran
│   ├── buyer/                  # Pembatalan pesanan dan ulasan
│   └── seller/                 # Produk, pesanan, dan pengaturan toko
├── pages.php                   # Loader seluruh halaman
├── pages/
│   ├── shared/                 # Header, footer, flash message, layout seller
│   ├── catalog/                # Beranda, toko, detail produk, product card
│   ├── auth/                   # Halaman login/register
│   ├── buyer/                  # Keranjang, checkout, pesanan pembeli
│   └── seller/                 # Dashboard dan halaman pengelolaan seller
├── bootstrap.php
├── db.php
└── helpers.php
```

### Cara kerja pemisahan

- `public/index.php` tetap menjadi router halaman.
- `app/actions.php` hanya memetakan route POST ke handler yang sesuai.
- Setiap handler aksi berada di `app/actions/<modul>/`.
- `app/pages.php` hanya memuat file halaman.
- Setiap fungsi render halaman berada di `app/pages/<modul>/`.
- File database, aset, konfigurasi, dan SQL tetap dipertahankan.

Jika menambahkan aksi baru, buat fungsi handler di folder modul yang sesuai lalu daftarkan route-nya di `app/actions.php`. Jika menambahkan halaman baru, buat file fungsi render di folder `app/pages/` yang sesuai lalu daftarkan `require_once` di `app/pages.php`.
