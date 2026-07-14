# laravel-api-package

Monorepo for Laravel API packages and an integration test harness.

## Packages

- [`packages/auth-profile-package`](packages/auth-profile-package) — registration, login, token auth, and profile API (`bhuba/auth-profile-package`)

## Integration test app

The [`test-app/`](test-app/) directory is a Laravel host application linked to the local package via Composer path repository. Use it to exercise the full HTTP flow (package login/register → package token → profile/refresh).

### Prerequisites

- Docker and Docker Compose

### Start the stack

```bash
docker compose up --build
```

The app is available at [http://localhost:8080](http://localhost:8080).

On first boot the entrypoint will:

1. Install Composer dependencies (if `vendor/` is missing)
2. Create `.env` from `.env.example` and generate `APP_KEY`
3. Wait for MySQL
4. Run `php artisan auth-profile:install` and `migrate:fresh --seed` (resets the dev database on each boot)

### Manual API flow

```bash
# 1. Login (package token)
curl -s -X POST http://localhost:8080/api/auth-profile/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# 2. Profile
curl -s http://localhost:8080/api/auth-profile/profile \
  -H "Authorization: Bearer {package-token}" \
  -H "Accept: application/json"

# 3. Refresh package token
curl -s -X POST http://localhost:8080/api/auth-profile/tokens/refresh \
  -H "Authorization: Bearer {package-token}" \
  -H "Accept: application/json"

# 4. Revoke package token
curl -s -o /dev/null -w "%{http_code}" -X POST http://localhost:8080/api/auth-profile/tokens/revoke \
  -H "Authorization: Bearer {package-token}" \
  -H "Accept: application/json"
# Expected: 204

# 5. Register a new user
curl -s -X POST http://localhost:8080/api/auth-profile/register \
  -H "Content-Type: application/json" \
  -d '{"name":"New User","email":"new@example.com","password":"password123"}'
```

### Postman collection

Import [`test-app/postman/auth-profile-package.postman_collection.json`](test-app/postman/auth-profile-package.postman_collection.json) into Postman (File → Import, or drag the file into the app). The collection uses `baseUrl` `http://localhost:8080` and saves the package token to `packageToken` after Login or Register. Run requests in order: Login → Get Profile → Refresh Token → Revoke Token.

### Smoke test

With the stack running:

```bash
bash test-app/scripts/smoke-test.sh
```

### Useful commands

```bash
docker compose exec app php artisan route:list --path=auth-profile
docker compose exec app php artisan about
```

### Package unit tests

```bash
cd packages/auth-profile-package
composer test:docker
```

## Development notes

- The package is symlinked from `packages/auth-profile-package`; source edits are picked up immediately.
- Package tokens are stored in `auth_profile_tokens` (published via `auth-profile:install`).
- Run `composer dump-autoload` inside the container only when adding new package classes.

### Database (MySQL)

With the stack running (`docker compose up`), connect from the host:

| Setting | Value |
|---------|-------|
| Host | `localhost` |
| Port | `3307` |
| Database | `auth_profile_test` |
| Username | `auth_profile` |
| Password | `secret` |

Package relevant tables: `users`, `auth_profile_tokens`.
