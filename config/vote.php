<?php

return [
    'ip_hmac_key' => env('VOTE_IP_HMAC_KEY'),
    'device_hmac_key' => env('VOTE_DEVICE_HMAC_KEY'),
    'encryption_key' => env('VOTE_ENCRYPTION_KEY'),
    'encryption_key_version' => (int) env('VOTE_ENCRYPTION_KEY_VERSION', 1),
    'max_gps_accuracy_meters' => (int) env('VOTE_MAX_GPS_ACCURACY_METERS', 100),
    'trusted_proxies' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TRUSTED_PROXIES', '')),
    ))),
    'territory_bounds' => json_decode(
        (string) env('VOTE_TERRITORY_BOUNDS_JSON', '{}'),
        true,
        flags: JSON_THROW_ON_ERROR,
    ),
];
