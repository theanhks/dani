# Hướng dẫn Deploy Laravel lên Render với Docker

## Render không hỗ trợ PHP native, nhưng hỗ trợ Docker!

### Các file đã tạo:
- ✅ `Dockerfile` - Build image với PHP-FPM + Nginx
- ✅ `docker/nginx.conf` - Config Nginx
- ✅ `docker/start.sh` - Script khởi động
- ✅ `.dockerignore` - Loại trừ files không cần thiết

## Cách Deploy trên Render:

### 1. Tạo Web Service trên Render:
- Vào: https://dashboard.render.com
- Click "New +" → "Web Service"
- Connect GitHub repo `theanhks/dani`
- Chọn:
  - **Environment**: `Docker`
  - **Branch**: `main`
  - **Root Directory**: (để trống)
  - **Dockerfile Path**: `Dockerfile` (hoặc để trống)

### 2. Environment Variables cần thêm:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.onrender.com
APP_KEY=base64:... (generate bằng: php artisan key:generate --show)

DB_CONNECTION=pgsql
DB_HOST=xxx.render.com
DB_PORT=5432
DB_DATABASE=xxx
DB_USERNAME=xxx
DB_PASSWORD=xxx

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Your App"
MAIL_ADMIN_EMAIL=admin@example.com
```

### 3. Tạo PostgreSQL Database:
- Click "New +" → "PostgreSQL"
- Chọn "Free" plan
- Copy connection string và điền vào env vars

### 4. Deploy:
- Click "Create Web Service"
- Render sẽ tự động build Docker image và deploy
- Lần đầu mất ~10-15 phút

## Lưu ý:

1. **Port**: Render sẽ tự động inject biến `$PORT`, Dockerfile đã được cấu hình để dùng port này
2. **Migrations**: Uncomment dòng `php artisan migrate --force` trong `docker/start.sh` nếu muốn auto migrate
3. **Storage**: Cần setup storage link hoặc dùng S3 cho file uploads (free tier không persist data)
4. **Queue/Jobs**: Nếu có, cần tạo thêm Worker service

## Troubleshooting:

- **Build fails**: Check logs trong Render dashboard
- **502 Bad Gateway**: Kiểm tra PHP-FPM và Nginx đã start chưa
- **Database connection**: Đảm bảo DB env vars đúng
- **APP_KEY**: Phải set trước khi deploy, hoặc script sẽ tự generate

## Alternative: Dùng Railway.app (Dễ hơn với PHP)

Railway hỗ trợ PHP native và Laravel tốt hơn:
- Chọn "Deploy from GitHub"
- Chọn repo
- Railway tự detect Laravel và setup
- Chỉ cần thêm env vars

