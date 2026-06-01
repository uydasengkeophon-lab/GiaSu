<link rel="stylesheet" href="/GiaSu/assets/css/schedule-edit.css">

<section class="schedule-edit-page">
    <h2>Sửa lịch học</h2>

    <form method="POST" action="?url=schedule/update" id="schedule-edit-form">
        <input type="hidden" name="id" value="<?php echo (int) $data['id']; ?>">

        <div class="field-group readonly-group">
            <label>Gia sư</label>
            <p><?php echo htmlspecialchars($data['tutor_name'] ?? ''); ?></p>
        </div>

        <div class="field-group readonly-group">
            <label>Học viên</label>
            <p><?php echo htmlspecialchars($data['student_name'] ?? ''); ?></p>
        </div>

        <?php if (!empty($isAdminEdit)): ?>
            <div class="field-group">
                <label for="gia_su">Gia sư</label>
                <select id="gia_su" name="gia_su" required>
                    <option value="">Chọn gia sư</option>
                    <?php foreach ($tutorOptions as $tutorOption): ?>
                        <option value="<?php echo (int) $tutorOption['id']; ?>" <?php echo ((int) $tutorOption['id'] === (int) $data['gia_su']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tutorOption['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field-group">
                <label for="hoc_vien">Học viên</label>
                <select id="hoc_vien" name="hoc_vien" required>
                    <option value="">Chọn học viên</option>
                    <?php foreach ($studentOptions as $studentOption): ?>
                        <option value="<?php echo (int) $studentOption['id']; ?>" <?php echo ((int) $studentOption['id'] === (int) $data['hoc_vien']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($studentOption['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="field-group">
            <label for="mon_hoc">Môn học</label>
            <input id="mon_hoc" type="text" name="mon_hoc" value="<?php echo htmlspecialchars($data['mon_hoc'] ?? ''); ?>" required>
        </div>

        <div class="field-group">
            <label for="ngay">Ngày học</label>
            <input id="ngay" type="date" name="ngay" value="<?php echo htmlspecialchars($data['ngay'] ?? ''); ?>" required>
        </div>

        <div class="field-group">
            <label for="gio_bat_dau">Giờ bắt đầu</label>
            <input id="gio_bat_dau" type="time" name="gio_bat_dau" value="<?php echo htmlspecialchars($data['gio_bat_dau'] ?? ''); ?>" required>
        </div>

        <div class="field-group">
            <label for="gio_ket_thuc">Giờ kết thúc</label>
            <input id="gio_ket_thuc" type="time" name="gio_ket_thuc" value="<?php echo htmlspecialchars($data['gio_ket_thuc'] ?? ''); ?>" required>
        </div>

        <div class="form-actions">
            <a href="?url=schedule/index" class="btn-secondary">Quay lại</a>
            <button type="submit" class="btn-primary">Cập nhật</button>
        </div>
    </form>
</section>

<script src="/GiaSu/assets/js/schedule-edit.js"></script>