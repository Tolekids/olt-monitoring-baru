<?php

return [
    'poll_interval_seconds' => (int) env('MONITORING_POLL_INTERVAL_SECONDS', 60),
    'device_timeout_seconds' => (int) env('MONITORING_DEVICE_TIMEOUT_SECONDS', 10),
    'metric_retention_days' => (int) env('MONITORING_METRIC_RETENTION_DAYS', 90),
    'syslog' => [
        'bind_address' => env('SYSLOG_BIND_ADDRESS', '0.0.0.0'),
        'port' => (int) env('SYSLOG_PORT', 514),
    ],
];
