<?php

return [
    'heartbeat_interval_seconds' => (int) env('VISITOR_MONITOR_HEARTBEAT_SECONDS', 15),
    'online_window_seconds' => (int) env('VISITOR_MONITOR_ONLINE_WINDOW_SECONDS', 35),
    'geo' => [
        'enabled' => (bool) env('VISITOR_MONITOR_GEO_ENABLED', true),
        'endpoint' => env('VISITOR_MONITOR_GEO_ENDPOINT', 'https://ipwho.is/{ip}'),
        'timeout_seconds' => (int) env('VISITOR_MONITOR_GEO_TIMEOUT_SECONDS', 3),
        'cache_seconds' => (int) env('VISITOR_MONITOR_GEO_CACHE_SECONDS', 86400),
    ],
];
