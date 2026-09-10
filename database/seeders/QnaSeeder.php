<?php

namespace Database\Seeders;

use App\Models\Qna;
use Illuminate\Database\Seeder;

class QnaSeeder extends Seeder
{
    public function run(): void
    {
        $qnas = [
            // =====================================================
            // SUDAH DIJAWAB
            // =====================================================

            [
                'nama' => 'Anonim1',
                'pertanyaan' => 'Kalau usaha rumahan yang hanya berjualan melalui WhatsApp, apakah tetap dicakup dalam SE2026?',
                'jawaban' => 'Ya. Selama terdapat kegiatan ekonomi yang dilakukan secara aktif untuk memperoleh pendapatan, usaha rumahan tetap dicakup meskipun pemasarannya hanya melalui WhatsApp.',
                'dijawab_oleh' => 'Pegawai Organik 01',
                'dijawab_at' => '2026-09-08 09:15:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim1',
                'pertanyaan' => 'Bagaimana menentukan kegiatan utama jika satu usaha menjual sembako sekaligus makanan siap saji?',
                'jawaban' => 'Kegiatan utama ditentukan berdasarkan kegiatan yang menjadi sumber pendapatan atau aktivitas utama usaha sesuai ketentuan konsep dan definisi SE2026.',
                'dijawab_oleh' => 'Pegawai Organik 02',
                'dijawab_at' => '2026-09-08 10:20:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim2',
                'pertanyaan' => 'Apakah usaha yang tidak memiliki NIB tetap harus didata?',
                'jawaban' => 'Tetap didata selama memenuhi cakupan kegiatan ekonomi SE2026. Kepemilikan NIB bukan satu-satunya dasar untuk menentukan apakah usaha dicakup.',
                'dijawab_oleh' => 'Pegawai Organik 01',
                'dijawab_at' => '2026-09-08 11:05:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim3',
                'pertanyaan' => 'Kalau pemilik usaha ikut bekerja di tokonya sendiri, apakah dihitung sebagai tenaga kerja?',
                'jawaban' => 'Ya, pemilik yang secara aktif bekerja dalam kegiatan usaha perlu diperhatikan dalam pencatatan tenaga kerja sesuai konsep SE2026.',
                'dijawab_oleh' => 'Pegawai Organik 03',
                'dijawab_at' => '2026-09-08 13:10:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim2',
                'pertanyaan' => 'Apa perbedaan omzet dengan keuntungan usaha?',
                'jawaban' => 'Omzet merupakan nilai pendapatan atau penjualan sebelum dikurangi biaya, sedangkan keuntungan merupakan pendapatan setelah memperhitungkan biaya usaha.',
                'dijawab_oleh' => 'Pegawai Organik 02',
                'dijawab_at' => '2026-09-08 14:30:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim4',
                'pertanyaan' => 'Kalau pedagang hanya berjualan pada hari tertentu atau saat ada pesanan, apakah tetap termasuk usaha?',
                'jawaban' => 'Bisa tetap termasuk usaha. Yang perlu diperhatikan adalah adanya kegiatan ekonomi yang dilakukan untuk memperoleh pendapatan, meskipun frekuensi kegiatannya tidak setiap hari.',
                'dijawab_oleh' => 'Pegawai Organik 04',
                'dijawab_at' => '2026-09-08 15:05:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim5',
                'pertanyaan' => 'Bagaimana dengan usaha yang sedang tutup sementara karena renovasi?',
                'jawaban' => 'Perlu dilihat kondisi dan status usahanya. Jika hanya berhenti sementara dan masih akan beroperasi kembali, perlakuannya berbeda dengan usaha yang benar-benar sudah tutup.',
                'dijawab_oleh' => 'Pegawai Organik 01',
                'dijawab_at' => '2026-09-09 08:40:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim3',
                'pertanyaan' => 'Kalau satu orang memiliki toko dan usaha laundry di lokasi yang sama, apakah dihitung satu usaha?',
                'jawaban' => 'Perlu dilihat apakah kedua kegiatan tersebut merupakan unit usaha yang berbeda. Jangan langsung menggabungkan hanya karena lokasi usahanya sama.',
                'dijawab_oleh' => 'Pegawai Organik 03',
                'dijawab_at' => '2026-09-09 09:25:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim6',
                'pertanyaan' => 'Apakah reseller yang tidak memiliki stok barang sendiri termasuk usaha?',
                'jawaban' => 'Jika kegiatan tersebut dilakukan secara rutin dengan tujuan memperoleh pendapatan, maka perlu diperhatikan sebagai kegiatan ekonomi sesuai konsep SE2026.',
                'dijawab_oleh' => 'Pegawai Organik 02',
                'dijawab_at' => '2026-09-09 10:10:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim7',
                'pertanyaan' => 'Kalau usaha menggunakan rumah pribadi sebagai tempat usaha, bagaimana pencatatannya?',
                'jawaban' => 'Tetap dicatat sebagai lokasi kegiatan usaha. Status kepemilikan tempat tidak menghilangkan keberadaan unit usaha.',
                'dijawab_oleh' => 'Pegawai Organik 04',
                'dijawab_at' => '2026-09-09 10:45:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim4',
                'pertanyaan' => 'Bagaimana menentukan KBLI jika responden memiliki beberapa jenis kegiatan usaha?',
                'jawaban' => 'Identifikasi terlebih dahulu seluruh kegiatan usaha, kemudian tentukan kegiatan utama berdasarkan ketentuan klasifikasi yang digunakan dalam SE2026.',
                'dijawab_oleh' => 'Pegawai Organik 01',
                'dijawab_at' => '2026-09-09 11:30:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim8',
                'pertanyaan' => 'Kalau usaha hanya menerima pembayaran melalui transfer atau QRIS, apakah tetap dianggap usaha biasa?',
                'jawaban' => 'Ya. Metode pembayaran tidak menentukan apakah suatu kegiatan merupakan usaha. Yang dilihat adalah kegiatan ekonominya.',
                'dijawab_oleh' => 'Pegawai Organik 03',
                'dijawab_at' => '2026-09-09 13:00:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim9',
                'pertanyaan' => 'Apakah anggota keluarga yang membantu usaha tanpa menerima gaji tetap dihitung sebagai tenaga kerja?',
                'jawaban' => 'Perlu dicatat sesuai konsep tenaga kerja yang berlaku dalam SE2026. Tidak menerima upah tidak otomatis berarti tidak termasuk tenaga kerja.',
                'dijawab_oleh' => 'Pegawai Organik 02',
                'dijawab_at' => '2026-09-09 13:45:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim5',
                'pertanyaan' => 'Bagaimana jika responden tidak mengetahui omzet usahanya?',
                'jawaban' => 'Lakukan probing dengan pendekatan yang mudah dipahami, misalnya menanyakan rata-rata penjualan dalam periode tertentu dan sumber pencatatan yang tersedia.',
                'dijawab_oleh' => 'Pegawai Organik 04',
                'dijawab_at' => '2026-09-09 14:20:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'PPL 010',
                'pertanyaan' => 'Apakah usaha online yang hanya menggunakan Instagram termasuk usaha digital?',
                'jawaban' => 'Penggunaan Instagram sebagai media pemasaran tidak serta-merta menentukan klasifikasi usaha. Yang perlu diperhatikan adalah kegiatan ekonomi utama yang dilakukan.',
                'dijawab_oleh' => 'Pegawai Organik 01',
                'dijawab_at' => '2026-09-09 15:00:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'PPL 011',
                'pertanyaan' => 'Kalau usaha baru berjalan dua bulan, apakah tetap dicatat?',
                'jawaban' => 'Ya, selama kegiatan usaha tersebut termasuk dalam cakupan SE2026 dan memenuhi kondisi pencacahan yang ditetapkan.',
                'dijawab_oleh' => 'Pegawai Organik 03',
                'dijawab_at' => '2026-09-10 08:30:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim6',
                'pertanyaan' => 'Bagaimana jika nama usaha di papan berbeda dengan nama yang diberikan responden?',
                'jawaban' => 'Lakukan konfirmasi kepada responden untuk memastikan nama usaha yang digunakan dan hubungan antara nama pada papan dengan usaha yang sedang didata.',
                'dijawab_oleh' => 'Pegawai Organik 02',
                'dijawab_at' => '2026-09-10 09:05:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'PPL 012',
                'pertanyaan' => 'Apakah usaha yang tidak mempunyai tempat usaha tetap tetap dapat didata?',
                'jawaban' => 'Ya, selama kegiatan ekonominya termasuk cakupan. Kondisi lokasi usaha perlu dicatat sesuai konsep dan definisi yang berlaku.',
                'dijawab_oleh' => 'Pegawai Organik 04',
                'dijawab_at' => '2026-09-10 09:40:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'PPL 013',
                'pertanyaan' => 'Kalau satu perusahaan memiliki cabang di Pangkep, apakah cabangnya ikut didata?',
                'jawaban' => 'Unit kegiatan ekonomi yang berada di wilayah pencacahan perlu diperhatikan sesuai ketentuan unit statistik dan cakupan SE2026.',
                'dijawab_oleh' => 'Pegawai Organik 01',
                'dijawab_at' => '2026-09-10 10:15:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim7',
                'pertanyaan' => 'Bagaimana jika PPL menemukan usaha yang menurut warga sudah tutup, tetapi papan namanya masih terpasang?',
                'jawaban' => 'Perlu dilakukan verifikasi lebih lanjut kepada pemilik atau pihak yang mengetahui usaha tersebut sebelum menentukan status akhirnya.',
                'dijawab_oleh' => 'Pegawai Organik 03',
                'dijawab_at' => '2026-09-10 10:45:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'PPL 014',
                'pertanyaan' => 'Kalau usaha tidak memiliki pembukuan, bagaimana cara menanyakan pendapatannya?',
                'jawaban' => 'Gunakan pertanyaan probing berdasarkan frekuensi transaksi, rata-rata penjualan, dan periode usaha agar responden dapat memberikan estimasi yang sesuai konsep.',
                'dijawab_oleh' => 'Pegawai Organik 02',
                'dijawab_at' => '2026-09-10 11:10:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'PPL 015',
                'pertanyaan' => 'Apakah pedagang keliling termasuk dalam pendataan SE2026?',
                'jawaban' => 'Perlu dilihat berdasarkan cakupan kegiatan ekonomi dan ketentuan lokasi/unit usaha yang berlaku dalam SE2026.',
                'dijawab_oleh' => 'Pegawai Organik 04',
                'dijawab_at' => '2026-09-10 11:40:00',
                'status' => 'dijawab',
            ],

            [
                'nama' => 'Anonim8',
                'pertanyaan' => 'Kalau PPL menemukan dua usaha dengan pemilik yang sama, bagaimana menentukan apakah keduanya merupakan usaha yang berbeda?',
                'jawaban' => 'Periksa kegiatan, lokasi, pengelolaan, dan karakteristik unit usahanya. Kesamaan pemilik saja belum cukup untuk menyatakan keduanya satu unit usaha.',
                'dijawab_oleh' => 'Pegawai Organik 01',
                'dijawab_at' => '2026-09-10 12:05:00',
                'status' => 'dijawab',
            ],

            // =====================================================
            // MASIH MENUNGGU
            // =====================================================

            [
                'nama' => 'PPL 016',
                'pertanyaan' => 'Bagaimana perlakuan usaha yang sebagian besar kegiatannya dilakukan di luar wilayah Pangkep tetapi alamat pemiliknya berada di Pangkep?',
                'jawaban' => null,
                'dijawab_oleh' => null,
                'dijawab_at' => null,
                'status' => 'menunggu',
            ],

            [
                'nama' => 'Anonim9',
                'pertanyaan' => 'Kalau satu lokasi digunakan untuk usaha keluarga yang berbeda-beda tetapi tidak ada pemisahan tempat yang jelas, bagaimana menentukan unit usahanya?',
                'jawaban' => null,
                'dijawab_oleh' => null,
                'dijawab_at' => null,
                'status' => 'menunggu',
            ],

            [
                'nama' => 'PPL 017',
                'pertanyaan' => 'Bagaimana pencatatan usaha yang hanya aktif pada musim tertentu seperti usaha yang mengikuti musim panen?',
                'jawaban' => null,
                'dijawab_oleh' => null,
                'dijawab_at' => null,
                'status' => 'menunggu',
            ],

            [
                'nama' => 'PPL 018',
                'pertanyaan' => 'Kalau responden memiliki usaha sekaligus bekerja sebagai karyawan di perusahaan lain, bagaimana menentukan kegiatan ekonominya?',
                'jawaban' => null,
                'dijawab_oleh' => null,
                'dijawab_at' => null,
                'status' => 'menunggu',
            ],

            [
                'nama' => 'PML 010',
                'pertanyaan' => 'Bagaimana menentukan kegiatan utama restoran yang juga memiliki usaha katering dan menjual makanan beku secara online?',
                'jawaban' => null,
                'dijawab_oleh' => null,
                'dijawab_at' => null,
                'status' => 'menunggu',
            ],

            [
                'nama' => 'PPL 019',
                'pertanyaan' => 'Jika responden tidak bersedia memberikan informasi karena menganggap data usaha bersifat rahasia, bagaimana cara melakukan pendekatan?',
                'jawaban' => null,
                'dijawab_oleh' => null,
                'dijawab_at' => null,
                'status' => 'menunggu',
            ],
        ];

        foreach ($qnas as $qna) {
            Qna::create($qna);
        }
    }
}
