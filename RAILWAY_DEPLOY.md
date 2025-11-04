# Hướng dẫn Deploy Laravel lên Railway.app (Khuyến nghị)

## Railway hỗ trợ PHP/Laravel native - KHÔNG CẦN DOCKER!

### Bước 1: Tạo tài khoản Railway
- Vào: https://railway.app
- Sign up với GitHub

### Bước 2: Deploy từ GitHub
1. Click "New Project"
2. Chọn "Deploy from GitHub repo"
3. Chọn repo `theanhks/dani`
4. Railway tự động detect Laravel và setup!

### Bước 3: Thêm Database PostgreSQL
1. Trong project, click "+ New"
2. Chọn "Database" → "PostgreSQL"
3. Railway tự động tạo và inject connection string

### Bước 4: Cấu hình Environment Variables
Trong Settings → Variables, thêm:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:... (generate bằng: php artisan key:generate --show)
APP_URL=https://your-app.railway.app

# Database (Railway tự động inject, nhưng có thể override)
DB_CONNECTION=pgsql

# Mail (Gmail)
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

### Bước 5: Chạy Migrations
1. Vào tab "Deployments"
2. Click vào deployment mới nhất
3. Mở "Logs" tab
4. Hoặc dùng Railway CLI:
```bash
railway run php artisan migrate
```

### Bước 6: Xong!
Railway tự động:
- ✅ Detect Laravel
- ✅ Install dependencies (`composer install`)
- ✅ Chạy `php artisan serve`
- ✅ Setup HTTPS
- ✅ Deploy tự động mỗi khi push code

## Lưu ý:

1. **Storage**: Railway free tier không persist `/storage`, nên:
   - Dùng S3 hoặc cloud storage cho uploads
   - Hoặc dùng database để store

2. **Queue/Jobs**: Nếu có, tạo thêm Worker service:
   - Click "+ New" → "Empty Service"
   - Command: `php artisan queue:work`

3. **Scheduled Tasks**: Dùng Railway Cron hoặc external cron service

## So sánh với Render:

| Feature | Railway | Render |
|---------|---------|--------|
| PHP Native | ✅ Có | ❌ Không (cần Docker) |
| Setup | ⚡ Rất đơn giản | 🔧 Phức tạp (Docker) |
| Sleep | ❌ Không | ✅ Có (free tier) |
| Database | ✅ Free | ✅ Free |
| Deploy Time | ~2-3 phút | ~10-15 phút |

## Troubleshooting:

- **Build fails**: Check logs trong Railway dashboard
- **Database connection**: Đảm bảo DB service đã được tạo
- **APP_KEY**: Generate trước và paste vào env vars

