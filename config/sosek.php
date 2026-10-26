<?php

return [

    /*
    | Role yang boleh mengunggah batch Anomali Sosek.
    | Semua role portal tetap bisa melihat, menandai selesai & export.
    */
    'upload_roles' => ['admin', 'anomali'],

    /*
    | Link FASIH mode edit. Kosongkan (null) untuk memakai logika link yang
    | sama dengan modul Anomali (accessor fasih_link AnomaliMikro).
    | Placeholder: {assignment_id}, {survey_id} (diambil dari link_fasih_sm)
    */
    'fasih_edit_url' => env('SOSEK_FASIH_EDIT_URL'),

    /*
    | Cadangan pencarian wilayah/PIC untuk baris yang tidak punya level_6_full_code
    | (file Umur, Profesi, NIK prov): cocokkan assignment_id ke region_code di tabel
    | modul Anomali. Set false bila tabel/kolomnya berbeda.
    */
    'cari_region_dari_anomali' => true,

];
