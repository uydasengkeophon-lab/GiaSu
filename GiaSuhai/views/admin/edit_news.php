<section class="admin-page container">
    <div class="admin-form-card">
        <h2 class="admin-page-title">Sửa Tin</h2>

        <form method="POST" action="?url=admin/updateNews" class="admin-form">
            <input type="hidden" name="id" value="<?php echo (int) $news['id']; ?>">

            <div class="form-group">
                <label for="title">Tiêu đề</label>
                <input id="title" type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($news['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="content">Nội dung</label>
                <textarea id="content" name="content" class="form-control admin-textarea" required><?php echo htmlspecialchars($news['content']); ?></textarea>
            </div>

            <button type="submit" class="btn-primary">Cập nhật</button>
        </form>
    </div>
</section>