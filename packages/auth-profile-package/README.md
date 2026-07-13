# Auth Profile Package

A reusable Laravel package that provides registration, login, token-based authentication, and a profile API endpoint.

## Requirements

- PHP 8.2+
- Laravel 12.x or 13.x

## Installation

```bash
composer require bhuba/auth-profile-package
php artisan auth-profile:install
```

The install command publishes the configuration and `auth_profile_tokens` migration, then prompts to run migrations (use `--run-migrations` in CI/Docker).

Configure `user_model` in `config/auth-profile-package.php` to point at your host application's User model.

### Manual publish (advanced)

```bash
php artisan vendor:publish --tag=auth-profile-package-config
php artisan vendor:publish --tag=auth-profile-package-migrations
php artisan migrate
```

## Configuration

| Key | Default | Description |
|-----|---------|-------------|
| `token_ttl` | `60` | Token lifetime in minutes |
| `route_prefix` | `api/auth-profile` | API route prefix |
| `user_model` | `App\Models\User` | Host user model class (must implement `Authenticatable`) |
| `profile_fields` | `['id', 'name', 'email']` | Fields returned by the profile endpoint |
| `rate_limiting.enabled` | `true` | Enable per-endpoint request throttling |
| `rate_limiting.login` | `5` attempts / `1` min | Login throttle (keyed by IP + email) |
| `rate_limiting.register` | `5` attempts / `1` min | Register throttle (keyed by IP) |
| `rate_limiting.profile` | `60` attempts / `1` min | Profile throttle (keyed by user ID) |
| `rate_limiting.refresh` | `10` attempts / `1` min | Refresh throttle (keyed by user ID) |
| `caching.store` | `null` | Cache store name (default application cache store) |
| `caching.profile.enabled` | `false` | Cache profile endpoint responses |
| `caching.profile.ttl_seconds` | `60` | Profile cache TTL |
| `caching.token_validation.enabled` | `false` | Cache valid token lookups |
| `caching.token_validation.ttl_seconds` | `60` | Token validation cache TTL |

After publishing, the host application may wire environment variables:

```php
return [
    'token_ttl' => env('AUTH_PROFILE_TOKEN_TTL', 60),
    'route_prefix' => env('AUTH_PROFILE_ROUTE_PREFIX', 'api/auth-profile'),
    'user_model' => App\Models\User::class,
    'profile_fields' => ['id', 'name', 'email'],
    'rate_limiting' => [
        'enabled' => env('AUTH_PROFILE_RATE_LIMITING_ENABLED', true),
        // ...
    ],
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
```

Caching requires a configured cache driver in the host application. Rate limiting uses Laravel's cache-backed rate limiter.

## Endpoints

Default prefix: `api/auth-profile`

### Register

`POST /api/auth-profile/register`

**Body:**

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password123"
}
```

**Response `201`:**

```json
{
  "token": "plain-text-token",
  "expires_at": "2026-07-10T14:20:00+00:00"
}
```

### Login

`POST /api/auth-profile/login`

**Body:**

```json
{
  "email": "jane@example.com",
  "password": "password123"
}
```

**Response `200`:**

```json
{
  "token": "plain-text-token",
  "expires_at": "2026-07-10T14:20:00+00:00"
}
```

Login and registration revoke all previous package tokens for that user (rotation).

When rate limiting is exceeded, endpoints return `429` with:

```json
{
  "message": "Too many requests. Please try again later."
}
```

### Refresh token

`POST /api/auth-profile/tokens/refresh`

Requires a valid package Bearer token.

**Response `200`:**

```json
{
  "token": "new-plain-text-token",
  "expires_at": "2026-07-10T15:20:00+00:00"
}
```

### Profile

`GET /api/auth-profile/profile`

Requires a valid package Bearer token. Returns fields configured in `profile_fields`.

**Response `200`:**

```json
{
  "id": 1,
  "name": "Jane Doe",
  "email": "jane@example.com"
}
```

## Extending

The package binds its services to interfaces in the service container. Host applications can replace any implementation in `AppServiceProvider::register()`:

```php
use Bhuba\AuthProfilePackage\Contracts\AuthServiceInterface;
use Bhuba\AuthProfilePackage\Contracts\ProfileRepositoryInterface;
use Bhuba\AuthProfilePackage\Contracts\ProfileServiceInterface;
use Bhuba\AuthProfilePackage\Contracts\TokenRepositoryInterface;
use Bhuba\AuthProfilePackage\Contracts\TokenServiceInterface;
use Bhuba\AuthProfilePackage\Contracts\UserModelResolverInterface;

$this->app->bind(UserModelResolverInterface::class, \App\Support\CustomUserModelResolver::class);
$this->app->bind(AuthServiceInterface::class, \App\Services\CustomAuthService::class);
$this->app->bind(TokenServiceInterface::class, \App\Services\CustomTokenService::class);
$this->app->bind(TokenRepositoryInterface::class, \App\Repositories\CustomTokenRepository::class);
$this->app->bind(ProfileRepositoryInterface::class, \App\Repositories\CustomProfileRepository::class);
$this->app->bind(ProfileServiceInterface::class, \App\Services\CustomProfileService::class);
```

| Interface | Default implementation | Typical override |
|-----------|------------------------|------------------|
| `UserModelResolverInterface` | `UserModelResolver` | Custom user model lookup or multi-tenant resolution |
| `AuthServiceInterface` | `AuthService` | Custom register/login logic |
| `TokenServiceInterface` | `TokenService` | Custom token issuance or TTL rules |
| `TokenRepositoryInterface` | `TokenRepository` | Alternative token storage |
| `ProfileRepositoryInterface` | `EloquentProfileRepository` | Custom profile field resolution |
| `ProfileServiceInterface` | `ProfileService` | Custom profile assembly or cache rules |

## Development

```bash
cd packages/auth-profile-package
composer install
composer test
```

Or run tests in Docker:

```bash
composer test:docker
```

## Integration testing

```bash
docker compose up --build
bash test-app/scripts/smoke-test.sh
```

See the [root README](../../README.md) for the full manual API flow.

## License

MIT
