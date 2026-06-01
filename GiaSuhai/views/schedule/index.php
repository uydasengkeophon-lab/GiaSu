<?php
// views/schedule/index.php

// Nhận diện vai trò để phân quyền giao diện (Mặc định nếu thiếu thì coi như học viên)
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
    1 => 'Sáng',
    2 => 'Chiều',
    3 => 'Tối'
];

$calendarEvents = [];
if (!empty($data)) {
    foreach ($data as $row) {
        $status = $row['display_status'] ?? 'Hệ thống';
        
        // Phân quyền tiền tố và tiêu đề hiển thị trên lịch ô vuông FullCalendar
        if ($role === 'tutor') {
            $prefix = (($row['source_type'] ?? 'schedule') === 'booking') ? 'Yêu cầu dạy: ' : 'Lịch dạy: ';
            $displayTitle = $prefix . ($row['mon_hoc'] ?? '') . ' - Học viên: ' . ($row['student_name'] ?? 'Chưa rõ');
        } else {
            $prefix = (($row['source_type'] ?? 'schedule') === 'booking') ? 'Đăng ký học: ' : 'Lịch học: ';
            $displayTitle = $prefix . ($row['mon_hoc'] ?? '') . ' - Gia sư: ' . ($row['tutor_name'] ?? 'Chưa rõ');
        }
        
        // CHỈ ĐẨY LÊN FULLCALENDAR: Nếu có ngày và giờ bắt đầu cụ thể
        if (!empty($row['ngay']) && $row['ngay'] !== '0000-00-00' && !empty($row['gio_bat_dau'])) {
            $endTime = !empty($row['gio_ket_thuc']) ? $row['gio_ket_thuc'] : $row['gio_bat_dau'];
            
            $calendarEvents[] = [
                'title' => $displayTitle . ' (' . $status . ')',
                'start' => $row['ngay'] . 'T' . $row['gio_bat_dau'],
                'end' => $row['ngay'] . 'T' . $endTime,
                'backgroundColor' => (($row['source_type'] ?? 'schedule') === 'booking') ? '#fff3e0' : '#2196F3',
                'textColor' => (($row['source_type'] ?? 'schedule') === 'booking') ? '#e65100' : '#ffffff',
                'borderColor' => (($row['source_type'] ?? 'schedule') === 'booking') ? '#ffb74d' : '#1976D2'
            ];
        }
    }
}
?>

<link rel="stylesheet" href="/GiaSu/assets/css/schedule.css">

