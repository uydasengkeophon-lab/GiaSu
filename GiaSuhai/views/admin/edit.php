<section class="admin-page container">
    <div class="admin-form-card">
        <h2 class="admin-page-title center">Chỉnh Sửa Thông Tin</h2>
        <p class="admin-subtitle center">
            Đang sửa: <strong><?php echo htmlspecialchars($user['username']); ?></strong> (<?php echo htmlspecialchars(ucfirst($user['role'])); ?>)
        </p>

        <form action="?url=admin/updatePost" method="POST" enctype="multipart/form-data" class="admin-form">
            <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">

            <div class="form-group">
                <label for="role">Vai trò:</label>
                <select id="role" name="role" class="form-control" data-role-switch="user-edit" required>
                    <option value="student" <?php echo ($user['role'] === 'student') ? 'selected' : ''; ?>>Học sinh</option>
                    <option value="tutor" <?php echo ($user['role'] === 'tutor') ? 'selected' : ''; ?>>Gia sư</option>
                </select>
            </div>

            <div class="form-group">
                <label for="full_name">Họ và tên:</label>
                <input id="full_name" type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($info['full_name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Số điện thoại:</label>
                <input id="phone" type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($info['phone'] ?? ''); ?>">
            </div>

            <div class="role-fields" data-role-fields="tutor">
                <div class="form-group">
                    <label for="avatar" class="admin-file-label">Ảnh đại diện:</label>

                    <?php if (!empty($tutorInfo['avatar'])): ?>
                        <div class="admin-current-avatar">
                            <img src="assets/uploads/<?php echo htmlspecialchars($tutorInfo['avatar']); ?>" alt="Ảnh hiện tại" class="admin-avatar-preview">
                            <small>Ảnh hiện tại</small>
                        </div>
                    <?php endif; ?>

                    <input id="avatar" type="file" name="avatar" class="form-control" accept="image/*">
                    <small class="admin-hint">(Để trống nếu không muốn thay đổi ảnh)</small>
                </div>

                <div class="form-group">
                    <label for="subjects">Môn dạy:</label>
                    <input id="subjects" type="text" name="subjects" class="form-control" value="<?php echo htmlspecialchars($tutorInfo['subjects'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="hourly_rate">Học phí / giờ:</label>
                    <input id="hourly_rate" type="number" name="hourly_rate" class="form-control" value="<?php echo htmlspecialchars($tutorInfo['hourly_rate'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="bio">Giới thiệu bản thân:</label>
                    <textarea id="bio" name="bio" rows="4" class="form-control admin-textarea"><?php echo htmlspecialchars($tutorInfo['bio'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="role-fields" data-role-fields="student">
                <div class="form-group">
                    <label for="grade_level">Lớp (Trình độ):</label>
                    <input id="grade_level" type="text" name="grade_level" class="form-control" value="<?php echo htmlspecialchars($studentInfo['grade_level'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="address">Địa chỉ:</label>
                    <input id="address" type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($studentInfo['address'] ?? ''); ?>">
                </div>
            </div>

            <button type="submit" class="btn-primary full-width admin-submit-btn">Lưu Thay Đổi</button>
            <a href="?url=admin/dashboard" class="admin-cancel-link">Hủy bỏ</a>
        </form>
    </div>
</section>