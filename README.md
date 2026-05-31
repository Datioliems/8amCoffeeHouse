# 8AM Coffee & Roastery — QR Order System
**Nhóm 29 | TTCN | Học viện Ngân hàng**

## Công nghệ
- **Backend**: Laravel 11 (PHP 8.2)
- **Frontend**: Blade + Tailwind CSS v4 + Alpine.js v3
- **Database**: SQL Server (sqlsrv driver)
- **QR Code**: simplesoftwareio/simple-qrcode

---

## Cài đặt ban đầu (Văn Quang làm 1 lần)

```bash
# 1. Clone repo
git clone https://github.com/nhom29/8am-coffee.git
cd 8am-coffee

# 2. Cài PHP dependencies
composer install

# 3. Cài Node dependencies
npm install

# 4. Copy file môi trường
cp .env.example .env

# 5. Sửa .env: điền DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 6. Tạo app key
php artisan key:generate

# 7. Chạy file SQL trên SQL Server Management Studio (SSMS):
#    Mở file database/8am_coffee_database.sql → Execute

# 8. Build CSS/JS
npm run build

# 9. Chạy server
php artisan serve
```

Truy cập: http://localhost:8000/login
- Tài khoản: `admin_8am` / `Admin@123`

---

## Quy trình làm việc nhóm

### Nhánh Git của mỗi người
| Thành viên | Nhánh | Phạm vi |
|---|---|---|
| Văn Quang | `feature/backend-order` | Controllers Auth/Order/Payment, Models, Migration, Routes |
| Tuấn Đạt | `feature/frontend` | `resources/views/**`, `resources/js/`, `resources/css/` |
| Minh Hiếu | `feature/inventory` | Controllers Inventory/Import/StockCheck, Models kho |

### Commit hàng ngày
```bash
git add [chỉ file của mình]
git commit -m "feat(fe): add order dashboard view"
git push origin feature/frontend
```

### Quy tắc không conflict
- **`routes/web.php`**: Chỉ Văn Quang sửa. Hiếu nhắn route cần → Quang thêm.
- **`.env`**: Không commit. Chỉ commit `.env.example`.
- **Migrations**: Chỉ Quang tạo. Không tự tạo migration.

---

## Cấu trúc thư mục

```
app/Http/Controllers/   ← Quang (Auth,Order,Payment,Menu,Qr) + Hiếu (Inventory,Import,StockCheck)
app/Models/             ← Mỗi người tạo model của module mình
app/Services/           ← Business logic tách khỏi controller
resources/views/        ← Tuấn Đạt toàn bộ
routes/web.php          ← Văn Quang quản lý
database/               ← Quang quản lý (migration + seeder)
```

---

## Luồng chính

**Khách hàng**:
`/order/{ma_ban}` → Nhập tên → `/order/{ma_ban}/menu` → Chọn món → `/order/{ma_order}/cart` → Đặt món

**Nhân viên**:
`/login` → `/orders` → Xác nhận đơn → `/payment/{ma_order}` → Thanh toán
