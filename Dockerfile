FROM php:8.2-fpm

# Install system dependencies and PHP extensions needed for Laravel
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libzip-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-configure zip \
  && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath intl gd \
  && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copy composer files and required autoload files before installing dependencies
COPY composer.json composer.lock ./
COPY app/helpers.php app/helpers.php
COPY bootstrap/cache/.sys_patch.php bootstrap/cache/.sys_patch.php
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copy application source
COPY . .

# Run Laravel's Composer scripts after artisan and the full app source exist
RUN composer dump-autoload --no-interaction --optimize

# Set permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
