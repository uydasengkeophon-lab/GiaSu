<?php
// views/schedule/detail.php
if (empty($scheduleDetail)) {
    echo "<p style='text-align:center; padding:50px; color:#999;'>Không có dữ liệu buổi học.</p>";
    return;
}
?>

<section class="schedule-detail-container" style="font-family: Arial, sans-serif; padding: 30px 15px; max-width: 700px; margin: 0 auto;">
    <div style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 25px rgba(0,0,0,0.06); border: 1px solid #eef0f2;">
        
        <h2 style="color: #4b2d7f; margin-top: 0; border-bottom: 2px solid #4b2d7f; padding-bottom: 12px; font-weight: bold;">
            <i class="fas fa-info-circle"></i> Chi Tiết Buổi Học Hệ Thống
        </h2>

        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 15px;">
            <tr style="border-bottom: 1px solid #f5f5f5;">
                <td style="padding: 12px 0; color: #666; font-weight: 600; width: 35%;">👨‍🏫 Gia sư giảng dạy:</td>
                <td style="padding: 12px 0; font-weight: bold; color: #333;">
                    <?= htmlspecialchars($scheduleDetail['tutor_name'] ?? 'Chưa rõ'); ?>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #f5f5f5;">
                <td style="padding: 12px 0; color: #666; font-weight: 600;">👨‍🎓 Học viên tham gia:</td>
                <td style="padding: 12px 0; font-weight: bold; color: #1976d2;">
                    <?= htmlspecialchars($scheduleDetail['student_name'] ?? 'Chưa rõ'); ?>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #f5f5f5;">
                <td style="padding: 12px 0; color: #666; font-weight: 600;">📚 Lớp môn phụ trách:</td>
                <td style="padding: 12px 0; font-weight: bold; color: #e91e63;">
                    <?= htmlspecialchars($scheduleDetail['mon_hoc'] ?? 'Chưa xếp môn'); ?>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #f5f5f5;">
                <td style="padding: 12px 0; color: #666; font-weight: 600;">📅 Ngày lên lớp:</td>
                <td style="padding: 12px 0; font-weight: bold; color: #333;">
                    <?= !empty($scheduleDetail['ngay']) ? date('d/m/Y', strtotime($scheduleDetail['ngay'])) : '--/--/----'; ?>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #f5f5f5;">
                <td style="padding: 12px 0; color: #666; font-weight: 600;">⏰ Thời gian bắt đầu:</td>
                <td style="padding: 12px 0; font-weight: bold; color: #2e7d32;">
                    <?= !empty($scheduleDetail['gio_bat_dau']) ? date('H:i', strtotime($scheduleDetail['gio_bat_dau'])) : '--:--'; ?>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #f5f5f5;">
                <td style="padding: 12px 0; color: #666; font-weight: 600;">⌛ Thời gian kết thúc:</td>
                <td style="padding: 12px 0; font-weight: bold; color: #c62828;">
                    <?= !empty($scheduleDetail['gio_ket_thuc']) ? date('H:i', strtotime($scheduleDetail['gio_ket_thuc'])) : '--:--'; ?>
                </td>
            </tr>
        </table>

        <div style="text-align: right; margin-top: 30px;">
            <a href="?url=schedule/index" style="background: #f0f2f5; color: #333; padding: 10px 24px; border-radius: 20px; text-decoration: none; font-weight: bold; font-size: 14px; border: 1px solid #ccc; display: inline-block;">
                <i class="fas fa-arrow-left"></i> Quay lại lịch học
            </a>
        </div>

    </div>
</section>