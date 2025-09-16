<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',         // local dev
        'http://127.0.0.1:3000',         // local dev alternative
        'http://192.168.25.202:3000',    // your current frontend IP
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Authorization'], // if sending JWT
    'supports_credentials' => false,        // JWT does not need cookies
];
