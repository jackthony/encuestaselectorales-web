<?php
return [
    'local_dev' => true,
    'rate_limit_threshold' => 5,
    'rate_limit_window_seconds' => 3600,
    'ip_salt' => 'replace-with-32-plus-random-bytes-string',
    'aes_key_path' => __DIR__ . '/ip.key',
    'trust_score' => [
        'country_pe' => 15,
        'gps_accuracy_good' => 20,
        'gps_accuracy_ok' => 10,
        'interaction_medium' => 20,
        'interaction_slow' => 5,
        'device_cookie_present' => 10,
        'recent_burst_clear' => 20,
        'district_match' => 20,
    ],
];
