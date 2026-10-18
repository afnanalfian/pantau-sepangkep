<?php

return [

    'keterangan' => 'Pendataan ASN Sensus Ekonomi 2026 Kabupaten Pangkajene dan Kepulauan',

    /*
    | Role yang boleh mengunggah batch. Semua role portal tetap bisa melihat,
    | menandai selesai & export.
    */
    'upload_roles' => ['admin', 'anomali'],

    /*
    | Link FASIH-SM.
    |   detail_url : halaman detail assignment
    |   search_url : halaman daftar data survei dengan kotak pencarian terisi
    | Placeholder: {assignment_id}, {q} (kata kunci, otomatis di-encode)
    */
    'fasih' => [
        'detail_url' => env('MATCHING_ASN_DETAIL_URL', 'https://fasih-sm.bps.go.id/app/assignment-detail/{assignment_id}'),
        'search_url' => env(
            'MATCHING_ASN_SEARCH_URL',
            'https://fasih-sm.bps.go.id/app/surveys/a0429e96-51a5-477b-a415-485f9c153004/fd68e454-ba45-4b85-8205-f3bf777ded24/data?page=1&perPage=10&search={q}'
        ),
    ],

];
