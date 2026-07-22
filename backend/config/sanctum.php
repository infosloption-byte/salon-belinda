<?php

use Laravel\Sanctum\Sanctum;

return [
    // Not used for our token-auth setup (admin/frontend/shop are separate
    // subdomains, so we skip Sanctum's cookie-based SPA mode entirely and
    // issue bearer tokens instead — see Api/Admin/AuthController). Left here
    // because Sanctum's service provider expects the key to exist.
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,localhost:5173,localhost:5174,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    'guard' => ['web'],

    // Personal access tokens issued to the admin app don't expire by
    // default; set a number of minutes here if you want forced re-login.
    'expiration' => null,

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];
