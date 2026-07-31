<div>
    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Total Anomali</span>
                <span class="text-2xl font-bold text-slate-800">{{ $dash['total'] }}</span>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Selesai Ditindaklanjuti</span>
                <span class="text-2xl font-bold text-emerald-600">{{ $dash['selesai'] }}</span>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Belum Ditindaklanjuti</span>
                <span class="text-2xl font-bold text-red-600">{{ $dash['total'] - $dash['selesai'] }}</span>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Progress</span>
                <span class="text-2xl font-bold text-orange-600">
                    {{ $dash['total'] > 0 ? round(($dash['selesai'] / $dash['total']) * 100, 1) : 0 }}%
                </span>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- By Jenis -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <h3 class="font-semibold text-slate-700 mb-3">By Jenis</h3>
            @if(count($dash['byJenis']) > 0)
                <div class="space-y-2">
                    @foreach($dash['byJenis'] as $jenis => $count)
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-slate-600 w-20">{{ ucfirst($jenis) }}</span>
                            <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-orange-500 rounded-full" style="width: {{ ($count / $dash['total']) * 100 }}%"></div>
                            </div>
                            <span class="text-sm font-semibold text-slate-700">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400">Tidak ada data</p>
            @endif
        </div>

        <!-- By Status -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <h3 class="font-semibold text-slate-700 mb-3">By Status</h3>
            @if(count($dash['byStatus']) > 0)
                <div class="space-y-2">
                    @foreach($dash['byStatus'] as $status => $count)
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-slate-600 w-20">{{ ucfirst($status) }}</span>
                            <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $status === 'sudah' ? 'bg-emerald-500' : 'bg-red-500' }}" 
                                     style="width: {{ ($count / $dash['total']) * 100 }}%"></div>
                            </div>
                            <span class="text-sm font-semibold text-slate-700">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400">Tidak ada data</p>
            @endif
        </div>

        <!-- Top 10 Anomali -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <h3 class="font-semibold text-slate-700 mb-3">Top 10 Nama Anomali</h3>
            @if(count($dash['byAnomali']) > 0)
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    @foreach($dash['byAnomali'] as $nama => $count)
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-sm text-slate-600 truncate flex-1">{{ $nama }}</span>
                            <span class="text-sm font-semibold text-slate-700 ml-2">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400">Tidak ada data</p>
            @endif
        </div>

        <!-- By Kecamatan -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <h3 class="font-semibold text-slate-700 mb-3">By Kecamatan</h3>
            @if(count($dash['byKecamatan']) > 0)
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    @foreach($dash['byKecamatan'] as $kec)
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-sm text-slate-600 flex-1">{{ $kec['kecamatan'] }}</span>
                            <span class="text-xs text-slate-500">
                                {{ $kec['selesai'] }}/{{ $kec['total'] }} 
                                ({{ $kec['persen'] }}%)
                            </span>
                            <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden ml-2">
                                <div class="h-full bg-orange-500 rounded-full" style="width: {{ $kec['persen'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400">Tidak ada data</p>
            @endif
        </div>
    </div>

    <!-- Status Penyelesaian -->
    @if(count($dash['byStatusPenyelesaian']) > 0)
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mt-6">
            <h3 class="font-semibold text-slate-700 mb-3">Status Penyelesaian</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach($dash['byStatusPenyelesaian'] as $status => $data)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-200">
                        <span class="text-sm font-medium text-slate-600">{{ $data['label'] }}</span>
                        <span class="text-lg font-bold text-slate-800">{{ $data['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>