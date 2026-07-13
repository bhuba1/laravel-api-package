<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Token Time To Live
    |--------------------------------------------------------------------------
    |
    | The number of minutes that issued package tokens remain valid. A new
    | token is issued on register, login, and refresh; previous tokens for
    | the same user are revoked when a new one is created.
    |
    */

    'token_ttl' => env('AUTH_PROFILE_TOKEN_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URI prefix for all package API routes. Register, login, profile,
    | and token refresh endpoints are registered under this prefix.
    |
    */

    'route_prefix' => env('AUTH_PROFILE_ROUTE_PREFIX', 'api/auth-profile'),

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The Eloquent model class used for registration, login, and profile
    | resolution. The model must extend Illuminate\Database\Eloquent\Model
    | and implement Illuminate\Contracts\Auth\Authenticatable.
    |
    */

    'user_model' => App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Profile Fields
    |--------------------------------------------------------------------------
    |
    | The user attributes returned by the profile endpoint. Each field must
    | exist on the configured user model.
    |
    */

    'profile_fields' => ['id', 'name', 'email'],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Per-endpoint request throttling. When enabled, excess requests receive
    | HTTP 429. Login is keyed by IP and email; register by IP; profile and
    | refresh by authenticated user ID with IP fallback.
    |
    */

    'rate_limiting' => [
        'enabled' => env('AUTH_PROFILE_RATE_LIMITING_ENABLED', true),
        'login' => [
            'max_attempts' => 5,
            'decay_minutes' => 1,
        ],
        'register' => [
            'max_attempts' => 5,
            'decay_minutes' => 1,
        ],
        'profile' => [
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
        'refresh' => [
            'max_attempts' => 10,
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Optional response and token-validation caching. Requires a configured
    | cache store in the host application. Disabled by default.
    |
    */

    'caching' => [
        'store' => env('AUTH_PROFILE_CACHE_STORE'),
        'profile' => [
            'enabled' => env('AUTH_PROFILE_CACHE_PROFILE', false),
            'ttl_seconds' => (int) env('AUTH_PROFILE_CACHE_PROFILE_TTL', 60),
        ],
        'token_validation' => [
            'enabled' => env('AUTH_PROFILE_CACHE_TOKEN_VALIDATION', false),
            'ttl_seconds' => (int) env('AUTH_PROFILE_CACHE_TOKEN_VALIDATION_TTL', 60),
        ],
    ],

];
