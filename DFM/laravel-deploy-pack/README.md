# Laravel Deploy Pack

Reusable files to deploy a Laravel app with this flow:

```
build image on your laptop  →  push to GHCR  →  Coolify pulls it and runs docker compose
```

**New to this? Read [PANDUAN.md](PANDUAN.md) first** (step-by-step guide from zero, in Indonesian). This README is the technical reference.

No GitHub Actions needed. `build-push.sh` / `build-push.ps1` do the build and push for you (the same two commands you already run by hand, plus tags and a few safeguards).

Tested: built against a Laravel 13 + Inertia/React app that uses `maatwebsite/excel` (needs `ext-gd`), started with SQLite in a volume, `/up` and the login page returned 200, migrations ran, files in the volume were owned by `www-data`, uploads above nginx's default 1 MB limit were accepted, and a restart kept the data. Both build scripts were run in build-only mode. `init.php` was run against copies of AFM (plain app, SQLite) and DFM (Reverb, queued broadcast events), the generated files built and the AFM image started from the generated `.env.coolify`; a synthetic app exercised queue, Horizon, scheduler, Reverb and every extension recipe. Pushing to GHCR and the Coolify deploy were not tested.

## What is in the pack

| File | Goes to | Edit it? |
|---|---|---|
| `Dockerfile` | app root | Only for extra PHP extensions and `VITE_*` args |
| `.dockerignore` | app root | Rarely |
| `docker/` (nginx, php-fpm, php.ini, supervisord, startup.sh) | app root | Rarely |
| `build-push.sh` or `build-push.ps1` | app root (keep the one you use) | No (reads the image name from `docker-compose.yaml`) |
| `.env.build.example` | app root, copied to `.env.build` | `VITE_*` values |
| `docker-compose.yaml` | app root, or paste into Coolify | Image name, service names, optional services |
| `init.php` | not copied: run it from here, it installs everything else into the app | No |
| `.env.coolify.example` | not copied: static checklist (init.php generates a tailored `.env.coolify` instead) | Reference |
| `PANDUAN.md` | not copied: beginner guide in Indonesian | Reference |

Coolify needs none of these from Git if you paste the compose file into it. Only the image matters.

## 1. Install the pack into the app

### Automatic (recommended): `init.php`

```bash
php laravel-deploy-pack/init.php /path/to/app --dry-run   # report only, writes nothing
php laravel-deploy-pack/init.php /path/to/app             # install
```

Options: `--image=ghcr.io/user/name` (default: GitHub owner from `git remote` + folder name, lowercase), `--db=mysql|mariadb|pgsql|sqlite` (production database; default is read from `.env.example`, which is `sqlite` in a fresh Laravel app. It only decides the DB lines of `.env.coolify`; the image contains the MySQL and PostgreSQL drivers either way), `--yes` (skip the confirmation prompt), `--dry-run`, `--force` (overwrite existing files, keeping `.bak`). Needs PHP 8.1+. Without local PHP:

```bash
docker run --rm -v "/path/to/laravel-deploy-pack:/pack:ro" -v "/path/to/app:/app" php:8.4-cli php /pack/init.php /app --image=ghcr.io/user/name
```

It reads the app and decides what you would otherwise check by hand:

| It reads | It does |
|---|---|
| `ext-*` requirements of **every** package in `composer.lock`, plus redis/memcached drivers in `.env.example` | Writes install recipes for the missing PHP extensions into the `Dockerfile` (`docker-php-ext-install` for core extensions, `pecl` for redis, apcu, imagick, memcached, mongodb, uuid). Extensions without a recipe are listed as warnings. |
| `ShouldQueue`, queued broadcast events, `Queueable`, `Bus::`, `->queue()`, `laravel/horizon`, and `QUEUE_CONNECTION` | Enables the `queue` service (`horizon` instead of `queue:work` when Horizon is installed). Jobs found with `QUEUE_CONNECTION=sync` are reported but need no worker. |
| `Schedule::` / `$schedule->` (comments ignored) in `routes/console.php`, `app/Console/Kernel.php`, `bootstrap/app.php` | Enables the `scheduler` service |
| `laravel/reverb` in `composer.lock` | Enables the `reverb` service and the `REVERB_HOST/PORT/SCHEME` wiring |
| `import.meta.env.VITE_*` in `resources/` and `VITE_*` in `.env.example` | One `ARG` per variable in the `Dockerfile`, and a merged `.env.build` (production values for Reverb, your existing entries untouched) |
| `package.json` / lock file | `npm ci`, `npm install` (no lock), or no frontend steps at all (backend-only app) |
| `.env.example` | A tailored `.env.coolify` (production values, SQLite path or `CHANGE_ME` DB settings, `QUEUE_CONNECTION=sync` when no jobs exist) |

Safe to re-run: files that already exist are left alone (`skipped`) unless `--force`, and in an existing `Dockerfile` only the blocks between `# >>> deploy-pack:...` markers are rewritten. A Dockerfile without markers is never touched. It also warns about a `.env` tracked by git, a `.dockerignore` that does not exclude `.env`, a missing `composer.lock`, Octane, and SQL Server.

