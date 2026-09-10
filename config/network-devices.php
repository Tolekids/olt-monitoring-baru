<?php

return [
    'timeout_seconds' => (int) env('MONITORING_DEVICE_TIMEOUT_SECONDS', 10),
    'zte' => [
        'system_name_oid' => env('ZTE_SYSTEM_NAME_OID', '.1.3.6.1.2.1.1.5.0'),
    ],
    'mikrotik' => [
        'default_api_port' => (int) env('MIKROTIK_API_PORT', 8728),
        'secure_api_port' => (int) env('MIKROTIK_SECURE_API_PORT', 8729),
    ],
];
