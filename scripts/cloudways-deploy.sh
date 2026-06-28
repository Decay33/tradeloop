#!/usr/bin/env bash
set -euo pipefail

APP_ROOT="${1:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
cd "$APP_ROOT"

if [ ! -f ".env" ]; then
    echo "Missing .env. Create it from .env.example and set Cloudways database/app values first."
    exit 1
fi

composer install --no-dev --optimize-autoloader

php artisan optimize:clear

if [ -f package-lock.json ]; then
    npm ci
else
    npm install
fi

npm run build

php artisan migrate --force

if [ "${RESET_DEMO:-false}" = "true" ]; then
    php artisan demo:reset
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "TradeLoop deployment complete."
