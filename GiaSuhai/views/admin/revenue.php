<?php
$money = function ($value) {
    return number_format((float) $value, 0, ',', '.') . 'đ';
};

$maxMonthly = 0;
foreach ($monthlyRevenue as $item) {
    $maxMonthly = max($maxMonthly, (float) $item['revenue']);
}

$subjectTotal = array_sum(array_map(function ($item) {
    return (int) $item['booking_count'];
}, $popularSubjects));
?>

<section class="admin-page admin-workspace">
    <div class="admin-page-head">
        <div>
            <p class="admin-kicker">Dashboard Admin</p>
            <h2 class="admin-page-title">Thống kê doanh thu</h2>
        </div>
    </div>

    <form method="get" class="admin-filter-panel">
        <input type="hidden" name="url" value="admin/revenue">
        <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
        <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
        <select name="tutor_id">
            <option value="0">Tất cả gia sư</option>
            <?php foreach ($tutorOptions as $tutor): ?>
                <option value="<?php echo (int) $tutor['id']; ?>" <?php echo ((int) $filters['tutor_id'] === (int) $tutor['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($tutor['full_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="search" name="subject" value="<?php echo htmlspecialchars($filters['subject']); ?>" placeholder="Lọc theo môn học">
        <button type="submit" class="admin-primary-btn"><i class="fas fa-chart-simple"></i> Xem thống kê</button>
    </form>

    <div class="stats-grid">
        <div class="stat-card revenue">
            <span>Tổng doanh thu</span>
            <strong><?php echo $money($summary['total_revenue'] ?? 0); ?></strong>
            <small><?php echo (int) ($summary['total_bookings'] ?? 0); ?> booking đã thu</small>
        </div>
        <div class="stat-card">
            <span>Doanh thu hôm nay</span>
            <strong><?php echo $money($periodRevenue['today'] ?? 0); ?></strong>
            <small>Theo ngày học</small>
        </div>
        <div class="stat-card">
            <span>Tuần này</span>
            <strong><?php echo $money($periodRevenue['this_week'] ?? 0); ?></strong>
            <small>ISO week hiện tại</small>
        </div>
        <div class="stat-card">
            <span>Tháng này</span>
            <strong><?php echo $money($periodRevenue['this_month'] ?? 0); ?></strong>
            <small><?php echo $money($periodRevenue['this_year'] ?? 0); ?> trong năm</small>
        </div>
    </div>

    <div class="stats-grid compact">
        <div class="metric-card"><span>Lớp học</span><strong><?php echo (int) ($systemStats['total_classes'] ?? 0); ?></strong></div>
        <div class="metric-card"><span>Học viên</span><strong><?php echo (int) ($systemStats['total_students'] ?? 0); ?></strong></div>
        <div class="metric-card"><span>Gia sư</span><strong><?php echo (int) ($systemStats['total_tutors'] ?? 0); ?></strong></div>
        <div class="metric-card"><span>Tổng booking</span><strong><?php echo (int) ($systemStats['total_bookings'] ?? 0); ?></strong></div>
    </div>

    <div class="dashboard-grid">
        <div class="admin-card chart-card">
            <h3 class="admin-card-title">Biểu đồ doanh thu theo tháng</h3>
            <div class="bar-chart">
                <?php if (!empty($monthlyRevenue)): ?>
                    <?php foreach ($monthlyRevenue as $item): ?>
                        <?php $height = $maxMonthly > 0 ? max(8, ((float) $item['revenue'] / $maxMonthly) * 100) : 8; ?>
                        <div class="bar-item">
                            <div class="bar-value"><?php echo $money($item['revenue']); ?></div>
                            <div class="bar-track"><span style="height: <?php echo $height; ?>%"></span></div>
                            <small><?php echo htmlspecialchars($item['period_label']); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="admin-empty">Chưa có doanh thu trong bộ lọc này.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-card chart-card">
            <h3 class="admin-card-title">Môn học phổ biến</h3>
            <div class="donut-list">
                <?php if (!empty($popularSubjects)): ?>
                    <?php foreach ($popularSubjects as $item): ?>
                        <?php $percent = $subjectTotal > 0 ? round(((int) $item['booking_count'] / $subjectTotal) * 100) : 0; ?>
                        <div class="donut-row">
                            <span><?php echo htmlspecialchars($item['subject_name']); ?></span>
                            <div><i style="width: <?php echo $percent; ?>%"></i></div>
                            <strong><?php echo $percent; ?>%</strong>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="admin-empty">Chưa có dữ liệu môn học.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="admin-card">
            <h3 class="admin-card-title">Gia sư doanh thu cao nhất</h3>
            <div class="rank-list">
                <?php foreach ($topTutorsRevenue as $index => $row): ?>
                    <div class="rank-row">
                        <span>#<?php echo $index + 1; ?></span>
                        <strong><?php echo htmlspecialchars($row['tutor_name']); ?></strong>
                        <em><?php echo $money($row['revenue']); ?></em>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($topTutorsRevenue)): ?><p class="admin-empty">Chưa có dữ liệu.</p><?php endif; ?>
            </div>
        </div>

        <div class="admin-card">
            <h3 class="admin-card-title">Gia sư nhiều học viên nhất</h3>
            <div class="rank-list">
                <?php foreach ($topTutorsStudents as $index => $row): ?>
                    <div class="rank-row">
                        <span>#<?php echo $index + 1; ?></span>
                        <strong><?php echo htmlspecialchars($row['tutor_name']); ?></strong>
                        <em><?php echo (int) $row['student_count']; ?> học viên</em>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($topTutorsStudents)): ?><p class="admin-empty">Chưa có dữ liệu.</p><?php endif; ?>
            </div>
        </div>
    </div>
</section>
