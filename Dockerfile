FROM composer:2 AS composer-builder
WORKDIR /build
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev

FROM node:22-alpine AS node-builder
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts
COPY vite.config.js ./
COPY resources/ resources/
COPY --from=composer-builder /build/vendor/laravel vendor/laravel
RUN npm run build

FROM php:8.4-fpm-alpine AS production
RUN apk add --no-cache nginx supervisor sqlite curl libpng libzip oniguruma libxml2 icu-libs
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS libpng-dev libzip-dev oniguruma-dev libxml2-dev icu-dev sqlite-dev \
    && docker-php-ext-install pdo_sqlite mbstring pcntl bcmath gd zip intl opcache \
    && pecl install redis && docker-php-ext-enable redis \
    && apk del .build-deps
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
RUN mkdir -p /run/nginx
COPY docker/supervisord.conf /etc/supervisor/conf.d/app.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
COPY --from=node-builder --chown=www-data:www-data /build/public/build public/build
COPY --from=composer-builder --chown=www-data:www-data /build/vendor vendor
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache database \
    && chown -R www-data:www-data storage bootstrap/cache database
EXPOSE 80
HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD curl -sf http://localhost/up || exit 1
ENTRYPOINT ["/entrypoint.sh"]
