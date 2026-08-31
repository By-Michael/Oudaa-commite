#!/bin/sh
set -e

echo "[start.sh] Booting..."

# Render mounts Secret Files at /etc/secrets/<name>, readable by root only.
# PHP-FPM runs as www-data, which can't read it there -- copy it into
# app-owned storage first so the SSL connection to Aiven can actually
# open the CA file.
if [ -f /etc/secrets/aiven-ca.pem ]; then
    cp /etc/secrets/aiven-ca.pem /app/storage/certs/aiven-ca.pem
    chown www-data:www-data /app/storage/certs/aiven-ca.pem
    chmod 644 /app/storage/certs/aiven-ca.pem
    echo "[start.sh] Aiven CA cert installed."
fi

# nginx's config format doesn't support reading $PORT directly, so
# substitute Render's injected port into the template before nginx starts.
export PORT="${PORT:-8000}"
envsubst '${PORT}' < /app/docker/nginx.conf > /etc/nginx/http.d/default.conf

# --- Config/route/view caching -------------------------------------------
# These are optimizations, not requirements — the app runs fine uncached.
# A bad cache step (e.g. a Closure-based route, which route:cache cannot
# serialize) must NEVER be allowed to take the whole app down. So we try
# to cache, but on any failure we clear whatever partial cache state was
# left behind and continue booting uncached, loudly, instead of dying
# silently under `set -e` before the app ever comes up.
echo "[start.sh] Caching config/routes/views..."

# NOTE: each of these is the condition of an `if`, which is the one place
# `set -e` does NOT abort on failure — that's what makes it safe to let
# any of these fail without taking the whole script (and app) down.
if ! php artisan config:cache > /tmp/cache-config.log 2>&1; then
    echo "[start.sh] ERROR: config:cache failed — see below. Continuing uncached." >&2
    cat /tmp/cache-config.log >&2
fi

if ! php artisan route:cache > /tmp/cache-route.log 2>&1; then
    echo "[start.sh] ERROR: route:cache failed — see below (a Closure route is the" >&2
    echo "[start.sh]        most common cause). Clearing route cache and continuing" >&2
    echo "[start.sh]        uncached rather than aborting startup." >&2
    cat /tmp/cache-route.log >&2
    php artisan route:clear || true
fi

if ! php artisan view:cache > /tmp/cache-view.log 2>&1; then
    echo "[start.sh] ERROR: view:cache failed — see below. Clearing view cache and" >&2
    echo "[start.sh]        continuing uncached." >&2
    cat /tmp/cache-view.log >&2
    php artisan view:clear || true
fi

# --- Database migration ----------------------------------------------------
# Unlike the cache steps above, this one genuinely must succeed — booting
# against a schema the code doesn't expect is worse than not booting at
# all. Keep this fatal (still under `set -e`), but log clearly so a
# migration failure is unambiguous in the deploy log rather than looking
# like a generic boot failure.
echo "[start.sh] Running migrations..."
if ! php artisan migrate --force; then
    echo "[start.sh] FATAL: migration failed. Aborting startup." >&2
    exit 1
fi

echo "[start.sh] Boot complete, starting supervisord."
exec supervisord -c /app/docker/supervisord.conf
