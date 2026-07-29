# Catatan Perbaikan (Debug) — Round 2

Lanjutan dari perbaikan sebelumnya (double-layout wrap 500 error). Semua di bawah
sudah ditest langsung: migrasi jalan bersih, semua route balik HTTP 200, method
yang di-rename sudah dikonfirmasi cocok dengan `wire:submit` di blade-nya.

## 🔴 Bug 1 — Upload harian & upload anomali error "Cannot read properties of undefined (reading 'name')"

**Penyebab:** Nama method PHP `upload()` di `UploadHarian` dan `AnomaliUpload`
**bentrok** dengan method JS bawaan Livewire, `$wire.upload()`, yang dipakai khusus
untuk API upload file (dipanggil otomatis saat `wire:model` pada `<input type="file">`
mengunggah file). Karena form memakai `wire:submit="upload"`, Livewire salah
mengira Anda memanggil `$wire.upload()` (fungsi bawaan, butuh argumen nama-file dsb),
padahal maksudnya memanggil method PHP Anda yang juga kebetulan bernama `upload`.
Itu sebabnya errornya identik di kedua form.

**Fix:** Rename method jadi `simpanUpload()` di kedua class, dan update
`wire:submit` di kedua blade jadi `wire:submit="simpanUpload"`.

File yang diubah:
- `app/Livewire/Dashboard/UploadHarian.php`
- `resources/views/livewire/dashboard/upload-harian.blade.php`
- `app/Livewire/Anomali/AnomaliUpload.php`
- `resources/views/livewire/anomali/anomali-upload.blade.php`

> Catatan untuk ke depannya: hindari menamai method Livewire dengan nama yang sama
> seperti method bawaan `$wire` (terutama `upload`, `set`, `get`, `call`, `entangle`).

## 🔴 Bug 2 — Editor pengumuman (Quill) tiba-tiba hilang / "Invalid Quill container"

**Penyebab:** Setiap Anda mengetik satu huruf, `quill.on('text-change', ...)`
langsung memanggil `$wire.set('konten', html)` — ini request PENUH ke server, bukan
sekadar update lokal. Livewire lalu me-render ulang & "morph" DOM sesuai HTML dari
server (yang isinya `<div>` kosong sesuai blade template), tapi DOM di browser
sudah diubah total oleh Quill (ada toolbar, dsb). Livewire tidak tahu itu buatan
Quill, jadi ditimpa balik ke versi kosong — makanya editornya hilang tiba-tiba,
lalu saat Alpine coba `init()` ulang di container yang sudah berantakan, Quill
menolak dengan "Invalid Quill container".

**Fix:**
1. Bungkus container editor dengan `wire:ignore` supaya Livewire **tidak pernah**
   menyentuh/menimpa area itu — ini pola standar untuk integrasi library JS pihak
   ketiga (Quill, Select2, dsb) dengan Livewire.
2. Debounce ~400ms sebelum kirim ke server, supaya tidak setiap ketukan huruf
   memicu request (sekaligus mengurangi beban server).

File yang diubah: `resources/views/livewire/pengumuman/pengumuman-manager.blade.php`

## 🔴 Bug 3 — Upload arsip gagal: "Data too long for column 'file_asli'"

**Penyebab:** `AppServiceProvider::boot()` memanggil `Schema::defaultStringLength(191)`.
Ini membuat **semua** kolom `$table->string()` tanpa panjang eksplisit di seluruh
migration project jadi `VARCHAR(191)` — bukan `VARCHAR(255)` seperti yang biasanya
diasumsikan. Nama file asli hasil upload (judul referensi/skripsi dsb, contoh kasus
Anda: 209 karakter) gampang melebihi 191, dan MySQL menolak insert-nya.

**Fix:** Migration baru `2026_01_06_000001_widen_filename_columns.php` memperlebar
kolom-kolom yang menyimpan nama file/path/link jadi `VARCHAR(500)`:
- `arsips.file_asli`, `arsips.file_path`
- `daily_uploads.nama_file`
- `anomali_mikros.link_fasih`
- `qg_aksi_preventifs.template_path`, `laporan_path`, `link_bukti_dukung`

Migration ini pakai raw SQL (`DB::statement(... MODIFY ...)`), **bukan**
`Schema::table()->change()`, supaya tidak perlu install `doctrine/dbal` tambahan
(package itu belum ada di project ini).

**Yang perlu Anda lakukan:** setelah menimpa file, jalankan:
```bash
php artisan migrate
```
di server production/lokal Anda (bukan `migrate:fresh` — supaya data yang sudah
ada tidak hilang).

## 🟢 Permintaan fitur — Quality Gates: semua role bisa ceklis bukti dukung

Sebelumnya tombol ceklis "Bukti Dukung" hanya tampil untuk role `admin`/`qg`
(lewat pengecekan `isQg()`), role lain hanya melihat teks status tanpa bisa klik.

**Fix:** Tombol ceklis sekarang tampil dan bisa diklik oleh **siapa saja yang sudah
login** (role apa pun) — sama persis seperti tombol "Unggah Laporan" yang memang
sejak awal terbuka untuk semua role.

File yang diubah:
- `app/Livewire/QualityGates/QgManager.php` (method `toggleChecklist`)
- `resources/views/livewire/qualitygates/qg-manager.blade.php`

Bagian CRUD lain di Quality Gates (tambah/edit/hapus Gate, UK, Aksi Preventif)
**tetap** dibatasi untuk role `admin`/`qg` saja — tidak diubah, karena permintaan
Anda spesifik soal ceklis bukti dukung saja.

---

## Ringkasan dari perbaikan round sebelumnya (masih berlaku)

- Double-layout wrap di semua Livewire full-page component (500 error) — sudah
  diperbaiki dengan `#[Layout(...)]` + menghapus wrapper `<x-layouts.xxx>` manual.
- Validasi `konten` di `PengumumanManager` (tadinya bisa disimpan kosong).
- `throttle:5,1` di route login.
- Jangan lupa `php artisan storage:link` kalau belum pernah dijalankan.
