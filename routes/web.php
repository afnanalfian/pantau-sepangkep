<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Anomali\AnomaliDetail;
use App\Livewire\Anomali\AnomaliList;
use App\Livewire\Anomali\AnomaliUpload;
use App\Livewire\Arsip\ArsipManager;
use App\Livewire\Dashboard\PublicDashboard;
use App\Livewire\Dashboard\UploadHarian;
use App\Livewire\Pengumuman\PengumumanDetail;
use App\Livewire\Pengumuman\PengumumanManager;
use App\Livewire\Pengumuman\PengumumanPublic;
use App\Livewire\Qna\QnaAdmin;
use App\Livewire\Qna\QnaPublic;
use App\Livewire\QualityGates\QgManager;
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

    // Quality Gates - semua role tampil, hanya qg/admin yang CRUD struktur
    Route::get('/quality-gates', QgManager::class)->name('qg');

    // Arsiparis - semua role read, hanya admin CRUD
    Route::get('/arsiparis', ArsipManager::class)->name('arsip');
});