<section class="schedule-page" style="font-family: Arial, sans-serif; padding: 15px; box-sizing: border-box;">
    
    <h2 class="schedule-title" style="color: #4b2d7f; font-weight: bold; margin-bottom: 20px;">
        <?php echo ($role === 'tutor') ? 'Lịch dạy của gia sư' : 'Lịch học của học viên'; ?>
    </h2>
    
    <div class="schedule-nav" style="margin-bottom: 25px; display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="?url=schedule/index" class="nav-link active" style="padding: 10px 20px; background: #4b2d7f; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; box-shadow: 0 2px 5px rgba(75,45,127,0.2);">
            📅 <?php echo ($role === 'tutor') ? 'Lịch Dạy Thường' : 'Lịch Học Thường'; ?>
        </a>
        <a href="?url=schedule/recurring" class="nav-link" style="padding: 10px 20px; background: #f0f0f0; color: #333; text-decoration: none; border-radius: 6px; font-weight: bold; transition: background 0.2s;">
            🔄 <?php echo ($role === 'tutor') ? 'Lịch Cố Định Hàng Tuần' : 'Lịch Cố Định Hàng Tuần'; ?>
        </a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <p class="alert alert-success" style="padding: 12px; background: #e8f5e9; color: #2e7d32; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #2e7d32; font-weight: 500;">
            <?php echo htmlspecialchars($_GET['msg']); ?>
        </p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p class="alert alert-error" style="padding: 12px; background: #ffebee; color: #c62828; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #c62828; font-weight: 500;">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </p>
    <?php endif; ?>

    <?php if (in_array($role, ['tutor', 'student'], true)): ?>
        <section class="weekly-timetable-panel">
            <div class="weekly-timetable-head">
                <div>
                    <span>Lịch cố định theo tuần</span>
                    <h3><?php echo $role === 'tutor' ? 'Lịch dạy của gia sư' : 'Lịch học đã thanh toán'; ?></h3>
                </div>
                <small><?php echo $role === 'student' ? 'Chỉ hiển thị lớp paid/approved' : 'Bao gồm trạng thái thanh toán của học viên'; ?></small>
            </div>

            <div class="weekly-timetable-grid">
                <div class="weekly-corner">Ca học</div>
                <?php foreach ($daysOfWeek as $dayLabel): ?>
                    <div class="weekly-day"><?php echo htmlspecialchars($dayLabel); ?></div>
                <?php endforeach; ?>

                <?php foreach ($sessionsOfDay as $sessionKey => $sessionLabel): ?>
                    <div class="weekly-session"><?php echo htmlspecialchars($sessionLabel); ?></div>
                    <?php foreach ($daysOfWeek as $dayKey => $dayLabel): ?>
                        <div class="weekly-cell">
                            <?php
                            $items = array_filter($weeklyTimetable ?? [], function ($row) use ($sessionKey, $dayKey) {
                                return (int)($row['phien_hoc'] ?? 0) === (int)$sessionKey
                                    && (int)($row['thu_trong_tuan'] ?? -1) === (int)$dayKey;
                            });
                            ?>
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                    <?php
                                    $status = $item['booking_status'] ?? (($item['hoc_vien'] ?? 0) > 0 ? 'approved' : 'open');
                                    $statusLabel = [
                                        'pending' => 'Chờ thanh toán',
                                        'paid' => 'Đã thanh toán',
                                        'approved' => 'Đã duyệt',
                                        'open' => 'Chưa có học viên'
                                    ][$status] ?? 'Đang xử lý';
                                    ?>
                                    <div class="weekly-class-card status-<?php echo htmlspecialchars($status); ?>">
                                        <strong><?php echo htmlspecialchars($item['mon_hoc'] ?? 'Môn học'); ?></strong>
                                        <span>
                                            <?php echo $role === 'tutor'
                                                ? htmlspecialchars($item['student_name'] ?? 'Chưa có học viên')
                                                : htmlspecialchars($item['tutor_name'] ?? 'Gia sư'); ?>
                                        </span>
                                        <em><?php echo htmlspecialchars($statusLabel); ?></em>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="weekly-empty">Trống</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <div id="calendar" class="calendar-box" style="margin-bottom: 35px; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #eef0f2;"></div>
    
    <script id="schedule-events" type="application/json">
        <?php echo json_encode($calendarEvents, JSON_UNESCAPED_UNICODE); ?>
    </script>

    <?php if (!empty($canCreate)): ?>
        <div class="form-container" style="background: #fdfbff; padding: 22px; border-radius: 10px; margin-bottom: 35px; border: 1px solid #e1d7f2; box-shadow: 0 2px 12px rgba(75,45,127,0.04);">
            <h3 style="margin-top: 0; margin-bottom: 5px; color: #4b2d7f; font-weight: bold;"><i class="fas fa-magic"></i> Tạo lịch học tự động theo Lớp cố định (Admin)</h3>
            <p style="font-size: 13px; color: #666; margin-bottom: 15px; font-style: italic;">* Hệ thống sẽ tự động quét danh sách và phát hành lịch cho TẤT CẢ học viên đang theo học môn này của gia sư.</p>
            
            <form method="POST" action="?url=schedule/store" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: end;">
                
                <div>
                    <label style="font-weight:600; display:block; margin-bottom:6px; color:#555;">Gia sư phụ trách:</label>
                    <select name="gia_su_id" required style="width:100%; padding:9px; border-radius:6px; border:1px solid #ccc; background:#fff;">
                        <option value="">Chọn gia sư</option>
                        <?php foreach ($tutorOptions as $tutorOption): ?>
                            <option value="<?php echo (int) $tutorOption['id']; ?>">
                                <?php echo htmlspecialchars($tutorOption['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="font-weight:600; display:block; margin-bottom:6px; color:#555;">Môn học quét lớp:</label>
                    <input type="text" name="mon_hoc" placeholder="Ví dụ: Lập trình PHP" required style="width:100%; padding:9px; border-radius:6px; border:1px solid #ccc; box-sizing:border-box;">
                </div>

                <div>
                    <label style="font-weight:600; display:block; margin-bottom:6px; color:#555;">Ngày học chi tiết:</label>
                    <input type="date" name="ngay" required style="width:100%; padding:8px; border-radius:6px; border:1px solid #ccc; box-sizing:border-box;">
                </div>

                <div>
                    <label style="font-weight:600; display:block; margin-bottom:6px; color:#555;">Giờ bắt đầu:</label>
                    <input type="time" name="gio_bat_dau" required style="width:100%; padding:8px; border-radius:6px; border:1px solid #ccc; box-sizing:border-box;">
                </div>

                <div>
                    <label style="font-weight:600; display:block; margin-bottom:6px; color:#555;">Giờ kết thúc:</label>
                    <input type="time" name="gio_ket_thuc" required style="width:100%; padding:8px; border-radius:6px; border:1px solid #ccc; box-sizing:border-box;">
                </div>

                <div style="grid-column: 1 / -1; text-align: right; margin-top: 5px;">
                    <button type="submit" style="background: #4b2d7f; color: #fff; padding: 10px 25px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: background 0.2s;">
                        <i class="fas fa-users"></i> Phát hành lịch cho cả lớp
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>


    <h3 style="margin-top: 10px; color: #4b2d7f; font-weight: bold; border-bottom: 2px solid #4b2d7f; padding-bottom: 8px; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-calendar-day"></i> Lịch Học Chi Tiết Theo Ngày Cụ Thể
    </h3>
    <div class="table-responsive" style="margin-bottom: 45px; overflow-x: auto;">
        <table class="schedule-table" style="width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.04); border: 1px solid #eee;">
            <thead>
                <tr style="background: #4b2d7f; color: #fff; text-align: left;">
                    <?php if ($role === 'admin'): ?>
                        <th style="padding: 12px 15px;">Gia sư giảng dạy</th>
                        <th style="padding: 12px 15px;">Học viên</th>
                    <?php else: ?>
                        <th style="padding: 12px 15px;"><?php echo ($role === 'tutor') ? 'Học viên' : 'Gia sư giảng dạy'; ?></th>
                    <?php endif; ?>
                    <th style="padding: 12px 15px;">Môn lớp</th>
                    <th style="padding: 12px 15px;">Ngày học</th>
                    <th style="padding: 12px 15px;">Bắt đầu</th>
                    <th style="padding: 12px 15px;">Kết thúc</th>
                    <th style="padding: 12px 15px;">Trạng thái lớp</th>
                    <th style="padding: 12px 15px; text-align: center;">Thao tác</th>
                </tr>
            </thead>
           
            <tbody>
    <?php 
    $hasOfficialRows = false;
    if (!empty($data)):
        foreach ($data as $row):
            if (!empty($row['ngay']) && $row['ngay'] !== '0000-00-00' && !empty($row['gio_bat_dau']) && $row['gio_bat_dau'] !== '00:00:00'):
                $hasOfficialRows = true;
                $sourceType = $row['source_type'] ?? 'schedule';
                $isOfficialSchedule = ($sourceType === 'schedule');
                $isTutorOwner = (($role === 'tutor') && (int)($row['gia_su'] ?? 0) === (int)($_SESSION['tutor_id'] ?? $currentUserId ?? 0));
                
                $canEditRow = !empty($canEdit) && $isOfficialSchedule && (($role === 'admin') || $isTutorOwner);
                $canDeleteRow = !empty($canDelete) && $isOfficialSchedule && ($role === 'admin');

                $statusClass = 'status-official';
                $bookingStatus = $row['booking_status'] ?? '';
                if ($sourceType === 'booking') {
                    if ($bookingStatus === 'pending') { $statusClass = 'status-pending'; } 
                    elseif ($bookingStatus === 'paid') { $statusClass = 'status-paid'; } 
                    elseif ($bookingStatus === 'approved') { $statusClass = 'status-approved'; }
                    elseif ($bookingStatus === 'rejected') { $statusClass = 'status-rejected'; }
                }

                // 🔍 DÒ TÌM VÀ ĐỔI TÊN MÔN HỌC CHÍNH XÁC THEO TỪNG GIA SƯ
                $tutorIdRaw = (int)($row['gia_su'] ?? 0);
                $monHocRaw = trim($row['mon_hoc'] ?? '');
                
                if (empty($monHocRaw) || $monHocRaw === 'Môn học') {
                    if ($tutorIdRaw > 0) {
                        $dbObj = new Database();
                        $connObj = $dbObj->connect();
                        
                        // Lấy chuyên môn thực tế từ bảng tutors để phân loại (Thiết kế web, Cơ sở dữ liệu, C++...)
                        $sqlTutorSubject = "SELECT subjects FROM tutors WHERE id = :gs LIMIT 1";
                        $stmtTS = $connObj->prepare($sqlTutorSubject);
                        $stmtTS->execute([':gs' => $tutorIdRaw]);
                        $tutorSubject = $stmtTS->fetchColumn();
                        
                        $monHocRaw = !empty($tutorSubject) ? $tutorSubject : 'Chưa phân môn';
                    } else {
                        $monHocRaw = 'Chưa rõ môn';
                    }
                }

                // 👥 QUÉT DANH SÁCH HỌC VIÊN TỰ ĐỘNG CHO NÚT BẤM
                $allClassStudents = [];
                if ($tutorIdRaw > 0) {
                    $dbObj = new Database();
                    $connObj = $dbObj->connect();
                    
                    // Cách 1: Quét học viên cố định từ bảng lịch tuần
                    $sqlStudents = "SELECT DISTINCT s.full_name 
                                    FROM lich_hoc_hang_tuan l
                                    JOIN students s ON l.hoc_vien = s.id
                                    WHERE l.gia_su = :gs AND l.hoc_vien > 0";
                    $stmtS = $connObj->prepare($sqlStudents);
                    $stmtS->execute([':gs' => $tutorIdRaw]);
                    $allClassStudents = $stmtS->fetchAll(PDO::FETCH_COLUMN);
                    
                    // Cách 2: Dự phòng quét từ đơn đăng ký mua lớp học (bookings) đã thanh toán hoặc duyệt
                    if (empty($allClassStudents)) {
                        $sqlBookings = "SELECT DISTINCT s.full_name 
                                        FROM bookings b
                                        JOIN students s ON b.student_id = s.id
                                        WHERE b.tutor_id = :gs AND b.status IN ('approved', 'paid')";
                        $stmtB = $connObj->prepare($sqlBookings);
                        $stmtB->execute([':gs' => $tutorIdRaw]);
                        $allClassStudents = $stmtB->fetchAll(PDO::FETCH_COLUMN);
                    }
                }
                
                // Chuỗi văn bản hiển thị danh sách chi tiết khi di chuột vào nút
                $hoverListText = !empty($allClassStudents) 
                    ? "Danh sách học viên lớp này:\n" . implode("\n", array_map(function($index, $name) { return ($index + 1) . ". " . $name; }, array_keys($allClassStudents), $allClassStudents))
                    : "Chưa có học viên nào đăng ký lớp này.";
    ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <?php if ($role === 'admin'): ?>
                        <td style="padding: 12px 15px;"><strong><?php echo htmlspecialchars($row['tutor_name'] ?? 'Chưa rõ'); ?></strong></td>
                        
                        <td style="padding: 12px 15px;">
                            <button class="btn-student-list" title="<?php echo htmlspecialchars($hoverListText); ?>" 
                                    style="background: #e3f2fd; color: #1976d2; border: 1px solid #bbdefb; padding: 5px 12px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 13px; display: inline-flex; align-items: center; gap: 5px; outline: none;">
                                👥 Danh sách (<?= count($allClassStudents); ?> HV)
                            </button>
                        </td>
                    <?php else: ?>
                        <td style="padding: 12px 15px;">
                            <strong><?php echo ($role === 'tutor') ? htmlspecialchars($row['student_name'] ?? 'Chưa rõ') : htmlspecialchars($row['tutor_name'] ?? 'Chưa rõ'); ?></strong>
                        </td>
                    <?php endif; ?>
                    
                    <td style="padding: 12px 15px;">
                        <span style="color: #e91e63; font-weight: 600;">
                            <?= htmlspecialchars($monHocRaw); ?>
                        </span>
                    </td>
                    
                    <td style="padding: 12px 15px; font-weight: 500; color: #333;"><?php echo date('d/m/Y', strtotime($row['ngay'])); ?></td>
                    <td style="padding: 12px 15px; color: #2e7d32; font-weight: bold;"><?php echo date('H:i', strtotime($row['gio_bat_dau'])); ?></td>
                    <td style="padding: 12px 15px; color: #c62828; font-weight: bold;"><?php echo date('H:i', strtotime($row['gio_ket_thuc'])); ?></td>
                    <td style="padding: 12px 15px;">
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <?php echo ($role === 'tutor' && $bookingStatus === 'pending') ? 'Chờ HV thanh toán' : htmlspecialchars($row['display_status'] ?? 'Lịch chính thức'); ?>
                        </span>
                    </td>
                    
                    <td style="padding: 12px 15px; text-align: center;">
                        <?php if ($canEditRow): ?>
                            <a href="?url=schedule/edit&id=<?php echo (int) $row['id']; ?>" style="color: #2196F3; font-weight: bold; text-decoration: none;">Sửa</a>
                        <?php elseif ($sourceType === 'booking'): ?>
                            <?php if ($role === 'student' && $bookingStatus === 'pending'): ?>
                                <a href="?url=payment/index&id=<?php echo (int)str_replace('booking_', '', $row['id']); ?>" style="background: #e91e63; color: white; padding: 5px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; display: inline-block;">Thanh toán</a>
                            <?php elseif ($role === 'tutor'): ?>
                                <span style="color: #2e7d32; font-size: 13px; font-weight: 500;"><i class="fas fa-hourglass-start"></i> Chờ xử lý lớp</span>
                            <?php else: ?>
                                <span style="color: #777; font-size: 13px;">Đơn đăng ký</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color: #bbb; font-size: 13px;">Chỉ xem</span>
                        <?php endif; ?>

                        <?php if ($canDeleteRow): ?>
                            <span style="color: #ddd; margin: 0 4px;">|</span>
                            <a href="?url=schedule/delete&id=<?php echo (int) $row['id']; ?>" onclick="return confirm('Xoá lịch này?')" style="color: #c62828; text-decoration: none; font-weight: bold;">Xoá</a>
                        <?php endif; ?>
                        
                        <span style="color: #ddd; margin: 0 4px;">|</span>
                        <a href="?url=schedule/detail&id=<?php echo (int) $row['id']; ?>" 
                           style="color: #2e7d32; font-weight: bold; text-decoration: none; transition: color 0.2s;"
                           onmouseover="this.style.color='#1b5e20'" onmouseout="this.style.color='#2e7d32'">
                            👁️ Xem chi tiết
                        </a>
                    </td>
                </tr>
    <?php 
            endif;
        endforeach;
    endif; 
    
    if (!$hasOfficialRows):
    ?>
        <tr>
            <td colspan="8" style="text-align: center; padding: 25px; color: #999; font-style: italic;">📬 Không có lịch học hoặc buổi dạy chi tiết nào được xếp trong ngày này.</td>
        </tr>
    <?php endif; ?>
</tbody>

        </table>
    </div>


    <h3 style="margin-top: 10px; color: #2e7d32; font-weight: bold; border-bottom: 2px solid #2e7d32; padding-bottom: 8px; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-book-reader"></i> Danh Sách Lớp Học Bạn Đang Tham Gia (Theo tuần)
    </h3>
    <div class="table-responsive" style="overflow-x: auto;">
        <table class="schedule-table" style="width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.04); border: 1px solid #eee;">
            <thead>
                <tr style="background: #2e7d32; color: #fff; text-align: left;">
                    <th style="padding: 12px 15px;"><?php echo ($role === 'tutor') ? 'Học viên đăng ký học' : 'Gia sư phụ trách'; ?></th>
                    <th style="padding: 12px 15px;">Lớp môn</th>
                    <th style="padding: 12px 15px;">Hình thức phân phối</th>
                    <th style="padding: 12px 15px;">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $displayedPartners = []; 
                $hasRecurringRows = false;

                if (!empty($data)):
                    foreach ($data as $row):
                        if (empty($row['ngay']) || $row['ngay'] === '0000-00-00' || empty($row['gio_bat_dau'])):
                            
                            $partnerId = ($role === 'tutor') ? ($row['hoc_vien'] ?? '') : ($row['gia_su'] ?? '');
                            if (in_array($partnerId, $displayedPartners)) {
                                continue; 
                            }
                            $displayedPartners[] = $partnerId;
                            $hasRecurringRows = true;
                            
                            $subjectName = ($row['mon_hoc'] === 'Môn học' || empty($row['mon_hoc'])) ? 'Lớp học hệ thống' : $row['mon_hoc'];
                ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px 15px;">
                                    <strong><?php echo ($role === 'tutor') ? htmlspecialchars($row['student_name'] ?? 'Học viên') : htmlspecialchars($row['tutor_name'] ?? 'Gia sư'); ?></strong>
                                </td>
                                <td style="padding: 12px 15px;">
                                    <span style="color: #4b2d7f; font-weight: bold;"><a href="?url=schedule/recurring" style="color: #4b2d7f; text-decoration: none;"><?php echo htmlspecialchars($subjectName); ?></a></span>
                                </td>
                                <td style="padding: 12px 15px;">
                                    <span style="background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                        🔄 Lịch học cố định hàng tuần
                                    </span>
                                </td>
                                <td style="padding: 12px 15px;">
                                    <span class="status-badge status-approved" style="background: #c8e6c9; color: #2e7d32; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                        ✅ Đang hoạt động
                                    </span>
                                </td>
                            </tr>
                <?php 
                        endif;
                    endforeach;
                endif;

                if (!$hasRecurringRows):
                ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 25px; color: #999; font-style: italic;">📭 Hiện chưa đăng ký lớp học cố định lặp lại nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script src="/GiaSu/assets/js/schedule-index.js"></script>
