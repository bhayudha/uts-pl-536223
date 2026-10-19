FROM php:8.4-cli-alpine AS builder

RUN apk add --no-cache git unzip zip libzip-dev \
    && docker-php-ext-install zip pdo_mysql

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Salin file composer terlebih dahulu agar layer cache bekerja
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --no-dev

COPY . .

RUN composer dump-autoload --optimize
RUN cp .env.example .env && php artisan key:generate

FROM php:8.4-cli-alpine

RUN apk add --no-cache libzip-dev \
    && docker-php-ext-install zip pdo_mysql

WORKDIR /app

COPY --from=builder /app /app

RUN addgroup -S laravel && adduser -S laravel -G laravel \
    && chown -R laravel:laravel /app \
    && chmod -R 775 /app/storage /app/bootstrap/cache

USER laravel

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]