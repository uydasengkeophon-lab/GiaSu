# ✅ Pre-Deployment Checklist

## 📋 Database Setup

- [ ] Bảng `lich_hoc_hang_tuan` tạo thành công
- [ ] Tất cả cột có đúng kiểu dữ liệu
- [ ] Foreign key tới `tutors` & `users` được tạo
- [ ] Indexes được tạo (gia_su, hoc_vien)
- [ ] Default values đúng (trang_thai = 1)

**Verify:**
```sql
DESCRIBE lich_hoc_hang_tuan;
SHOW TABLES LIKE 'lich_hoc_hang_tuan';
```

---

## 🔧 Model Implementation

- [ ] `Schedule.php` có phương thức `createRecurringSchedule()`
- [ ] `Schedule.php` có phương thức `getRecurringSchedulesForUser()`
- [ ] `Schedule.php` có phương thức `updateRecurringSchedule()`
- [ ] `Schedule.php` có phương thức `deleteRecurringSchedule()`
- [ ] `Schedule.php` có phương thức `canRegisterNow()`
- [ ] `Schedule.php` có phương thức `generateMonthSchedulesFromRecurring()`
- [ ] `Schedule.php` có phương thức `checkRecurringConflict()`

**Verify:**
```php
$schedule = new Schedule($conn);
echo (method_exists($schedule, 'createRecurringSchedule')) ? 'OK' : 'FAIL';
```

---

## 🎮 Controller Implementation

- [ ] `ScheduleController.php` có method `recurring()`
- [ ] `ScheduleController.php` có method `storeRecurring()`
- [ ] `ScheduleController.php` có method `editRecurring()`
- [ ] `ScheduleController.php` có method `updateRecurring()`
- [ ] `ScheduleController.php` có method `deleteRecurring()`
- [ ] Validation hạn đăng ký được implement
- [ ] Kiểm tra quyền hạn được implement
- [ ] Lỗi handling được implement

**Verify:**
```php
$controller = new ScheduleController();
echo (method_exists($controller, 'recurring')) ? 'OK' : 'FAIL';
```

---

## 🎨 Views Implementation

- [ ] File `views/schedule/recurring.php` tồn tại
- [ ] File `views/schedule/edit_recurring.php` tồn tại
- [ ] Cả hai view có form validation
- [ ] Navigation links được thêm vào `index.php`
- [ ] CSS responsive hoạt động đúng
- [ ] JavaScript validation hoạt động

**Verify:**
```
Truy cập: http://localhost/GiaSuhai/?url=schedule/recurring
Truy cập: http://localhost/GiaSuhai/?url=schedule/editRecurring&id=1
```

---

## 🛠️ Helper Implementation

- [ ] File `helpers/ScheduleHelper.php` tồn tại
- [ ] Lớp `ScheduleHelper` có hàm `canRegisterNow()`
- [ ] Lớp `ScheduleHelper` có hàm `getRegistrationStatus()`
- [ ] Lớp `ScheduleHelper` có hàm `getDayName()`
- [ ] Lớp `ScheduleHelper` có hàm `getSessionInfo()`
- [ ] Lớp `ScheduleHelper` có hàm chuyển đổi ngày

**Verify:**
```php
require_once 'helpers/ScheduleHelper.php';
echo ScheduleHelper::getDayName(0);  // Should output "Thứ 2"
```

---

## 🧪 Testing

### Unit Tests

- [ ] Test `canRegisterNow()` - Ngày 01-07
- [ ] Test `canRegisterNow()` - Ngày 08-31
- [ ] Test `getDayName()` - Tất cả ngày
- [ ] Test `getSessionInfo()` - Buổi 1 & 2
- [ ] Test `generateMonthSchedulesFromRecurring()` - Một tháng

**Run:**
```
Truy cập: http://localhost/GiaSuhai/test_recurring_schedule.php
```

### Integration Tests

- [ ] Tạo lịch cố định - Thành công
- [ ] Tạo lịch cố định ngoài hạn - Lỗi
- [ ] Xem lịch cố định - Thành công
- [ ] Chỉnh sửa lịch cố định - Thành công
- [ ] Xoá lịch cố định - Thành công
- [ ] Kiểm tra trùng lịch - Lỗi

### Manual Tests

