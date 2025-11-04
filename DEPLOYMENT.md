# Hướng dẫn Deploy Laravel lên Hosting Free

## Các lựa chọn Hosting Free cho Laravel

### 1. **Railway.app** (Khuyến nghị - Dễ nhất cho Laravel)
- **Free Tier**: $5 credit/tháng (đủ cho app nhỏ)
- **Ưu điểm**: 
  - Hỗ trợ PHP/Laravel native (không cần Docker)
  - Setup siêu đơn giản, tự động detect Laravel
  - Database PostgreSQL free
  - HTTPS tự động
  - Không sleep như Render
  - Deploy nhanh (~2-3 phút)
- **Nhược điểm**: 
  - Credit có hạn, nhưng đủ cho app nhỏ
- **Link**: https://railway.app

### 1b. **Render.com** (Cần Docker)
- **Free Tier**: 750 giờ/tháng
- **Ưu điểm**: 
  - Database PostgreSQL free
  - HTTPS tự động
- **Nhược điểm**: 
  - **KHÔNG hỗ trợ PHP native**, phải dùng Docker
  - App sẽ "sleep" sau 15 phút (free tier)
  - Setup phức tạp hơn (cần Dockerfile)
- **Link**: https://render.com

### 2. **Fly.io**
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
**Railway.app** - Dễ nhất, hỗ trợ PHP native, setup trong 5 phút

### Cho người muốn dùng Docker:
**Render.com** - Cần Dockerfile (đã có sẵn trong repo)

### Cho người có kinh nghiệm:
**Fly.io** - VPS thật, performance tốt

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

