#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

preserve_app_key=""
if [ -f .env ] && grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  preserve_app_key="$(grep '^APP_KEY=' .env | cut -d= -f2-)"
fi

if [ ! -f .env ] || ! grep -q '^DB_HOST=mysql' .env 2>/dev/null; then
  cp .env.example .env
  if [ -n "${preserve_app_key}" ]; then
    sed -i "s|^APP_KEY=.*|APP_KEY=${preserve_app_key}|" .env
  fi
fi

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  php artisan key:generate --force
fi

if [ ! -d vendor ]; then
  composer install --no-interaction --prefer-dist
fi

echo "Waiting for MySQL..."
until mysqladmin ping -h"${DB_HOST:-mysql}" -u"${DB_USERNAME:-auth_profile}" -p"${DB_PASSWORD:-secret}" --silent 2>/dev/null; do
  sleep 2
done
echo "MySQL is ready."

#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

preserve_app_key=""
if [ -f .env ] && grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  preserve_app_key="$(grep '^APP_KEY=' .env | cut -d= -f2-)"
fi

if [ ! -f .env ] || ! grep -q '^DB_HOST=mysql' .env 2>/dev/null; then
  cp .env.example .env
  if [ -n "${preserve_app_key}" ]; then
    sed -i "s|^APP_KEY=.*|APP_KEY=${preserve_app_key}|" .env
  fi
fi

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  php artisan key:generate --force
fi

if [ ! -d vendor ]; then
  composer install --no-interaction --prefer-dist
fi

echo "Waiting for MySQL..."
until mysqladmin ping -h"${DB_HOST:-mysql}" -u"${DB_USERNAME:-auth_profile}" -p"${DB_PASSWORD:-secret}" --silent 2>/dev/null; do
  sleep 2
done
echo "MySQL is ready."

php artisan auth-profile:install --no-interaction
php artisan migrate:fresh --seed --force

exec php artisan serve --host=0.0.0.0 --port=8000

exec php artisan serve --host=0.0.0.0 --port=8000
