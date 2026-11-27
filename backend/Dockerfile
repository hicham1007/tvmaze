# syntax=docker/dockerfile:1

FROM php:8.4-fpm-alpine AS base

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && install-php-extensions opcache redis

WORKDIR /app

FROM base AS build

RUN install-php-extensions zip
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --no-dev --optimize

FROM base AS runtime

# No APP_KEY: the API is stateless and encrypts or signs nothing.
ENV APP_NAME=series-api \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    CACHE_STORE=file \
    SESSION_DRIVER=array \
    QUEUE_CONNECTION=sync

COPY --from=build /app /app

RUN chown -R www-data:www-data storage bootstrap/cache
USER www-data

# php-fpm speaks FastCGI, not HTTP: the proxy is what turns requests into it.
EXPOSE 9000

HEALTHCHECK --interval=5s --timeout=3s --retries=10 \
    CMD nc -z 127.0.0.1 9000 || exit 1
