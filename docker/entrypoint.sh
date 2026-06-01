#!/usr/bin/env bash
set -e

# Host (Railway/Render) cấp cổng qua $PORT — cho Apache lắng nghe đúng cổng.
PORT="${PORT:-8080}"
sed -ri "s/^Listen 80\$/Listen ${PORT}/" /etc/apache2/ports.conf || true
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf || true

# Chạy migration mỗi lần deploy (an toàn, idempotent).
php artisan migrate --force || true

# Seed dữ liệu lần đầu nếu đặt RUN_SEED=true (chi nhánh, bàn, menu, tồn kho, superadmin).
if [ "${RUN_SEED}" = "true" ]; then
    php artisan db:seed --force || true
fi

# Cache cấu hình cho nhanh.
php artisan config:cache || true
php artisan route:cache  || true
php artisan view:cache   || true

exec apache2-foreground
