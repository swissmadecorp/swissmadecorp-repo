<?php

return [
    'presence_ttl_seconds' => env('CHAT_PRESENCE_TTL_SECONDS', 90),
    'customer_history_limit' => env('CHAT_CUSTOMER_HISTORY_LIMIT', 50),
    'typing_ttl_seconds' => env('CHAT_TYPING_TTL_SECONDS', 6),
    'customer_poll_interval_seconds' => env('CHAT_CUSTOMER_POLL_INTERVAL_SECONDS', 4),
    'customer_online_window_seconds' => env('CHAT_CUSTOMER_ONLINE_WINDOW_SECONDS', 45),
    'customer_inactive_reminder_seconds' => env('CHAT_CUSTOMER_INACTIVE_REMINDER_SECONDS', 60),
    'customer_inactive_close_seconds' => env('CHAT_CUSTOMER_INACTIVE_CLOSE_SECONDS', 120),
];
