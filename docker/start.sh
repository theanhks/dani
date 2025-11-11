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
chown -R www-data:www-data /var/run/php

# 🔥 Start PHP-FPM trước (background)
php-fpm -D

# 🔥 Đợi socket được tạo (tránh 502)
echo "⏳ Waiting for PHP-FPM socket..."
while [ ! -S /var/run/php/php8.2-fpm.sock ]; do
  sleep 0.5
done
echo "✅ PHP-FPM socket ready."

# 🔥 Start Nginx ở foreground (Render cần foreground process)
exec nginx -g "daemon off;"