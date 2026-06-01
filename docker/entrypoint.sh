#!/usr/bin/env bash
set -e

# Host (Railway/Render) cấp cổng qua $PORT — cho Apache lắng nghe đúng cổng.
PORT="${PORT:-8080}"
sed -ri "s/^Listen 80\$/Listen ${PORT}/" /etc/apache2/ports.conf || true
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf || true

# Chạy migration với retry — MySQL trên Railway có thể lên chậm hơn app.
MIGRATED=0
for i in $(seq 1 20); do
    if php artisan migrate --force; then MIGRATED=1; break; fi
    echo ">> DB chưa sẵn sàng hoặc lỗi migrate — thử lại sau 3s ($i/20)..."
    sleep 3
done

# Seed dữ liệu lần đầu nếu RUN_SEED=true (chỉ khi migrate thành công).
if [ "$MIGRATED" = "1" ] && [ "${RUN_SEED}" = "true" ]; then
    php artisan db:seed --force || true
fi

# Cache cấu hình cho nhanh.
php artisan config:cache || true
php artisan route:cache  || true
php artisan view:cache   || true

exec apache2-foreground
