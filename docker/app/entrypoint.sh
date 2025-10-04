#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

upsert_env() {
  local key="$1"
  local val="${2-}"
  local val_escaped="${val//&/\\&}"
  touch .env
  if grep -qE "^${key}=.*$" .env; then
    sed -i "s|^${key}=.*$|${key}=${val_escaped}|g" .env
  else
    printf "%s=%s\n" "$key" "$val" >> .env
  fi
}

env() {
  local key="$1"
  touch .env
  grep -qE "^${key}=.*$" .env || printf "%s=\n" "$key" >> .env
}

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

if [ ! -f composer.json ]; then
  echo "[entrypoint] Bootstrapping Laravel skeleton..."
  composer create-project laravel/laravel . "11.*" --no-interaction --prefer-dist
fi

if [ ! -d vendor ]; then
  echo "[entrypoint] Running composer install..."
  composer install --no-interaction --prefer-dist
fi

if ! grep -qE '^APP_KEY=.{10,}$' .env; then
  php artisan key:generate --no-interaction --ansi || true
fi

if [ "${APP_ENV:-local}" != "production" ]; then
  php artisan optimize:clear || true
fi

tries=30
until php -r "new PDO('mysql:host=${DB_HOST:-mysql};port=${DB_PORT:-3306};dbname=${DB_DATABASE:-lab08}','${DB_USERNAME:-lab08}','${DB_PASSWORD:-secret}');" >/dev/null 2>&1; do
  echo "[entrypoint] Waiting for MySQL at ${DB_HOST:-mysql}:${DB_PORT:-3306}..."
  sleep 2
  tries=$((tries-1))
  [ $tries -le 0 ] && echo "[entrypoint] MySQL wait timeout, continuing..." && break
done

if [ "${MIGRATE:-0}" = "1" ]; then
  echo "[entrypoint] Running migrations..."
  php artisan migrate --force || true
  php artisan db:seed --force || true
fi


env APP_KEY

upsert_env APP_NAME      "${APP_NAME:-Lab08}"
upsert_env APP_ENV       "${APP_ENV:-local}"
upsert_env APP_URL       "${APP_URL:-http://localhost:8080}"
upsert_env TZ            "${TZ:-Europe/Sofia}"

upsert_env DB_CONNECTION "${DB_CONNECTION:-mysql}"
upsert_env DB_HOST       "${DB_HOST:-mysql}"
upsert_env DB_PORT       "${DB_PORT:-3306}"
upsert_env DB_DATABASE   "${DB_DATABASE:-lab08}"
upsert_env DB_USERNAME   "${DB_USERNAME:-lab08}"
upsert_env DB_PASSWORD   "${DB_PASSWORD:-lab123}"

upsert_env REDIS_CLIENT  "${REDIS_CLIENT:-phpredis}"
upsert_env REDIS_HOST    "${REDIS_HOST:-redis}"
upsert_env REDIS_PORT    "${REDIS_PORT:-6379}"

echo "[entrypoint] Starting php-fpm..."
exec php-fpm -F