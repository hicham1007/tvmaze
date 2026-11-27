<?php

declare(strict_types=1);

// Same origin as the frontend (the proxy routes /api here), so no origin is allowed by default.

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET'],

    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Accept', 'Content-Type'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
