<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Token Time To Live
    |--------------------------------------------------------------------------
    |
    | The number of minutes that issued package tokens remain valid. Token
    | rotation behavior on login, register, and refresh is controlled by
    | tokens.mode (see below).
    |
    */

    'token_ttl' => env('AUTH_PROFILE_TOKEN_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Token Mode
    |--------------------------------------------------------------------------
    |
    | single   - only one active token per user; login/register/refresh revoke
    |            all previous tokens before issuing a new one.
    | multiple - login/register add tokens without revoking others; refresh
    |            revokes only the presented bearer token.
    |
    */

    'tokens' => [
        'mode' => env('AUTH_PROFILE_TOKEN_MODE', 'single'),
    ],

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
    | Registration Fields
    |--------------------------------------------------------------------------
    |
    | Fields accepted by the register endpoint. Built-in validation is applied
    | per field (email, password, name); override via register_field_rules.
    | Fields listed in register_password_fields are hashed before storage.
    |
    */

    'register_fields' => ['name', 'email', 'password'],

    'register_password_fields' => ['password'],

    'register_field_rules' => [
        // 'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')],
    ],

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
        'revoke' => [
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
