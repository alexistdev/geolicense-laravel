<?php

return [
    /*
    |--------------------------------------------------------------------------
    | License Activation Token
    |--------------------------------------------------------------------------
    |
    | These control the short-lived JWT (HMAC-SHA256) issued to a client machine
    | when it activates a license, and validated on every verify. They mirror the
    | Spring app's `jwt.secret` / `jwt.expiration` so tokens are interchangeable.
    |
    | expiration is expressed in milliseconds (to match the original config).
    |
    */
    'token' => [
        'secret' => env('GEOLICENSE_TOKEN_SECRET', ''),
        'expiration' => (int) env('GEOLICENSE_TOKEN_EXPIRATION', 86400000),
    ],
];
