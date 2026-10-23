# =============================================================
# Laravel (+ Vite / Inertia) — production Dockerfile
# Target: image built on your machine (build-push.sh / build-push.ps1),
# pushed to GHCR, pulled by Coolify.
#
# Stages:
#   php-base   PHP + extensions (the ONLY place to edit extensions)
#   builder    composer install + npm build (discarded afterwards)
#   production nginx + php-fpm + supervisor serving the app on :8000
#
# Builder and production both start from php-base, so a Composer
# package that needs an extension (ext-gd, ext-intl, ...) cannot
# pass in one stage and fail in the other.
# =============================================================

ARG PHP_VERSION=8.4

# -------------------------------------------------------------
# Stage 0: PHP base — add PHP extensions HERE
# -------------------------------------------------------------
FROM php:${PHP_VERSION}-fpm-alpine AS php-base

RUN apk add --no-cache \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        oniguruma-dev \
        icu-dev \
        postgresql-dev \
        sqlite-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        opcache

# Extra extensions found by init.php (PHP extensions that composer
# packages require). This block is rewritten every time init.php runs:
# put hand-made additions ABOVE it, not inside it.
# >>> deploy-pack:extra-extensions
# (none needed: your composer packages only use extensions the image already has)
# <<< deploy-pack:extra-extensions

# -------------------------------------------------------------
# Stage 1: build PHP dependencies and frontend assets
# -------------------------------------------------------------
FROM php-base AS builder

WORKDIR /app

RUN apk add --no-cache nodejs npm git unzip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Dependencies first (cached until composer.lock / package-lock.json change)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader

# >>> deploy-pack:frontend-deps
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
# <<< deploy-pack:frontend-deps

# Application source
COPY . .

# Needed before the Vite build: Wayfinder runs `php artisan` during it
RUN composer dump-autoload --optimize --no-dev --no-scripts

# VITE_* values are compiled into the JS bundle, so they must be passed at
# build time (--build-arg, filled from .env.build by the build script),
# NOT as runtime environment variables. init.php writes one ARG per
# VITE_* variable the frontend reads (import.meta.env.VITE_*).
# Empty args are unset first: an empty string would defeat the
# `import.meta.env.VITE_X ?? "default"` fallbacks in the frontend code.
# >>> deploy-pack:vite-build
ARG VITE_APP_NAME=
RUN for v in VITE_APP_NAME; do \
        eval "[ -n \"\$$v\" ] || unset $v"; \
    done; \
    npm run build
# <<< deploy-pack:vite-build

RUN rm -rf node_modules

# -------------------------------------------------------------
# Stage 2: production image
# -------------------------------------------------------------
FROM php-base AS production

RUN apk add --no-cache nginx supervisor curl wget

WORKDIR /app

COPY --from=builder /app /app

# .dockerignore strips these directories' contents, so recreate them.
RUN mkdir -p storage/app/public storage/framework/cache storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

RUN rm -f /etc/nginx/http.d/default.conf
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# config:cache must run at START (needs the real runtime env vars),
# never at build time. startup.sh does this.
COPY docker/startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

EXPOSE 8000

CMD ["/usr/local/bin/startup.sh"]
