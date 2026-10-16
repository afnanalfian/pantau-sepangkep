<x-layouts.app :title="'Beranda'">

@php
    $role = session('role');
    $roleLabel = session('role_label');
    $jam = now()->hour;
    $sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
@endphp

<!-- ============================================ -->
<!-- GREETING SECTION - Gunakan warna berbeda dari sidebar -->
<!-- ============================================ -->
<div class="bg-gradient-to-r from-orange-600 to-orange-700 rounded-xl sm:rounded-2xl p-5 sm:p-6 lg:p-8 text-white mb-6 sm:mb-8 shadow-lg shadow-orange-600/20">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-orange-100 text-xs sm:text-sm font-medium">{{ $sapaan }},</p>
            <h2 class="font-display font-bold text-2xl sm:text-3xl mt-0.5 sm:mt-1">{{ $roleLabel }}</h2>
            <p class="text-orange-100/80 mt-2 max-w-xl text-xs sm:text-sm leading-relaxed">
                Anda login ke Portal Pegawai Pantau Sepangkep. Gunakan menu di samping untuk mengakses
                modul sesuai hak akses Anda.
            </p>
        </div>
        <div class="mt-3 sm:mt-0 flex-shrink-0">
            <span class="inline-block px-3 py-1 rounded-full bg-white/20 border border-white/30 text-white text-[10px] sm:text-xs font-medium">
                {{ $roleLabel }}
            </span>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MENU GRID -->
<!-- ============================================ -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
    
    <!-- QnA - Admin/INDA only -->
    @if($role === 'admin' || $role === 'inda')
    <a href="{{ route('pegawai.qna') }}" 
       class="group bg-white rounded-xl p-4 sm:p-5 border border-slate-200 hover:border-orange-300 hover:shadow-md transition active:scale-[0.98]">
        <div class="w-10 sm:w-11 h-10 sm:h-11 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600 mb-3 group-hover:bg-orange-600 group-hover:text-white transition">
            <x-icon name="chat" class="w-5 sm:w-6 h-5 sm:h-6" />
        </div>
        <div class="font-display font-semibold text-slate-900 text-sm sm:text-base group-hover:text-orange-600 transition">QnA</div>
        <p class="text-xs text-slate-500 mt-0.5 sm:mt-1 leading-relaxed">Jawab pertanyaan dari publik.</p>
    </a>
    @endif

    <!-- Pengumuman -->
    <a href="{{ route('pegawai.pengumuman') }}" 
       class="group bg-white rounded-xl p-4 sm:p-5 border border-slate-200 hover:border-orange-300 hover:shadow-md transition active:scale-[0.98]">
        <div class="w-10 sm:w-11 h-10 sm:h-11 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600 mb-3 group-hover:bg-emerald-600 group-hover:text-white transition">
            <x-icon name="megaphone" class="w-5 sm:w-6 h-5 sm:h-6" />
        </div>
        <div class="font-display font-semibold text-slate-900 text-sm sm:text-base group-hover:text-orange-600 transition">Pengumuman</div>
        <p class="text-xs text-slate-500 mt-0.5 sm:mt-1 leading-relaxed">Buat & kelola pengumuman.</p>
    </a>

    <!-- Anomali -->
    <a href="{{ route('pegawai.anomali') }}" 
       class="group bg-white rounded-xl p-4 sm:p-5 border border-slate-200 hover:border-orange-300 hover:shadow-md transition active:scale-[0.98]">
        <div class="w-10 sm:w-11 h-10 sm:h-11 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600 mb-3 group-hover:bg-amber-600 group-hover:text-white transition">
            <x-icon name="alert" class="w-5 sm:w-6 h-5 sm:h-6" />
        </div>
        <div class="font-display font-semibold text-slate-900 text-sm sm:text-base group-hover:text-orange-600 transition">Anomali</div>
        <p class="text-xs text-slate-500 mt-0.5 sm:mt-1 leading-relaxed">Pantau anomali data pekanan.</p>
    </a>

    <!-- Quality Gates -->
    <a href="{{ route('pegawai.qg') }}" 
       class="group bg-white rounded-xl p-4 sm:p-5 border border-slate-200 hover:border-orange-300 hover:shadow-md transition active:scale-[0.98]">
        <div class="w-10 sm:w-11 h-10 sm:h-11 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 mb-3 group-hover:bg-indigo-600 group-hover:text-white transition">
            <x-icon name="shield" class="w-5 sm:w-6 h-5 sm:h-6" />
        </div>
        <div class="font-display font-semibold text-slate-900 text-sm sm:text-base group-hover:text-orange-600 transition">Quality Gates</div>
        <p class="text-xs text-slate-500 mt-0.5 sm:mt-1 leading-relaxed">Ukuran kualitas & aksi preventif.</p>
    </a>

    <!-- Arsiparis -->
    <a href="{{ route('pegawai.arsip') }}" 
       class="group bg-white rounded-xl p-4 sm:p-5 border border-slate-200 hover:border-orange-300 hover:shadow-md transition active:scale-[0.98]">
        <div class="w-10 sm:w-11 h-10 sm:h-11 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 mb-3 group-hover:bg-slate-700 group-hover:text-white transition">
            <x-icon name="archive" class="w-5 sm:w-6 h-5 sm:h-6" />
        </div>
        <div class="font-display font-semibold text-slate-900 text-sm sm:text-base group-hover:text-orange-600 transition">Arsiparis</div>
        <p class="text-xs text-slate-500 mt-0.5 sm:mt-1 leading-relaxed">Berkas surat & undangan sensus.</p>
    </a>

    <!-- Dashboard Publik -->
    <a href="{{ route('dashboard.publik') }}" target="_blank" 
       class="group bg-white rounded-xl p-4 sm:p-5 border border-slate-200 hover:border-orange-300 hover:shadow-md transition active:scale-[0.98]">
        <div class="w-10 sm:w-11 h-10 sm:h-11 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 mb-3 group-hover:bg-slate-700 group-hover:text-white transition">
            <x-icon name="external" class="w-5 sm:w-6 h-5 sm:h-6" />
        </div>
        <div class="font-display font-semibold text-slate-900 text-sm sm:text-base group-hover:text-orange-600 transition">Dashboard Publik</div>
        <p class="text-xs text-slate-500 mt-0.5 sm:mt-1 leading-relaxed">Buka dashboard progres lapangan.</p>
    </a>
