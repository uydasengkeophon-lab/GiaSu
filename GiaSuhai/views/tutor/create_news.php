<link rel="stylesheet" href="/GiaSu/assets/css/tutor.css">

<section class="tutor-page container tutor-form-page">
    <h2>Đăng Thông Báo</h2>

    <form action="?url=tutor/storeNews" method="POST" enctype="multipart/form-data" class="tutor-simple-form">
        <label for="title">Tiêu đề</label>
        <input id="title" type="text" name="title" required>

        <label for="content">Nội dung</label>
        <textarea id="content" name="content" rows="5" required></textarea>

        <label for="image">Hình ảnh</label>
        <input id="image" type="file" name="image">

        <button type="submit" class="btn-primary">Đăng Tin</button>
    </form>
</section>

<script src="/GiaSu/assets/js/tutor.js"></script>