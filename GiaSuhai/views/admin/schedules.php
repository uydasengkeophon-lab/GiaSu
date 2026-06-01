<?php
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
    1 => 'Sáng 08:00 - 11:30',
    2 => 'Chiều 14:00 - 17:30',
    3 => 'Tối 18:00 - 21:30'
];

$statusOptions = [
    '' => 'Tất cả trạng thái',
    'open' => 'Còn trống',
    'approved' => 'Đã duyệt',
    'pending' => 'Chờ duyệt',
    'cancelled' => 'Đã huỷ'
];

$buildPageUrl = function ($targetPage) use ($filters) {
    $query = array_merge(['url' => 'admin/schedules'], $filters, ['page' => $targetPage]);
    return '?' . http_build_query($query);
};
?>

<section class="admin-page admin-workspace">
    <div class="admin-page-head">
        <div>
            <p class="admin-kicker">Lịch học cố định</p>
            <h2 class="admin-page-title">Quản lý lịch học</h2>
        </div>
        <a href="?url=schedule/index" class="admin-ghost-link"><i class="fas fa-calendar-day"></i> Xem lịch ngày</a>
    </div>

    <?php if (!empty($_GET['msg'])): ?>
        <div class="admin-alert success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="admin-alert error"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <form method="get" class="admin-filter-panel">
        <input type="hidden" name="url" value="admin/schedules">
        <input type="search" name="q" value="<?php echo htmlspecialchars($filters['q'] ?? ''); ?>" placeholder="Tìm gia sư, học viên, môn học, ghi chú...">
        <select name="tutor_id">
            <option value="0">Tất cả gia sư</option>
            <?php foreach ($tutorOptions as $tutor): ?>
                <option value="<?php echo (int) $tutor['id']; ?>" <?php echo ((int) $filters['tutor_id'] === (int) $tutor['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($tutor['full_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="student_id">
            <option value="0">Tất cả học viên</option>
            <?php foreach ($studentOptions as $student): ?>
                <option value="<?php echo (int) $student['id']; ?>" <?php echo ((int) $filters['student_id'] === (int) $student['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($student['full_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="day">
            <option value="">Tất cả thứ</option>
            <?php foreach ($daysOfWeek as $key => $label): ?>
                <option value="<?php echo $key; ?>" <?php echo ((string) ($filters['day'] ?? '') === (string) $key) ? 'selected' : ''; ?>>
                    <?php echo $label; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="status">
            <?php foreach ($statusOptions as $key => $label): ?>
                <option value="<?php echo htmlspecialchars($key); ?>" <?php echo (($filters['status'] ?? '') === $key) ? 'selected' : ''; ?>>
                    <?php echo $label; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="admin-primary-btn"><i class="fas fa-filter"></i> Lọc</button>
    </form>

    <div class="admin-card admin-create-card">
        <h3 class="admin-card-title">Tạo khung lịch mới</h3>
        <form method="post" action="?url=admin/storeSchedule" class="admin-inline-form">
            <select name="gia_su_id" required>
                <option value="">Chọn gia sư</option>
                <?php foreach ($tutorOptions as $tutor): ?>
                    <option value="<?php echo (int) $tutor['id']; ?>"><?php echo htmlspecialchars($tutor['full_name']); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="hoc_vien_id">
                <option value="0">Lịch trống</option>
                <?php foreach ($studentOptions as $student): ?>
                    <option value="<?php echo (int) $student['id']; ?>"><?php echo htmlspecialchars($student['full_name']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="mon_hoc" placeholder="Môn học" required>
            <select name="thu_trong_tuan" required>
                <option value="">Thứ</option>
                <?php foreach ($daysOfWeek as $key => $label): ?>
                    <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
            <select name="phien_hoc" required>
                <option value="">Ca học</option>
                <?php foreach ($sessionsOfDay as $key => $label): ?>
                    <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="so_buoi" min="1" value="1" title="Số buổi học">
            <input type="text" name="admin_ghi_chu" placeholder="Ghi chú admin">
            <button type="submit" class="admin-primary-btn"><i class="fas fa-plus"></i> Thêm</button>
        </form>
    </div>

    <div class="admin-timetable">
        <?php foreach ($sessionsOfDay as $sessionKey => $sessionLabel): ?>
            <div class="timetable-row">
                <div class="timetable-time"><?php echo htmlspecialchars($sessionLabel); ?></div>
                <div class="timetable-days">
                    <?php foreach ($daysOfWeek as $dayKey => $dayLabel): ?>
                        <?php
                        $items = array_filter($schedules, function ($row) use ($sessionKey, $dayKey) {
                            return (int) $row['phien_hoc'] === (int) $sessionKey && (int) $row['thu_trong_tuan'] === (int) $dayKey;
                        });
                        ?>
                        <div class="timetable-day">
                            <span class="timetable-day-title"><?php echo $dayLabel; ?></span>
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                    <span class="schedule-chip status-<?php echo htmlspecialchars($item['status_key']); ?>">
                                        <?php echo htmlspecialchars($item['mon_hoc']); ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="timetable-empty">Trống</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="admin-card">
        <div class="admin-table-wrap">
            <table class="admin-table admin-schedule-table">
                <thead>
                    <tr>
                        <th>Ca học</th>
                        <th>Gia sư</th>
                        <th>Học viên</th>
                        <th>Môn học</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                        <th>Cập nhật</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($schedules)): ?>
                        <?php foreach ($schedules as $row): ?>
                            <tr>
                                <form method="post" action="?url=admin/updateSchedule">
                                    <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($daysOfWeek[(int) $row['thu_trong_tuan']] ?? ''); ?></strong><br>
                                        <span><?php echo htmlspecialchars($sessionsOfDay[(int) $row['phien_hoc']] ?? ''); ?></span>
                                    </td>
                                    <td>
                                        <select name="gia_su_id" required>
                                            <?php foreach ($tutorOptions as $tutor): ?>
                                                <option value="<?php echo (int) $tutor['id']; ?>" <?php echo ((int) $row['gia_su'] === (int) $tutor['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($tutor['full_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="hoc_vien_id">
                                            <option value="0">Lịch trống</option>
                                            <?php foreach ($studentOptions as $student): ?>
                                                <option value="<?php echo (int) $student['id']; ?>" <?php echo ((int) $row['hoc_vien'] === (int) $student['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($student['full_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="mon_hoc" value="<?php echo htmlspecialchars($row['mon_hoc']); ?>" required>
                                        <div class="admin-mini-grid">
                                            <select name="thu_trong_tuan" required>
                                                <?php foreach ($daysOfWeek as $key => $label): ?>
                                                    <option value="<?php echo $key; ?>" <?php echo ((int) $row['thu_trong_tuan'] === (int) $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select name="phien_hoc" required>
                                                <?php foreach ($sessionsOfDay as $key => $label): ?>
                                                    <option value="<?php echo $key; ?>" <?php echo ((int) $row['phien_hoc'] === (int) $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-pill status-<?php echo htmlspecialchars($row['status_key']); ?>"><?php echo htmlspecialchars($row['status_label']); ?></span>
                                        <select name="trang_thai">
                                            <option value="1" <?php echo ((int) $row['trang_thai'] === 1) ? 'selected' : ''; ?>>Đã duyệt</option>
                                            <option value="2" <?php echo ((int) $row['trang_thai'] === 2) ? 'selected' : ''; ?>>Chờ duyệt</option>
                                            <option value="0" <?php echo ((int) $row['trang_thai'] === 0) ? 'selected' : ''; ?>>Đã huỷ</option>
                                        </select>
                                        <input type="number" name="so_buoi" min="1" value="<?php echo (int) ($row['so_buoi'] ?? 1); ?>" title="Số buổi">
                                    </td>
                                    <td><textarea name="admin_ghi_chu" rows="2"><?php echo htmlspecialchars($row['admin_ghi_chu'] ?? ''); ?></textarea></td>
                                    <td class="admin-actions-cell">
                                        <button type="submit" class="admin-small-btn">Lưu</button>
                                        <a href="?url=admin/approveSchedule&id=<?php echo (int) $row['id']; ?>" class="admin-action approve">Duyệt</a>
                                        <a href="?url=admin/cancelSchedule&id=<?php echo (int) $row['id']; ?>" class="admin-action delete" data-confirm="Huỷ lịch học này?">Huỷ</a>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="admin-empty">Không tìm thấy lịch học phù hợp.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?php echo htmlspecialchars($buildPageUrl($i)); ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    </div>
</section>
