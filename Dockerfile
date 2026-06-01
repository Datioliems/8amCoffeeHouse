# ─────────────────────────────────────────────────────────────
# 8AM Coffee — Dockerfile (Railway / Render / bất kỳ host Docker nào)
# Multi-stage: build asset Vite bằng Node, rồi chạy PHP + Apache.
# ─────────────────────────────────────────────────────────────

# Stage 1: build CSS/JS (Three.js, Alpine, Tailwind)
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json vite.config.js ./
RUN npm ci
COPY resources resources
COPY public public
RUN npm run build

# Stage 2: PHP runtime
FROM php:8.2-apache

# Extension cần cho Laravel + MySQL
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev unzip git \
 && docker-php-ext-install pdo_mysql bcmath zip \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
# Lấy asset đã build từ stage 1
COPY --from=assets /app/public/build public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction \
 && chown -R www-data:www-data storage bootstrap/cache public/images

# Apache document root -> thư mục public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080
CMD ["/usr/local/bin/entrypoint.sh"]
