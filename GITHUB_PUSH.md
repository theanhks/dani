# Hướng dẫn Push Code lên GitHub

## Vấn đề hiện tại
SSH key đang authenticate với account `theanh-tran`, nhưng repo thuộc account `theanhks`.

## Giải pháp

### Cách 1: Dùng Personal Access Token (HTTPS) - Khuyến nghị

1. **Tạo Personal Access Token trên GitHub:**
   - Vào: https://github.com/settings/tokens
   - Click "Generate new token (classic)"
   - Chọn scope: `repo` (full control)
   - Copy token (chỉ hiện 1 lần!)

2. **Push code:**
   ```bash
   git push -u origin main
   ```
   - Khi hỏi username: nhập `theanhks`
   - Khi hỏi password: **paste token** (không phải password)

### Cách 2: Add SSH Key vào account theanhks

1. **Copy SSH key:**
   ```bash
   cat ~/.ssh/id_ed25519.pub
   ```

2. **Add vào GitHub:**
   - Login vào account `theanhks`
   - Vào: https://github.com/settings/keys
   - Click "New SSH key"
   - Paste key và save

3. **Đổi lại remote sang SSH:**
   ```bash
   git remote set-url origin git@github.com:theanhks/dani.git
   git push -u origin main
   ```

### Cách 3: Dùng GitHub CLI (gh)

```bash
# Install GitHub CLI
brew install gh

# Login
gh auth login

# Push
git push -u origin main
```

## Kiểm tra remote hiện tại:
```bash
git remote -v
```

