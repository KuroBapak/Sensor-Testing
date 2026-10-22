# DFM Deployment Troubleshooting Guide - 500 Server Error

## Issue
Getting 500 Server Error on deployed application.

## Root Causes Identified

### 1. **Hardcoded Development Environment Variables**
**Problem**: `docker-compose.yaml` had `APP_ENV=local` and `APP_DEBUG=true` hardcoded, overriding Coolify's environment variables.

**Fix**: Changed to use environment variable substitution:
```yaml
APP_ENV: ${APP_ENV:-production}
APP_DEBUG: ${APP_DEBUG:-false}
```

### 2. **Config Cached During Build with Wrong Environment**
**Problem**: `Dockerfile` was running `php artisan config:cache` during build, which baked local `.env` values (local database, etc.) into the image.

**Fix**: 
- Removed `config:cache` from build step
- Created `docker/startup.sh` to cache config at runtime with production environment variables
- Only cache routes and views during build (they don't depend on environment)

### 3. **No Runtime Environment Validation**
**Problem**: No way to know if environment variables were properly set or database was accessible.

**Fix**: Added startup script that:
- Clears build-time cache
- Validates database connection
- Caches config with production environment
- Provides clear error messages

## Deployment Steps

### 1. Rebuild Docker Image
```bash
docker build --no-cache -t ghcr.io/kurobapak/sensor-testing:latest -f Dockerfile .
docker push ghcr.io/kurobapak/sensor-testing:latest
```

### 2. Configure Environment in Coolify Dashboard
Go to your service → Environment Variables and ensure these are set:

**Required:**
```env
APP_NAME=DFM
APP_ENV=production
APP_KEY=base64:jl0OrkWJViv7qhKAscHg7CgSim10jc4kAAS0uXhJ8QI=
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-database-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

SESSION_DRIVER=database
CACHE_STORE=database
```

**Optional:**
```env
AUTO_MIGRATE=true    # Auto-run migrations on container start
```

### 3. Deploy in Coolify
- Pull latest image
- Restart service
- Check container logs for startup messages

### 4. Verify Deployment
Check container logs should show:
```
🚀 Starting DFM Application...
🧹 Clearing build-time cache...
⚡ Optimizing Laravel for production...
🔍 Testing database connection...
Database: Connected ✅
✅ Application ready!
```

## Common Issues & Solutions

### Issue: "No application encryption key has been specified"
**Cause**: `APP_KEY` not set in Coolify environment variables
**Solution**: Set `APP_KEY` in Coolify Dashboard (use the one from your local `.env`)

### Issue: "SQLSTATE[HY000] [2002] Connection refused"
**Cause**: Database host/credentials incorrect
**Solution**: 
- Check `DB_HOST` is the correct hostname (not `127.0.0.1` in Docker)
- Verify database credentials in Coolify Dashboard
- Test connection: `docker exec <container> php artisan tinker --execute="DB::connection()->getPdo();"`

### Issue: Blank page or 500 error
**Cause**: Missing storage permissions
**Solution**: 
```bash
docker exec <container> chown -R www-data:www-data /app/storage /app/bootstrap/cache
docker exec <container> chmod -R 775 /app/storage /app/bootstrap/cache
```

### Issue: Frontend assets not loading
**Cause**: `public/build` missing from image
**Solution**: 
- Check build logs during image build
- Verify: `docker run --rm ghcr.io/kurobapak/sensor-testing:latest ls -la /app/public/build`
- Rebuild if missing

## Diagnostic Commands

```bash
# Find container ID
docker ps | grep sensor-testing

# Check services status
docker exec <container-id> supervisorctl status

# View Laravel logs
docker exec <container-id> tail -f /app/storage/logs/laravel.log

# Check environment
docker exec <container-id> env | grep APP_

# Test database
docker exec <container-id> php artisan tinker --execute="DB::connection()->getPdo();"

# Clear cache manually
docker exec <container-id> php artisan config:clear
docker exec <container-id> php artisan cache:clear
docker exec <container-id> php artisan optimize

# Restart container
docker restart <container-id>
```

## Files Changed
- `docker-compose.yaml` - Fixed environment variable handling
- `Dockerfile` - Removed build-time config caching, added startup script
- `docker/startup.sh` - New runtime optimization script
- `debug-deployment.sh` - Diagnostic script

## Next Steps
1. Rebuild and push new image
2. Update environment variables in Coolify
3. Redeploy
4. Monitor container logs during startup
5. If still failing, run `debug-deployment.sh` and share output
