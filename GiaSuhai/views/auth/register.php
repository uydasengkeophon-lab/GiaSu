<link rel="stylesheet" href="/GiaSu/assets/css/auth.css">

<div class="auth-container">
    <div class="auth-box">
        <h2 class="auth-title">Đăng Ký Thành Viên</h2>
        <form action="?url=auth/registerPost" method="POST">
            <?php echo SecurityHelper::csrfField(); ?>

            <div class="form-group">
                <label>Họ và tên đầy đủ:</label>
                <input type="text" name="full_name" required placeholder="Ví dụ: Nguyễn Văn A" class="form-control">
            </div>

            <div class="form-group">
                <label>Tên đăng nhập:</label>
                <input type="text" name="username" required placeholder="username" class="form-control">
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required placeholder="email@gmail.com" class="form-control">
            </div>

            <div class="form-group">
                <label>Mật khẩu:</label>
                <input type="password" name="password" required placeholder="******" class="form-control">
            </div>

            <div class="form-group">
                <label>Bạn là ai?</label>
                <select name="role" id="roleSelect" class="form-control">
                    <option value="student">Học sinh / Phụ huynh</option>
                    <option value="tutor">Gia sư</option>
                </select>
            </div>

            <div class="form-group" id="subjectsGroup" style="display:none;">
                <label>Môn dạy (ví dụ: Toán, Lý):</label>
                <input type="text" name="subjects" id="subjectsInput" placeholder="Nhập môn dạy" class="form-control">
            </div>
            <button type="submit" class="btn-primary full-width auth-submit-btn">Đăng Ký</button>
        </form>
    </div>
</div>

<script src="/GiaSu/assets/js/auth.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var role = document.getElementById('roleSelect');
        var subjGroup = document.getElementById('subjectsGroup');
        role.addEventListener('change', function() {
            if (role.value === 'tutor') subjGroup.style.display = '';
            else subjGroup.style.display = 'none';
        });
        // init
        if (role.value === 'tutor') subjGroup.style.display = '';
    });
</script>
