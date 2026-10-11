<?php

/*
|--------------------------------------------------------------------------
| Modul "Pemindahan Pendapatan" & "Pendapatan 1 Juta ke Bawah"
|--------------------------------------------------------------------------
|
| fasih_edit_url:
|   Kosongkan (null) supaya link edit FASIH memakai logika yang SAMA PERSIS
|   dengan modul Anomali (accessor fasih_link milik model AnomaliMikro),
|   seperti modul Cek KBLI.
|
|   Kalau ingin diatur sendiri, isi template dengan placeholder {assignment_id}.
|   Contoh: 'https://fasih-sm.bps.go.id/.../{assignment_id}/...'
|
|   Urutan pemakaian: template milik modul -> template umum -> logika Anomali.
*/

return [

    'fasih_edit_url' => env('PENDAPATAN_FASIH_EDIT_URL'),

    'pemindahan' => [
        'upload_roles' => ['admin', 'anomali'],
        'fasih_edit_url' => env('PEMINDAHAN_FASIH_EDIT_URL'),
    ],

    'sejuta' => [
        'upload_roles' => ['admin', 'anomali'],
        'fasih_edit_url' => env('SEJUTA_FASIH_EDIT_URL'),
    ],

];
