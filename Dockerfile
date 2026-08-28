# FrankenPHP: a single binary that's a full production web server (built
# on Caddy) + PHP runtime in one process. Replaces `php artisan serve`,
# which is single-threaded and was serializing every request (page, CSS,
# images) behind each other -- that's what caused the consistent ~500ms
# delay on every asset, not any one file being slow. FrankenPHP serves
# static files itself (no PHP boot at all for CSS/images) and handles
# many PHP requests concurrently, while still being one process / one
# container -- no architecture change from the "single instance" goal.
FROM dunglas/frankenphp:1-php8.4-alpine

RUN apk add --no-cache libzip-dev \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --optimize-autoloader

COPY . .

RUN composer dump-autoload --optimize \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs storage/certs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Point FrankenPHP/Caddy at Laravel's public/ dir as document root, and
# have it fall back to public/index.php for anything that isn't a real
# static file -- the same routing behavior php artisan serve gave us,
# just handled by a real web server instead of PHP's built-in one.
RUN cat > /etc/caddy/Caddyfile <<'EOF'
{
	frankenphp
	order php_server before file_server
}

:{$PORT:8000} {
	root * /app/public
	encode gzip

	php_server {
		root /app/public
	}
}
EOF

EXPOSE 8000

# config:cache/route:cache/view:cache run at container start (not build
# time) since Render injects env vars right before this CMD runs -- caching
# during the build would bake in empty/missing values instead.
CMD php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan migrate --force \
    && frankenphp run --config /etc/caddy/Caddyfile
