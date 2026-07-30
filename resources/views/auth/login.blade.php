<x-layouts.public :title="'Login Pegawai'">

<!-- ============================================ -->
<!-- LOGIN PAGE - Mobile First -->
<!-- ============================================ -->
<section class="min-h-[calc(100vh-56px-60px)] sm:min-h-[calc(100vh-64px-80px)] flex items-center justify-center px-4 py-10 sm:py-16 bg-slate-900">
    <div class="w-full max-w-sm sm:max-w-md">
        
        <!-- Header -->
        <div class="text-center mb-6 sm:mb-8">
            <div class="flex items-center justify-center gap-2 mb-3 sm:mb-4">
                <img src="{{ asset('images/logo_bps.png') }}" class="h-8 sm:h-10 w-auto" onerror="this.style.display='none'">
                <img src="{{ asset('images/logo_sensus.png') }}" class="h-8 sm:h-10 w-auto" onerror="this.style.display='none'">
            </div>
            <h1 class="font-display font-bold text-xl sm:text-2xl text-white">Login Pegawai</h1>
            <p class="text-slate-400 text-xs sm:text-sm mt-1">Masukkan kode akses yang telah diberikan.</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl p-5 sm:p-7">
            
            <!-- Error Message -->
            @if($errors->any())
                <div class="mb-4 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg bg-red-50 text-red-600 text-xs sm:text-sm font-medium border border-red-200 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 sm:w-5 h-4 sm:h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('login.submit') }}" class="space-y-4 sm:space-y-5">
                @csrf
                
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Kode Akses</label>
                    <input type="password" 
                           name="kode" 
                           autofocus 
                           required
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition font-mono tracking-wide text-sm sm:text-base"
                           placeholder="Masukkan kode akses...">
                </div>

                <button type="submit" 
                        class="w-full py-2.5 sm:py-3 rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-semibold text-sm sm:text-base transition active:scale-[0.98]">
                    Masuk
                </button>
            </form>
        </div>

        <!-- Back Link -->
        <p class="text-center text-slate-500 text-xs sm:text-sm mt-5 sm:mt-6">
            <a href="{{ route('landing') }}" class="hover:text-orange-400 transition inline-flex items-center gap-1.5 group">
                <span class="group-hover:-translate-x-1 transition">←</span>
                Kembali ke beranda
            </a>
        </p>
    </div>
</section>

</x-layouts.public>