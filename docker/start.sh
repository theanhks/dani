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
echo "🌐 Configuring Nginx to listen on port $PORT"
sed -i "s/listen 8080;/listen $PORT;/g" /etc/nginx/conf.d/default.conf

# Verify nginx config
echo "📋 Nginx configuration:"
grep "listen" /etc/nginx/conf.d/default.conf || true

# Đảm bảo thư mục run cho PHP-FPM
mkdir -p /var/run/php
chown -R www-data:www-data /var/run/php

# 🔥 Start PHP-FPM trước (background)
echo "🔧 Starting PHP-FPM..."
php-fpm -D

# Đợi một chút để PHP-FPM khởi động
sleep 2
echo "✅ PHP-FPM started (checking socket will verify it's running)"

# 🔥 Đợi socket được tạo (tránh 502)
echo "⏳ Waiting for PHP-FPM socket..."
SOCKET_PATH="/var/run/php/php-fpm.sock"
timeout=30
elapsed=0
while [ ! -S "$SOCKET_PATH" ] && [ $elapsed -lt $timeout ]; do
  sleep 0.5
  elapsed=$((elapsed + 1))
done

if [ ! -S "$SOCKET_PATH" ]; then
  echo "❌ PHP-FPM socket not found at $SOCKET_PATH after $timeout seconds"
  echo "Checking PHP-FPM status..."
  ps aux | grep php-fpm || true
  ls -la /var/run/php/ || true
  exit 1
fi

echo "✅ PHP-FPM socket ready at $SOCKET_PATH"

# Test nginx configuration
echo "🔍 Testing Nginx configuration..."
nginx -t || {
  echo "❌ Nginx configuration test failed"
  exit 1
}

# 🔥 Start Nginx ở foreground (Render cần foreground process)
echo "🚀 Starting Nginx on port $PORT..."
exec nginx -g "daemon off;"