</div>

<!-- ============================================ -->
<!-- QUICK INFO / STATS -->
<!-- ============================================ -->
<div class="mt-6 sm:mt-8 grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
    <div class="bg-white rounded-xl p-3 sm:p-4 border border-slate-200 text-center hover:border-orange-300 transition">
        <div class="text-lg sm:text-xl font-bold text-orange-600">2026</div>
        <div class="text-[10px] sm:text-xs text-slate-400">Tahun Sensus</div>
    </div>
    <div class="bg-white rounded-xl p-3 sm:p-4 border border-slate-200 text-center hover:border-orange-300 transition">
        <div class="text-lg sm:text-xl font-bold text-slate-700">SE</div>
        <div class="text-[10px] sm:text-xs text-slate-400">Sensus Ekonomi</div>
    </div>
    <div class="bg-white rounded-xl p-3 sm:p-4 border border-slate-200 text-center hover:border-orange-300 transition">
        <div class="text-lg sm:text-xl font-bold text-slate-700">{{ $roleLabel }}</div>
        <div class="text-[10px] sm:text-xs text-slate-400">Hak Akses</div>
    </div>
    <div class="bg-white rounded-xl p-3 sm:p-4 border border-slate-200 text-center hover:border-orange-300 transition">
        <div class="text-lg sm:text-xl font-bold text-emerald-600">✓</div>
        <div class="text-[10px] sm:text-xs text-slate-400">Active</div>
    </div>
</div>

</x-layouts.app>