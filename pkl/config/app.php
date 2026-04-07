<?php

return [
    'name'         => $_ENV['APP_NAME']    ?? 'PKL App',
    'env'          => $_ENV['APP_ENV']     ?? 'production',
    'url'          => $_ENV['APP_URL']     ?? '',
    'secret'       => $_ENV['APP_SECRET']  ?? '',
    'storage_path' => $_ENV['STORAGE_PATH'] ?? __DIR__ . '/../storage',

    'timezone' => 'Asia/Jakarta',

    'wa' => [
        'device_id'    => $_ENV['WA_DEVICE_ID']    ?? '',
        'admin_number' => $_ENV['WA_ADMIN_NUMBER']  ?? '',
        'api_url'      => 'https://api.whacenter.com/api/send',
    ],

    'session' => [
        'lifetime'  => 60 * 60 * 8, // 8 jam
        'name'      => 'pkl_session',
    ],
];