<section class="admin-page container">
    <h2 class="admin-page-title">Quản Lý Tin Tức</h2>

    <div class="admin-card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tiêu đề</th>
                        <th>Nội dung</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($news)): ?>
                        <?php foreach ($news as $n): ?>
                            <tr>
                                <td><?php echo (int) $n['id']; ?></td>
                                <td><?php echo htmlspecialchars($n['title']); ?></td>
                                <td><?php echo htmlspecialchars($n['content']); ?></td>

                                <td>
                                    <?php if ($n['status'] == 0): ?>
                                        <span class="status-badge pending">Chờ duyệt</span>
                                    <?php else: ?>
                                        <span class="status-badge paid">Đã duyệt</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <a href="?url=admin/approveNews&id=<?php echo (int) $n['id']; ?>" class="admin-action approve" data-confirm="Duyệt tin này?">✔</a>
                                    <a href="?url=admin/editNews&id=<?php echo (int) $n['id']; ?>" class="admin-action edit">Sửa</a>
                                    <a href="?url=admin/deleteNews&id=<?php echo (int) $n['id']; ?>" class="admin-action delete" data-confirm="Xóa tin này?">Xóa</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="admin-empty">Chưa có tin tức.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>