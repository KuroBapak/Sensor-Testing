#!/bin/sh
set -e

echo "🚀 Starting DFM Application..."

# Wait a moment for environment to be fully ready
sleep 2

# Clear any stale cache from build time
echo "🧹 Clearing build-time cache..."
php artisan config:clear || true
php artisan cache:clear || true

# Optimize for production with runtime environment variables
echo "⚡ Optimizing Laravel for production..."
php artisan config:cache || echo "⚠️  Config cache failed - check APP_KEY"
php artisan route:cache || echo "⚠️  Route cache failed"
php artisan view:cache || echo "⚠️  View cache failed"

# Check database connection
echo "🔍 Testing database connection..."
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database: Connected ✅';" || echo "⚠️  Database connection failed"

# Run migrations if needed (optional - can be done separately)
if [ "$AUTO_MIGRATE" = "true" ]; then
    echo "🔄 Running migrations..."
    php artisan migrate --force || echo "⚠️  Migration failed"
fi

echo "✅ Application ready!"
echo ""

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
