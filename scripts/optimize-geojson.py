#!/usr/bin/env python3
"""
Mengecilkan file GeoJSON SLS dari Wilkerstat supaya ringan dibuka di HP.

Yang dilakukan:
  1. membuang properti yang tidak dipakai peta (hanya menyisakan
     idsubsls, nmsls, nmkec, nmdesa, kdkec, kddesa);
  2. menyederhanakan geometri (Douglas-Peucker, toleransi ~2 meter);
  3. membulatkan koordinat ke 5 desimal (~1 meter).

Hasil pada file 7309: 4,1 MB -> 1,33 MB (~239 KB setelah gzip),
tanpa perbedaan bentuk yang terlihat sampai zoom maksimum.

Pemakaian:
    pip install shapely
    python3 scripts/optimize-geojson.py Final_SLS_202517309.geojson public/geo/sls-7309.geojson
"""
import json
import os
import sys

from shapely.geometry import mapping, shape

PROPERTI_DIPAKAI = ['idsubsls', 'nmsls', 'nmkec', 'nmdesa', 'kdkec', 'kddesa']
TOLERANSI = 0.00002   # derajat (~2 meter)
DESIMAL = 5


def bulatkan(koordinat, desimal=DESIMAL):
    if isinstance(koordinat[0], (int, float)):
        return [round(koordinat[0], desimal), round(koordinat[1], desimal)]
    return [bulatkan(k, desimal) for k in koordinat]


def main(sumber, tujuan):
    data = json.load(open(sumber))
    hasil = []

    for f in data['features']:
        geometri = shape(f['geometry']).simplify(TOLERANSI, preserve_topology=True)
        if geometri.is_empty:
            geometri = shape(f['geometry'])

        gm = mapping(geometri)
        hasil.append({
            'type': 'Feature',
            'properties': {k: f['properties'].get(k) for k in PROPERTI_DIPAKAI},
            'geometry': {
                'type': gm['type'],
                'coordinates': bulatkan(json.loads(json.dumps(gm['coordinates']))),
            },
        })

    os.makedirs(os.path.dirname(tujuan) or '.', exist_ok=True)
    json.dump({'type': 'FeatureCollection', 'features': hasil},
              open(tujuan, 'w'), separators=(',', ':'))

    print(f'{len(hasil)} SLS diproses')
    print(f'sebelum : {os.path.getsize(sumber) / 1e6:.2f} MB')
    print(f'sesudah : {os.path.getsize(tujuan) / 1e6:.2f} MB')


if __name__ == '__main__':
    if len(sys.argv) != 3:
        print(__doc__)
        sys.exit(1)
    main(sys.argv[1], sys.argv[2])
