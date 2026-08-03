<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'PANTAU SEPANGKEP' }} — BPS Kabupaten Pangkajene dan Kepulauan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    {{-- Leaflet: dipakai oleh tab "Peta SLS" pada dashboard publik --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    @livewireStyles
    <style>
        body { font-family: 'Inter', sans-serif; background:#F8FAFC; }
        .font-display { font-family: 'Space Grotesk', sans-serif; }
    </style>
</head>
<body class="text-slate-800 antialiased">

    <!-- ============================================ -->
    <!-- HEADER - Mobile First -->
    <!-- ============================================ -->
    <header class="sticky top-0 z-40 bg-white border-b border-orange-200">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14 sm:h-16">
                
                <!-- Logo - Compact untuk mobile -->
                <a href="{{ route('landing') }}" class="flex items-center gap-2 flex-shrink-0">
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <img src="{{ asset('images/logo_bps.png') }}" alt="Logo BPS" class="h-7 sm:h-8 w-auto" onerror="this.style.display='none'">
                        <img src="{{ asset('images/logo_sensus.png') }}" alt="Logo Sensus" class="h-7 sm:h-8 w-auto" onerror="this.style.display='none'">
                    </div>
                    <div class="hidden sm:block border-l border-orange-200 pl-3">
                        <div class="font-display font-bold text-orange-600 text-sm tracking-tight">PANTAU SEPANGKEP</div>
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">Sensus Ekonomi 2026</div>
                    </div>
                </a>

                <!-- Navigation - Mobile friendly -->
                <nav class="flex items-center gap-0.5 sm:gap-1">
                    <a href="{{ route('dashboard.publik') }}" 
                       class="px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium text-slate-600 hover:text-orange-600 hover:bg-orange-50 transition {{ request()->routeIs('dashboard.publik') ? 'bg-orange-50 text-orange-600' : '' }}">
                        <span class="hidden sm:inline">Dashboard</span>
                        <span class="sm:hidden">Home</span>
                    </a>
                    <a href="{{ route('qna.publik') }}" 
                       class="px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium text-slate-600 hover:text-orange-600 hover:bg-orange-50 transition {{ request()->routeIs('qna.publik') ? 'bg-orange-50 text-orange-600' : '' }}">
                        QnA
                    </a>
                    <a href="{{ route('pengumuman.publik') }}" 
                       class="hidden sm:inline-block px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-orange-600 hover:bg-orange-50 transition {{ request()->routeIs('pengumuman.*') ? 'bg-orange-50 text-orange-600' : '' }}">
                        Pengumuman
                    </a>
                    @if(session('role'))
                        <a href="{{ route('pegawai.home') }}" class="ml-1 sm:ml-2 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium bg-orange-600 text-white hover:bg-orange-700 transition whitespace-nowrap">
                            <span class="hidden sm:inline">Portal Pegawai</span>
                            <span class="sm:hidden">Pegawai</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="ml-1 sm:ml-2 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium bg-orange-600 text-white hover:bg-orange-700 transition whitespace-nowrap">
                            Login
                        </a>
                    @endif
                </nav>
            </div>
        </div>
    </header>

    <!-- ============================================ -->
    <!-- MAIN CONTENT -->
    <!-- ============================================ -->
    <main>
        {{ $slot }}
    </main>

    <!-- ============================================ -->
    <!-- FOOTER - Mobile First -->
    <!-- ============================================ -->
    <footer class="mt-16 bg-slate-900 text-white">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center gap-2 py-6 sm:py-8 text-xs text-slate-400 text-center">
                <p>&copy; {{ date('Y') }} BPS Kabupaten Pangkajene</p>
                <p class="text-orange-400 text-[10px] sm:text-xs">Pantau Sepangkep · Sensus Ekonomi 2026</p>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>