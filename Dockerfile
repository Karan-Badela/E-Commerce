FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM php:8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    libzip-dev \
    libonig-dev \
    libpq-dev \
    libicu-dev \
    && docker-php-ext-install \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    zip \
    bcmath \
    intl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
COPY --from=assets /app/public/build ./public/build
COPY docker/render-nginx.conf /etc/nginx/sites-available/default

RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer dump-autoload --no-dev --optimize --no-interaction --no-scripts \
    && php artisan package:discover --ansi \
    && php artisan filament:assets \
    && php artisan storage:link \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000

CMD ["sh", "-c", "php artisan migrate --force && if [ -n \"$ADMIN_EMAIL\" ]; then php artisan app:make-admin; fi && php-fpm -D && nginx -g 'daemon off;'"]
