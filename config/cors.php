<?php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',               // your local frontend
        'https://gulf.sardaritskillshare.com', // live backend
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Authorization'],   // if returning JWT
    'supports_credentials' => false,         // JWT does not require cookies
];