What it cannot know: extensions with no recipe, non-npm package managers, anything the app does that is not visible in the files above. Read the WARNINGS block.

### Manual copy

From the new app's root (bash):

```bash
PACK=/d/GitHub/Sensor-Testing/DFM/laravel-deploy-pack
cp "$PACK/Dockerfile" "$PACK/.dockerignore" "$PACK/docker-compose.yaml" "$PACK/build-push.sh" .
cp "$PACK/.env.build.example" .env.build
cp -r "$PACK/docker" .
```

PowerShell:

```powershell
$pack = "D:\GitHub\Sensor-Testing\DFM\laravel-deploy-pack"
Copy-Item "$pack\Dockerfile","$pack\.dockerignore","$pack\docker-compose.yaml","$pack\build-push.ps1" .
Copy-Item "$pack\.env.build.example" .env.build
Copy-Item "$pack\docker" . -Recurse
```

## 2. Adjust for the app (copy-paste is not enough)

If you used `init.php`, checks 1, 2, 3 and 5 below are already done for you (read its report). If you copied the files by hand, check these six things. Everything else works as-is.

| # | Check | If yes, do this |
|---|---|---|
| 1 | Does any Composer package need a PHP extension? (`composer check-platform-reqs`, or read the build error) | Add it to the extension list in **stage 0** of the `Dockerfile` (`docker-php-ext-install ...`). Stage 0 is shared by build and production, so one edit covers both. Example: `maatwebsite/excel` needs `gd`. |
| 2 | Is there a `package-lock.json`, and does `npm run build` work? | The Dockerfile uses `npm ci` + `npm run build`. Create the lock file, or edit those two lines for pnpm/yarn. |
| 3 | Does the frontend read `import.meta.env.VITE_*`? | Each variable needs an `ARG` in the Dockerfile **and** a line in `.env.build`. They are compiled into the JS, so setting them in Coolify does nothing. |
| 4 | Which database? | External MySQL/PostgreSQL: set `DB_*` in Coolify. SQLite: `DB_DATABASE=/app/storage/database/database.sqlite` (inside the volume; `startup.sh` creates the file). Never mount a volume on `/app/database`. |
| 5 | Jobs, queued mail/exports, scheduled tasks, Reverb? | Uncomment `queue`, `scheduler` or `reverb` in `docker-compose.yaml`. If you do not use them, keep them off. Without a worker, `QUEUE_CONNECTION=database` silently never runs jobs: use `sync` instead. |
| 6 | Names | The image name, in **one** place: the `image:` lines of `docker-compose.yaml` (lowercase). The build scripts read it from there. `init.php` fills it in, so by hand it is one find-and-replace of `ghcr.io/your-github-user/your-app`. |

Also check:
- Upload limit: nginx and `php.ini` are both set to 100M. Change both together.
- Does the app write to a disk other than `local`/`public`? Mount or configure storage for it.
- `APP_TIMEZONE`, `APP_LOCALE` etc. go in the Coolify env.

## 3. Build and push from your laptop

One-time login (classic token with `write:packages`; Docker remembers it):

```bash
echo "$GH_TOKEN" | docker login ghcr.io -u <github-user> --password-stdin
```

Every release, from the app root:

```bash
./build-push.sh
```

```powershell
.\build-push.ps1
```

No arguments and no environment variables: the image name is read from the first active `image:` line of `docker-compose.yaml`. It stops with a clear message if that line still holds the `your-github-user` placeholder. `IMAGE=...` / `-Image ...` override it when you need another name.

What it does, compared with running the two commands by hand:

| By hand | Script |
|---|---|
| `docker build --no-cache -t ...:latest` | Builds with the layer cache (much faster; use `NO_CACHE=1` / `-NoCache` only to debug a stale layer). Always `--platform linux/amd64`, so an ARM laptop still produces an image the server can run. |
| only `:latest` | Also tags `:sha-<git-commit>` (and `-dirty` when you have uncommitted changes). This is what you roll back to. |
| `docker push :latest` | Pushes the sha tag first, then `:latest`. |
| click Deploy in Coolify | Prints a reminder, or redeploys automatically if `COOLIFY_WEBHOOK` and `COOLIFY_TOKEN` are set in your shell. |
| `VITE_*` values lost | Read from `.env.build` and passed as build args. |

More than one image (like your Laravel app and an AI server): run the script once per image. The first one reads its name from the compose file; the second lives in a subfolder with no compose file, so give it a name:

```bash
./build-push.sh
CONTEXT=AIServer IMAGE=ghcr.io/kurobapak/project-smartgarden-brin-ai ./build-push.sh
```

```powershell
.\build-push.ps1
.\build-push.ps1 -Context AIServer -Image ghcr.io/kurobapak/project-smartgarden-brin-ai
```

Build only, without pushing: `PUSH=0 ./build-push.sh` or `.\build-push.ps1 -NoPush`.

