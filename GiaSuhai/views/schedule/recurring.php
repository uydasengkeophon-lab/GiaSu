<?php
// views/schedule/recurring.php

// Gán biến dữ liệu truyền từ Controller vào biến $data dùng trong View
if (isset($recurringSchedules)) {
    $data = $recurringSchedules;
}

$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? $currentRole ?? 'student';

$daysOfWeek = [
    0 => 'Thứ Hai',
    1 => 'Thứ Ba',
    2 => 'Thứ Tư',
    3 => 'Thứ Năm',
    4 => 'Thứ Sáu',
    5 => 'Thứ Bảy',
    6 => 'Chủ Nhật'
];

$sessionsOfDay = [
    1 => 'Buổi Sáng (08:00 - 11:30)',
    2 => 'Buổi Chiều (14:00 - 17:30)',
    3 => 'Buổi Tối (18:00 - 21:30)'
];
?>

<link rel="stylesheet" href="/GiaSu/assets/css/schedule.css">

<section class="schedule-page" style="font-family: Arial, sans-serif; padding: 15px; box-sizing: border-box;">

    <h2 class="schedule-title" style="color: #2e7d32; font-weight: bold; margin-bottom: 20px;">
        <?php
        echo ($role === 'tutor')
            ? 'Khung lịch dạy cố định hàng tuần'
            : (($role === 'admin')
                ? 'Quản lý khung lịch cố định hệ thống'
                : 'Đăng ký lịch học cố định hàng tuần');
        ?>
    </h2>

    <div class="schedule-nav" style="margin-bottom: 25px; display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="?url=schedule/index"
           class="nav-link"
           style="padding: 10px 20px; background: #f0f0f0; color: #333; text-decoration: none; border-radius: 6px; font-weight: bold;">
            📅 <?php echo ($role === 'tutor') ? 'Lịch Dạy Thường' : 'Lịch Học Thường'; ?>
        </a>

        <a href="?url=schedule/recurring"
           class="nav-link active"
           style="padding: 10px 20px; background: #2e7d32; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">
            🔄 Khung Lịch Cố Định Hàng Tuần
        </a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <p style="padding:12px; background:#e8f5e9; color:#2e7d32; border-radius:6px; margin-bottom:20px; border-left:4px solid #2e7d32; font-weight:500;">
            ✓ <?php echo htmlspecialchars($_GET['msg']); ?>
        </p>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <p style="padding:12px; background:#ffebee; color:#c62828; border-radius:6px; margin-bottom:20px; border-left:4px solid #c62828; font-weight:500;">
            ✕ <?php echo htmlspecialchars($_GET['error']); ?>
        </p>
    <?php endif; ?>

    <?php if ($role === 'admin'): ?>
        <div style="background:#f4faf4; padding:22px; border-radius:10px; margin-bottom:35px; border:1px solid #c8e6c9;">
            <h3 style="margin-top:0; margin-bottom:15px; color:#2e7d32; font-weight:bold;">
                Admin thiết lập khung lịch cố định
            </h3>

            <form method="POST" action="?url=schedule/storeRecurring" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; align-items:end;">
                <div>
                    <label style="font-weight:600;">Chọn Gia sư:</label>
                    <select name="gia_su_id" required style="width:100%; padding:9px; border-radius:6px; border:1px solid #ccc;">
                        <option value="">Chọn gia sư</option>
                        <?php if (!empty($tutorOptions)): ?>
                            <?php foreach ($tutorOptions as $tutorOption): ?>
                                <option value="<?php echo (int)$tutorOption['id']; ?>">
                                    <?php echo htmlspecialchars($tutorOption['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label style="font-weight:600;">Môn học:</label>
                    <input type="text" name="mon_hoc" required placeholder="Ví dụ: Lập trình PHP" style="width:100%; padding:9px; border-radius:6px; border:1px solid #ccc;">
                </div>

                <div>
                    <label style="font-weight:600;">Ngày học:</label>
                    <select name="thu_trong_tuan" required style="width:100%; padding:9px; border-radius:6px; border:1px solid #ccc;">
                        <option value="">Chọn thứ</option>
                        <?php foreach ($daysOfWeek as $key => $value): ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="font-weight:600;">Phiên học:</label>
                    <select name="phien_hoc" required style="width:100%; padding:9px; border-radius:6px; border:1px solid #ccc;">
                        <option value="">Chọn phiên học</option>
                        <?php foreach ($sessionsOfDay as $key => $value): ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="grid-column:1/-1; text-align:right;">
                    <button type="submit" style="background:#2e7d32; color:white; padding:10px 25px; border:none; border-radius:6px; font-weight:bold; cursor:pointer;">
                        Tạo khung lịch cố định
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="table-responsive" style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; background:#fff; border:1px solid #eee; font-size: 14px;">
            <thead>
                <tr style="background:#2e7d32; color:white;">
                    <th style="padding:12px; text-align:left;">Gia sư giảng dạy</th>
                    <th style="padding:12px; text-align:left;">Học viên đăng ký</th>
                    <th style="padding:12px; text-align:left;">Môn học</th>
                    <th style="padding:12px; text-align:left;">Lặp lại</th>
                    <th style="padding:12px; text-align:left;">Phiên học</th>
                    <th style="padding:12px; text-align:left;">Tình trạng</th>
                    <th style="padding:12px; text-align:center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($data)): ?>
                <?php foreach ($data as $row): 
                    $thuInt = (int)($row['thu_trong_tuan'] ?? -1);
                    $displayDay = $daysOfWeek[$thuInt] ?? 'Không xác định';

                    $phienInt = (int)($row['phien_hoc'] ?? 0);
                    $displaySession = $sessionsOfDay[$phienInt] ?? 'Chưa xếp';

                    // ĐỒNG BỘ LUỒNG TRẠNG THÁI MỚI CHO LỚP HỌC ĐẠI TRÀ
                    $bookingStatus = $row['booking_status'] ?? null;
                    $bookingId = $row['booking_id'] ?? null;
                    $isAssignedRaw = !empty($row['hoc_vien']) && (int)$row['hoc_vien'] !== 0;
                ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:12px;">
                            <strong><?php echo htmlspecialchars($row['tutor_name'] ?? 'Gia sư'); ?></strong>
                            <?php if ($role === 'tutor' && (int)($row['gia_su_id'] ?? 0) === (int)($_SESSION['user_id'] ?? 0)): ?>
                                <span style="background:#e8f5e9; color:#2e7d32; font-size:11px; padding:2px 6px; border-radius:4px; margin-left:5px; font-weight:bold;">Lịch của bạn</span>
                            <?php endif; ?>
                        </td>

                        <td style="padding:12px;">
                            <?php if ($role === 'student'): ?>
                                <span style="font-weight: 600; color: #1976d2;"><i class="fas fa-user"></i> Bạn (Lớp chung)</span>
                            <?php else: ?>
                                <span style="font-weight: 600; color: #333;"><?php echo htmlspecialchars($row['student_name'] ?? 'Chưa có học viên'); ?></span>
                            <?php endif; ?>
                        </td>

                        <td style="padding:12px; font-weight: 600; color: #e91e63;">
                            <?php echo htmlspecialchars($row['mon_hoc'] ?? 'Chưa xếp môn'); ?>
                        </td>

                        <td style="padding:12px; font-weight: 500; color: #4b2d7f;">
                            <i class="fas fa-redo-alt" style="font-size:11px;"></i> <?php echo $displayDay; ?>
                        </td>

                        <td style="padding:12px; color:#555;">
                            <?php echo $displaySession; ?>
                        </td>

                        <td style="padding:12px;">
                            <?php if ($role === 'student'): ?>
                                <?php if ($bookingStatus === 'approved' || $isAssignedRaw): ?>
                                    <span style="background:#e8f5e9; color:#2e7d32; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;">🟢 Đang học chính thức</span>
                                <?php elseif ($bookingStatus === 'da_thanh_toan' || $bookingStatus === 'paid'): ?>
                                    <span style="background:#e3f2fd; color:#1565c0; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;">🔵 Chờ Admin duyệt</span>
                                <?php else: ?>
                                    <span style="background:#fff3e0; color:#e65100; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;">🟡 Chờ thanh toán học phí</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($isAssignedRaw): ?>
                                    <span style="background:#ffebee; color:#c62828; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;">🔒 Đã gán học viên</span>
                                <?php else: ?>
                                    <span style="background:#e8f5e9; color:#2e7d32; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;">🟢 Còn trống</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>

                        <td style="padding:12px; text-align:center; white-space: nowrap;">
                            <?php if ($role === 'admin'): ?>
                                <a href="?url=schedule/editRecurring&id=<?php echo (int)$row['id']; ?>" style="color:#1976d2; text-decoration:none; margin-right:10px; font-weight:bold;">✏️ Sửa</a>
                                <a href="?url=schedule/deleteRecurring&id=<?php echo (int)$row['id']; ?>" onclick="return confirm('Bạn chắc chắn muốn xoá lịch này?')" style="color:#c62828; text-decoration:none; font-weight:bold;">🗑 Xóa</a>
                            <?php elseif ($role === 'student'): ?>
                                <?php if ($bookingStatus === 'approved' || $isAssignedRaw): ?>
                                    <span style="color:#2e7d32; font-weight:bold; font-size:13px;"><i class="fas fa-lock"></i> Đang học</span>
                                <?php else: ?>
                                    <a href="?url=payment/index&id=<?php echo (int)$bookingId; ?>" 
                                       style="background:#ff9800; color:white; padding:5px 12px; border-radius:4px; text-decoration:none; font-weight:bold; font-size:12px; display:inline-block; box-shadow: 0 2px 4px rgba(255,152,0,0.2);">
                                        💳 Xem hóa đơn nộp tiền
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color:#999; font-style:italic; font-size:13px;">Chỉ xem</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:30px; color:#999; font-style:italic;">
                        📬 Hiện tại chưa có dữ liệu khung lịch cố định nào được đồng bộ với tài khoản của bạn.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>