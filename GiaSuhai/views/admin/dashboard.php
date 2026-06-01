<section class="admin-page container">
    <h2 class="admin-page-title">Trang Quản Trị Admin</h2>

    <div class="admin-card">
        <div class="admin-header-row">
            <h4 class="admin-card-title">Danh sách thành viên</h4>
            <form method="get" action="" class="admin-search-form" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                <input type="hidden" name="url" value="admin/dashboard">
                <div style="display:flex;gap:8px;align-items:center;">
                    <label for="q" style="font-weight:600;">Tìm kiếm:</label>
                    <input type="search" id="q" name="q" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" placeholder="Tên đăng nhập, email, ID..." style="padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;min-width:220px;">
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <label for="role" style="font-weight:600;">Vai trò:</label>
                    <select id="role" name="role" style="padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;">
                        <option value="" <?php echo (isset($_GET['role']) && $_GET['role'] === '') ? 'selected' : ''; ?>>Tất cả</option>
                        <option value="tutor" <?php echo (isset($_GET['role']) && $_GET['role'] === 'tutor') ? 'selected' : ''; ?>>Gia sư</option>
                        <option value="student" <?php echo (isset($_GET['role']) && $_GET['role'] === 'student') ? 'selected' : ''; ?>>Học sinh</option>
                    </select>
                </div>
                <button type="submit" style="background:#4b2d7f;color:#fff;padding:10px 18px;border:none;border-radius:8px;cursor:pointer;">Tìm</button>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên đăng nhập</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo (int) $user['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['role'] == 'tutor'): ?>
                                        <span class="role-badge role-tutor">Gia sư</span>
                                    <?php else: ?>
                                        <span class="role-badge role-student">Học sinh</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?url=admin/detail&id=<?php echo (int) $user['id']; ?>" class="admin-action view" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i> Xem
                                    </a>
                                    <a href="?url=admin/edit&id=<?php echo (int) $user['id']; ?>" class="admin-action edit">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                    <a href="?url=admin/delete&id=<?php echo (int) $user['id']; ?>" class="admin-action delete" data-confirm="Bạn có chắc chắn muốn xóa người này không? Dữ liệu bên bảng Tutor/Student cũng sẽ mất hết!">
                                        <i class="fas fa-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="admin-empty">Chưa có thành viên nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>