# 📅 Hệ Thống Lịch Học Cố Định Hàng Tuần

## 📋 Mô Tả Chung

Hệ thống lịch học cố định cho phép tạo và quản lý lịch học theo tuần lặp lại, với các buổi học cố định (sáng hoặc chiều). Chỉ admin mới có thể tạo hoặc chỉnh sửa lịch cố định, và chỉ được phép làm việc này từ ngày **01-07** của mỗi tháng.

### 🎯 Tính Năng Chính

1. **Lịch Học Cố Định Theo Tuần**
   - Các buổi học lặp lại mỗi tuần trên ngày và giờ đã chỉ định
   - Hai buổi học mỗi ngày: Sáng (7:00-11:00) và Chiều (14:00-17:00)
   - Áp dụng cho tất cả các ngày trong tuần (Thứ 2 - Chủ nhật)

2. **Hạn Đăng Ký Hàng Tháng**
   - Chỉ được phép tạo/chỉnh sửa lịch cố định từ ngày 01-07 của tháng
   - Tự động sinh lịch học cho tháng tiếp theo

3. **Quản Lý Linh Hoạt**
   - Admin có thể tạo, chỉnh sửa, và xoá lịch cố định
   - Gia sư có thể xem lịch cố định của mình
   - Học viên có thể xem lịch của mình

## 🗄️ Cấu Trúc Cơ Sở Dữ Liệu

### Bảng: `lich_hoc_hang_tuan` (Recurring Schedules)

```sql
CREATE TABLE lich_hoc_hang_tuan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gia_su INT NOT NULL,                          -- ID gia sư
    hoc_vien INT NOT NULL,                        -- ID học viên
    mon_hoc VARCHAR(255) NOT NULL,                -- Tên môn học
    thu_trong_tuan INT NOT NULL,                  -- Ngày trong tuần (0-6, 0=Thứ 2)
    phien_hoc INT NOT NULL,                       -- Buổi học (1=sáng, 2=chiều)
    trang_thai TINYINT DEFAULT 1,                 -- Trạng thái (1=hoạt động, 0=đã xoá)
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tutor (gia_su),
    INDEX idx_student (hoc_vien),
    INDEX idx_tutor_student (gia_su, hoc_vien)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Các Cột

| Cột | Kiểu | Mô Tả |
|-----|------|-------|
| `id` | INT | ID duy nhất của lịch cố định |
| `gia_su` | INT | ID gia sư (tham chiếu bảng tutors) |
| `hoc_vien` | INT | ID học viên (tham chiếu bảng users) |
| `mon_hoc` | VARCHAR | Tên môn học (ví dụ: Toán, Tiếng Anh) |
| `thu_trong_tuan` | INT | Ngày trong tuần: 0=Thứ 2, 1=Thứ 3, ..., 6=Chủ nhật |
| `phien_hoc` | INT | Buổi học: 1=Sáng (7-11), 2=Chiều (14-17) |
| `trang_thai` | TINYINT | Trạng thái: 1=hoạt động, 0=đã xoá |
| `ngay_tao` | TIMESTAMP | Thời điểm tạo |
| `ngay_cap_nhat` | TIMESTAMP | Thời điểm cập nhật lần cuối |

## 📌 Ngày và Giờ Học

### Các Ngày Trong Tuần

```
0 = Thứ 2 (Monday)
1 = Thứ 3 (Tuesday)
2 = Thứ 4 (Wednesday)
3 = Thứ 5 (Thursday)
4 = Thứ 6 (Friday)
5 = Thứ 7 (Saturday)
6 = Chủ nhật (Sunday)
```

### Các Buổi Học

```
Buổi 1 (Sáng):   7:00 - 11:00 (4 tiếng)
Buổi 2 (Chiều):  14:00 - 17:00 (3 tiếng)
```

## 🛠️ API và Hàm Model

### Schedule Model Methods

#### Tạo Lịch Cố Định
```php
public function createRecurringSchedule(
    int $gia_su,           // ID gia sư
    int $hoc_vien,         // ID học viên
    string $mon,           // Tên môn học
    int $thu_trong_tuan,   // Ngày trong tuần (0-6)
    int $phien_hoc         // Buổi học (1-2)
): bool
```

#### Lấy Lịch Cố Định
```php
// Lấy lịch cố định cho một người dùng
public function getRecurringSchedulesForUser(
    string $role,          // 'admin', 'tutor', 'student'
    int $userId            // ID người dùng
): array

// Lấy lịch cố định theo ID
public function getRecurringScheduleById(int $id): array

