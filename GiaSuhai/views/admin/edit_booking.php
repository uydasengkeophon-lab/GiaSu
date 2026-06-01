<section class="admin-page container">
    <h2 class="admin-page-title">Sửa Booking</h2>

    <div class="admin-card">
        <form method="POST" action="?url=admin/updateBooking" class="admin-form">
            <input type="hidden" name="id" value="<?php echo (int) $booking['id']; ?>">

            <div class="form-group">
                <label for="tutor_id">Gia sư</label>
                <select name="tutor_id" id="tutor_id" required>
                    <option value="">-- Chọn gia sư --</option>
                    <?php foreach ($tutorOptions as $option): ?>
                        <option value="<?php echo (int) $option['id']; ?>" <?php echo ((int) $booking['tutor_id'] === (int) $option['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="student_id">Học viên</label>
                <select name="student_id" id="student_id" required>
                    <option value="">-- Chọn học viên --</option>
                    <?php foreach ($studentOptions as $option): ?>
                        <option value="<?php echo (int) $option['id']; ?>" <?php echo ((int) $booking['student_id'] === (int) $option['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="amount">Số tiền</label>
                <input type="number" name="amount" id="amount" value="<?php echo htmlspecialchars($booking['amount']); ?>" min="0" required>
            </div>

            <div class="form-group">
                <label for="study_date">Ngày học</label>
                <input type="date" name="study_date" id="study_date" value="<?php echo htmlspecialchars($booking['study_date']); ?>" required>
            </div>

            <div class="form-group">
                <label for="start_time">Giờ bắt đầu</label>
                <input type="time" name="start_time" id="start_time" value="<?php echo htmlspecialchars($booking['start_time']); ?>" required>
            </div>

            <div class="form-group">
                <label for="end_time">Giờ kết thúc</label>
                <input type="time" name="end_time" id="end_time" value="<?php echo htmlspecialchars($booking['end_time']); ?>" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                <a href="?url=admin/bookings" class="btn btn-secondary">Huỷ</a>
            </div>
        </form>
    </div>
</section>
