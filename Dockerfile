# Base image có PHP-FPM sẵn
FROM php:8.2-fpm

# Cài extension cần thiết cho Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev libzip-dev zip unzip git curl nginx supervisor \
    libpq-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Cài Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Làm việc trong /var/www/html
WORKDIR /var/www/html

# Copy composer file trước để cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy toàn bộ mã nguồn
COPY . .

# Copy cấu hình nginx và supervisord
RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Phân quyền storage và cache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 777 storage bootstrap/cache

# Expose port Render sử dụng
ENV PORT=8080
EXPOSE 8080

# Khởi động: migrate xong rồi start supervisor
CMD ["/usr/bin/supervisord"]