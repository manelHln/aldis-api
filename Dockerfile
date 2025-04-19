FROM php:8.2-fpm-alpine

ARG user
ARG uid

# Install required packages
RUN apk update && apk add \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    shadow \
    postgresql-dev  # Needed for PostgreSQL

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Create non-root user
RUN useradd -G www-data,root -u $uid -d /home/$user $user \
    && mkdir -p /home/$user/.composer \
    && chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

# Ensure Laravel has correct permissions
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache \
    && chown -R $user:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy Composer files first for caching
COPY composer.json composer.lock /var/www/
RUN composer install --no-dev --no-scripts --prefer-dist --no-progress --optimize-autoloader \
    && chown -R $user:$user /var/www/vendor

# Copy Laravel project
COPY . /var/www

# Switch to non-root user
USER $user

# Set the default command to run php-fpm
CMD ["php-fpm"]
