# PANTAU SEPANGKEP

Portal informasi & pemantauan Sensus Ekonomi 2026 — BPS Kabupaten Pangkajene dan Kepulauan.
Dibangun dengan TALL Stack: **T**ailwind CSS, **A**lpine.js, **L**aravel, **L**ivewire.

Paket ini berisi **kode aplikasi kustom saja** (bukan instalasi Laravel penuh). Anda perlu menjalankan
`composer create-project laravel/laravel` terlebih dahulu di komputer Anda sendiri, karena sandbox
tempat kode ini dibuat tidak memiliki akses ke Packagist (registry Composer) untuk mengunduh framework
Laravel itu sendiri. Semua kode di paket ini sudah lengkap dan siap ditempel ke instalasi Laravel segar.

## 1. Buat project Laravel baru

```bash
composer create-project laravel/laravel pantau-sepangkep
cd pantau-sepangkep
composer require livewire/livewire phpoffice/phpspreadsheet
npm install
npm install -D tailwindcss postcss autoprefixer @tailwindcss/typography
npx tailwindcss init -p
```

## 2. Salin file dari paket ini

Salin (timpa) folder-folder berikut dari paket ini ke dalam project Laravel Anda:

```
app/            -> pantau-sepangkep/app/
database/       -> pantau-sepangkep/database/
resources/      -> pantau-sepangkep/resources/
routes/web.php  -> pantau-sepangkep/routes/web.php
public/images/  -> pantau-sepangkep/public/images/
tailwind.config.js  -> pantau-sepangkep/tailwind.config.js
postcss.config.js   -> pantau-sepangkep/postcss.config.js
vite.config.js      -> pantau-sepangkep/vite.config.js
```

> **Logo BPS & Sensus Ekonomi**: sesuai instruksi Anda, aplikasi ini mengasumsikan file logo disimpan
> sebagai `public/images/logo_bps.png` dan `public/images/logo_sensus.png`. Paket ini sudah menyertakan
> **placeholder sederhana** dengan nama file tersebut agar aplikasi tidak menampilkan gambar rusak —
> **silakan timpa kedua file itu dengan logo asli Anda** (ukuran disarankan persegi, minimal 160x160px,
> latar transparan/PNG).

## 3. Daftarkan middleware `role`

Buka `bootstrap/app.php` (Laravel 11) dan tambahkan alias middleware seperti contoh di
`bootstrap-app-middleware-snippet.php` (disertakan dalam paket ini). Jika Anda memakai Laravel versi lama
dengan `app/Http/Kernel.php`, ikuti petunjuk di bagian bawah file yang sama.

## 4. Konfigurasi environment

```bash
cp .env.example .env
php artisan key:generate
```

Atur koneksi database Anda di `.env` (MySQL/PostgreSQL/SQLite — semua didukung), lalu:

```bash
php artisan storage:link
php artisan migrate
```

## 5. Build asset & jalankan

```bash
npm run build      # atau `npm run dev` saat development
php artisan serve
```

Buka `http://localhost:8000`.

## Kode Akses Login (hardcode, sesuai permintaan)

| Kode Akses | Role | Hak Akses |
|---|---|---|
| `AdminGanteng7309#` | Admin | Akses penuh ke semua modul |
| `IndaHebat7309` | INDA (Instruktur Daerah) | Membuat pengumuman, menjawab QnA |
| `MiciBusuk7309` | Admin Anomali | Mengunggah data anomali pekanan |
| `SoraJojo7309` | Admin Quality Gate | CRUD Gate/UK/Aksi Preventif |
| `statistikpangkep` | Pegawai | Akses baca umum + fitur pegawai biasa |

## Struktur Modul

1. **Landing page** (`/`) — 3 tombol: Dashboard Publik, QnA, Pengumuman, + Login Pegawai.
2. **Dashboard Publik** (`/dashboard-publik`) — 7 tab: Dashboard Utama, Kinerja PPL, Kinerja PML,
   Detail SLS/Blok Sensus, Tidak Ditemukan, Gabungan, Produktivitas Harian. Data diambil dari unggahan
   excel 50 kolom oleh admin (`/dashboard-publik/upload`, khusus role `admin`). Unggahan pada tanggal yang
   sama akan **menggantikan** data hari itu (bukan menumpuk), dan database mitra/petugas ikut diperbarui
   otomatis setiap unggahan.
