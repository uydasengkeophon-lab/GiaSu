<?php
// views/tutor/detail.php
// Đảm bảo Controller đã truyền biến $tutor (thông tin gia sư) và $recurringSchedules (lịch cố định của riêng gia sư này)

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

// Đồng bộ khóa Session người dùng đăng nhập hệ thống an toàn
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? 'student';
$currentUserId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;
?>

<link rel="stylesheet" href="/GiaSu/assets/css/tutor.css">

<section class="tutor-detail-page" style="font-family: Arial, sans-serif; padding: 30px 15px; max-width: 1100px; margin: 0 auto;">
    <div style="display: flex; flex-wrap: wrap; gap: 30px; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #eee;">
        
        <div style="flex: 1; min-width: 280px; text-align: center; border-right: 1px solid #eee; padding-right: 20px;">
            <img src="<?php echo !empty($tutor['avatar']) ? 'assets/uploads/' . $tutor['avatar'] : 'assets/images/placeholder.svg'; ?>" 
                 alt="Avatar" style="width: 160px; height: 160px; object-fit: cover; border-radius: 50%; border: 4px solid #f0f2f5;">
            
            <h3 style="font-size: 24px; color: #333; margin: 15px 0 5px 0; font-weight: bold;"><?php echo htmlspecialchars($tutor['full_name']); ?></h3>
            <span style="background: #e91e63; color: white; padding: 4px 12px; border-radius: 15px; font-size: 13px; font-weight: bold; display: inline-block; margin-bottom: 15px;">
                <?php echo htmlspecialchars($tutor['subjects'] ?? 'Môn học'); ?>
            </span>
            
            <div style="color: #e91e63; font-size: 22px; font-weight: bold; margin-bottom: 15px;">
                <?php echo number_format($tutor['hourly_rate'] ?? 100000); ?>đ <span style="font-size: 14px; color: #666; font-weight: normal;">/ giờ</span>
            </div>
            
            <div style="text-align: left; background: #fafafa; padding: 15px; border-radius: 8px; font-size: 14px; line-height: 1.6;">
                <p style="margin: 0 0 8px 0;"><strong>📝 Giới thiệu:</strong> <i><?php echo htmlspecialchars($tutor['bio'] ?? 'Dạy hiểu nhanh, nhiệt tình.'); ?></i></p>
                <p style="margin: 0;"><strong>📞 Liên hệ:</strong> <?php echo htmlspecialchars($tutor['phone'] ?? 'Chưa cập nhật'); ?></p>
            </div>
            
            <a href="?url=tutor" style="display: inline-block; margin-top: 20px; color: #4b2d7f; text-decoration: none; font-weight: bold;">← Quay lại danh sách</a>
        </div>

        <div style="flex: 2; min-width: 350px;">
            <h4 style="margin-top: 0; color: #2e7d32; font-weight: bold; border-bottom: 2px solid #2e7d32; padding-bottom: 8px; font-size: 18px;">
                <i class="fas fa-calendar-alt"></i> Khung lịch dạy trống của Gia sư (Đăng ký học theo tuần)
            </h4>
            <p style="font-size: 13px; color: #666; margin-bottom: 20px;">* Hệ thống lớp học mở rộng cửa cho mọi sinh viên. Lịch học chỉ khóa lại khi bạn bị trùng lịch học cá nhân.</p>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: #fff; font-size: 14px;">
                    <thead>
                        <tr style="background: #2e7d32; color: white; text-align: left;">
                            <th style="padding: 10px;">Thứ lặp lại</th>
                            <th style="padding: 10px;">Phiên học (Thời gian)</th>
                            <th style="padding: 10px;">Tình trạng</th>
                            <th style="padding: 10px; text-align: center;">Đăng ký học</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $hasSlot = false;
                        if (!empty($recurringSchedules)):
                            foreach ($recurringSchedules as $slot):
                                $hasSlot = true;
                                $slotId = (int)$slot['id'];
                                $thuInt = (int)($slot['thu_trong_tuan'] ?? -1);
                                $phienInt = (int)($slot['phien_hoc'] ?? 0);
                                
                                // Logic quét kiểm tra trùng lịch cá nhân đa tầng của sinh viên đang đăng nhập
                                $isStudentConflicted = false;
                                $conflictedSubjectName = '';

                                if ($role === 'student' && $currentUserId > 0) {
                                    $dbObj = new Database();
                                    $connObj = $dbObj->connect();
                                    
                                    // 1. Quét xem sinh viên hiện tại đã có thời khóa biểu chính thức ở khung giờ này chưa
                                    $sqlCheckFixed = "SELECT l.mon_hoc FROM lich_hoc_hang_tuan l
                                                      JOIN students s ON l.hoc_vien = s.id
                                                      WHERE s.user_id = :user_id 
                                                        AND l.thu_trong_tuan = :thu 
                                                        AND l.phien_hoc = :phien 
                                                        AND l.trang_thai = 1 LIMIT 1";
                                    $stmtFixed = $connObj->prepare($sqlCheckFixed);
                                    $stmtFixed->execute([':user_id' => $currentUserId, ':thu' => $thuInt, ':phien' => $phienInt]);
                                    $fixedRow = $stmtFixed->fetch(PDO::FETCH_ASSOC);

                                    if ($fixedRow) {
                                        $isStudentConflicted = true;
                                        $conflictedSubjectName = $fixedRow['mon_hoc'];
                                    } else {
                                        // 2. Quét tiếp bảng bookings xem sinh viên có đơn đặt lớp nào đang chờ duyệt/chờ đóng tiền ở khung giờ này không
                                        $sqlCheckBooking = "SELECT COALESCE(t.subjects, 'Môn học') AS mon_hoc FROM bookings b 
                                                            JOIN lich_hoc_hang_tuan l ON b.schedule_id = l.id 
                                                            JOIN tutors t ON b.tutor_id = t.id 
                                                            WHERE b.student_id = :user_id 
                                                              AND l.thu_trong_tuan = :thu 
                                                              AND l.phien_hoc = :phien 
                                                              AND b.status IN ('cho_thanh_toan', 'pending', 'da_thanh_toan', 'paid', 'approved') LIMIT 1";
                                        $stmtBooking = $connObj->prepare($sqlCheckBooking);
                                        $stmtBooking->execute([':user_id' => $currentUserId, ':thu' => $thuInt, ':phien' => $phienInt]);
                                        $bookingRow = $stmtBooking->fetch(PDO::FETCH_ASSOC);
                                        
                                        if ($bookingRow) {
                                            $isStudentConflicted = true;
                                            $conflictedSubjectName = $bookingRow['mon_hoc'];
                                        }
                                    }
                                }
                        ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 12px 10px; font-weight: bold; color: #4b2d7f;">
                                        <?php echo $daysOfWeek[$thuInt] ?? 'Hàng tuần'; ?>
                                    </td>
                                    
                                    <td style="padding: 12px 10px; color: #333;">
                                        <?php echo $sessionsOfDay[$phienInt] ?? 'Chưa xếp buổi'; ?>
                                    </td>
                                    
                                    <td style="padding: 12px 10px;">
                                        <?php if ($isStudentConflicted): ?>
                                            <span class="status-badge status-conflict" style="background:#fff3e0; color:#e65100; padding:3px 8px; border-radius:4px; font-size:12px; font-weight:bold;" title="Bạn đã đăng ký học môn <?= htmlspecialchars($conflictedSubjectName); ?> vào khung giờ này!">
                                                ⚠️ Trùng lịch cá nhân
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-available" style="background:#e8f5e9; color:#2e7d32; padding:3px 8px; border-radius:4px; font-size:12px; font-weight:bold;">
                                                🟢 Sẵn sàng nhận lớp
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td style="padding: 12px 10px; text-align: center;">
                                        <?php if ($role !== 'student'): ?>
                                            <span style="color:#999; font-style:italic; font-size:12px;">Chỉ học viên</span>
                                        <?php elseif ($isStudentConflicted): ?>
                                            <button disabled style="background:#ffe0b2; color:#b66a00; border:1px solid #ffcc80; padding:5px 12px; border-radius:4px; cursor:not-allowed; font-size:12px; font-weight:500;">
                                                Trùng lịch môn khác
                                            </button>
                                        <?php else: ?>
                                            <a href="?url=schedule/registerRecurring&id=<?php echo $slotId; ?>" 
                                               onclick="return confirm('Bạn muốn gửi đơn đăng ký học cố định khung giờ này với gia sư?')"
                                               style="background: #e91e63; color: white; padding: 6px 14px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 12px; display: inline-block; box-shadow: 0 2px 5px rgba(233,30,99,0.2);">
                                                🎯 Chọn học
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                        <?php 
                            endforeach;
                        endif; 

                        if (!$hasSlot):
                        ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 25px; color: #999; font-style: italic;">📬 Gia sư hiện tại chưa được xếp lịch dạy trống cố định nào tuần này.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>