<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Anomali\AnomaliDetail;
use App\Livewire\Anomali\AnomaliList;
use App\Livewire\Anomali\AnomaliUpload;
use App\Livewire\MissingValue\MissingValueDetail;
use App\Livewire\MissingValue\MissingValueList;
use App\Livewire\MissingValue\MissingValueUpload;
use App\Livewire\Arsip\ArsipManager;
use App\Livewire\Dashboard\PublicDashboard;
use App\Livewire\Dashboard\UploadHarian;
use App\Livewire\Pengumuman\PengumumanDetail;
use App\Livewire\Pengumuman\PengumumanManager;
use App\Livewire\Pengumuman\PengumumanPublic;
use App\Livewire\Qna\QnaAdmin;
use App\Livewire\Qna\QnaPublic;
use App\Livewire\QualityGates\QgManager;
use App\Livewire\NtbNegatif\NtbNegatifDetail;
use App\Livewire\NtbNegatif\NtbNegatifList;
use App\Livewire\NtbNegatif\NtbNegatifUpload;
use App\Livewire\CekKbli\KbliDetail;
use App\Livewire\CekKbli\KbliList;
use App\Livewire\CekKbli\KbliUpload;
use App\Livewire\PemindahanPendapatan\PemindahanDetail;
use App\Livewire\PemindahanPendapatan\PemindahanList;
use App\Livewire\PemindahanPendapatan\PemindahanUpload;
use App\Livewire\PendapatanSejuta\SejutaDetail;
use App\Livewire\PendapatanSejuta\SejutaList;
use App\Livewire\PendapatanSejuta\SejutaUpload;
use App\Livewire\Anomali24\Anomali24Detail;
use App\Livewire\Anomali24\Anomali24List;
use App\Livewire\Anomali24\Anomali24Upload;
use App\Livewire\FlagRasio3\FlagRasio3Detail;
use App\Livewire\FlagRasio3\FlagRasio3List;
use App\Livewire\FlagRasio3\FlagRasio3Upload;
use App\Livewire\MatchingAsn\MatchingAsnDetail;
use App\Livewire\MatchingAsn\MatchingAsnList;
use App\Livewire\MatchingAsn\MatchingAsnUpload;
use App\Livewire\NtbFlag3\NtbFlag3Detail;
use App\Livewire\NtbFlag3\NtbFlag3List;
use App\Livewire\NtbFlag3\NtbFlag3Upload;
use App\Livewire\NtbFlag3\NtbFlag3GantiRasio;
use App\Livewire\Sosek\SosekDetail;
use App\Livewire\Sosek\SosekList;
use App\Livewire\Sosek\SosekUpload;
use App\Livewire\KbliKeliru\KbliKeliruDetail;
use App\Livewire\KbliKeliru\KbliKeliruList;
use App\Livewire\KbliKeliru\KbliKeliruUpload;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Publik (tanpa login)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/dashboard-publik', PublicDashboard::class)->name('dashboard.publik');
Route::get('/dashboard-publik/upload', UploadHarian::class)->name('dashboard.upload'); // dijaga role di dalam komponen

