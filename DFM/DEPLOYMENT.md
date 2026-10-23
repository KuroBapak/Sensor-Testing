# Deployment Guide - DFM (Laravel 13 + Inertia React)

## Arsitektur Deployment

Satu image Docker yang immutable, dibangun oleh GitHub Actions, disimpan di GHCR, lalu di-pull oleh Coolify.

- **Build**: multi-stage Dockerfile (Vite/Inertia di stage 1, PHP 8.4-FPM + Nginx + Supervisor di stage 2)
- **Registry**: GitHub Container Registry — `ghcr.io/kurobapak/sensor-testing`
- **Orchestration**: Coolify (Docker Compose, hanya `image:`, tanpa `build:`)
- **Database**: eksternal (dikelola Coolify), bukan SQLite

Satu image dijalankan dengan empat peran:

| Service | Command | Fungsi |
|---|---|---|
| `dfm-app` | `startup.sh` → supervisord (nginx + php-fpm) | web, port 8000 |
| `dfm-queue` | `php artisan queue:work` | menjalankan job (`RunBackupJob`, `DetectAnomalyJob`, `CheckGeofenceJob`) |
| `dfm-reverb` | `php artisan reverb:start` | WebSocket realtime, port 8080 |
| `dfm-scheduler` | `php artisan schedule:work` | scheduler (cukup satu instance) |

## File yang dibutuhkan di repo

Monorepo ini berisi beberapa proyek, jadi workflow GitHub harus di root repo, bukan di `DFM/.github`.

```
.github/workflows/dfm-build-push.yml   # (root repo) build + push + trigger Coolify
DFM/
  Dockerfile                           # multi-stage build
  .dockerignore                        # jaga .env, .git, vendor tidak masuk image
  docker-compose.yaml                  # definisi service untuk Coolify
  .env.example                         # dokumentasi variabel
  docker/
    nginx.conf  php-fpm.conf  php.ini  supervisord.conf  startup.sh
```

Coolify sendiri tidak butuh source code: compose boleh ditempel langsung di Coolify (**Docker Compose Empty**). Jika memakai sumber Git, set **Base Directory** ke `/DFM`.

## Alur Build & Deploy

1. `git push` ke `main` (perubahan di `DFM/**`).
2. GitHub Actions membangun image dan push dengan tag `sha-<commit>` dan `latest`.
3. Langkah terakhir memanggil webhook Deploy Coolify.
4. Coolify pull image (`pull_policy: always`) dan menjalankan compose.

Build manual (darurat):

```bash
docker build -t ghcr.io/kurobapak/sensor-testing:latest ./DFM
docker push ghcr.io/kurobapak/sensor-testing:latest
```

### Setup sekali di GitHub

Repository → Settings → Secrets and variables → Actions.

**Secrets**

| Nama | Isi |
|---|---|
| `COOLIFY_WEBHOOK` | URL Deploy webhook aplikasi di Coolify (Configuration → Webhooks) |
| `COOLIFY_TOKEN` | API token Coolify dengan permission **Deploy** (aktifkan API access di Settings dulu) |

Jika kedua secret kosong, image tetap dibuild dan dipush, hanya langkah deploy yang dilewati.

**Variables** (bukan secret — nilai ini masuk ke bundle JS yang publik)

| Nama | Contoh |
|---|---|
| `VITE_APP_NAME` | `DFM` |
| `VITE_BROADCAST_CONNECTION` | `reverb` |
| `VITE_REVERB_APP_KEY` | sama dengan `REVERB_APP_KEY` |
| `VITE_REVERB_HOST` | domain publik Reverb, mis. `ws.example.com` |
| `VITE_REVERB_PORT` | `443` |
| `VITE_REVERB_SCHEME` | `https` |

**PENTING**: variabel `VITE_*` di-compile ke JavaScript saat build. Menambahkannya di Coolify tidak berpengaruh — ubah di GitHub Variables lalu build ulang image. Jika `VITE_BROADCAST_CONNECTION` tidak diisi, aplikasi jatuh ke mode `log` (tanpa realtime, fallback refresh berkala).

### Setup sekali di server (jika package GHCR private)

Login sebagai user yang dipakai Coolify untuk menjalankan Docker, token classic dengan scope `read:packages`:

