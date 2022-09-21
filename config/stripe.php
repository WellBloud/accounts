<?php

return [
    'secretKey' => env('STRIPE_SECRET', ''),
    'webhookSecretKey' => env('STRIPE_WEBHOOK_SECRET', ''),
];
