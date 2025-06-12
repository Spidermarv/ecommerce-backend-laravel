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
    sqlite3 \
    libsqlite3-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd

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

# Remove lock file and install fresh dependencies
RUN rm -f composer.lock && composer update --no-dev --optimize-autoloader

# Generate application key
RUN php artisan key:generate --force

# Create SQLite database
RUN touch /var/www/html/database.sqlite
RUN chmod 664 /var/www/html/database.sqlite

# Run migrations and seeders
RUN php artisan migrate --force
RUN php artisan db:seed --force

# Cache configuration
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Change ownership of our applications
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html/storage

# Expose port
EXPOSE $PORT

# Start the application
CMD php artisan serve --host=0.0.0.0 --port=$PORT