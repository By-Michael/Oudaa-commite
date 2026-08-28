#!/bin/sh
set -e

# nginx's config format doesn't support reading $PORT directly, so
# substitute Render's injected port into the template before nginx starts.
export PORT="${PORT:-8000}"
envsubst '${PORT}' < /app/docker/nginx.conf > /etc/nginx/http.d/default.conf

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

exec supervisord -c /app/docker/supervisord.conf
