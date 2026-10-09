# =============================================================
# DFM — Multi-stage Dockerfile for Laravel 13 + Inertia React
# Target: Production deployment via Coolify
# =============================================================

# Stage 1: Build dependencies and assets
FROM php:8.4-fpm-alpine AS builder

WORKDIR /app

# Install Node.js, npm, composer, and required zip extension for composer
RUN apk add --no-cache nodejs npm git unzip curl zip libzip-dev \
    && docker-php-ext-install zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files and install PHP dependencies first 
# (Needed by Wayfinder/Ziggy during Vite build)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# Copy npm files and install Node dependencies
COPY package*.json ./
COPY pnpm-workspace.yaml ./
RUN npm ci --no-audit --no-fund

# Copy the rest of the application
COPY . .

# Build frontend assets (Vite will run php artisan wayfinder:generate automatically)
RUN npm run build

# Remove node_modules to keep the production image clean
RUN rm -rf node_modules

# Stage 2: Production PHP image
FROM php:8.4-fpm-alpine AS production

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    sqlite \
    sqlite-dev \
    curl \
    wget \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
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

# Set working directory
WORKDIR /app

# Copy built application from builder stage
COPY --from=builder /app /app

# Set permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Create SQLite database if it doesn't exist
RUN mkdir -p /app/database \
    && touch /app/database/database.sqlite \
    && chown -R www-data:www-data /app/database \
    && chmod -R 775 /app/database

# Configure nginx
RUN rm /etc/nginx/http.d/default.conf
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Configure PHP-FPM
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Configure Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Optimize Laravel
RUN php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true

# Expose port
EXPOSE 8000

# Start services via supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

