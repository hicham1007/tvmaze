<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    // Where shows come from. The provider is chosen by name; each one has its
    // own block of settings below. See App\Providers\AppServiceProvider.
    'shows' => [
        'provider' => env('SHOWS_PROVIDER', 'tvmaze'),
        // Seconds a successful answer of the provider is reused.
        'cache_ttl' => [
            'search' => (int) env('SHOWS_SEARCH_CACHE_TTL', 600),
            'show' => (int) env('SHOWS_DETAIL_CACHE_TTL', 3600),
        ],
    ],

    'tvmaze' => [
        'base_url' => env('TVMAZE_BASE_URL', 'https://api.tvmaze.com'),
        // TVMaze asks API clients to identify themselves.
        'user_agent' => env('TVMAZE_USER_AGENT', 'series-api/1.0'),
        'connect_timeout' => (int) env('TVMAZE_CONNECT_TIMEOUT', 3),
        'timeout' => (int) env('TVMAZE_TIMEOUT', 5),
        // Total tries per request (1 = no retry). Only network errors and 5xx are retried.
        'attempts' => (int) env('TVMAZE_ATTEMPTS', 2),
        'retry_sleep_ms' => (int) env('TVMAZE_RETRY_SLEEP_MS', 200),
    ],

];