// Lấy tất cả lịch cố định
public function getAllRecurringSchedules(): array
```

#### Cập Nhật Lịch Cố Định
```php
public function updateRecurringSchedule(
    int $id,
    int $gia_su,
    int $hoc_vien,
    string $mon,
    int $thu_trong_tuan,
    int $phien_hoc
): bool
```

#### Xoá Lịch Cố Định
```php
public function deleteRecurringSchedule(int $id): bool
```

#### Kiểm Tra Trùng Lịch
```php
public function checkRecurringConflict(
    int $gia_su,
    int $thu_trong_tuan,
    int $phien_hoc,
    int|null $excludeId = null
): bool
```

#### Kiểm Tra Hạn Đăng Ký
```php
public function canRegisterNow(): bool
```

#### Sinh Lịch Học Từ Lịch Cố Định
```php
public function generateMonthSchedulesFromRecurring(
    int|null $month = null,  // Mặc định tháng hiện tại
    int|null $year = null    // Mặc định năm hiện tại
): array
```

### Controller Routes

| Route | Method | Mô Tả |
|-------|--------|-------|
| `?url=schedule/recurring` | GET | Hiển thị danh sách lịch cố định |
| `?url=schedule/storeRecurring` | POST | Tạo lịch cố định mới |
| `?url=schedule/editRecurring&id={id}` | GET | Hiển thị form chỉnh sửa |
| `?url=schedule/updateRecurring` | POST | Cập nhật lịch cố định |
| `?url=schedule/deleteRecurring&id={id}` | GET | Xoá lịch cố định |

## ✅ Quy Tắc Kinh Doanh

### 1. Hạn Đăng Ký Hàng Tháng
- **Giai đoạn đăng ký:** Ngày 01-07 của mỗi tháng
- **Hành động cho phép:** Tạo, chỉnh sửa lịch cố định
- **Hành động không cho phép:** Ngoài giai đoạn đăng ký
- **Lỗi:** "Chỉ được phép đăng ký lịch cố định từ ngày 01-07 của tháng!"

### 2. Hạn Chế Quyền
- **Admin:** Có thể tạo, chỉnh sửa, xoá lịch cố định của bất kỳ ai
- **Gia sư:** Chỉ có thể xem và chỉnh sửa lịch của mình
- **Học viên:** Chỉ có thể xem lịch của mình
- **Khách:** Không có quyền truy cập

### 3. Ngăn Chặn Trùng Lịch
- Một gia sư không thể có hai lịch cố định cùng ngày, cùng buổi
- Hệ thống kiểm tra xung đột trước khi lưu
- Lỗi: "Gia sư đã có lịch cố định vào thời gian này!"

## 📊 Ví Dụ Sử Dụng

### Tạo Lịch Cố Định

```php
// Tạo lịch: Thứ 2 sáng (7-11) cho gia sư ID 1 và học viên ID 5
$scheduleModel->createRecurringSchedule(
    $gia_su = 1,
    $hoc_vien = 5,
    $mon = 'Toán',
    $thu_trong_tuan = 0,  // Thứ 2
    $phien_hoc = 1        // Buổi sáng
);
```

### Sinh Lịch Học Cho Tháng Tiếp Theo

```php
// Lấy tất cả lịch cố định và sinh ra lịch học cho tháng 2 năm 2026
$schedulesForMonth = $scheduleModel->generateMonthSchedulesFromRecurring(
    $month = 2,
    $year = 2026
);

// Kết quả sẽ là mảng các lịch học với ngày cụ thể:
// [
//     [
//         'recurring_id' => 1,
//         'gia_su' => 1,
//         'hoc_vien' => 5,
//         'mon_hoc' => 'Toán',
//         'ngay' => '2026-02-02',    // Thứ 2, tuần 1
//         'gio_bat_dau' => '07:00:00',
//         'gio_ket_thuc' => '11:00:00',
//         'phien_hoc' => 1
//     ],
//     ...
// ]
```

## 🎨 Helper Functions

### ScheduleHelper Class

```php
require_once 'helpers/ScheduleHelper.php';

// Kiểm tra có thể đăng ký không
$canRegister = ScheduleHelper::canRegisterNow();  // bool

// Lấy thông tin trạng thái đăng ký
$status = ScheduleHelper::getRegistrationStatus();
// ['can_register' => bool, 'message' => string, 'days_left' => int, ...]

// Lấy tên ngày
$dayName = ScheduleHelper::getDayName(0);  // "Thứ 2"

// Lấy thông tin buổi học
$info = ScheduleHelper::getSessionInfo(1);
// ['start' => '07:00:00', 'end' => '11:00:00', 'name' => 'Sáng (7:00 - 11:00)', ...]

// Tính số lần học trong tháng
$count = ScheduleHelper::countSchedulesInMonth(0);  // số lần thứ 2 trong tháng

// Lấy tất cả các ngày thứ 2 trong tháng
$dates = ScheduleHelper::getDatesInMonth(0, 2, 2026);
```

## 🔒 Bảo Mật

### Xác Thực
- Tất cả hành động yêu cầu đăng nhập (`ensureLoggedIn()`)
- Kiểm tra quyền hạn cho từng hành động

### Xác Thực Dữ Liệu
- Validation ID: Phải > 0
- Validation ngày: Phải trong khoảng 0-6
- Validation buổi học: Phải là 1 hoặc 2
- Sanitize: Tất cả input từ user được kiểm tra

### Kiểm Tra Quyền
- Admin: Có toàn quyền
- Tutor: Chỉ được chỉnh sửa lịch của mình
- Student: Chỉ được xem
- Khách: Không có quyền

## 📝 Lịch Sử Thay Đổi

| Phiên Bản | Ngày | Mô Tả |
|-----------|------|-------|
| 1.0 | 2026-05-14 | Phiên bản đầu tiên |

## 🤝 Support

Để có hỗ trợ, vui lòng liên hệ với:
- Admin: Quản trị viên hệ thống
- Email: support@giasu.example.com
