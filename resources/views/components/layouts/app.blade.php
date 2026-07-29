<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Portal Pegawai' }} — PANTAU SEPANGKEP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    @livewireStyles
    <style>
        body { font-family: 'Inter', sans-serif; background:#F5F7FA; }
        .font-display { font-family: 'Space Grotesk', sans-serif; }
    </style>
</head>
<body class="text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

@php
    $role = session('role');
    $roleLabel = session('role_label');
    $menu = [
        ['route' => 'pegawai.home', 'label' => 'Beranda', 'icon' => 'home', 'roles' => null],
        ['route' => 'pegawai.qna', 'label' => 'QnA', 'icon' => 'chat', 'roles' => ['admin', 'inda']],
        ['route' => 'pegawai.pengumuman', 'label' => 'Pengumuman', 'icon' => 'megaphone', 'roles' => null],
        ['route' => 'pegawai.anomali', 'label' => 'Anomali', 'icon' => 'alert', 'roles' => null],
        ['route' => 'pegawai.qg', 'label' => 'Quality Gates', 'icon' => 'shield', 'roles' => null],
        ['route' => 'pegawai.arsip', 'label' => 'Arsiparis', 'icon' => 'archive', 'roles' => null],
    ];
@endphp

<div class="flex min-h-screen">
    <aside class="hidden lg:flex lg:flex-col w-64 bg-[#0B2A4A] shrink-0">
        <div class="h-16 flex items-center gap-2 px-5 border-b border-white/10">
            <img src="{{ asset('images/logo_bps.png') }}" class="h-8 w-auto" onerror="this.style.display='none'">
            <span class="font-display font-bold text-white text-sm">PANTAU SEPANGKEP</span>
        </div>
        <nav class="flex-1 px-3 py-5 space-y-1">
            @foreach($menu as $item)
                @if(!$item['roles'] || $role === 'admin' || in_array($role, $item['roles']))
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                       {{ request()->routeIs($item['route']) ? 'bg-[#0F7B8A] text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <x-icon name="{{ $item['icon'] }}" class="w-5 h-5 shrink-0" />
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>
        <div class="p-3 border-t border-white/10">
            <a href="{{ route('dashboard.publik') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/60 hover:bg-white/10 hover:text-white transition">
                <x-icon name="external" class="w-5 h-5 shrink-0" /> Dashboard Publik
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-300 hover:bg-red-500/10 transition">
                    <x-icon name="logout" class="w-5 h-5 shrink-0" /> Keluar
                </button>
            </form>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-5 lg:px-8">
            <div>
                <h1 class="font-display font-bold text-lg text-[#0B2A4A]">{{ $title ?? 'Portal Pegawai' }}</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold px-3 py-1.5 rounded-full bg-[#0F7B8A]/10 text-[#0F7B8A]">{{ $roleLabel }}</span>
            </div>
        </header>

        <main class="flex-1 p-5 lg:p-8">
            @if(session('status'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium border border-emerald-200">{{ session('status') }}</div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>

@livewireScripts
</body>
</html>
