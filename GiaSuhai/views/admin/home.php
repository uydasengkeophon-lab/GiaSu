<section class="admin-page container">
    <h2 class="admin-page-title">Danh Sách Liên Hệ</h2>

    <div class="admin-card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Nội dung</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contacts)): ?>
                        <?php foreach ($contacts as $c): ?>
                            <tr>
                                <td><?php echo (int) $c['id']; ?></td>
                                <td><?php echo htmlspecialchars($c['fullname'] ?? $c['name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($c['email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($c['message'] ?? ''); ?></td>
                                <td>
                                    <a href="index.php?url=admin/deleteContact&id=<?php echo (int) $c['id']; ?>" class="admin-action delete" data-confirm="Xóa liên hệ?">Xóa</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="admin-empty">Chưa có liên hệ.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>