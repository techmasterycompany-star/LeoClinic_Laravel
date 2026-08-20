#!/bin/bash
set -e

echo "Running Laravel deployment tasks..."

# Generate app key if not already set (safe to run every time; only sets if missing)
php artisan key:generate --force || true

# Clear and cache config for production
php artisan config:clear
php artisan config:cache

# Run database migrations (safe to run on every deploy)
php artisan migrate --force

# Cache routes and views for performance
php artisan route:cache || true
php artisan view:cache || true

echo "Deployment tasks complete. Starting Apache..."

exec "$@"
