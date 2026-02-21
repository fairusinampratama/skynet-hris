#!/bin/sh

# Skynet HRIS - Coolify Deployment Script
# This script runs on container startup

set -e

echo "🚀 Starting Skynet HRIS deployment..."

echo "📂 Fixing permissions..."
chmod -R 777 storage bootstrap/cache

echo "🔗 Creating storage symlink..."
php artisan storage:link || true

echo "⚡ Optimizing application..."
php artisan optimize

echo "📦 Running database migrations..."
php artisan migrate --force

echo "✅ Deployment tasks completed."

echo "🚀 Starting services..."

# Find concurrently executable
if [ -f "./node_modules/.bin/concurrently" ]; then
    CONCURRENTLY="./node_modules/.bin/concurrently"
else
    CONCURRENTLY="npx concurrently"
fi

$CONCURRENTLY -c "#93c5fd,#c4b5fd,#fb7185" \
    "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}" \
    "php artisan schedule:work" \
    "php artisan queue:work --tries=3"
