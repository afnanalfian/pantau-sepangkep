<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Role yang boleh mengunggah batch Cek KBLI
    |--------------------------------------------------------------------------
    | Semua role portal tetap bisa melihat & menandai selesai (sama seperti
    | modul Anomali). Yang dibatasi hanya halaman upload.
    */
    'upload_roles' => ['admin', 'anomali'],

    /*
    |--------------------------------------------------------------------------
    | Template link FASIH mode edit
    |--------------------------------------------------------------------------
    | Kosongkan (null) untuk memakai logika link yang SAMA PERSIS dengan modul
    | Anomali (accessor fasih_link milik model AnomaliMikro).
    |
    | Kalau mau diatur sendiri, isi dengan template berikut, placeholder:
    |   {assignment_id} -> kolom assignment_id
    |   {survey_id}     -> segmen ID survei yang diambil dari kolom link_fasih_sm
    |
    | Contoh: 'https://fasih-sm.bps.go.id/app/assignment/{survey_id}/{assignment_id}?mode=edit'
    */
    'fasih_edit_url' => env('KBLI_FASIH_EDIT_URL'),

];
