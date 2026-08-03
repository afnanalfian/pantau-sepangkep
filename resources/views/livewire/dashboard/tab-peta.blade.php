@php
    $petaKey = md5(json_encode([count($peta['data']), $peta['progresRata'], $peta['adaFilter'], $filterKecamatan, $filterDesa, $filterPml, $filterOrganik, $search]));
@endphp

<div wire:key="tab-peta">

@include('livewire.dashboard._filter-bar', [
    'exportMethod' => 'exportPeta',
    'showPerPage' => false,
    'searchPlaceholder' => 'Cari SLS / kode region / PPL / PML...',
])

<!-- ============================================ -->
<!-- STAT CARDS -->
<!-- ============================================ -->
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
    <div class="bg-white rounded-xl p-3 sm:p-4 border border-slate-200 shadow-sm">
        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">SLS Tampil</p>
        <p class="font-display font-bold text-xl sm:text-2xl text-slate-900 mt-0.5">{{ number_format($peta['jumlahSls']) }}</p>
    </div>
    <div class="bg-white rounded-xl p-3 sm:p-4 border border-slate-200 shadow-sm">
        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Rata-rata Progres</p>
        <p class="font-display font-bold text-xl sm:text-2xl text-slate-900 mt-0.5">{{ $peta['progresRata'] }}%</p>
    </div>
    <div class="bg-white rounded-xl p-3 sm:p-4 border border-emerald-200 shadow-sm">
        <p class="text-[10px] font-semibold text-emerald-600 uppercase tracking-wide">&ge; 90%</p>
        <p class="font-display font-bold text-xl sm:text-2xl text-emerald-600 mt-0.5">{{ number_format($peta['hijau']) }}</p>
    </div>
    <div class="bg-white rounded-xl p-3 sm:p-4 border border-amber-200 shadow-sm">
        <p class="text-[10px] font-semibold text-amber-600 uppercase tracking-wide">50 &ndash; 90%</p>
        <p class="font-display font-bold text-xl sm:text-2xl text-amber-600 mt-0.5">{{ number_format($peta['oranye']) }}</p>
    </div>
    <div class="bg-white rounded-xl p-3 sm:p-4 border border-red-200 shadow-sm col-span-2 sm:col-span-1">
        <p class="text-[10px] font-semibold text-red-600 uppercase tracking-wide">&lt; 50%</p>
        <p class="font-display font-bold text-xl sm:text-2xl text-red-600 mt-0.5">{{ number_format($peta['merah']) }}</p>
    </div>
</div>

<!-- ============================================ -->
<!-- PETA -->
<!-- ============================================ -->
<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-4 py-3 border-b border-slate-100">
        <div>
            <h3 class="font-display font-semibold text-slate-800 text-sm sm:text-base">Peta Progres per SLS</h3>
            <p class="text-[11px] text-slate-400">
                Klik wilayah untuk melihat detail petugas &amp; capaian. Warna mengikuti persentase progres.
            </p>
        </div>
        <p class="text-[10px] text-slate-400">Batas wilayah: SLS Wilkerstat 2025 &middot; BPS</p>
    </div>

    {{-- wire:ignore penting: Leaflet mengelola isi div ini sendiri --}}
    <div wire:ignore id="petaWrap" class="relative w-full h-[65vh] min-h-[380px] sm:h-[72vh] bg-slate-100">
        <div id="petaSls" class="w-full h-full"></div>
    </div>
</div>

<p class="text-[10px] sm:text-xs text-slate-400 mt-3">
    Peta mengikuti filter di atas. Wilayah abu-abu = SLS yang belum ada datanya pada tarikan terakhir.
</p>

