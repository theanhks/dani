#!/bin/bash
set -e

cd /var/www/html

# Nếu chưa có APP_KEY thì generate (Render không có .env file)
if [ -z "$APP_KEY" ]; then
    if [ -f .env ]; then
        echo "No APP_KEY env, generating new key into .env..."
        php artisan key:generate --force || true
    else
        echo "No .env file and no APP_KEY env. You should set APP_KEY in Render Dashboard!"
    fi
fi

# Chỉ clear + cache config & route (AN TOÀN, không đụng đến view cache nữa)
if [ "$APP_ENV" != "local" ]; then
    echo "Clearing config & route caches..."
    php artisan config:clear || true
    php artisan route:clear || true

    echo "Caching config & routes for better performance..."
    php artisan config:cache || true
    php artisan route:cache || true
fi

# (Tùy chọn) Chạy migrate tự động khi deploy – bỏ comment nếu cần
# echo "Running migrations..."
# php artisan migrate --force --no-interaction || true

# Cập nhật port Nginx theo biến PORT của Render
PORT=${PORT:-8080}
echo "Configuring Nginx to listen on port $PORT..."
sed -i "s/listen [0-9]\+;/listen ${PORT};/g" /etc/nginx/conf.d/default.conf

# Fix quyền storage & bootstrap/cache
echo "Fixing permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 775 storage bootstrap/cache

# Kiểm tra config PHP-FPM
echo "Testing PHP-FPM configuration..."
php-fpm -t || true

# Khởi động PHP-FPM (background)
echo "Starting PHP-FPM..."
php-fpm -D

# Kiểm tra config Nginx
echo "Testing Nginx configuration..."
nginx -t

# Khởi động Nginx (foreground - bắt buộc cho Docker)
echo "Starting Nginx on port $PORT..."
exec nginx -g "daemon off;"