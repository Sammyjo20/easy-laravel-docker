<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | This value should be used to define the trusted proxies used for the
    | application. Since this application will likely be hosted with a
    | tool like Docker, you may want to set this value to 0.0.0.0/0
    | however you should set it as something explicit. This is a
    | comma-separated string.
    |
    | Example: 192.168.1.1,10.0.0.1/8
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

];