Route::get('/qna', QnaPublic::class)->name('qna.publik');
Route::get('/pengumuman', PengumumanPublic::class)->name('pengumuman.publik');
Route::get('/pengumuman/{pengumuman}', PengumumanDetail::class)->name('pengumuman.detail');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Portal Pegawai (perlu login dengan kode akses)
|--------------------------------------------------------------------------
*/
Route::middleware(['role:pegawai,inda,anomali,qg'])->prefix('portal')->name('pegawai.')->group(function () {
    Route::get('/', function () {
        return view('pegawai.home');
    })->name('home');

    // QnA - admin & inda saja yang bisa jawab
    Route::get('/qna', QnaAdmin::class)->name('qna');

    // Pengumuman - semua role bisa CRUD
    Route::get('/pengumuman', PengumumanManager::class)->name('pengumuman');

    // Anomali - semua role tampil, hanya role anomali/admin yang bisa upload & ubah status
    Route::get('/anomali', AnomaliList::class)->name('anomali');
    Route::get('/anomali/upload', AnomaliUpload::class)->name('anomali.upload');
    Route::get('/anomali/{batch}', AnomaliDetail::class)->name('anomali.detail');

    Route::get('/missing-value', MissingValueList::class)->name('missing-value');
    Route::get('/missing-value/upload', MissingValueUpload::class)->name('missing-value.upload');
    Route::get('/missing-value/{batch}', MissingValueDetail::class)->name('missing-value.detail');

    Route::get('/ntb-negatif', NtbNegatifList::class)->name('ntb-negatif');
    Route::get('/ntb-negatif/upload', NtbNegatifUpload::class)->name('ntb-negatif.upload');
    Route::get('/ntb-negatif/{batch}', NtbNegatifDetail::class)->name('ntb-negatif.detail');

    // Cek KBLI
    Route::get('/cek-kbli', KbliList::class)->name('cek-kbli');
    Route::get('/cek-kbli/upload', KbliUpload::class)->name('cek-kbli.upload');
    Route::get('/cek-kbli/{batch}', KbliDetail::class)->name('cek-kbli.detail');
    // Anomali 24 (Anomali KBLI Tidak Sesuai 01122)
    Route::get('/anomali-24', Anomali24List::class)->name('anomali24');
    Route::get('/anomali-24/upload', Anomali24Upload::class)->name('anomali24.upload');
    Route::get('/anomali-24/{batch}', Anomali24Detail::class)->name('anomali24.detail');

    // Anomali NTB Flag 3
    Route::get('/ntb-flag-3', NtbFlag3List::class)->name('ntb-flag3');
    Route::get('/ntb-flag-3/upload', NtbFlag3Upload::class)->name('ntb-flag3.upload');
    Route::get('/ntb-flag-3/ganti-rasio', NtbFlag3GantiRasio::class)->name('ntb-flag3.ganti-rasio');
    Route::get('/ntb-flag-3/{batch}', NtbFlag3Detail::class)->name('ntb-flag3.detail')->whereNumber('batch');

    // Pemindahan Pendapatan
    Route::get('/pemindahan-pendapatan', PemindahanList::class)->name('pemindahan-pendapatan');
    Route::get('/pemindahan-pendapatan/upload', PemindahanUpload::class)->name('pemindahan-pendapatan.upload');
    Route::get('/pemindahan-pendapatan/{batch}', PemindahanDetail::class)->name('pemindahan-pendapatan.detail');

    // Anomali Flag Rasio 3 (172 UB, 173 UM)
    Route::get('/flag-rasio-3', FlagRasio3List::class)->name('flag-rasio3');
    Route::get('/flag-rasio-3/upload', FlagRasio3Upload::class)->name('flag-rasio3.upload');
    Route::get('/flag-rasio-3/{batch}', FlagRasio3Detail::class)->name('flag-rasio3.detail');

    // Matching ASN Pangkep
    Route::get('/matching-asn', MatchingAsnList::class)->name('matching-asn');
    Route::get('/matching-asn/upload', MatchingAsnUpload::class)->name('matching-asn.upload');
    Route::get('/matching-asn/{batch}', MatchingAsnDetail::class)->name('matching-asn.detail');

    // Pendapatan 1 Juta ke Bawah
    Route::get('/pendapatan-sejuta', SejutaList::class)->name('pendapatan-sejuta');
    Route::get('/pendapatan-sejuta/upload', SejutaUpload::class)->name('pendapatan-sejuta.upload');
    Route::get('/pendapatan-sejuta/{batch}', SejutaDetail::class)->name('pendapatan-sejuta.detail');

    // Anomali Sosek
    Route::get('/anomali-sosek', SosekList::class)->name('sosek');
    Route::get('/anomali-sosek/upload', SosekUpload::class)->name('sosek.upload');
    Route::get('/anomali-sosek/{batch}', SosekDetail::class)->name('sosek.detail')->whereNumber('batch');

    // KBLI Keliru (di dalam group pegawai.)
    Route::get('/kbli-keliru', KbliKeliruList::class)->name('kbli-keliru');
    Route::get('/kbli-keliru/upload', KbliKeliruUpload::class)->name('kbli-keliru.upload');
    Route::get('/kbli-keliru/{batch}', KbliKeliruDetail::class)->name('kbli-keliru.detail')->whereNumber('batch');

    // Quality Gates - semua role tampil, hanya qg/admin yang CRUD struktur
    Route::get('/quality-gates', QgManager::class)->name('qg');

    // Arsiparis - semua role read, hanya admin CRUD
    Route::get('/arsiparis', ArsipManager::class)->name('arsip');
});
