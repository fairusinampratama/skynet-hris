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

# Start Supervisor which will run PHP-FPM, Nginx, Queue Worker, and Scheduler
echo "🚀 Starting Supervisor to manage all processes..."
exec supervisord -c /etc/supervisord.conf
