<?php

return [
    'heartbeat_interval_seconds' => (int) env('VISITOR_MONITOR_HEARTBEAT_SECONDS', 5),
    'online_window_seconds' => (int) env('VISITOR_MONITOR_ONLINE_WINDOW_SECONDS', 120),
    'background_window_seconds' => (int) env('VISITOR_MONITOR_BACKGROUND_WINDOW_SECONDS', 900),
    'retention_days' => (int) env('VISITOR_MONITOR_RETENTION_DAYS', 7),
    'cookie_domain' => env('VISITOR_MONITOR_COOKIE_DOMAIN', env('SESSION_DOMAIN')),
    'bots' => [
        'exclude_googlebot' => (bool) env('VISITOR_MONITOR_EXCLUDE_GOOGLEBOT', true),
        'verification_cache_seconds' => (int) env('VISITOR_MONITOR_BOT_CACHE_SECONDS', 86400),
        'googlebot_host_suffixes' => [
            '.googlebot.com',
            '.google.com',
            '.googleusercontent.com',
        ],
    ],
    'geo' => [
        'enabled' => (bool) env('VISITOR_MONITOR_GEO_ENABLED', true),
        'endpoint' => env('VISITOR_MONITOR_GEO_ENDPOINT', 'https://ipwho.is/{ip}'),
        'timeout_seconds' => (int) env('VISITOR_MONITOR_GEO_TIMEOUT_SECONDS', 3),
        'cache_seconds' => (int) env('VISITOR_MONITOR_GEO_CACHE_SECONDS', 86400),
    ],
];
