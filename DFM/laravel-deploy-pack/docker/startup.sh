#!/bin/sh
set -e

echo "🚀 Starting application..."

# storage is a mounted volume (and .dockerignore strips its contents):
# make sure the directories Laravel needs exist.
mkdir -p storage/app/public storage/framework/cache storage/framework/sessions \
    storage/framework/views storage/logs

# SQLite: the database file must exist before the first query.
# Keep it inside the storage volume (e.g. DB_DATABASE=/app/storage/database/database.sqlite).
# Never mount a volume over /app/database - it hides the migrations in newer images.
if [ "$DB_CONNECTION" = "sqlite" ] && [ -n "$DB_DATABASE" ]; then
    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
fi

# Rebuild caches with the RUNTIME environment variables
echo "⚡ Optimizing Laravel for production..."
php artisan optimize:clear || true
php artisan optimize || echo "⚠️  Optimize failed - check APP_KEY"

# Public disk uploads (public/storage -> storage/app/public)
php artisan storage:link --force || true

# Informational only: does not stop the container
echo "🔍 Testing database connection..."
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database: Connected ✅';" || echo "⚠️  Database connection failed"

# Opt-in migrations. A failed migration stops the container on purpose (set -e).
# --isolated (a cache lock, so concurrent starts cannot race) only works when the
# lock does not live in the database itself: with CACHE_STORE=database the
# cache_locks table does not exist yet on a fresh database and the migration fails.
if [ "$AUTO_MIGRATE" = "true" ]; then
    echo "🔄 Running migrations..."
    case "${CACHE_STORE:-database}" in
        redis|memcached|dynamodb|file) php artisan migrate --force --isolated ;;
        *) php artisan migrate --force ;;
    esac
fi

# Artisan ran as root above; give the volume back to php-fpm (www-data) so
# files created here (logs, sqlite) do not cause permission errors later.
chown -R www-data:www-data /app/storage

echo "✅ Application ready!"

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
