{{--
    Chart.js yang di-init lewat Alpine.
    Kenapa tidak pakai <script> + livewire:init seperti di anomali?
    Karena <script> di dalam komponen tidak dijalankan ulang ketika Livewire
    me-render ulang (mis. pindah Data Mikro -> Dashboard), sehingga grafik
    hilang. Alpine x-init selalu jalan setiap elemen ini muncul di DOM, dan
    destroy() membersihkan instance lama.

    Parameter: $key (string), $cfg (array config Chart.js), $height (px)
--}}
<div wire:key="mv-chart-{{ $key }}-{{ md5(json_encode($cfg)) }}"
     wire:ignore
     x-data="{
        chart: null,
        init() {
            if (typeof Chart === 'undefined') return;
            this.chart = new Chart(this.$refs.canvas, {{ \Illuminate\Support\Js::from($cfg) }});
        },
        destroy() { if (this.chart) { this.chart.destroy(); this.chart = null; } }
     }"
     class="relative" style="height: {{ (int) ($height ?? 250) }}px;">
    <canvas x-ref="canvas"></canvas>
</div>
