# 🚀 Quick Start Guide - Lịch Học Cố Định

## ⚡ 30-Second Setup

```bash
# 1. Chạy setup script
php setup_recurring_schedules.php

# 2. Truy cập ứng dụng
# Admin: http://localhost/GiaSuhai/?url=schedule/recurring

# 3. Tạo lịch cố định (ngày 01-07 mỗi tháng)
```

---

## 📱 Các Trang Chính

### 1. Quản Lý Lịch Cố Định
**URL:** `?url=schedule/recurring`

- ✅ Xem danh sách lịch cố định
- ➕ Tạo lịch mới
- ✏️ Chỉnh sửa lịch
- 🗑️ Xoá lịch

### 2. Chỉnh Sửa Lịch Cố Định
**URL:** `?url=schedule/editRecurring&id={id}`

- Cập nhật thông tin lịch
- Thay đổi ngày/buổi
- Cập nhật môn học

### 3. Lịch Học Thường (Cũ)
**URL:** `?url=schedule/index`

- Xem lịch học hàng ngày
- Xem lịch từ booking

---

## 📋 Form Tạo Lịch Cố Định

```
┌─────────────────────────────┐
│  Tạo Lịch Học Cố Định       │
├─────────────────────────────┤
│ Gia Sư:        [Dropdown]   │
│ Học Viên:      [Dropdown]   │
│ Môn Học:       [Textbox]    │
│ Ngày/Tuần:     [Dropdown]   │
│  - Thứ 2, Thứ 3, ...        │
│ Buổi Học:      [Dropdown]   │
│  - Sáng (7-11)              │
│  - Chiều (14-17)            │
├─────────────────────────────┤
│ [✅ Tạo Lịch Cố Định]       │
└─────────────────────────────┘
```

---

## ⏰ Thời Gian Học

### Buổi Sáng (7:00 - 11:00)
- Bắt đầu: 7:00 AM
- Kết thúc: 11:00 AM  
- Thời lượng: 4 tiếng

### Buổi Chiều (14:00 - 17:00)
- Bắt đầu: 2:00 PM
- Kết thúc: 5:00 PM
- Thời lượng: 3 tiếng

---

## 📅 Ngày Trong Tuần

```
0 = Thứ 2 (Monday)
1 = Thứ 3 (Tuesday)
2 = Thứ 4 (Wednesday)
3 = Thứ 5 (Thursday)
4 = Thứ 6 (Friday)
5 = Thứ 7 (Saturday)
6 = Chủ Nhật (Sunday)
```

---

## 🔐 Quyền Hạn

| Role | Tạo | Sửa | Xoá | Xem |
|------|-----|-----|-----|-----|
| Admin | ✅ | ✅ | ✅ | ✅ |
| Tutor | ❌ | ✅* | ❌ | ✅ |
| Student | ❌ | ❌ | ❌ | ✅ |

*Tutor chỉ có thể sửa lịch của mình

---

## 📌 Hạn Đăng Ký

**Chỉ từ ngày 01-07 của mỗi tháng**

```
May 2026:
01 [✅] - Được
02 [✅] - Được
03 [✅] - Được
04 [✅] - Được
05 [✅] - Được
06 [✅] - Được
07 [✅] - Được
08 [❌] - Không được
...
31 [❌] - Không được
```

---

## 🧪 Test & Verification

### Chạy Test
```
Truy cập: http://localhost/GiaSuhai/test_recurring_schedule.php
```

### Kiểm Tra
```
✅ Test 1: Registration Status
✅ Test 2: Session Information
✅ Test 3: Day Names
✅ Test 4: Count Schedules
✅ Test 5: Get Dates
✅ Test 6: Generate Schedules
✅ Test 7: Day Conversion
```

---

## 💾 Database Queries

### Xem Tất Cả Lịch Cố Định
```sql
SELECT * FROM lich_hoc_hang_tuan WHERE trang_thai = 1;
```

### Xem Lịch Của Một Gia Sư
```sql
SELECT * FROM lich_hoc_hang_tuan 
WHERE gia_su = 1 AND trang_thai = 1;
```

### Xem Lịch Của Một Học Viên
```sql
SELECT * FROM lich_hoc_hang_tuan 
WHERE hoc_vien = 5 AND trang_thai = 1;
```

### Đếm Số Lịch Cố Định
```sql
SELECT COUNT(*) as total FROM lich_hoc_hang_tuan 
WHERE trang_thai = 1;
```

---

## 🎨 UI Elements

### Badge - Ngày Trong Tuần
```
┌──────────┐
│ Thứ 2    │
└──────────┘
Màu: Xanh nhạt (#e3f2fd)
```

### Badge - Buổi Học
```
Sáng:     Cam (#fff3e0)
Chiều:    Tím (#f3e5f5)
```

### Badge - Trạng Thái
```
Hoạt động:  Xanh (#c8e6c9)
Đã xoá:     Đỏ (#ffcdd2)
```

---

## 🐛 Error Messages

| Lỗi | Nguyên Nhân | Giải Pháp |
|-----|-----------|---------|
| "Chỉ được từ ngày 01-07" | Ngoài giai đoạn | Chờ đến ngày 01 tháng sau |
| "Gia sư đã có lịch" | Trùng lịch | Xoá lịch cũ hoặc chọn ngày khác |
| "ID không hợp lệ" | ID ≤ 0 | Kiểm tra URL |
| "Không được sửa lịch này" | Không có quyền | Kiểm tra role |

---

## 🔄 Workflow Điển Hình

### Admin Tạo Lịch Cố Định

```
1. Đăng nhập Admin
   ↓
2. Vào ?url=schedule/recurring
   ↓
3. Điền form tạo lịch
   - Chọn gia sư
   - Chọn học viên
   - Nhập môn học
   - Chọn ngày
   - Chọn buổi
   ↓
4. Click "Tạo Lịch Cố Định"
   ↓
5. Hệ thống tạo lịch & sinh lịch tháng
   ↓
6. Thành công! ✅
```

### Tutor Xem & Chỉnh Sửa Lịch

```
1. Đăng nhập Tutor
   ↓
2. Vào ?url=schedule/recurring
   ↓
3. Xem danh sách lịch của mình
   ↓
4. Click "Sửa" để chỉnh sửa
   ↓
5. Cập nhật môn học hoặc ngày/buổi
   ↓
6. Click "Cập Nhật"
   ↓
7. Thành công! ✅
```

---

## 📚 Documentation Links

- 📖 [Tài Liệu Đầy Đủ](SCHEDULE_RECURRING_DOCS.md)
- 📋 [Tóm Tắt Thay Đổi](IMPLEMENTATION_SUMMARY.md)
- 🧪 [Test & Examples](test_recurring_schedule.php)
- 🔧 [Setup Script](setup_recurring_schedules.php)

---

## 🆘 Need Help?

1. Kiểm tra câu hỏi thường gặp trong [Tài Liệu](SCHEDULE_RECURRING_DOCS.md)
2. Chạy test: `test_recurring_schedule.php`
3. Kiểm tra logs database
4. Liên hệ Support

---

**Version:** 1.0
**Last Updated:** May 14, 2026
**Status:** ✅ Production Ready
