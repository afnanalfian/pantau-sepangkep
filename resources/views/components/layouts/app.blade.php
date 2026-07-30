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
        body { font-family: 'Inter', sans-serif; background:#F1F5F9; }
        .font-display { font-family: 'Space Grotesk', sans-serif; }
        
        button, a { 
            -webkit-tap-highlight-color: transparent; 
        }
    </style>
</head>
<body class="text-slate-800 antialiased" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" x-init="window.addEventListener('resize', () => { sidebarOpen = window.innerWidth >= 1024 })">

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

<!-- ============================================ -->
<!-- APP WRAPPER -->
<!-- ============================================ -->
<div class="flex min-h-screen">

    <!-- ============================================ -->
    <!-- MOBILE OVERLAY -->
    <!-- ============================================ -->
    <div x-show="sidebarOpen && window.innerWidth < 1024" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

    <!-- ============================================ -->
    <!-- SIDEBAR -->
    <!-- ============================================ -->
    <aside class="fixed lg:sticky top-0 left-0 z-50 h-screen w-72 sm:w-80 lg:w-64 bg-slate-900 border-r border-slate-800 shrink-0 flex flex-col transform transition-transform duration-300 ease-in-out 
           -translate-x-full 
           lg:translate-x-0" 
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        
        <!-- ========================================== -->
        <!-- SIDEBAR HEADER -->
        <!-- ========================================== -->
        <div class="h-14 sm:h-16 flex items-center gap-3 px-4 sm:px-6 border-b border-slate-800 flex-shrink-0">
            <img src="{{ asset('images/logo_bps.png') }}" class="h-7 sm:h-8 w-auto" onerror="this.style.display='none'">
            <div>
                <div class="font-display font-bold text-orange-400 text-sm tracking-tight">PANTAU SEPANGKEP</div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider">Portal Pegawai</div>
            </div>
            <button @click="sidebarOpen = false" class="ml-auto lg:hidden p-2 text-slate-400 hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- ========================================== -->
        <!-- SIDEBAR NAVIGATION (flex-1) -->
        <!-- ========================================== -->
        <nav class="flex-1 px-3 py-3 sm:py-4 space-y-0.5 overflow-y-auto">
            @foreach($menu as $item)
                @if(!$item['roles'] || $role === 'admin' || in_array($role, $item['roles']))
                    <a href="{{ route($item['route']) }}"
                       @click="if(window.innerWidth < 1024) sidebarOpen = false"
                       class="flex items-center gap-3 px-3 py-3 sm:py-2.5 rounded-lg text-sm font-medium transition active:scale-95
                       {{ request()->routeIs($item['route']) 
                          ? 'bg-orange-600 text-white' 
                          : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <x-icon name="{{ $item['icon'] }}" class="w-5 h-5 shrink-0" />
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>

        <!-- ========================================== -->
        <!-- SIDEBAR FOOTER (flex-shrink-0) -->
        <!-- ========================================== -->
        <div class="flex-shrink-0 border-t border-slate-800">
            
            <!-- Dashboard Publik -->
            <a href="{{ route('dashboard.publik') }}" target="_blank" 
               class="flex items-center gap-3 px-4 sm:px-6 py-3 sm:py-2.5 text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Dashboard Publik
            </a>
            
            <!-- Logout - Terintegrasi dengan sidebar -->
            <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-800/50">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center gap-3 px-4 sm:px-6 py-3 sm:py-2.5 text-sm font-medium text-red-400 hover:bg-red-500/10 hover:text-red-300 transition active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <!-- ============================================ -->
    <!-- MAIN CONTENT -->
    <!-- ============================================ -->
    <div class="flex-1 flex flex-col min-w-0 w-full">
        
        <!-- Top Header -->
        <header class="sticky top-0 z-30 bg-white border-b border-orange-200">
            <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-14 sm:h-16">
                
                <div class="flex items-center gap-2 min-w-0">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-slate-100 transition active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="font-display font-semibold text-sm sm:text-lg text-slate-800 truncate">{{ $title ?? 'Portal Pegawai' }}</h1>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <!-- Logout button di header (mobile) -->
                    {{-- <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
                        @csrf
                        <button type="submit" class="text-xs font-medium px-2 py-1 rounded-lg text-red-500 hover:bg-red-50 transition">
                            Logout
                        </button>
                    </form> --}}
                    <span class="text-[10px] sm:text-xs font-medium px-2 sm:px-3 py-1 sm:py-1.5 rounded-full bg-orange-100 text-orange-700 border border-orange-200 whitespace-nowrap">
                        {{ $roleLabel }}
                    </span>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-3 sm:p-5 lg:p-8">
            @if(session('status'))
                <div class="mb-4 sm:mb-6 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg bg-emerald-50 text-emerald-700 text-xs sm:text-sm font-medium border border-emerald-200">
                    {{ session('status') }}
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>

@livewireScripts
</body>
</html>