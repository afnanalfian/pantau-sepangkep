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
    @livewireStyles
    <style>
        body { font-family: 'Inter', sans-serif; background:#F5F7FA; }
        .font-display { font-family: 'Space Grotesk', sans-serif; }
    </style>
</head>
<body class="text-slate-800 antialiased">

    <header class="sticky top-0 z-40 bg-[#0B2A4A] border-b border-white/10">
        <div class="max-w-7xl mx-auto px-5 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo_bps.png') }}" alt="Logo BPS" class="h-9 w-auto" onerror="this.style.display='none'">
                    <img src="{{ asset('images/logo_sensus.png') }}" alt="Logo Sensus Ekonomi 2026" class="h-9 w-auto" onerror="this.style.display='none'">
                </div>
                <div class="hidden sm:block leading-tight border-l border-white/20 pl-3">
                    <div class="font-display font-bold text-white text-[15px] tracking-tight">PANTAU SEPANGKEP</div>
                    <div class="text-[10px] text-white/60 uppercase tracking-wider">Sensus Ekonomi 2026 · Pangkep</div>
                </div>
            </a>

            <nav class="flex items-center gap-1">
                <a href="{{ route('dashboard.publik') }}" class="px-3 py-2 rounded-lg text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition {{ request()->routeIs('dashboard.publik') ? 'bg-white/10 text-white' : '' }}">Dashboard</a>
                <a href="{{ route('qna.publik') }}" class="px-3 py-2 rounded-lg text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition {{ request()->routeIs('qna.publik') ? 'bg-white/10 text-white' : '' }}">QnA</a>
                <a href="{{ route('pengumuman.publik') }}" class="px-3 py-2 rounded-lg text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition {{ request()->routeIs('pengumuman.*') ? 'bg-white/10 text-white' : '' }}">Pengumuman</a>
                @if(session('role'))
                    <a href="{{ route('pegawai.home') }}" class="ml-2 px-3.5 py-2 rounded-lg text-sm font-semibold bg-[#0F7B8A] text-white hover:bg-[#0d6b78] transition">Portal Pegawai</a>
                @else
                    <a href="{{ route('login') }}" class="ml-2 px-3.5 py-2 rounded-lg text-sm font-semibold bg-[#E2A63B] text-[#0B2A4A] hover:bg-[#d69a2f] transition">Login Pegawai</a>
                @endif
            </nav>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-slate-200 bg-white">
        <div class="max-w-7xl mx-auto px-5 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} BPS Kabupaten Pangkajene dan Kepulauan &mdash; Pantau Sepangkep, Sensus Ekonomi 2026.</p>
            <p class="font-medium text-slate-400">Dibangun untuk mendukung transparansi data lapangan.</p>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
