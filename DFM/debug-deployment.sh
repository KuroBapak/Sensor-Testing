#!/bin/bash
# Debug script for 500 error on deployed DFM application

echo "=== DFM Deployment Diagnostics ==="
echo ""

# Find the DFM container
echo "1. Finding DFM container..."
CONTAINER_ID=$(docker ps | grep "ghcr.io/kurobapak/sensor-testing" | awk '{print $1}')

if [ -z "$CONTAINER_ID" ]; then
    echo "❌ DFM container not found!"
    echo "Available containers:"
    docker ps
    exit 1
fi

echo "✅ Found container: $CONTAINER_ID"
echo ""

# Check if services are running
echo "2. Checking services status (PHP-FPM, Nginx)..."
docker exec $CONTAINER_ID supervisorctl status
echo ""

# Check Laravel logs
echo "3. Last 30 lines of Laravel logs..."
docker exec $CONTAINER_ID tail -n 30 /app/storage/logs/laravel.log 2>/dev/null || echo "No Laravel logs found"
echo ""

# Check environment variables
echo "4. Checking critical environment variables..."
docker exec $CONTAINER_ID sh -c 'echo "APP_ENV=$APP_ENV"'
docker exec $CONTAINER_ID sh -c 'echo "APP_DEBUG=$APP_DEBUG"'
docker exec $CONTAINER_ID sh -c 'echo "APP_KEY exists: $([ -n \"$APP_KEY\" ] && echo YES || echo NO)"'
docker exec $CONTAINER_ID sh -c 'echo "DB_CONNECTION=$DB_CONNECTION"'
docker exec $CONTAINER_ID sh -c 'echo "DB_HOST=$DB_HOST"'
echo ""

# Test database connection
echo "5. Testing database connection..."
docker exec $CONTAINER_ID php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection: OK';" 2>&1 || echo "❌ Database connection failed"
echo ""

# Check storage permissions
echo "6. Checking storage permissions..."
docker exec $CONTAINER_ID ls -la /app/storage
echo ""

# Check if public/build exists
echo "7. Checking if frontend assets were built..."
docker exec $CONTAINER_ID ls -la /app/public/build 2>/dev/null || echo "❌ /app/public/build not found - frontend assets missing!"
echo ""

# Test the /up endpoint
echo "8. Testing Laravel /up endpoint..."
docker exec $CONTAINER_ID wget -q -O- http://127.0.0.1:8000/up 2>&1
echo ""

# Check nginx error logs
echo "9. Recent nginx errors..."
docker logs --tail 50 $CONTAINER_ID 2>&1 | grep -i error | tail -10 || echo "No nginx errors found"
echo ""

# Check if APP_KEY is set
echo "10. Validating APP_KEY..."
docker exec $CONTAINER_ID php artisan tinker --execute='echo config("app.key") ? "APP_KEY is set" : "APP_KEY is MISSING!";' 2>&1
echo ""

echo "=== Diagnostics Complete ==="
