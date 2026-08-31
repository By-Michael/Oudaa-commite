#!/bin/sh
set -e

# Render mounts Secret Files at /etc/secrets/<name>, readable by root only.
# PHP-FPM runs as www-data, which can't read it there -- copy it into
# app-owned storage first so the SSL connection to Aiven can actually
# open the CA file.
if [ -f /etc/secrets/aiven-ca.pem ]; then
    cp /etc/secrets/aiven-ca.pem /app/storage/certs/aiven-ca.pem
    chown www-data:www-data /app/storage/certs/aiven-ca.pem
    chmod 644 /app/storage/certs/aiven-ca.pem
fi

# nginx's config format doesn't support reading $PORT directly, so
# substitute Render's injected port into the template before nginx starts.
export PORT="${PORT:-8000}"
envsubst '${PORT}' < /app/docker/nginx.conf > /etc/nginx/http.d/default.conf

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

exec supervisord -c /app/docker/supervisord.conf
