# nginx + PHP-FPM in one container, managed by supervisord. Standard,
# battle-tested way to run Laravel in production -- still one deployed
# instance, just two cooperating processes inside it (normal, not the
# multi-instance/multi-dyno setup you wanted to avoid). Switched away
# from FrankenPHP: Render's sandboxing blocks a syscall its Go/Caddy
# core needs ("Operation not permitted"), a known incompatibility, not
# something fixable from inside the Dockerfile.
FROM php:8.4-fpm-alpine

RUN apk add --no-cache libzip-dev nginx supervisor gettext \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --optimize-autoloader

COPY . .

RUN composer dump-autoload --optimize \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs storage/certs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x docker/start.sh

EXPOSE 8000

CMD ["/app/docker/start.sh"]
