<?php
// views/admin/bookings.php
// Mã nguồn hiển thị danh sách phê duyệt đơn đăng ký học cố định hàng tuần dành cho Admin
?>

<style>
    /* Hệ thống CSS Badge trạng thái màu sắc trực quan theo đúng yêu cầu */
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    /* Các class màu sắc cho trạng thái thanh toán */
    .status-badge.status-unpaid { background-color: #ffe0b2; color: #e65100; } /* Cam/Vàng - Chưa đóng tiền */
    .status-badge.status-paid-bill { background-color: #e3f2fd; color: #1565c0; } /* Xanh dương - Đã đóng tiền */

    /* Các class màu sắc cho trạng thái duyệt */
    .status-badge.pending { background-color: #fff8e1; color: #ffb300; } /* Vàng - Chờ thanh toán */
    .status-badge.checking { background-color: #e3f2fd; color: #1e88e5; } /* Xanh dương - Chờ phê duyệt */
    .status-badge.approved { background-color: #e8f5e9; color: #2e7d32; } /* Xanh lá - Đã duyệt cấp lớp */
    .status-badge.rejected { background-color: #ffebee; color: #c62828; } /* Đỏ - Từ chối / Đã hủy */

    /* Định dạng nút hành động thao tác Dashboard */
    .admin-action {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 12px;
        font-weight: bold;
        margin-right: 3px;
        transition: all 0.2s ease;
    }
    .admin-action.edit { background: #0288d1; color: white; }
    .admin-action.edit:hover { background: #01579b; }
    .admin-action.delete { background: #e53935; color: white; }
    .admin-action.delete:hover { background: #b71c1c; }
    
    .admin-action.pay-confirm { background: #ff9800; color: white; }
    .admin-action.pay-confirm:hover { background: #f57c00; }
    .admin-action.approve-btn { background: #2e7d32; color: white; }
    .admin-action.approve-btn:hover { background: #1b5e20; }
</style>

<section class="admin-page container" style="font-family: Arial, sans-serif; padding: 20px;">
    <h2 class="admin-page-title" style="color: #4a148c; font-weight: bold; margin-bottom: 5px;">
        <i class="fas fa-clipboard-check"></i> Quản Lý Duyệt Đăng Ký Học
    </h2>
    <p style="color: #78909c; font-size: 13px; margin-bottom: 25px; margin-top: 0;">Kiểm duyệt quy trình thanh toán học phí học phí và kích hoạt xếp lớp cố định lặp lại hàng tuần.</p>

    <?php if (!empty($_GET['msg'])): ?>
        <div class="admin-alert success" style="background: #e8f5e9; color: #2e7d32; padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c8e6c9;">
            ✓ <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="admin-alert error" style="background: #ffebee; color: #c62828; padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #ffcdd2;">
            ✕ <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <div class="admin-card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 15px;">
        <div class="admin-table-wrap" style="overflow-x: auto;">
            <table class="admin-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: #4a148c; color: white; text-align: left;">
                        <th style="padding: 12px 10px;">ID</th>
                        <th style="padding: 12px 10px;">Học viên</th>
                        <th style="padding: 12px 10px;">Gia sư</th>
                        <th style="padding: 12px 10px;">Môn học</th>
                        <th style="padding: 12px 10px;">Thứ học</th>
                        <th style="padding: 12px 10px;">Ca học</th>
                        <th style="padding: 12px 10px;">Trạng thái thanh toán</th>
                        <th style="padding: 12px 10px;">Trạng thái duyệt</th>
                        <th style="padding: 12px 10px;">Ngày đăng ký</th>
                        <th style="padding: 12px 10px; text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Khởi tạo mảng map dữ liệu số thành chuỗi Tiếng Việt thân thiện
                    $dayMap = [0 => 'Thứ Hai', 1 => 'Thứ Ba', 2 => 'Thứ Tư', 3 => 'Thứ Năm', 4 => 'Thứ Sáu', 5 => 'Thứ Bảy', 6 => 'Chủ Nhật'];
                    $sessionMap = [1 => 'Buổi Sáng', 2 => 'Buổi Chiều', 3 => 'Buổi Tối'];

                    if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $b): 
                            $status = $b['booking_status'] ?? $b['status'];
                            $bookingId = (int)($b['booking_id'] ?? $b['id']);
                        ?>
                            <tr style="border-bottom: 1px solid #eceff1;">
                                <td style="padding: 12px 10px; font-weight: bold; color: #4a148c;">#<?php echo $bookingId; ?></td>
                                <td style="padding: 12px 10px; font-weight: bold; color: #333;"><?php echo htmlspecialchars($b['student_name']); ?></td>
                                <td style="padding: 12px 10px; color: #555;"><?php echo htmlspecialchars($b['tutor_name']); ?></td>
                                <td style="padding: 12px 10px; color: #e91e63; font-weight: bold;"><?php echo htmlspecialchars($b['subject_name'] ?? 'Chưa gán môn'); ?></td>
                                <td style="padding: 12px 10px;"><?php echo isset($b['thu_trong_tuan']) ? $dayMap[(int)$b['thu_trong_tuan']] : 'Lịch lẻ'; ?></td>
                                <td style="padding: 12px 10px;"><?php echo isset($b['phien_hoc']) ? $sessionMap[(int)$b['phien_hoc']] : 'Chưa xếp ca'; ?></td>

                                <td style="padding: 12px 10px;">
                                    <?php if ($status === 'cho_thanh_toan' || $status === 'pending'): ?>
                                        <span class="status-badge status-unpaid">Chưa đóng tiền</span>
                                    <?php else: ?>
                                        <span class="status-badge status-paid-bill">Đã thanh toán</span>
                                    <?php endif; ?>
                                </td>

                                <td style="padding: 12px 10px;">
                                    <?php if ($status === 'cho_thanh_toan' || $status === 'pending'): ?>
                                        <span class="status-badge pending">Chờ thanh toán</span>
                                    <?php elseif ($status === 'da_thanh_toan' || $status === 'paid'): ?>
                                        <span class="status-badge checking">Chờ phê duyệt</span>
                                    <?php elseif ($status === 'approved'): ?>
                                        <span class="status-badge approved">Đã duyệt</span>
                                    <?php elseif ($status === 'rejected'): ?>
                                        <span class="status-badge rejected">Đã từ chối</span>
                                    <?php else: ?>
                                        <span class="status-badge pending"><?php echo htmlspecialchars($status); ?></span>
                                    <?php endif; ?>
                                </td>

                                <td style="padding: 12px 10px; color: #888; font-size: 12px;">
                                    <?php echo !empty($b['booking_date']) ? date('H:i d/m/Y', strtotime($b['booking_date'])) : date('d/m/Y'); ?>
                                </td>

                                <td style="padding: 12px 10px; text-align: center; white-space: nowrap;">
                                    <a href="?url=admin/editBooking&id=<?php echo $bookingId; ?>" class="admin-action edit">Sửa</a>
                                    <a href="?url=admin/deleteBooking&id=<?php echo $bookingId; ?>" class="admin-action delete" onclick="return confirm('Bạn có chắc chắn muốn xoá đơn đăng ký này?')">Xoá</a>
                                    
                                    <?php if ($status === 'cho_thanh_toan' || $status === 'pending'): ?>
                                        <a href="?url=admin/updatePaymentStatus&id=<?php echo $bookingId; ?>&status=da_thanh_toan" 
                                           class="admin-action pay-confirm" 
                                           onclick="return confirm('Xác nhận học viên đã hoàn tất đóng học phí cho trung tâm?')">
                                            ✓ Xác nhận tiền
                                        </a>
                                    <?php elseif ($status === 'da_thanh_toan' || $status === 'paid'): ?>
                                        <a href="?url=admin/approveBooking&id=<?php echo $bookingId; ?>" 
                                           class="admin-action approve-btn" 
                                           onclick="return confirm('Xác nhận phê duyệt đơn đăng ký học? Hệ thống sẽ gán học viên vào lịch cố định ngay!')">
                                            🎯 Duyệt cấp lớp
                                        </a>
                                    <?php elseif ($status === 'approved'): ?>
                                        <span style="color: #2e7d32; font-weight: bold; font-size: 12px; margin-left: 5px;"><i class="fas fa-check-circle"></i> Đang học</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="admin-empty" style="text-align: center; padding: 30px; color: #999; font-style: italic;">📬 Hiện tại không có dữ liệu đơn đăng ký học nào trên hệ thống.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>