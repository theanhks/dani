# Hướng dẫn Deploy Laravel lên Hosting Free

## Các lựa chọn Hosting Free cho Laravel

### 1. **Render.com** (Khuyến nghị - Dễ nhất)
- **Free Tier**: 750 giờ/tháng (đủ dùng)
- **Ưu điểm**: 
  - Setup đơn giản, tự động deploy từ GitHub
  - Hỗ trợ Laravel tốt
  - Database PostgreSQL free
  - HTTPS tự động
- **Nhược điểm**: 
  - App sẽ "sleep" sau 15 phút không dùng (free tier)
  - Deploy lần đầu mất ~5-10 phút
- **Link**: https://render.com

### 2. **Railway.app**
- **Free Tier**: $5 credit/tháng (đủ cho app nhỏ)
- **Ưu điểm**:
  - Deploy nhanh, tự động từ GitHub
  - Database PostgreSQL free
  - Không sleep như Render
  - Hỗ trợ Redis, Queue
- **Nhược điểm**: 
  - Credit có hạn, cần upgrade nếu dùng nhiều
- **Link**: https://railway.app

### 3. **Fly.io**
- **Free Tier**: 3 VMs nhỏ, 3GB storage, 160GB bandwidth
- **Ưu điểm**:
  - VPS thật, không bị giới hạn nhiều
  - Hỗ trợ tốt cho Laravel
  - Có thể scale dễ dàng
- **Nhược điểm**: 
  - Setup phức tạp hơn một chút
  - Cần CLI tool
- **Link**: https://fly.io

### 4. **InfinityFree** (Traditional Hosting)
- **Free Tier**: Unlimited
- **Ưu điểm**:
  - Không giới hạn bandwidth
  - cPanel quen thuộc
- **Nhược điểm**: 
  - Không hỗ trợ Laravel tốt (cần setup thủ công)
  - Hạn chế về performance
  - Quảng cáo có thể hiển thị
- **Link**: https://infinityfree.net

### 5. **Oracle Cloud Free Tier** (VPS thật)
- **Free Tier**: 2 VMs, 100GB storage, 10TB bandwidth
- **Ưu điểm**:
  - VPS hoàn toàn free, không giới hạn
  - Full control
  - Performance tốt
- **Nhược điểm**: 
  - Cần setup server từ đầu (Linux, Nginx, PHP, MySQL)
  - Phức tạp hơn cho người mới
  - Cần credit card (nhưng không charge)
- **Link**: https://www.oracle.com/cloud/free/

## Khuyến nghị

### Cho người mới bắt đầu:
**Render.com** - Dễ nhất, setup trong 10 phút

### Cho người có kinh nghiệm:
**Railway.app** hoặc **Fly.io** - Performance tốt hơn

### Cho người muốn học VPS:
**Oracle Cloud Free Tier** - Full control, học được nhiều

## Chuẩn bị Deploy

### 1. Cập nhật `.env` cho production:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.onrender.com

# Database (từ hosting provider)
DB_CONNECTION=pgsql
DB_HOST=xxx
DB_PORT=5432
DB_DATABASE=xxx
DB_USERNAME=xxx
DB_PASSWORD=xxx

# Mail (Gmail App Password)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Your App Name"
MAIL_ADMIN_EMAIL=admin@example.com
```

### 2. Thêm file `.env.example`:
```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"
MAIL_ADMIN_EMAIL=
```

### 3. Tạo file `render.yaml` (cho Render.com):
```yaml
services:
  - type: web
    name: laravel-app
    env: php
    buildCommand: composer install --no-dev && php artisan key:generate && php artisan migrate --force
    startCommand: php artisan serve --host=0.0.0.0 --port=$PORT
    envVars:
      - key: APP_ENV
        value: production
      - key: APP_DEBUG
        value: false
      - key: LOG_CHANNEL
        value: stderr
```

### 4. Tạo file `Procfile` (cho Railway/Heroku):
```
web: vendor/bin/heroku-php-apache2 public/
```

## Checklist trước khi deploy:

- [ ] Commit code lên GitHub
- [ ] Update `.env` production values
- [ ] Set `APP_DEBUG=false`
- [ ] Generate `APP_KEY` mới
- [ ] Test database connection
- [ ] Test mail sending
- [ ] Run migrations
- [ ] Clear cache: `php artisan config:clear && php artisan cache:clear`

