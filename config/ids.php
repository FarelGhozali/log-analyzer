<?php

return [

    'admin' => [
        'name' => env('IDS_ADMIN_NAME', 'Administrator'),
        'email' => env('IDS_ADMIN_EMAIL', 'admin@ids.test'),
        'password' => env('IDS_ADMIN_PASSWORD', 'password'),
    ],

    'uploads' => [
        'max_size_kb' => (int) env('IDS_MAX_UPLOAD_KB', 5120),
    ],

];