<style>
    #petaSls { z-index: 0; }
    .peta-fullscreen { position: fixed !important; inset: 0 !important; z-index: 9999 !important; height: 100vh !important; }
    .peta-panel {
        background: rgba(255,255,255,.95); padding: 10px 12px; border-radius: 10px;
        box-shadow: 0 4px 14px rgba(15,23,42,.12); font-family: Inter, sans-serif;
        font-size: 11px; color: #334155; line-height: 1.35;
    }
    .peta-legend-item { display:flex; align-items:center; gap:7px; cursor:pointer; padding:2px 0; user-select:none; }
    .peta-legend-item.mati { opacity:.4; text-decoration: line-through; }
    .peta-swatch { width:14px; height:14px; border-radius:3px; flex:0 0 auto; border:1px solid rgba(0,0,0,.08); }
    .peta-btn { background:#fff; width:32px; height:32px; border-radius:8px; box-shadow:0 2px 8px rgba(15,23,42,.15);
        display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; border:none; }
    .peta-btn:hover { background:#F1F5F9; }
    .leaflet-popup-content { margin: 10px 12px; font-family: Inter, sans-serif; }
    .leaflet-popup-content-wrapper { border-radius: 12px; }
    #petaLoading { position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
        background:rgba(248,250,252,.85); z-index:500; font-size:13px; color:#64748B; gap:8px; }
</style>

<script>
(function () {
    const payload = {
        // data[regionCode] = [progres, total, selesai, draft, muatan, iPPL, iPML, iOrganik]
        data: @json($peta['data']),
        kamus: @json($peta['kamus']),
        url: @json($peta['urlGeojson']),
        adaFilter: @json($peta['adaFilter']),
        kunci: @json($petaKey),
    };

    const el = document.getElementById('petaSls');
    const wrap = document.getElementById('petaWrap');
    if (!el || !wrap) return;

    if (typeof L === 'undefined') {
        el.innerHTML = '<div style="padding:24px;text-align:center;color:#EF4444;font-size:13px">' +
            'Pustaka peta (Leaflet) belum termuat. Pastikan CSS &amp; JS Leaflet sudah ditambahkan di layout.</div>';
        return;
    }

    const App = window.PetaSLS || (window.PetaSLS = { kategoriAktif: { hijau: true, oranye: true, merah: true, nodata: true } });
    App.payload = payload;

    // ---------- util ----------
    const KATEGORI = {
        hijau:  { label: 'Selesai &ge; 90%', warna: '#10B981' },
        oranye: { label: '50% &ndash; 90%',  warna: '#F59E0B' },
        merah:  { label: 'Di bawah 50%',     warna: '#EF4444' },
        nodata: { label: 'Belum ada data',   warna: '#CBD5E1' },
    };

    const kategoriDari = (p) => p === null || p === undefined ? 'nodata' : (p >= 90 ? 'hijau' : (p >= 50 ? 'oranye' : 'merah'));
    const angka = (n) => (n === null || n === undefined) ? '-' : Number(n).toLocaleString('id-ID');
    const kode = (f) => String((f.properties && (f.properties.idsubsls || f.properties.idsls)) || '').replace(/\D/g, '');

    // baris data dikirim dalam bentuk array ringkas + kamus nama petugas
    function dataDari(f) {
        const a = App.payload.data[kode(f)];
        if (!a) return null;
        const k = App.payload.kamus;
        return {
            p: a[0], t: a[1], s: a[2], d: a[3], m: a[4],
            ppl: k.ppl[a[5]], pml: k.pml[a[6]], org: k.org[a[7]],
        };
    }

    function gaya(f) {
        const d = dataDari(f);
        const kat = kategoriDari(d ? d.p : null);

        if (!App.kategoriAktif[kat]) {
            return { fillColor: '#E2E8F0', fillOpacity: .04, color: '#CBD5E1', weight: .3, opacity: .12 };
        }
        if (!d) {
            return App.payload.adaFilter
                ? { fillColor: '#E2E8F0', fillOpacity: .05, color: '#CBD5E1', weight: .3, opacity: .2 }
                : { fillColor: '#CBD5E1', fillOpacity: .45, color: '#FFFFFF', weight: .6, opacity: .85 };
        }
        return { fillColor: KATEGORI[kat].warna, fillOpacity: .8, color: '#FFFFFF', weight: .7, opacity: 1 };
    }

    function isiPopup(f) {
        const d = dataDari(f);
        const p = f.properties || {};
        const nama = p.nmsls || 'SLS';
        if (!d) {
            return '<div style="font-size:12px">'
                + '<div style="font-weight:700;color:#0F172A;margin-bottom:2px">' + nama + '</div>'
                + '<div style="color:#64748B">' + (p.nmdesa || '-') + ', ' + (p.nmkec || '-') + '</div>'
                + '<div style="margin-top:6px;color:#94A3B8;font-style:italic">Belum ada data pada tarikan terakhir</div>'
                + '<div style="margin-top:4px;font-family:monospace;color:#CBD5E1;font-size:10px">' + kode(f) + '</div></div>';
        }
        const kat = kategoriDari(d.p);
        return '<div style="font-size:12px;min-width:210px">'
            + '<div style="font-weight:700;color:#0F172A;margin-bottom:2px">' + nama + '</div>'
            + '<div style="color:#64748B">' + (p.nmdesa || '-') + ', ' + (p.nmkec || '-') + '</div>'
            + '<div style="margin:8px 0;display:flex;align-items:center;gap:8px">'
            +   '<span style="background:' + KATEGORI[kat].warna + ';color:#fff;font-weight:700;padding:3px 9px;border-radius:999px;font-size:13px">' + d.p + '%</span>'
            +   '<span style="color:#64748B">' + angka(d.s) + ' / ' + angka(d.t) + ' selesai</span>'
            + '</div>'
            + '<table style="width:100%;border-top:1px solid #E2E8F0;padding-top:6px">'
            +   baris('PPL', d.ppl) + baris('PML', d.pml) + baris('Organik', d.org)
            +   baris('Draft', angka(d.d)) + baris('Muatan', angka(d.m))
            + '</table>'
            + '<div style="margin-top:6px;font-family:monospace;color:#CBD5E1;font-size:10px">' + kode(f) + '</div></div>';
    }

    function baris(label, nilai) {
        return '<tr><td style="color:#94A3B8;padding:1px 8px 1px 0;white-space:nowrap">' + label + '</td>'
            + '<td style="color:#334155;font-weight:600">' + (nilai || '-') + '</td></tr>';
    }

    // ---------- pasang / perbarui ----------
    function pasangEvent(f, layer) {
        layer.on({
            mouseover: (e) => {
                const d = dataDari(f);
                // kategori yang sedang disembunyikan tidak ikut disorot
                if (!App.kategoriAktif[kategoriDari(d ? d.p : null)]) return;
                const l = e.target;
                l.setStyle({ weight: 2.5, color: '#0F172A', fillOpacity: .92 });
                if (l.bringToFront) l.bringToFront();
                if (App.info) App.info.perbarui(f);
            },
            mouseout: (e) => {
                e.target.setStyle(gaya(f));
                if (App.info) App.info.perbarui(null);
            },
            click: (e) => {
                App.map.fitBounds(e.target.getBounds(), { maxZoom: 16, padding: [40, 40] });
                e.target.bindPopup(isiPopup(f), { maxWidth: 280 }).openPopup();
            },
        });
    }

    function terapkanGaya() {
        if (!App.layer) return;
        let hitung = { hijau: 0, oranye: 0, merah: 0, nodata: 0 };
        const cocok = [];

        App.layer.eachLayer((l) => {
            const f = l.feature;
            const d = dataDari(f);
            hitung[kategoriDari(d ? d.p : null)]++;
            if (d) cocok.push(l);
            l.setStyle(gaya(f));
            if (l.isPopupOpen && l.isPopupOpen()) l.setPopupContent(isiPopup(f));
        });

        App.hitung = hitung;
        if (App.legend) App.legend.perbarui();
        return cocok;
    }

    function zoomKeData(cocok) {
        if (!cocok || !cocok.length) return;
        try {
            const b = L.featureGroup(cocok).getBounds();
            if (b.isValid()) App.map.fitBounds(b, { padding: [20, 20] });
        } catch (e) { /* abaikan */ }
    }

    // ---------- kontrol ----------
    function buatKontrol() {
        // panel info (hover) — disembunyikan di layar kecil
        const Info = L.Control.extend({
            onAdd: function () {
                this._div = L.DomUtil.create('div', 'peta-panel');
                this._div.style.maxWidth = '220px';
                this._div.style.display = window.innerWidth < 640 ? 'none' : 'block';
                this.perbarui(null);
                return this._div;
            },
            perbarui: function (f) {
                if (!this._div) return;
                if (!f) {
                    this._div.innerHTML = '<b style="color:#0F172A">Arahkan kursor</b><br><span style="color:#94A3B8">ke sebuah SLS untuk melihat ringkasannya</span>';
                    return;
                }
                const d = dataDari(f);
                const p = f.properties || {};
                const nama = p.nmsls || 'SLS';
                this._div.innerHTML = '<b style="color:#0F172A">' + nama + '</b><br>'
                    + '<span style="color:#94A3B8">' + (p.nmdesa || '-') + ', ' + (p.nmkec || '-') + '</span>'
                    + (d
                        ? '<div style="margin-top:5px"><b style="color:' + KATEGORI[kategoriDari(d.p)].warna + ';font-size:15px">' + d.p + '%</b> '
                          + '<span style="color:#64748B">(' + angka(d.s) + '/' + angka(d.t) + ')</span><br>'
                          + '<span style="color:#64748B">PPL: ' + (d.ppl || '-') + '</span></div>'
                        : '<div style="margin-top:5px;color:#94A3B8;font-style:italic">Belum ada data</div>');
            },
        });
        App.info = new Info({ position: 'topright' });
        App.info.addTo(App.map);

        // legenda + saklar kategori
        const Legend = L.Control.extend({
            onAdd: function () {
                this._div = L.DomUtil.create('div', 'peta-panel');
                L.DomEvent.disableClickPropagation(this._div);
                this.perbarui();
                return this._div;
            },
            perbarui: function () {
                if (!this._div) return;
                const h = App.hitung || {};
                let html = '<div style="font-weight:700;color:#0F172A;margin-bottom:5px">Progres SLS</div>';
                Object.keys(KATEGORI).forEach((k) => {
                    html += '<div class="peta-legend-item ' + (App.kategoriAktif[k] ? '' : 'mati') + '" data-kat="' + k + '">'
                        + '<span class="peta-swatch" style="background:' + KATEGORI[k].warna + '"></span>'
                        + '<span>' + KATEGORI[k].label + '</span>'
                        + '<b style="margin-left:auto;color:#0F172A">' + (h[k] || 0) + '</b></div>';
                });
                html += '<div style="margin-top:6px;color:#94A3B8;font-size:10px">Klik untuk sembunyikan/tampilkan</div>';
                this._div.innerHTML = html;
                this._div.querySelectorAll('.peta-legend-item').forEach((row) => {
                    row.addEventListener('click', () => {
                        const k = row.getAttribute('data-kat');
                        App.kategoriAktif[k] = !App.kategoriAktif[k];
                        terapkanGaya();
                    });
                });
            },
        });
        App.legend = new Legend({ position: 'bottomright' });
        App.legend.addTo(App.map);

        // tombol: zoom ke data & layar penuh
        const Tombol = L.Control.extend({
            onAdd: function () {
                const box = L.DomUtil.create('div');
                box.style.display = 'flex';
                box.style.flexDirection = 'column';
                box.style.gap = '6px';
                L.DomEvent.disableClickPropagation(box);

                const bZoom = L.DomUtil.create('button', 'peta-btn', box);
                bZoom.innerHTML = '&#9678;';
                bZoom.title = 'Zoom ke data yang tampil';
                bZoom.onclick = () => zoomKeData(terapkanGaya());

                const bFull = L.DomUtil.create('button', 'peta-btn', box);
                bFull.innerHTML = '&#9974;';
                bFull.title = 'Layar penuh';
                bFull.onclick = () => {
                    wrap.classList.toggle('peta-fullscreen');
                    bFull.innerHTML = wrap.classList.contains('peta-fullscreen') ? '&#10005;' : '&#9974;';
                    setTimeout(() => App.map.invalidateSize(), 220);
                };

                return box;
            },
        });
        new Tombol({ position: 'topleft' }).addTo(App.map);
    }

    // ---------- inisialisasi ----------
    // peta lama sudah lepas dari DOM (mis. pindah tab) -> buang dulu
    if (App.map && !document.body.contains(App.map.getContainer())) {
        App.map.remove();
        App.map = null;
        App.layer = null;
        App.info = null;
        App.legend = null;
    }

    if (App.map) {                       // peta masih hidup: cukup perbarui warna
        if (App.kunci !== payload.kunci) {
            App.kunci = payload.kunci;
            zoomKeData(terapkanGaya());
        } else {
            terapkanGaya();
        }
        return;
    }

    App.kunci = payload.kunci;

    const loading = document.createElement('div');
    loading.id = 'petaLoading';
    loading.innerHTML = '<span>Memuat batas wilayah SLS&hellip;</span>';
    wrap.appendChild(loading);

    App.map = L.map(el, { preferCanvas: true, zoomControl: true, attributionControl: true })
        .setView([-4.72, 119.55], 10);

    const basemaps = {
        'Terang': L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 19, attribution: '&copy; OpenStreetMap, &copy; CARTO',
        }),
        'Jalan (OSM)': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: '&copy; OpenStreetMap',
        }),
        'Citra Satelit': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19, attribution: 'Tiles &copy; Esri',
        }),
    };
    basemaps['Terang'].addTo(App.map);
    L.control.layers(basemaps, null, { position: 'topleft', collapsed: true }).addTo(App.map);

    buatKontrol();

    const gambar = (geo) => {
        App.geo = geo;
        App.layer = L.geoJSON(geo, { style: gaya, onEachFeature: pasangEvent }).addTo(App.map);
        loading.remove();
        zoomKeData(terapkanGaya());
    };

    if (App.geo) {
        gambar(App.geo);
    } else {
        fetch(payload.url)
            .then((r) => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(gambar)
            .catch((err) => {
                loading.innerHTML = '<span style="color:#EF4444;text-align:center;padding:0 20px">'
                    + 'Gagal memuat file peta.<br><span style="font-size:11px;color:#94A3B8">'
                    + 'Pastikan berkas <code>public/geo/sls-7309.geojson</code> sudah tersedia. (' + err.message + ')</span></span>';
            });
    }
})();
</script>

</div>
