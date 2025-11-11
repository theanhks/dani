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

# PHP-FPM đã được cấu hình để dùng TCP, không cần tìm socket nữa
SOCKET_PATH="127.0.0.1:9000"

# Verify nginx config
echo "📋 Nginx configuration:"
grep "listen" /etc/nginx/conf.d/default.conf || true

# PHP-FPM đã được cấu hình để dùng TCP (127.0.0.1:9000), không cần thư mục socket

# 🔥 Start PHP-FPM trước (background)
echo "🔧 Starting PHP-FPM..."

# Kiểm tra cấu hình PHP-FPM trước khi start
echo "📋 Checking PHP-FPM configuration..."
if [ -f /usr/local/etc/php-fpm.d/www.conf ]; then
  echo "✅ Found www.conf at /usr/local/etc/php-fpm.d/www.conf"
  grep "listen" /usr/local/etc/php-fpm.d/www.conf || true
else
  echo "⚠️  www.conf not found, PHP-FPM will use default config"
fi

# Test PHP-FPM config trước khi start
echo "🔍 Testing PHP-FPM configuration..."
php-fpm -t || {
  echo "❌ PHP-FPM configuration test failed"
  exit 1
}

php-fpm -D

# Đợi một chút để PHP-FPM khởi động
sleep 3
echo "✅ PHP-FPM started"

# Kiểm tra PHP-FPM có đang listen trên TCP port 9000 không
echo "⏳ Checking if PHP-FPM is listening on TCP 127.0.0.1:9000..."
timeout=10
elapsed=0
while [ $elapsed -lt $timeout ]; do
  # Kiểm tra port 9000 (2328 trong hex = 9000)
  if grep -q ":2328 " /proc/net/tcp 2>/dev/null || (command -v nc >/dev/null 2>&1 && nc -z 127.0.0.1 9000 2>/dev/null); then
    echo "✅ PHP-FPM is listening on TCP 127.0.0.1:9000"
    break
  fi
  sleep 1
  elapsed=$((elapsed + 1))
  if [ $elapsed -lt $timeout ]; then
    echo "   Still waiting... ($elapsed seconds)"
  fi
done

if [ $elapsed -ge $timeout ]; then
  echo "⚠️  Could not verify PHP-FPM TCP connection, but continuing anyway..."
fi

echo "✅ PHP-FPM ready at $SOCKET_PATH"

# Test nginx configuration
echo "🔍 Testing Nginx configuration..."
nginx -t || {
  echo "❌ Nginx configuration test failed"
  exit 1
}

# 🔥 Start Nginx ở foreground (Render cần foreground process)
echo "🚀 Starting Nginx on port $PORT..."
exec nginx -g "daemon off;"