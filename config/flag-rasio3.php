<?php

return [

    /*
    | Role yang boleh mengunggah batch Anomali Flag Rasio 3.
    | Semua role portal tetap bisa melihat, menandai selesai & export.
    */
    'upload_roles' => ['admin', 'anomali'],

    /*
    | Template link FASIH mode edit. Kosongkan (null) untuk memakai logika
    | link yang sama dengan modul Anomali (accessor fasih_link AnomaliMikro).
    | Placeholder: {assignment_id}
    */
    'fasih_edit_url' => env('FLAG_RASIO3_FASIH_EDIT_URL'),

];
