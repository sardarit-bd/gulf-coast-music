<?php
return [
    'paths' => ['api/*', 'printify/*', 'login', 'logout', 'me', 'refresh'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'https://gulf-coast.vercel.app',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization'],

    'supports_credentials' => false,
];
