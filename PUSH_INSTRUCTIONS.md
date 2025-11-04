# Hướng dẫn Push Code - Quick Guide

## Hiện tại đã chuyển về HTTPS

Để push code, bạn cần **Personal Access Token**:

### 1. Tạo Token:
- Vào: https://github.com/settings/tokens
- Click "Generate new token (classic)"
- Đặt tên: `site-base-push`
- Chọn scope: ✅ **repo** (full control)
- Click "Generate token"
- **Copy token ngay** (chỉ hiện 1 lần!)

### 2. Push code:
```bash
git push -u origin main
```

Khi hỏi:
- **Username**: `theanhks`
- **Password**: **paste token** (không phải password GitHub)

### Hoặc dùng token trực tiếp:
```bash
git push -u https://YOUR_TOKEN@github.com/theanhks/dani.git main
```

---

## Nếu muốn dùng SSH (sau này):

1. Đảm bảo key này đã được add vào GitHub:
   ```
   ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIOmnowluPA7MxdvsVt+zuYD+DQS+b4omT2eoZoFIb2ep anh.tran@cubicstack.net
   ```

2. Test: `ssh -T git@github.com-theanhks`

3. Push: `git push -u origin main`

