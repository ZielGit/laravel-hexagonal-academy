# ============================================
# Base Stage - Common dependencies
# ============================================
FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    curl \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    postgresql-dev \
    icu-dev \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# ============================================
# Development Stage
# ============================================
FROM base AS development

# Install Xdebug for debugging
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

# Configure Xdebug
RUN echo "xdebug.mode=debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Create user with same UID/GID as host user (to avoid permission issues)
ARG USER_ID=1000
ARG GROUP_ID=1000

RUN addgroup -g ${GROUP_ID} -S appgroup \
    && adduser -u ${USER_ID} -S appuser -G appgroup

# Copy application
COPY --chown=appuser:appgroup . /var/www

# Install Composer dependencies
RUN composer install --no-interaction --no-scripts --prefer-dist

# Set permissions
RUN chown -R appuser:appgroup /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

USER appuser

EXPOSE 9000

CMD ["php-fpm"]

# ============================================
# Production Stage
# ============================================
FROM base AS production

# Copy application
COPY . /var/www

# Install Composer dependencies (production, no dev)
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader

# Optimize Laravel
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Create user
RUN addgroup -g 1000 -S appgroup \
    && adduser -u 1000 -S appuser -G appgroup

# Set permissions
RUN chown -R appuser:appgroup /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

USER appuser

EXPOSE 9000

CMD ["php-fpm"]

# ============================================
# Testing Stage (for CI/CD)
# ============================================
FROM development AS testing

USER root

# Install additional testing tools
RUN composer require --dev phpunit/phpunit phpstan/phpstan --no-interaction

USER appuser

# Run tests
RUN php artisan test
