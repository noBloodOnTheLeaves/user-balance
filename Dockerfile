FROM php:8.4-fpm

# System deps
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
