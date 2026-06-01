<link rel="stylesheet" href="/GiaSu/assets/css/tutor.css">

<section class="tutor-page container tutor-form-page">
    <h2>Đăng ký Gia sư</h2>
    <form action="?url=tutor/store" method="POST" class="tutor-simple-form">
        <label for="full_name">Họ và tên:</label>
        <input id="full_name" type="text" name="full_name" required>

        <label for="subjects">Môn dạy:</label>
        <input id="subjects" type="text" name="subjects" required>

        <label for="hourly_rate">Học phí/giờ:</label>
        <input id="hourly_rate" type="number" name="hourly_rate" required>

        <button type="submit" class="btn-primary">Lưu thông tin</button>
    </form>
</section>

<script src="/GiaSu/assets/js/tutor.js"></script>