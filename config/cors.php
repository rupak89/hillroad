<?php

$normalizeOrigin = static function (?string $origin): ?string {
    if ($origin === null || trim($origin) === '') {
        return null;
    }

    $origin = trim($origin);

    // APP_URL is sometimes configured without a scheme locally; a CORS
    // origin always needs one.
    return str_contains($origin, '://') ? $origin : 'http://'.$origin;
};

$allowedOrigins = array_unique(array_filter(array_merge(
    // The app's own origin is always allowed (same-origin SPA).
    [$normalizeOrigin(env('APP_URL'))],
    // Optional extra origins (e.g. a separately hosted SPA).
    array_map(
        $normalizeOrigin,
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
    ),
    // Vite dev server.
    env('APP_ENV', 'production') === 'local'
        ? ['http://127.0.0.1:5173', 'http://localhost:5173']
        : []
)));

$allowedOrigins = array_values($allowedOrigins);

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Only the configured origins may call the API with session cookies.
    | `login`/`logout` are included because they are web routes rather than
    | API routes but are still called by the SPA over XHR.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_unique($allowedOrigins)),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
