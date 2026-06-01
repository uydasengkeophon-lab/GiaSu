<?php include_once "./views/layouts/header.php"; ?>

<section class="contact-section">
    <div class="contact-container">

        <h2 class="contact-title">Liên Hệ Với Trung Tâm Gia Sư APPCO</h2>
        <p class="contact-desc">
            Nếu bạn cần tìm gia sư hoặc muốn đăng ký làm gia sư, vui lòng để lại thông tin bên dưới.
        </p>

        <div class="contact-wrapper">

            <!-- Thông tin liên hệ -->
            <div class="contact-info">
                <h3>Thông Tin Trung Tâm</h3>
                <p><strong>📍 Địa chỉ:</strong> Số 12 - Phường Chiềng Sinh - TP Sơn La</p>
                <p><strong>📞 Điện thoại:</strong> 0987 654 321</p>
                <p><strong>📧 Email:</strong> appcogiasu@gmail.com</p>
            </div>

            <!-- Form liên hệ -->
            <div class="contact-form">
                <h3>Gửi Tin Nhắn</h3>

                <<form action="index.php?url=contact/submit" method="POST">
    <input name="fullname" placeholder="Họ tên">
    <input name="email" placeholder="Email">
    <textarea name="message" placeholder="Nội dung"></textarea>
    <button>Gửi liên hệ</button>
</form>


                </form>
            </div>

        </div>
    </div>
</section>

<?php include_once "./views/layouts/footer.php"; ?>

