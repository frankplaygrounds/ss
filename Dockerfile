# Dockerfile
FROM php:8.1-apache

# Install necessary extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    unzip \
    && docker-php-ext-install zip gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy Composer configuration
COPY composer.json /var/www/html/

# Install dependencies with Composer
RUN composer install

# Copy application files
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80
