# Dockerfile - Laravel 12 + PHP 8.2 + Nginx cho Render

FROM php:8.2-fpm

# Cài tiện ích & extension
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    zip unzip git curl nginx \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy composer từ image composer official
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy file composer trước để cache install
COPY composer.json composer.lock ./

# Install dependencies (CHO PHÉP chạy scripts của composer)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy toàn bộ source vào container
COPY . .

# Xoá default nginx config, copy config riêng
RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Thay www.conf của php-fpm bằng bản custom
RUN if [ -f /usr/local/etc/php-fpm.d/www.conf ]; then \
      mv /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf.bak; \
    fi
COPY docker/www.conf /usr/local/etc/php-fpm.d/www.conf

# Script start
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Quyền cho Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Render sẽ set env PORT, mình để default là 8080
ENV PORT=8080

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]