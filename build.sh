#!/bin/bash
set -e

echo "🚀 Starting Laravel build process..."

echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "🔑 Generating application key..."
php artisan key:generate --force

echo "🗄️ Running database migrations..."
php artisan migrate --force # This will run against the production DB (e.g., PostgreSQL on Render)

echo "🌱 Seeding database with sample data..."
php artisan db:seed --force

echo "⚡ Caching configuration for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🎉 Build completed successfully!"
