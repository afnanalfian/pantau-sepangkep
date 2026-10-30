<?php

return [

    /*
    | Role yang boleh mengunggah batch KBLI Keliru.
    | Semua role portal tetap bisa melihat, menandai selesai & export.
    */
    'upload_roles' => ['admin', 'anomali'],

    /*
    | Link FASIH mode edit. Kosongkan (null) untuk memakai logika link yang sama
    | dengan modul Anomali (accessor fasih_link AnomaliMikro).
    | Placeholder: {assignment_id}
    */
    'fasih_edit_url' => env('KBLI_KELIRU_FASIH_EDIT_URL'),

    /* Arti kode rincian 13b (b1–b4) */
    'kode_ya_tidak' => ['1' => 'Ya', '2' => 'Tidak'],

];