- [ ] Đăng nhập Admin
  - [ ] Vào schedule/recurring
  - [ ] Tạo lịch (ngày 01-07)
  - [ ] Xem danh sách
  - [ ] Chỉnh sửa lịch
  - [ ] Xoá lịch
  
- [ ] Đăng nhập Tutor
  - [ ] Vào schedule/recurring
  - [ ] Xem lịch của mình
  - [ ] Chỉnh sửa lịch của mình
  - [ ] Không thể tạo lịch mới
  
- [ ] Đăng nhập Student
  - [ ] Vào schedule/recurring
  - [ ] Xem lịch của mình
  - [ ] Không thể chỉnh sửa hoặc xoá

---

## 📊 Performance

- [ ] Trang recurring load < 2 giây
- [ ] Query database tối ưu (có index)
- [ ] Không có N+1 query problems
- [ ] Cache hoạt động nếu có

**Verify:**
```sql
EXPLAIN SELECT * FROM lich_hoc_hang_tuan WHERE gia_su = 1;
```

---

## 🔒 Security

- [ ] Tất cả POST request check CSRF token nếu cần
- [ ] Sanitize tất cả input từ user
- [ ] Validate ID (phải > 0)
- [ ] Validate ngày (0-6)
- [ ] Validate buổi (1-2)
- [ ] Kiểm tra quyền trước mỗi hành động
- [ ] Không show lỗi SQL trực tiếp cho user

**Test:**
```php
// Test sanitization
$_POST['mon_hoc'] = "<script>alert('xss')</script>";
// Should be escaped/sanitized

// Test authorization
// Login as student và thử tạo lịch
// Should fail with "Chỉ quản trị viên được tạo lịch cố định!"
```

---

## 📚 Documentation

- [ ] File `SCHEDULE_RECURRING_DOCS.md` tồn tại & hoàn chỉnh
- [ ] File `IMPLEMENTATION_SUMMARY.md` tồn tại & hoàn chỉnh
- [ ] File `QUICK_START.md` tồn tại & hoàn chỉnh
- [ ] Tất cả hàm có docstring
- [ ] Tất cả API endpoint được documented

**Verify:**
```
Kiểm tra: ls -la *.md
```

---

## 🚀 Deployment

- [ ] Backup database trước deployment
- [ ] Chạy setup script: `php setup_recurring_schedules.php`
- [ ] Chạy test page: `test_recurring_schedule.php`
- [ ] Test tất cả user roles
- [ ] Test browser khác nhau (Chrome, Firefox, Safari)
- [ ] Test mobile responsive
- [ ] Check logs cho error nào không

**Pre-flight:**
```bash
# Kiểm tra lỗi PHP
php -l models/Schedule.php
php -l controllers/ScheduleController.php
php -l helpers/ScheduleHelper.php

# Chạy setup
php setup_recurring_schedules.php
```

---

## 📋 Post-Deployment

- [ ] Monitor error logs (24h đầu)
- [ ] Test user reports bất kỳ issue nào
- [ ] Check database performance
- [ ] Xác nhận email notification nếu có
- [ ] Document bất kỳ issue tìm thấy

---

## 🎯 Acceptance Criteria

### Must Have ✅
- [x] Lịch cố định theo tuần hoạt động
- [x] Hai buổi học (sáng/chiều) hoạt động
- [x] Hạn đăng ký 01-07 tháng hoạt động
- [x] Quản lý quyền hạn hoạt động
- [x] Ngăn chặn trùng lịch hoạt động

### Nice to Have
- [ ] Export lịch CSV
- [ ] Email notification
- [ ] SMS reminder
- [ ] Calendar integration

---

## 📞 Support Contacts

- **Database Admin:** [Contact]
- **Server Admin:** [Contact]
- **Project Manager:** [Contact]
- **Developer Lead:** [Contact]

---

## 📝 Sign-Off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Developer | [Name] | | |
| QA Lead | [Name] | | |
| Product Owner | [Name] | | |
| CTO | [Name] | | |

---

## 🎉 Final Checklist

- [ ] Tất cả checklist items đã hoàn thành
- [ ] Tất cả test pass
- [ ] Tất cả documentation update
- [ ] No critical issues
- [ ] No warning messages
- [ ] Production ready ✅

---

**Generated:** May 14, 2026
**Status:** ✅ Ready for Review
**Next Steps:** Schedule deployment meeting
