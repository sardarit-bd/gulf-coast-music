<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000'],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Authorization'], // if sending JWT
    'supports_credentials' => false,        // JWT does not need cookies
];
