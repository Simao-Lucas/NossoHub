<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Immich API
    |--------------------------------------------------------------------------
    |
    | External Immich instance used for all media. Laravel never stores
    | photo/video files — only Immich asset IDs as metadata.
    |
    */

    'base_url' => rtrim(env('IMMICH_BASE_URL', 'http://localhost:2283'), '/'),

    'api_key' => env('IMMICH_API_KEY', ''),

    'timeout' => (int) env('IMMICH_TIMEOUT', 30),
];
