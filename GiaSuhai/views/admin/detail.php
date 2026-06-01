<section class="admin-page container">
    <div class="admin-form-card admin-detail-card">

        <div class="admin-header-row">
            <h2 class="admin-page-title">Hồ Sơ Chi Tiết</h2>
            <a href="?url=admin/dashboard" class="btn-primary admin-back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <div class="admin-panel account-panel">
            <h4 class="admin-panel-title">
                <i class="fas fa-user-shield"></i> Tài khoản
            </h4>
            <div class="admin-grid-2">
                <p><strong>ID:</strong> #<?php echo (int) $user['id']; ?></p>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Vai trò:</strong>
                    <?php if ($user['role'] == 'tutor'): ?>
                        <span class="role-badge role-tutor">GIASU</span>
                    <?php else: ?>
                        <span class="role-badge role-student">HỌC SINH</span>
                    <?php endif; ?>
                </p>
                <p><strong>Ngày tạo:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
            </div>
        </div>

        <?php if ($info): ?>
            <div class="admin-panel profile-panel">
                <h4 class="admin-panel-title secondary">
                    <i class="fas fa-id-card"></i> Thông tin cá nhân
                </h4>

                <div class="admin-profile-content">
                    <p><strong>Họ và tên:</strong> <?php echo htmlspecialchars($info['full_name'] ?? ''); ?></p>
                    <p><strong>Số điện thoại:</strong> <?php echo !empty($info['phone']) ? htmlspecialchars($info['phone']) : 'Chưa cập nhật'; ?></p>

                    <?php if ($user['role'] == 'tutor'): ?>
                        <p><strong>Môn dạy:</strong> <?php echo htmlspecialchars($info['subjects'] ?? ''); ?></p>
                        <p><strong>Học phí:</strong> <?php echo number_format($info['hourly_rate'] ?? 0); ?> VNĐ/giờ</p>
                        <hr class="admin-dashed-divider">
                        <p><strong>Giới thiệu:</strong></p>
                        <p class="admin-bio-box">
                            <?php echo $info['bio'] ? nl2br($info['bio']) : 'Chưa có bài giới thiệu.'; ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($user['role'] == 'student'): ?>
                        <p><strong>Trình độ (Lớp):</strong> <?php echo htmlspecialchars($info['grade_level'] ?? ''); ?></p>
                        <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($info['address'] ?? ''); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <p class="admin-error-text">
                Người dùng này chưa cập nhật thông tin hồ sơ chi tiết.
            </p>
        <?php endif; ?>

        <div class="admin-footer-action">
            <a href="?url=admin/edit&id=<?php echo (int) $user['id']; ?>" class="btn-primary admin-edit-btn">
                <i class="fas fa-edit"></i> Chỉnh sửa người này
            </a>
        </div>
    </div>
</section>