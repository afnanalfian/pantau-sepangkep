# PANTAU SEPANGKEP

Portal informasi & pemantauan Sensus Ekonomi 2026 — BPS Kabupaten Pangkajene dan Kepulauan.
Dibangun dengan TALL Stack: **T**ailwind CSS, **A**lpine.js, **L**aravel, **L**ivewire.

## Konfigurasi environment

```bash
composer i
npm i
cp .env.example .env
php artisan key:generate
```

Atur koneksi database Anda di `.env` (MySQL/PostgreSQL/SQLite — semua didukung), lalu:

```bash
php artisan storage:link
php artisan migrate
```

## Build asset & jalankan

```bash
npm run build      # atau `npm run dev` saat development
php artisan serve
```

Buka `http://localhost:8000`.

## Kode Akses Login (hardcode)

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
