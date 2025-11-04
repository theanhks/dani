# Sử dụng image PHP chính thức có Composer
FROM php:8.2-fpm

# Cài đặt các extension cần thiết
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    nginx \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Cài Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Tạo thư mục app
WORKDIR /var/www/html

# Copy composer files trước để cache dependency
COPY composer.json composer.lock ./

# Cài đặt dependency (không dev dependencies)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy toàn bộ code
COPY . .

# Copy config Nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Chạy composer scripts (nếu có)
RUN composer dump-autoload --optimize

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expose port (Render sẽ inject $PORT)
ENV PORT=8080
EXPOSE 8080

# Start script
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]

