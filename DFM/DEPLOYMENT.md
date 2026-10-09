# Deployment Guide - DFM (Laravel 13 + Inertia React)

## Arsitektur Deployment

Aplikasi ini menggunakan Docker multi-stage build untuk deployment ke Coolify:
- **Frontend**: Node.js 20 untuk build Vite/Inertia assets
- **Backend**: PHP 8.4-FPM + Nginx + Supervisor
- **Registry**: GitHub Container Registry (ghcr.io)
- **Orchestration**: Coolify

## Struktur Docker

```
Dockerfile                    # Multi-stage build
docker/
  ├── nginx.conf             # Nginx config untuk Laravel
  ├── php-fpm.conf           # PHP-FPM pool config
  ├── php.ini                # PHP optimization
  └── supervisord.conf       # Supervisor untuk PHP-FPM + Nginx
docker-compose.yaml          # Coolify deployment config
.dockerignore                # Exclude files dari build
```

## Proses Build & Deploy

### 1. Build Docker Image

```bash
docker build --no-cache -t ghcr.io/kurobapak/dfm-app:latest -f Dockerfile .
```

**Proses build:**
1. Stage 1: Build frontend assets dengan Vite
   - Install npm dependencies
   - Build React + Inertia assets ke `public/build`
2. Stage 2: Production PHP image
   - Install PHP extensions (pdo, gd, opcache, dll)
   - Copy aplikasi + built assets
   - Install composer dependencies (production only)
   - Cache Laravel config/routes/views
   - Setup permissions

### 2. Push ke GitHub Container Registry

```bash
docker push ghcr.io/kurobapak/dfm-app:latest
```

### 3. Deploy di Coolify

Coolify akan otomatis:
1. Pull image terbaru dari GHCR
2. Start container dengan docker-compose.yaml
3. Mount volumes untuk storage & database
4. Connect ke network `coolify`

## Konfigurasi Environment di Coolify Dashboard

**PENTING**: Coolify **TIDAK** menggunakan file `.env` dari repository untuk alasan keamanan. Semua environment variables harus dikonfigurasi melalui **Coolify Dashboard → Service → Environment Variables**.

Tambahkan variabel berikut di Coolify Dashboard:

```env
APP_NAME=DFM
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=database-host
DB_PORT=3306
DB_DATABASE=dfm_production
DB_USERNAME=dfm_user
DB_PASSWORD=secret

SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Jika pakai external storage (S3)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
```

## Post-Deployment

### Migrasi Database

Tambahkan command di Coolify untuk run migration otomatis:

```bash
php artisan migrate --force
```

### Optimize Laravel

Laravel sudah di-optimize saat build, tapi jika perlu re-cache:

```bash
docker exec -it <container-id> php artisan optimize
```

### Health Check

Aplikasi expose health endpoint di `/up` (Laravel 13 built-in).
Docker healthcheck akan hit endpoint ini setiap 30 detik.

## Volumes

- `storage:/app/storage` - Laravel storage (logs, cache, uploads)
- `database:/app/database` - SQLite database (jika pakai SQLite)

## Port

Container expose port `8000`, Coolify akan handle reverse proxy.

## Troubleshooting

### 1. Build gagal di frontend stage
```bash
# Check node_modules dan package-lock.json
npm ci --no-audit
npm run build
```

### 2. Permission error
```bash
# Di container, pastikan www-data punya akses
chown -R www-data:www-data /app/storage /app/bootstrap/cache
```

### 3. Health check failed
```bash
# Check nginx + php-fpm running
docker exec -it <container-id> supervisorctl status
# Check /up endpoint
docker exec -it <container-id> wget -q -O- http://127.0.0.1:8000/up
```

### 4. Assets tidak load
```bash
# Pastikan public/build ada di image
docker run --rm ghcr.io/kurobapak/dfm-app:latest ls -la /app/public/build
```

## Security Checklist

- ✅ `APP_DEBUG=false` di production
- ✅ `APP_ENV=production`
- ✅ Opcache enabled
- ✅ PHP expose_php=Off
- ✅ Laravel config/route/view cached
- ✅ Composer install `--no-dev`
- ✅ Proper file permissions (www-data)

## Monitoring

### Logs
```bash
# Application logs
docker exec -it <container-id> tail -f /app/storage/logs/laravel.log

# Nginx logs
docker logs <container-id>
```

### Performance
- Opcache stats: Check di `/app/storage/logs/opcache.log`
- Response time: Monitor via Coolify metrics
- Database queries: Enable query log jika perlu debug

## Rollback

Jika deploy bermasalah:

```bash
# Tag previous version
docker tag ghcr.io/kurobapak/dfm-app:latest ghcr.io/kurobapak/dfm-app:rollback
# Update docker-compose.yaml ke tag lama
# Coolify redeploy
```

## CI/CD (Optional)

Bisa setup GitHub Actions untuk auto build & push:

```yaml
name: Build & Push
on:
  push:
    branches: [main]
jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Login to GHCR
        run: echo "${{ secrets.GITHUB_TOKEN }}" | docker login ghcr.io -u ${{ github.actor }} --password-stdin
      - name: Build & Push
        run: |
          docker build -t ghcr.io/kurobapak/dfm-app:latest .
          docker push ghcr.io/kurobapak/dfm-app:latest
```
