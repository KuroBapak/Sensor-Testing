#!/bin/sh
set -e

echo "🚀 Starting DFM Application..."

# The storage directory is a mounted volume; make sure the framework dirs exist.
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs

# Clear any stale cache from build time
echo "🧹 Clearing build-time cache..."
php artisan config:clear || true
php artisan cache:clear || true

# Optimize for production with runtime environment variables
# (optimize = config + events + routes + views cache)
echo "⚡ Optimizing Laravel for production..."
php artisan optimize || echo "⚠️  Optimize failed - check APP_KEY"

# Check database connection
echo "🔍 Testing database connection..."
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database: Connected ✅';" || echo "⚠️  Database connection failed"

# Run migrations if requested. A failed migration stops the container (set -e).
# --isolated (a cache lock against concurrent starts) only works when the lock is
# not stored in the database: with CACHE_STORE=database the cache_locks table does
# not exist yet on a fresh database and the migration fails.
if [ "$AUTO_MIGRATE" = "true" ]; then
    echo "🔄 Running migrations..."
    case "${CACHE_STORE:-database}" in
        redis|memcached|dynamodb|file) php artisan migrate --force --isolated ;;
        *) php artisan migrate --force ;;
    esac
fi

# Artisan ran as root above; hand the volume back to php-fpm (www-data) so
# log files created here do not cause permission errors later.
chown -R www-data:www-data /app/storage

echo "✅ Application ready!"
echo ""

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
