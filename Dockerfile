FROM php:8.3-cli-alpine

RUN apk add --no-cache libzip-dev \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --optimize-autoloader

COPY . .

RUN composer dump-autoload --optimize \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# One process, one instance: no queue worker, no supervisor. Provisioning
# runs inline (QUEUE_CONNECTION=sync) so `php artisan serve` is the whole
# app. DB is Aiven MySQL (see .env / DB_MYSQL_SSL_CA), not a local file,
# so there's no disk-persistence concern for the database itself anymore
# — just make sure DB_MYSQL_SSL_CA points at a ca.pem baked into the
# image or mounted in, e.g. COPY it in above under storage/certs/.
EXPOSE 8000

CMD php artisan migrate --force \
    && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
