#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

docker run --rm \
  -v "$(pwd):/app" \
  -w /app \
  php:8.4-cli-bookworm \
  bash -lc '
    set -euo pipefail
    apt-get update -qq
    DEBIAN_FRONTEND=noninteractive apt-get install -y -qq git unzip libzip-dev libsqlite3-dev >/dev/null
    docker-php-ext-install -j"$(nproc)" pdo_sqlite zip >/dev/null
    if ! command -v composer >/dev/null 2>&1; then
      curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    fi
    composer install --no-interaction
    composer test
  '
