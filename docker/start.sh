#!/bin/bash
set -e

cd /var/www/html

# Nếu chưa có APP_KEY env thì tạm generate vào .env
if [ -z "$APP_KEY" ]; then
  if [ -f .env ]; then
    echo "⚙️  No APP_KEY env, generating new key into .env..."
    php artisan key:generate --force || true
  else
    echo "⚠️  No .env file and no APP_KEY env. You should set APP_KEY in Render."
  fi
fi

# Clear/cache config, routes, views nếu không phải local
if [ "$APP_ENV" != "local" ]; then
  echo "🧹 Clearing caches..."
  php artisan config:clear || true
  php artisan route:clear || true
  php artisan view:clear || true

  echo "📦 Caching config/routes/views..."
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
fi

# (OPTIONAL) migrate khi container start – nếu muốn thì bỏ comment:
# echo "🛢  Running migrations..."
# php artisan migrate --force || true

# Cập nhật Nginx listen bằng PORT của Render
PORT=${PORT:-8080}
echo "🌐 Configuring Nginx to listen on port $PORT"
sed -i "s/listen 8080;/listen ${PORT};/g" /etc/nginx/conf.d/default.conf

# Quyền cho storage + cache
echo "🔧 Fixing permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 775 storage bootstrap/cache

echo "🔍 Testing PHP-FPM configuration..."
php-fpm -t

echo "🚀 Starting PHP-FPM..."
php-fpm -D

echo "🔍 Testing Nginx configuration..."
nginx -t

echo "🚀 Starting Nginx on port $PORT..."
exec nginx -g "daemon off;"