```bash
echo "$GH_TOKEN" | docker login ghcr.io --username <github-username> --password-stdin
```

Atau jadikan package public (Package settings → Change visibility).

## Environment di Coolify Dashboard

Semua variabel diatur di **Coolify → Environment Variables**. Compose memuatnya lewat `env_file: .env`. `REVERB_HOST/PORT/SCHEME` sengaja di-override di compose ke `dfm-reverb:8080` (http) agar broadcast dikirim lewat jaringan internal; host publik untuk browser ditentukan oleh `VITE_REVERB_*` saat build.

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
CACHE_STORE=database
QUEUE_CONNECTION=database

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...

# Opsional
AUTO_MIGRATE=false
APP_IMAGE_TAG=latest      # isi sha-<commit> untuk pin / rollback
```

### Domain

Di Coolify, beri domain per service:

- `dfm-app` → domain utama, port `8000`
- `dfm-reverb` → subdomain terpisah (mis. `ws.example.com`), port `8080`; harus sama dengan `VITE_REVERB_HOST`

## Migrasi Database

Pilih salah satu:

- **Manual / post-deploy command** (disarankan): `php artisan migrate --force`
- **Otomatis**: set `AUTO_MIGRATE=true`. `startup.sh` menjalankan `migrate --force` (dengan `--isolated` bila `CACHE_STORE` redis/memcached/file; dengan `CACHE_STORE=database` flag itu dilewati karena tabel `cache_locks` belum ada di database baru). Jika migrasi gagal, container berhenti — sengaja, agar tidak jalan dengan skema setengah jadi.

Jaga migrasi tetap backward-compatible selama satu rilis (expand/contract), karena rollback kode lebih mudah daripada rollback skema.

## Rollback

Setiap build menghasilkan tag `sha-<commit>` yang tidak berubah.

1. Coolify → Environment Variables → `APP_IMAGE_TAG=sha-<commit-lama>`
2. Redeploy
3. Cek `/up` dan alur utama

Kembalikan ke `latest` dengan menghapus `APP_IMAGE_TAG`.

## Volumes

- `storage:/app/storage` — log, cache, upload (dipakai bersama oleh app, queue, reverb, scheduler)

Volume `database` sudah dihapus: volume itu menimpa `/app/database` sehingga migrasi baru di image tidak pernah terlihat container. Jangan mount volume ke `/app/database`.

## Health Check

- `dfm-app`: `/up` (bawaan Laravel) setiap 30 detik
- `dfm-reverb`: `/up` di port 8080
- `/up` hanya membuktikan aplikasi booting, bukan koneksi database. Untuk cek DB, tambahkan listener `DiagnosingHealth`.

## Troubleshooting

### Build gagal di stage frontend
```bash
npm ci --no-audit
npm run build
```

### Realtime tidak jalan
- Cek `docker logs <dfm-reverb>` dan healthcheck-nya
- Pastikan GitHub Variables `VITE_*` terisi dan image dibuild ulang setelah mengubahnya
- Cek domain `dfm-reverb` mengarah ke port 8080

### Job tidak jalan
```bash
docker logs <dfm-queue> --tail 50
docker exec <dfm-queue> php artisan queue:failed
```

### Permission error di storage
Worker memakai `user: www-data`. Jika ada file milik root di volume:
```bash
docker exec <dfm-app> chown -R www-data:www-data /app/storage
```
(`startup.sh` melakukan ini otomatis setiap start.)

### Health check gagal
```bash
docker exec <dfm-app> supervisorctl status
docker exec <dfm-app> wget -q -O- http://127.0.0.1:8000/up
```

### Assets tidak load
```bash
docker run --rm ghcr.io/kurobapak/sensor-testing:latest ls -la /app/public/build
```

`debug-deployment.sh` menjalankan rangkaian pengecekan di atas.

## Security Checklist

- `APP_DEBUG=false`, `APP_ENV=production`
- `.env` tidak masuk image (`.dockerignore`) dan tidak di-commit
- Secret hanya di Coolify / GitHub Secrets; `VITE_*` bersifat publik
- Opcache aktif, `expose_php=Off`
- Config/route/view/event di-cache saat start (`php artisan optimize`)
- Composer `--no-dev`
