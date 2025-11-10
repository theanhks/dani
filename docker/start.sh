#!/bin/bash
set -e

# Generate APP_KEY nếu chưa có
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Cache config, route, view (trừ local)
if [ "$APP_ENV" != "local" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Update Nginx config với PORT từ Render
PORT=${PORT:-8080}
sed -i "s/listen 8080;/listen $PORT;/g" /etc/nginx/conf.d/default.conf

# Đảm bảo thư mục run cho PHP-FPM
mkdir -p /var/run/php
chown www-data:www-data /var/run/php

# Khởi động Nginx trước (background)
nginx

# Giữ PHP-FPM foreground để Render detect port
exec php-fpm -F