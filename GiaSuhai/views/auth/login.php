<link rel="stylesheet" href="/GiaSu/assets/css/auth.css">

<div class="auth-container">
    <div class="auth-box">
        <h2>Đăng Nhập</h2>
        <form action="?url=auth/loginPost" method="POST">
            <?php echo SecurityHelper::csrfField(); ?>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="Nhập email của bạn">
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" required placeholder="Nhập mật khẩu">
            </div>
            <button type="submit" class="btn-primary full-width">Đăng nhập</button>
        </form>
        <p class="auth-link">Chưa có tài khoản? <a href="?url=auth/register">Đăng ký ngay</a></p>
    </div>
</div>

<script src="/GiaSu/assets/js/auth.js"></script>
