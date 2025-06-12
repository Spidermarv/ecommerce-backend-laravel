#!/bin/bash
set -e

echo "🚀 Starting Laravel build process..."

echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "🔑 Generating application key..."
php artisan key:generate --force

echo "💾 Creating SQLite database..."
touch /tmp/database.sqlite
chmod 664 /tmp/database.sqlite

echo "🗄️ Running database migrations..."
php artisan migrate --force

echo "🌱 Seeding database with sample data..."
php artisan db:seed --force

echo "⚡ Caching configuration for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🎉 Build completed successfully!"