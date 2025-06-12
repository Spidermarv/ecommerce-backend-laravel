# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev # For PostgreSQL

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd # Added pdo_pgsql

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html

# Set composer to allow superuser (required for Docker)
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install dependencies without dev packages (composer.lock should ideally be committed)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Application key should be set via environment variable in production.
# If you must generate it in the image (not recommended for consistency),
# ensure it's done only once or managed carefully.
# RUN php artisan key:generate --force

# Run migrations and seeders
# Migrations and seeding are better handled by the build.sh script or Render's deploy hooks
# against the actual production database, not during image build.
# RUN php artisan migrate --force
# RUN php artisan db:seed --force

# Cache configuration
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Change ownership of our applications
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html/storage

# Expose port (Render will set $PORT)
# The EXPOSE instruction doesn't actually publish the port. It functions as a type of documentation.
# To actually publish the port when running the container, use the -p flag on `docker run`.
EXPOSE 8000 # Default Laravel port, Render will map its $PORT to this.

# Start the application
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}