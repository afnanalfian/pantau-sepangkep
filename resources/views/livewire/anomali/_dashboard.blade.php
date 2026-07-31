<div>
    <!-- ============================================ -->
    <!-- STATISTIK CARDS -->
    <!-- ============================================ -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Total Anomali</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($dash['total']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Selesai</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($dash['selesai']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Belum</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($dash['total'] - $dash['selesai']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Progress</p>
                    <p class="text-2xl font-bold text-orange-600 mt-1">
                        {{ $dash['total'] > 0 ? round(($dash['selesai'] / $dash['total']) * 100, 1) : 0 }}%
                    </p>
                </div>
                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- CHART ROW 1 - Pie Charts -->
    <!-- ============================================ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-orange-500 rounded-full"></span>
                Kasus Berdasarkan Jenis
            </h3>
            <div class="relative" style="height: 250px;">
                <canvas id="chartJenis"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-emerald-500 rounded-full"></span>
                Status Tindak Lanjut
            </h3>
            <div class="relative" style="height: 250px;">
                <canvas id="chartStatus"></canvas>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- CHART ROW 2 - Bar Chart -->
    <!-- ============================================ -->
    <div class="grid grid-cols-1 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-purple-500 rounded-full"></span>
                Top 10 Jenis Anomali
            </h3>
            <div class="relative" style="height: 300px;">
                <canvas id="chartAnomali"></canvas>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- CHART ROW 3 - Horizontal Bar per Kecamatan -->
    <!-- ============================================ -->
    <div class="grid grid-cols-1 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-blue-500 rounded-full"></span>
                Progress Penyelesaian per Kecamatan
            </h3>
            <div class="relative" style="height: {{ count($dash['byKecamatan']) > 0 ? count($dash['byKecamatan']) * 40 + 50 : 200 }}px; min-height: 200px;">
                <canvas id="chartKecamatan"></canvas>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- STATUS PENYELESAIAN CARDS -->
    <!-- ============================================ -->
    @if(count($dash['byStatusPenyelesaian']) > 0)
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
            <span class="w-1 h-5 bg-indigo-500 rounded-full"></span>
            Status Penyelesaian Anomali
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @foreach($dash['byStatusPenyelesaian'] as $status => $data)
                @php
                    $colors = [
                        'revoked_pml' => 'border-blue-200 bg-blue-50',
                        'diselesaikan_admin' => 'border-emerald-200 bg-emerald-50',
                        'reject_admin' => 'border-red-200 bg-red-50',
                    ];
                    $textColors = [
                        'revoked_pml' => 'text-blue-700',
                        'diselesaikan_admin' => 'text-emerald-700',
                        'reject_admin' => 'text-red-700',
                    ];
                    $icons = [
                        'revoked_pml' => '🔄',
                        'diselesaikan_admin' => '✅',
                        'reject_admin' => '❌',
                    ];
                @endphp
                <div class="flex items-center justify-between p-4 rounded-lg border-2 {{ $colors[$status] ?? 'border-slate-200 bg-slate-50' }}">
                    <div>
                        <span class="text-lg">{{ $icons[$status] ?? '📊' }}</span>
                        <span class="text-sm font-medium text-slate-600 ml-2">{{ $data['label'] }}</span>
                    </div>
                    <span class="text-xl font-bold {{ $textColors[$status] ?? 'text-slate-800' }}">{{ $data['count'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- ============================================ -->
    <!-- CHART SCRIPTS -->
    <!-- ============================================ -->
    <script>
        document.addEventListener('livewire:init', function() {
            // Fungsi untuk destroy chart yang sudah ada
            function destroyChart(chartInstance) {
                if (chartInstance && typeof chartInstance.destroy === 'function') {
                    chartInstance.destroy();
                }
                return null;
            }

            // Data dari server
            const jenisData = @json($dash['byJenis']);
            const statusData = @json($dash['byStatus']);
            const anomaliData = @json($dash['byAnomali']);
            const kecamatanData = @json($dash['byKecamatan']);

            // Warna-warna yang digunakan
            const colors = {
                orange: ['#F59E0B', '#FCD34D', '#FBBF24', '#F59E0B', '#D97706'],
                green: ['#10B981', '#34D399', '#6EE7B7', '#A7F3D0', '#D1FAE5'],
                red: ['#EF4444', '#F87171', '#FCA5A5', '#FECACA', '#FEE2E2'],
                blue: ['#3B82F6', '#60A5FA', '#93C5FD', '#BFDBFE', '#DBEAFE'],
                purple: ['#8B5CF6', '#A78BFA', '#C4B5FD', '#DDD6FE', '#EDE9FE'],
                gradient: ['#F59E0B', '#F97316', '#EF4444', '#EC4899', '#8B5CF6', '#3B82F6', '#10B981']
            };

            // 1. CHART JENIS - Pie Chart
            const ctxJenis = document.getElementById('chartJenis');
            if (ctxJenis) {
                let chartJenis = null;
                
                function renderJenis() {
                    const labels = Object.keys(jenisData).map(k => k === 'usaha' ? 'Usaha' : 'Keluarga');
                    const values = Object.values(jenisData);
                    
                    if (window._chartJenis) window._chartJenis = destroyChart(window._chartJenis);
                    
                    if (values.length > 0) {
                        window._chartJenis = new Chart(ctxJenis, {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: values,
                                    backgroundColor: ['#F59E0B', '#1E293B'],
                                    borderWidth: 2,
                                    borderColor: '#ffffff'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            padding: 15,
                                            usePointStyle: true,
                                            pointStyle: 'circle',
                                            font: { size: 12 }
                                        }
                                    }
                                },
                                cutout: '65%'
                            }
                        });
                    }
                }
                
                renderJenis();
                Livewire.on('render', renderJenis);
            }

            // 2. CHART STATUS - Pie Chart
            const ctxStatus = document.getElementById('chartStatus');
            if (ctxStatus) {
                let chartStatus = null;
                
                function renderStatus() {
                    const labels = Object.keys(statusData).map(k => k === 'sudah' ? 'Sudah Tindak Lanjut' : 'Belum Tindak Lanjut');
                    const values = Object.values(statusData);
                    
                    if (window._chartStatus) window._chartStatus = destroyChart(window._chartStatus);
                    
                    if (values.length > 0) {
                        window._chartStatus = new Chart(ctxStatus, {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: values,
                                    backgroundColor: ['#10B981', '#EF4444'],
                                    borderWidth: 2,
                                    borderColor: '#ffffff'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            padding: 15,
                                            usePointStyle: true,
                                            pointStyle: 'circle',
                                            font: { size: 12 }
                                        }
                                    }
                                },
                                cutout: '65%'
                            }
                        });
                    }
                }
                
                renderStatus();
                Livewire.on('render', renderStatus);
            }

            // 3. CHART ANOMALI - Horizontal Bar
            const ctxAnomali = document.getElementById('chartAnomali');
            if (ctxAnomali) {
                let chartAnomali = null;
                
                function renderAnomali() {
                    const labels = Object.keys(anomaliData);
                    const values = Object.values(anomaliData);
                    
                    if (window._chartAnomali) window._chartAnomali = destroyChart(window._chartAnomali);
                    
                    if (values.length > 0) {
                        const maxValue = Math.max(...values);
                        
                        window._chartAnomali = new Chart(ctxAnomali, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Jumlah Kasus',
                                    data: values,
                                    backgroundColor: labels.map((_, i) => 
                                        colors.gradient[i % colors.gradient.length]
                                    ),
                                    borderColor: labels.map((_, i) => 
                                        colors.gradient[i % colors.gradient.length]
                                    ),
                                    borderWidth: 1,
                                    borderRadius: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                indexAxis: 'y',
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.parsed.x + ' kasus';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: {
                                            display: false
                                        },
                                        beginAtZero: true,
                                        max: maxValue + (maxValue * 0.2)
                                    },
                                    y: {
                                        grid: {
                                            display: false
                                        }
                                    }
                                }
                            }
                        });
                    }
                }
                
                renderAnomali();
                Livewire.on('render', renderAnomali);
            }

            // 4. CHART KECAMATAN - Horizontal Bar
            const ctxKecamatan = document.getElementById('chartKecamatan');
            if (ctxKecamatan) {
                let chartKecamatan = null;
                
                function renderKecamatan() {
                    const labels = kecamatanData.map(item => item.kecamatan);
                    const totalData = kecamatanData.map(item => item.total);
                    const selesaiData = kecamatanData.map(item => item.selesai);
                    const persenData = kecamatanData.map(item => item.persen);
                    
                    if (window._chartKecamatan) window._chartKecamatan = destroyChart(window._chartKecamatan);
                    
                    if (labels.length > 0) {
                        window._chartKecamatan = new Chart(ctxKecamatan, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: 'Selesai',
                                        data: selesaiData,
                                        backgroundColor: '#10B981',
                                        borderColor: '#10B981',
                                        borderWidth: 1,
                                        borderRadius: 4
                                    },
                                    {
                                        label: 'Total',
                                        data: totalData,
                                        backgroundColor: '#F59E0B',
                                        borderColor: '#F59E0B',
                                        borderWidth: 1,
                                        borderRadius: 4,
                                        opacity: 0.7
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                indexAxis: 'y',
                                plugins: {
                                    legend: {
                                        position: 'top',
                                        labels: {
                                            usePointStyle: true,
                                            pointStyle: 'circle',
                                            padding: 15,
                                            font: { size: 11 }
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.dataset.label || '';
                                                const value = context.parsed.x || 0;
                                                const index = context.dataIndex;
                                                const persen = persenData[index] || 0;
                                                return label + ': ' + value + ' (' + persen + '%)';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: {
                                            display: true,
                                            color: 'rgba(0,0,0,0.05)'
                                        },
                                        beginAtZero: true,
                                        stacked: false
                                    },
                                    y: {
                                        grid: {
                                            display: false
                                        }
                                    }
                                }
                            }
                        });
                    }
                }
                
                renderKecamatan();
                Livewire.on('render', renderKecamatan);
            }
        });

        // Re-render charts when Livewire updates
        document.addEventListener('livewire:update', function() {
            // Charts will be re-rendered via Livewire.on events
        });
    </script>
</div>