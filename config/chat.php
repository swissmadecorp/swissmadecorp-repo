<?php

return [
    'presence_ttl_seconds' => env('CHAT_PRESENCE_TTL_SECONDS', 90),
    'customer_history_limit' => env('CHAT_CUSTOMER_HISTORY_LIMIT', 50),
    'typing_ttl_seconds' => env('CHAT_TYPING_TTL_SECONDS', 6),
    'customer_poll_interval_seconds' => env('CHAT_CUSTOMER_POLL_INTERVAL_SECONDS', 4),
];
