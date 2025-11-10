#!/bin/bash
set -e

# Generate APP_KEY nếu chưa có
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Cache config và routes (chỉ khi không phải dev)
if [ "$APP_ENV" != "local" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Chạy migrations (optional - bỏ comment nếu cần)
# if [ "$APP_ENV" = "production" ]; then
#     php artisan migrate --force
# fi

# Update Nginx config với PORT từ env (Render inject $PORT)
PORT=${PORT:-8080}
sed -i "s/listen 8080;/listen $PORT;/g" /etc/nginx/conf.d/default.conf

# Đảm bảo thư mục run cho PHP-FPM
mkdir -p /var/run/php
chown www-data:www-data /var/run/php

# Start PHP-FPM foreground (không daemonize)
php-fpm -F

# Start Nginx foreground
nginx -g 'daemon off;'