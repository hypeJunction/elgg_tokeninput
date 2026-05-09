FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libicu-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Elgg
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        intl \
        mysqli \
        pdo_mysql \
        xml \
        zip \
        opcache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create Elgg data directory
RUN mkdir -p /var/www/data && chown www-data:www-data /var/www/data

# Install Elgg 7.x
COPY elgg-composer.json /var/www/html/composer.json
RUN cd /var/www/html && composer install --no-interaction --prefer-dist

# Register Elgg coding standard with phpcs
RUN vendor/bin/phpcs --config-set installed_paths /var/www/html/vendor/elgg/sniffs/src

# Apache config: allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Remove any pre-existing settings so installer can create them
RUN rm -f /var/www/html/elgg-config/settings.php

# Create web root bootstrap
COPY index.php /var/www/html/index.php
RUN cp vendor/elgg/elgg/install/config/htaccess.dist /var/www/html/.htaccess \
    && mkdir -p /var/www/html/mod

# Copy install script
COPY elgg-install.sh /usr/local/bin/elgg-install.sh
RUN chmod +x /usr/local/bin/elgg-install.sh

EXPOSE 80
