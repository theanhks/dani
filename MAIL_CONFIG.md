# Mail Configuration Guide

## Gmail Configuration

Thêm các biến sau vào file `.env`:

```env
# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Gmail App Password Setup

1. Đăng nhập vào Google Account
2. Vào **Security** → **2-Step Verification** (bật nếu chưa có)
3. Vào **Security** → **App passwords**
4. Tạo App Password mới cho "Mail"
5. Copy password và dán vào `MAIL_PASSWORD` trong `.env`

**Lưu ý:** Không dùng mật khẩu Gmail thông thường, phải dùng App Password.

## Usage Example

```php
use App\Services\MailService;

// Send simple email
$mailService = new MailService();
$mailService->send(
    'recipient@example.com',
    'Subject',
    '<h1>Hello</h1><p>This is a test email.</p>'
);

// Send email with view
$mailService->sendWithView(
    'recipient@example.com',
    'Subject',
    'emails.welcome',
    ['name' => 'John Doe']
);

// Send to multiple recipients
$mailService->sendMultiple(
    ['user1@example.com', 'user2@example.com'],
    'Subject',
    '<p>Email content</p>'
);
```


