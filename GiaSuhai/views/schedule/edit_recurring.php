<?php
if (!isset($data)) {
    echo "Không tìm thấy dữ liệu lịch cố định";
    exit;
}
?>

<link rel="stylesheet" href="/GiaSuhai/assets/css/schedule-edit.css">

<section class="schedule-page">
    <div class="page-header">
        <a href="?url=schedule/recurring" class="back-link">← Quay lại</a>
        <h2 class="schedule-title">Chỉnh Sửa Lịch Học Cố Định</h2>
    </div>

    <div class="form-container" style="max-width: 600px;">
        <form method="POST" action="?url=schedule/updateRecurring" class="edit-recurring-form">
            <input type="hidden" name="id" value="<?php echo (int)$data['id']; ?>">

            <div class="form-group">
                <label for="mon_hoc">Môn Học *</label>
                <input 
                    type="text" 
                    name="mon_hoc" 
                    id="mon_hoc" 
                    value="<?php echo htmlspecialchars($data['mon_hoc'] ?? ''); ?>"
                    placeholder="Ví dụ: Toán, Tiếng Anh..." 
                    required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="thu_trong_tuan">Ngày Trong Tuần *</label>
                    <select name="thu_trong_tuan" id="thu_trong_tuan" required>
                        <option value="">-- Chọn ngày --</option>
                        <option value="0" <?php echo ((int)$data['thu_trong_tuan'] === 0) ? 'selected' : ''; ?>>Thứ 2 (Monday)</option>
                        <option value="1" <?php echo ((int)$data['thu_trong_tuan'] === 1) ? 'selected' : ''; ?>>Thứ 3 (Tuesday)</option>
                        <option value="2" <?php echo ((int)$data['thu_trong_tuan'] === 2) ? 'selected' : ''; ?>>Thứ 4 (Wednesday)</option>
                        <option value="3" <?php echo ((int)$data['thu_trong_tuan'] === 3) ? 'selected' : ''; ?>>Thứ 5 (Thursday)</option>
                        <option value="4" <?php echo ((int)$data['thu_trong_tuan'] === 4) ? 'selected' : ''; ?>>Thứ 6 (Friday)</option>
                        <option value="5" <?php echo ((int)$data['thu_trong_tuan'] === 5) ? 'selected' : ''; ?>>Thứ 7 (Saturday)</option>
                        <option value="6" <?php echo ((int)$data['thu_trong_tuan'] === 6) ? 'selected' : ''; ?>>Chủ Nhật (Sunday)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="phien_hoc">Buổi Học *</label>
                    <select name="phien_hoc" id="phien_hoc" required>
                        <option value="">-- Chọn buổi --</option>
                        <option value="1" <?php echo ((int)$data['phien_hoc'] === 1) ? 'selected' : ''; ?>>Sáng (7:00 - 11:00)</option>
                        <option value="2" <?php echo ((int)$data['phien_hoc'] === 2) ? 'selected' : ''; ?>>Chiều (14:00 - 17:00)</option>
                    </select>
                </div>
            </div>

            <?php if (isset($isAdminEdit) && $isAdminEdit): ?>
                <div class="form-group">
                    <label for="gia_su">Gia Sư *</label>
                    <select name="gia_su" id="gia_su" required>
                        <option value="">-- Chọn gia sư --</option>
                        <?php foreach ($tutorOptions as $tutor): ?>
                            <option value="<?php echo (int)$tutor['id']; ?>" 
                                <?php echo ((int)$tutor['id'] === (int)$data['gia_su']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tutor['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="hoc_vien">Học Viên *</label>
                    <select name="hoc_vien" id="hoc_vien" required>
                        <option value="">-- Chọn học viên --</option>
                        <?php foreach ($studentOptions as $student): ?>
                            <option value="<?php echo (int)$student['id']; ?>" 
                                <?php echo ((int)$student['id'] === (int)$data['hoc_vien']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($student['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="info-group">
                    <label>Gia Sư</label>
                    <p class="info-text"><?php echo htmlspecialchars($data['tutor_name'] ?? 'N/A'); ?></p>
                </div>

                <div class="info-group">
                    <label>Học Viên</label>
                    <p class="info-text"><?php echo htmlspecialchars($data['student_name'] ?? 'N/A'); ?></p>
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Cập Nhật Lịch Cố Định</button>
                <a href="?url=schedule/recurring" class="btn btn-secondary">Hủy</a>
            </div>
        </form>

        <div style="background: #e3f2fd; border-left: 4px solid #2196F3; padding: 12px; border-radius: 4px; margin-top: 20px;">
            <strong>ℹ️ Thông Tin:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Ngày hiện tại: <strong><?php echo $data['day_name']; ?></strong></li>
                <li>Buổi học: <strong><?php echo $data['session_name']; ?></strong></li>
                <li>Thay đổi sẽ có hiệu lực cho tất cả các buổi học tiếp theo</li>
            </ul>
        </div>
    </div>
</section>

<style>
    .page-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        gap: 20px;
    }

    .back-link {
        color: #2196F3;
        text-decoration: none;
        font-weight: bold;
        transition: color 0.3s;
    }

    .back-link:hover {
        color: #1976D2;
    }

    .form-container {
        background: white;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #ddd;
    }

    .edit-recurring-form {
        display: flex;
        flex-direction: column;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 15px;
    }

    .form-group label {
        margin-bottom: 5px;
        font-weight: bold;
        color: #333;
    }

    .form-group input,
    .form-group select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #2196F3;
        box-shadow: 0 0 5px rgba(33, 150, 243, 0.3);
    }

    .info-group {
        margin-bottom: 15px;
    }

    .info-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #333;
    }

    .info-text {
        padding: 8px 12px;
        background: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 4px;
        color: #666;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        transition: background 0.3s, color 0.3s;
    }

    .btn-primary {
        background: #2196F3;
        color: white;
        flex: 1;
    }

    .btn-primary:hover {
        background: #1976D2;
    }

    .btn-secondary {
        background: #f0f0f0;
        color: #333;
        flex: 1;
    }

    .btn-secondary:hover {
        background: #e0e0e0;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
    }
</style>
