# Panduan struktur app

## Actions
`actions.php` adalah dispatcher. Logic tiap POST dipisah menjadi satu fungsi per file di `actions/`.

## Pages
`pages.php` adalah loader. Fungsi tampilan dipisah berdasarkan area fitur di `pages/`.

## Prinsip
1. Satu file fokus pada satu aksi atau satu halaman.
2. Helper lintas fitur tetap berada di `helpers.php`.
3. Koneksi database tetap melalui `db()` dari `db.php`.
4. Jangan menaruh query dan proses bisnis baru langsung ke `public/index.php`.
5. Untuk logic yang dipakai lebih dari satu halaman/aksi, pertimbangkan membuat service/helper khusus.
