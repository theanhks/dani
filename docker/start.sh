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

# Chạy migrations (optional - có thể comment nếu không muốn auto migrate)
# php artisan migrate --force

# Update Nginx config với PORT từ env (Render inject $PORT)
PORT=${PORT:-8080}
sed -i "s/listen 8080;/listen $PORT;/g" /etc/nginx/sites-available/default

# Start PHP-FPM và Nginx
php-fpm -D
nginx -g 'daemon off;'

