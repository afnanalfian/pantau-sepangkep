<?php

return [

    /*
    | Role yang boleh mengunggah batch Anomali NTB Flag 3.
    | Semua role portal tetap bisa melihat, menandai selesai & export.
    */
    'upload_roles' => ['admin', 'anomali'],

    /*
    | Link FASIH mode edit. Kosongkan (null) untuk memakai logika link yang
    | sama dengan modul Anomali (accessor fasih_link AnomaliMikro).
    | Placeholder: {assignment_id}, {survey_id} (= kolom survey_period_id)
    */
    'fasih_edit_url' => env('NTB_FLAG3_FASIH_EDIT_URL'),

];