3. **QnA** (`/qna`) — publik bisa bertanya (boleh anonim); dijawab oleh Admin/INDA di portal pegawai.
4. **Pengumuman** (`/pengumuman`) — publik & pegawai bisa melihat; semua pegawai bisa CRUD di portal
   (editor kaya teks memakai Quill.js: bold, italic, ukuran/jenis font, list bernomor & bullet, link,
   serta lampiran file/gambar/link). Badge jumlah pengumuman baru (3 hari terakhir) tampil di landing page.
5. **Login Pegawai** (`/login`) — kode akses hardcode di atas, tanpa username/password.
6. **Anomali** (`/portal/anomali`) — anomali pekanan independen per tanggal. Admin Anomali mengunggah
   4 file excel sekaligus (Radar Usaha, Radar Keluarga, Data Mikro Usaha, Data Mikro Keluarga). Setiap
   batch menampilkan dashboard visualisasi (per jenis, per jenis anomali, status tindak lanjut, monitoring
   per kecamatan) serta tabel Data Mikro lengkap dengan filter/search/pagination dan tombol tandai/batalkan
   tindak lanjut. Nama PPL/PML/Organik pada tabel mikro diambil otomatis dari database mitra (hasil unggahan
   dashboard publik) berdasarkan Email Petugas.
7. **Quality Gates** (`/portal/quality-gates`) — struktur Gate > UK (Ukuran Kualitas) > Aksi Preventif.
   CRUD struktur hanya oleh role `qg`/`admin`; upload laporan bisa oleh pegawai manapun; ceklis bukti
   dukung oleh `qg`/`admin`. Aksi preventif dianggap selesai jika laporan sudah diunggah **dan** ceklis
   bukti dukung tercentang.
8. **Arsiparis** (`/portal/arsiparis`) — semua pegawai bisa melihat & mengunduh berkas; CRUD hanya `admin`.

## Catatan & Asumsi Penting

- **Export Excel**: semua tombol "Export Excel" pada tabel menghasilkan file `.xls` yang dapat dibuka
  langsung oleh Microsoft Excel/Google Sheets/LibreOffice (tanpa perlu library tambahan seperti
  Maatwebsite/Excel), sehingga instalasi tetap ringan.
- **Excel 2 (Radar Anomali Keluarga)**: pada contoh data yang Anda kirimkan, baris contoh untuk Excel 2
  tampak tertukar dengan format Data Mikro (kemungkinan salah tempel). Kode ini mengasumsikan **Excel 2
  memiliki struktur kolom yang identik dengan Excel 1** (Radar per kecamatan: Kode, Kecamatan, Total
  Assignment, Anomali 1–8 Belum/Sudah Tindak Lanjut beserta persentasenya), hanya berbeda kategori
  (keluarga, bukan usaha). Jika template asli Excel 2 Anda berbeda, beri tahu saya untuk saya sesuaikan
  parsernya di `app/Services/AnomaliUploadService.php`.
- **Rich text editor** memakai Quill.js (CDN). Quill mendukung bold/italic/underline, ukuran & jenis font,
  list bernomor dan bullet, serta link. Quill **tidak memiliki tipe list "a, b, c" (huruf) secara native**,
  jadi list huruf tidak disertakan — hanya bernomor & bullet seperti kemampuan bawaan Quill.
- **"Info singkat" anomali di modul Quality Gates**: sesuai catatan Anda, kartu kecil di dashboard Quality
  Gates menampilkan tanggal batch anomali yang paling baru beserta persentase penyelesaiannya.
- Semua form upload memvalidasi tipe file `.xlsx`/`.xls` dan menampilkan pesan error yang jelas bila format
  template tidak sesuai (kolom wajib kosong/ tidak ditemukan akan dilewati baris tersebut, bukan membuat
  aplikasi error).

## Jika Ada Kolom/Perhitungan yang Meleset

Karena parser mengandalkan **nama header persis** sesuai template yang Anda kirimkan, pastikan file excel
yang diunggah admin memakai nama kolom yang sama persis (huruf besar/kecil dan spasi berpengaruh untuk
sebagian kolom). Jika ada perubahan template di kemudian hari, sesuaikan mapping di:

- `app/Services/DashboardUploadService.php` (dashboard publik, 50 kolom)
- `app/Services/AnomaliUploadService.php` (4 template anomali)
