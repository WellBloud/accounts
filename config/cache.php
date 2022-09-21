<?php

return [
    'default' => env('CACHE_DRIVER', 'redis'),
    'default_ttl' => env('CACHE_TTL', 3600),
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],
        'array' => [
            'driver' => 'array',
        ],
    ],
];
