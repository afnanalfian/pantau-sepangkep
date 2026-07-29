<x-layouts.public :title="'Login Pegawai'">

<section class="min-h-[calc(100vh-64px-90px)] flex items-center justify-center px-5 py-16 bg-[#0B2A4A]">
    <div class="w-full max-w-sm">
        <div class="text-center mb-7">
            <div class="flex items-center justify-center gap-2 mb-4">
                <img src="{{ asset('images/logo_bps.png') }}" class="h-10 w-auto" onerror="this.style.display='none'">
                <img src="{{ asset('images/logo_sensus.png') }}" class="h-10 w-auto" onerror="this.style.display='none'">
            </div>
            <h1 class="font-display font-bold text-2xl text-white">Login Pegawai</h1>
            <p class="text-white/60 text-sm mt-1.5">Masukkan kode akses yang telah diberikan kepada Anda.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-7">
            @if($errors->any())
                <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 text-red-600 text-sm font-medium border border-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kode Akses</label>
                    <input type="password" name="kode" autofocus required
                           class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-[#0F7B8A] focus:ring-2 focus:ring-[#0F7B8A]/20 outline-none transition font-mono tracking-wide"
                           placeholder="Masukkan kode akses...">
                </div>
                <button type="submit" class="w-full py-3 rounded-lg bg-[#0B2A4A] hover:bg-[#0d3760] text-white font-semibold transition">
                    Masuk
                </button>
            </form>
        </div>

        <p class="text-center text-white/40 text-xs mt-6">
            <a href="{{ route('landing') }}" class="hover:text-white/70 transition">&larr; Kembali ke beranda</a>
        </p>
    </div>
</section>

</x-layouts.public>
