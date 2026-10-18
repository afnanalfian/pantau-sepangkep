{{-- Keterangan cakupan modul. Variabel: $jenisOptions, opsional $compact (bool) --}}
@php $compact = $compact ?? false; @endphp
<div class="rounded-xl border border-amber-200 bg-amber-50/70 {{ $compact ? 'p-3' : 'p-4' }}">
    <div class="flex items-start gap-2.5">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="text-xs sm:text-sm text-amber-900">
            <p class="font-semibold">Modul ini mencakup 2 macam anomali:</p>
            <ul class="mt-1 space-y-0.5">
                @foreach($jenisOptions as $kode => $j)
                    <li class="flex items-start gap-2">
                        <span class="inline-block mt-0.5 text-[10px] font-bold px-1.5 py-0.5 rounded {{ $j['badge'] }}">{{ $kode }}</span>
                        <span><b>Anomali {{ $j['kode'] }}</b> : {{ $j['label'] }} - Anomali Flag Rasio 3 NTB Output</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
