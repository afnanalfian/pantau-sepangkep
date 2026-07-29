<x-layouts.app :title="'Beranda'">

@php
    $role = session('role');
    $roleLabel = session('role_label');
    $jam = now()->hour;
    $sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
@endphp

<div class="bg-gradient-to-br from-[#0B2A4A] to-[#123C69] rounded-2xl p-8 text-white mb-8">
    <p class="text-white/60 text-sm font-medium">{{ $sapaan }},</p>
    <h2 class="font-display font-bold text-3xl mt-1">{{ $roleLabel }}</h2>
    <p class="text-white/70 mt-2 max-w-xl text-sm leading-relaxed">
        Anda login ke Portal Pegawai Pantau Sepangkep. Gunakan menu di samping untuk mengakses
        modul QnA, Pengumuman, Anomali, Quality Gates, dan Arsiparis sesuai hak akses Anda.
    </p>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @if($role === 'admin' || $role === 'inda')
    <a href="{{ route('pegawai.qna') }}" class="bg-white rounded-xl p-5 border border-slate-200 hover:shadow-md transition">
        <x-icon name="chat" class="w-7 h-7 text-[#0F7B8A] mb-3" />
        <div class="font-semibold text-[#0B2A4A]">QnA</div>
        <p class="text-xs text-slate-500 mt-1">Jawab pertanyaan yang masuk dari publik.</p>
    </a>
    @endif

    <a href="{{ route('pegawai.pengumuman') }}" class="bg-white rounded-xl p-5 border border-slate-200 hover:shadow-md transition">
        <x-icon name="megaphone" class="w-7 h-7 text-emerald-600 mb-3" />
        <div class="font-semibold text-[#0B2A4A]">Pengumuman</div>
        <p class="text-xs text-slate-500 mt-1">Buat & kelola pengumuman untuk pegawai dan petugas.</p>
    </a>

    <a href="{{ route('pegawai.anomali') }}" class="bg-white rounded-xl p-5 border border-slate-200 hover:shadow-md transition">
        <x-icon name="alert" class="w-7 h-7 text-amber-600 mb-3" />
        <div class="font-semibold text-[#0B2A4A]">Anomali</div>
        <p class="text-xs text-slate-500 mt-1">Pantau & tindak lanjuti anomali data pekanan.</p>
    </a>

    <a href="{{ route('pegawai.qg') }}" class="bg-white rounded-xl p-5 border border-slate-200 hover:shadow-md transition">
        <x-icon name="shield" class="w-7 h-7 text-indigo-600 mb-3" />
        <div class="font-semibold text-[#0B2A4A]">Quality Gates</div>
        <p class="text-xs text-slate-500 mt-1">Ukuran kualitas & aksi preventif pengendalian mutu.</p>
    </a>

    <a href="{{ route('pegawai.arsip') }}" class="bg-white rounded-xl p-5 border border-slate-200 hover:shadow-md transition">
        <x-icon name="archive" class="w-7 h-7 text-slate-600 mb-3" />
        <div class="font-semibold text-[#0B2A4A]">Arsiparis</div>
        <p class="text-xs text-slate-500 mt-1">Berkas surat, undangan, dan himbauan sensus.</p>
    </a>

    <a href="{{ route('dashboard.publik') }}" target="_blank" class="bg-white rounded-xl p-5 border border-slate-200 hover:shadow-md transition">
        <x-icon name="external" class="w-7 h-7 text-slate-600 mb-3" />
        <div class="font-semibold text-[#0B2A4A]">Dashboard Publik</div>
        <p class="text-xs text-slate-500 mt-1">Buka dashboard progres lapangan (tab baru).</p>
    </a>
</div>

</x-layouts.app>
