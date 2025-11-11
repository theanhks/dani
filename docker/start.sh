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

# Kiểm tra cấu hình PHP-FPM trước khi start
echo "📋 Checking PHP-FPM configuration..."
if [ -f /usr/local/etc/php-fpm.d/www.conf ]; then
  echo "✅ Found www.conf at /usr/local/etc/php-fpm.d/www.conf"
  grep "listen" /usr/local/etc/php-fpm.d/www.conf || true
else
  echo "⚠️  www.conf not found, PHP-FPM will use default config"
fi

php-fpm -D

# Đợi một chút để PHP-FPM khởi động
sleep 3
echo "✅ PHP-FPM started (checking socket will verify it's running)"

# 🔥 Đợi socket được tạo (tránh 502)
echo "⏳ Waiting for PHP-FPM socket..."
# Tìm socket ở các vị trí có thể
POSSIBLE_SOCKETS=(
  "/var/run/php/php-fpm.sock"
  "/var/run/php/php8.2-fpm.sock"
  "/var/run/php-fpm.sock"
  "/tmp/php-fpm.sock"
  "/tmp/php8.2-fpm.sock"
)

SOCKET_PATH=""
timeout=30
elapsed=0

while [ -z "$SOCKET_PATH" ] && [ $elapsed -lt $timeout ]; do
  for path in "${POSSIBLE_SOCKETS[@]}"; do
    if [ -S "$path" ]; then
      SOCKET_PATH="$path"
      echo "✅ Found socket at: $SOCKET_PATH"
      break
    fi
  done
  
  if [ -z "$SOCKET_PATH" ]; then
    sleep 0.5
    elapsed=$((elapsed + 1))
    if [ $((elapsed % 10)) -eq 0 ]; then
      echo "   Still waiting... ($elapsed seconds)"
      # List all sockets để debug
      find /var/run /tmp -type s -name "*fpm*" 2>/dev/null || true
    fi
  fi
done

if [ -z "$SOCKET_PATH" ]; then
  echo "⚠️  Unix socket not found, checking if PHP-FPM is using TCP..."
  # Thử kiểm tra xem PHP-FPM có đang listen trên TCP port 9000 không
  if command -v nc >/dev/null 2>&1 && nc -z 127.0.0.1 9000 2>/dev/null; then
    echo "✅ PHP-FPM is listening on TCP 127.0.0.1:9000"
    SOCKET_PATH="127.0.0.1:9000"
    # Cập nhật nginx để dùng TCP thay vì Unix socket
    echo "🔧 Updating Nginx config to use TCP: $SOCKET_PATH"
    sed -i "s|fastcgi_pass unix:/var/run/php/php-fpm.sock;|fastcgi_pass $SOCKET_PATH;|g" /etc/nginx/conf.d/default.conf
  else
    echo "❌ PHP-FPM socket not found after $timeout seconds"
    echo "Searching for any PHP-FPM sockets..."
    find /var/run /tmp -type s -name "*fpm*" 2>/dev/null || echo "No sockets found"
    echo "Checking /var/run/php directory:"
    ls -la /var/run/php/ 2>/dev/null || echo "Directory doesn't exist"
    echo "Checking PHP-FPM config files:"
    ls -la /usr/local/etc/php-fpm.d/ 2>/dev/null || true
    exit 1
  fi
else
  # Cập nhật nginx config với socket path thực tế
  echo "🔧 Updating Nginx config to use socket: $SOCKET_PATH"
  sed -i "s|fastcgi_pass unix:/var/run/php/php-fpm.sock;|fastcgi_pass unix:$SOCKET_PATH;|g" /etc/nginx/conf.d/default.conf
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