Before pushing a release, build only and run it locally once if you changed the Dockerfile.

## 4. Server setup (once per server)

Only if the GHCR package is private. SSH in as the user Coolify uses for Docker, with a classic token that has `read:packages`:

```bash
echo "$GH_TOKEN" | docker login ghcr.io --username <github-username> --password-stdin
```

Or make the package public (GitHub → Packages → the image → Package settings → Change visibility). The first push creates it as private.

## 5. Coolify setup (once per app)

1. New Resource → **Docker Compose Empty** → paste `docker-compose.yaml` (with your image and service names).
2. Environment Variables: enter the values from `.env.coolify.example`. Generate the key with `php artisan key:generate --show`.
3. Domains: set the domain on the `app` service, port `8000` (and the `reverb` service, port `8080`, if used).
4. Optional auto-deploy: copy the app's Deploy webhook URL (Webhooks tab) and an API token with the Deploy permission (Settings → API; enable API access first), and export them as `COOLIFY_WEBHOOK` / `COOLIFY_TOKEN` before running the script. The URL is different for every app.
5. Deploy once, then check the container logs for `✅ Application ready!`.

## 6. Day to day

Build and push (section 3), then Deploy in Coolify (or let the script trigger it). Coolify pulls `:latest` (`pull_policy: always`) and restarts.

**Always push before you redeploy.** If the build or push failed, Coolify happily redeploys the old image.

**Migrations.** Either run `php artisan migrate --force` yourself after a deploy, or set `AUTO_MIGRATE=true` so every start runs `migrate --force` (with `--isolated` when `CACHE_STORE` is redis/memcached/file; with `CACHE_STORE=database` the lock table does not exist yet on a fresh database). With the second option a failed migration stops the container on purpose. Keep migrations backward-compatible for one release so a code rollback still works.

**Rollback.** In Coolify set `APP_IMAGE_TAG=sha-<older-commit>` and redeploy. The script prints the tag each time it finishes. Remove the variable to follow `:latest` again.

## 7. Troubleshooting

| Symptom | Cause / fix |
|---|---|
| Build fails at `composer install` with "requires ext-xxx" | Add the extension to stage 0 of the Dockerfile (check 1). |
| `import.meta.env.VITE_*` values are empty in production | The value was not in `.env.build` (or has no `ARG`). Fix it and rebuild the image. |
| 500 on every page | `APP_KEY` missing in Coolify, or `APP_ENV`/`APP_DEBUG` wrong. Check the container logs. |
| "Connection refused" to the database | `DB_HOST` is `127.0.0.1`. Use the database's service hostname. Keep the `coolify` network in the compose file if the DB is a Coolify resource. |
| New migrations never appear | A volume is mounted over `/app/database`. Remove it. |
| `Permission denied` writing `storage/logs/laravel.log` | A process ran as root against the volume. Workers need `user: www-data`; `startup.sh` fixes ownership on every start. |
| Upload fails with HTTP 413 | `client_max_body_size` in `docker/nginx.conf` is below the file size. |
| After login nothing happens until you refresh (Inertia apps), or redirects/assets use `http://` on an HTTPS site | The container only sees plain HTTP behind the Coolify proxy, so Laravel built `http://` redirect URLs and the browser blocked them as mixed content. `docker/nginx.conf` now forwards `X-Forwarded-Proto` to PHP (the `map $http_x_forwarded_proto` block). Apps installed before this fix must copy the new `docker/nginx.conf` and rebuild. The nginx part is a safety net; the documented Laravel fix is `$middleware->trustProxies(at: '*');` in `bootstrap/app.php` (it also gives the real client IP). `init.php` warns when that call is missing. Check with `curl -sI https://your-domain/some-protected-page`: the `Location:` header must start with `https://`. |
| Queued jobs never run | No worker service, or `QUEUE_CONNECTION` does not match. |
| Redeploy shows old code | The push did not happen or failed, or `APP_IMAGE_TAG` is pinned to an old sha. |
| `docker push` says denied | Not logged in to ghcr.io on the laptop, or the token lacks `write:packages`. |
| Coolify cannot pull the image | Package is private and the server is not logged in (section 4). |
| `exec format error` when the container starts | The image was built for the wrong CPU. The script forces `linux/amd64`; if you build by hand, add `--platform linux/amd64`. |

Useful commands on the server:

```bash
docker ps | grep your-app
docker logs <container> --tail 100
docker exec <container> php artisan about
docker exec <container> supervisorctl status
```

## Why it is built this way

- **One shared PHP base stage.** Build and production use the same extensions, so a package cannot pass in one stage and fail in the other.
- **`optimize` runs at container start, not at build.** `config:cache` freezes environment variables. Running it at build would bake the wrong values into the image.
- **Immutable `sha-` tags plus `latest`.** `latest` alone cannot be rolled back and can change under you.
- **One image, many roles.** The queue worker, scheduler and Reverb run the same image with a different command, so they never run mismatched code.
- **`VITE_*` at build time.** Vite compiles them into the bundle. This is the most common "works locally, not on the server" trap.
