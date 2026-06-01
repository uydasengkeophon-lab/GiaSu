<?php
// views/tutor/profile.php
if (!isset($myProfile)) {
    echo "<p class='tutor-empty'>Không tìm thấy thông tin hồ sơ của bạn.</p>";
    return;
}
?>

<link rel="stylesheet" href="/GiaSu/assets/css/tutor.css">

<section class="tutor-page container" style="padding: 40px 0; font-family: Arial, sans-serif;">
    <div class="profile-card tutor-profile-card" style="max-width: 750px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #eee;">
        
        <h2 class="tutor-profile-title" style="color: #4b2d7f; text-align: center; margin-bottom: 25px; font-weight: bold; border-bottom: 2px solid #4b2d7f; padding-bottom: 10px;">
            <i class="fas fa-id-card"></i> Hồ Sơ Của Tôi
        </h2>

        <div id="profile-view-mode" class="profile-content tutor-profile-content" style="display: flex; flex-wrap: wrap; gap: 30px; align-items: flex-start;">
            
            <div class="profile-img tutor-profile-img-wrap" style="flex: 1; min-width: 200px; text-align: center;">
                <?php
                $avatarSrc = !empty($myProfile['avatar']) ? 'assets/uploads/' . $myProfile['avatar'] : 'https://via.placeholder.com/300x300?text=Avatar';
                ?>
                <img src="<?php echo $avatarSrc; ?>" alt="Avatar" class="tutor-profile-img" style="width: 200px; height: 200px; object-fit: cover; border-radius: 50%; border: 4px solid #f0f2f5; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            </div>
            
            <div class="profile-info tutor-profile-info" style="flex: 2; min-width: 300px; display: flex; flex-direction: column; gap: 10px;">
                <h3 class="tutor-profile-name" style="font-size: 26px; color: #333; margin: 0 0 5px 0; font-weight: bold;"><?php echo htmlspecialchars($myProfile['full_name']); ?></h3>

                <p class="tutor-profile-row" style="margin: 0; font-size: 15px;"><strong style="color:#555;"><i class="fas fa-book" style="color:#4b2d7f; width:20px;"></i> Môn dạy:</strong> 
                    <?php echo $myProfile['subjects'] ? htmlspecialchars($myProfile['subjects']) : '<span style="color:#c62828; font-style:italic;">Chưa cập nhật môn</span>'; ?>
                </p>

                <p class="tutor-profile-row" style="margin: 0; font-size: 15px;"><strong style="color:#555;"><i class="fas fa-money-bill-wave" style="color:#2e7d32; width:20px;"></i> Học phí:</strong> 
                    <span class="tutor-profile-rate" style="color: #e91e63; font-weight: bold; font-size: 18px;">
                        <?php echo number_format($myProfile['hourly_rate']); ?>đ
                    </span> / giờ
                </p>

                <p class="tutor-profile-row" style="margin: 0; font-size: 15px;"><strong style="color:#555;"><i class="fas fa-phone" style="color:#1976d2; width:20px;"></i> Số điện thoại:</strong> 
                    <?php echo !empty($myProfile['phone']) ? htmlspecialchars($myProfile['phone']) : '<span style="color:#777; font-style:italic;">Chưa cập nhật</span>'; ?>
                </p>

                <hr class="tutor-profile-divider" style="border: 0; border-top: 1px dashed #ddd; margin: 10px 0;">

                <p style="margin: 0; font-weight: bold; color: #333;">Giới thiệu bản thân:</p>
                <p class="tutor-profile-bio" style="margin: 0; color: #666; line-height: 1.5; background: #fafafa; padding: 12px; border-radius: 6px; border: 1px solid #f0f0f0; font-style: italic;">
                    <?php echo $myProfile['bio'] ? nl2br(htmlspecialchars($myProfile['bio'])) : 'Bạn chưa viết bài giới thiệu về bản thân để thu hút học viên.'; ?>
                </p>

                <div class="tutor-profile-action" style="margin-top: 15px;">
                    <button type="button" id="btn-enable-edit" class="btn-primary" style="background: #4b2d7f; color: #fff; padding: 10px 22px; border: none; border-radius: 25px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 10px rgba(75,45,127,0.2); transition: background 0.2s;">
                        <i class="fas fa-user-edit"></i> Chỉnh sửa thông tin hồ sơ
                    </button>
                </div>
            </div>
        </div>

        <div id="profile-edit-mode" style="display: none;">
            <form action="?url=tutor/updateProfilePost" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px;">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group" style="display: flex; flex-direction: column; gap: 5px;">
                        <label style="font-weight: 600; color: #444;">Họ và tên đầy đủ *</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($myProfile['full_name']); ?>" required style="padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px;">
                    </div>
                    
                    <div class="form-group" style="display: flex; flex-direction: column; gap: 5px;">
                        <label style="font-weight: 600; color: #444;">Số điện thoại</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($myProfile['phone'] ?? ''); ?>" placeholder="Nhập số điện thoại liên hệ" style="padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group" style="display: flex; flex-direction: column; gap: 5px;">
                        <label style="font-weight: 600; color: #444;">Môn học giảng dạy</label>
                        <input type="text" name="subjects" value="<?php echo htmlspecialchars($myProfile['subjects'] ?? ''); ?>" placeholder="Ví dụ: Toán, Lý, Lập trình PHP" style="padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px;">
                    </div>
                    
                    <div class="form-group" style="display: flex; flex-direction: column; gap: 5px;">
                        <label style="font-weight: 600; color: #444;">Mức học phí yêu cầu (VNĐ/giờ)</label>
                        <input type="number" name="hourly_rate" value="<?php echo (int)($myProfile['hourly_rate'] ?? 0); ?>" min="0" style="padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px;">
                    </div>
                </div>

                <div class="form-group" style="display: flex; flex-direction: column; gap: 5px;">
                    <label style="font-weight: 600; color: #444;">Thay đổi ảnh đại diện (Avatar)</label>
                    <input type="file" name="avatar" accept="image/*" style="padding: 6px; border: 1px solid #ccc; border-radius: 6px; background: #fff;">
                    <small style="color: #777; font-style: italic;">(Để trống nếu muốn giữ nguyên ảnh đại diện cũ)</small>
                </div>

                <div class="form-group" style="display: flex; flex-direction: column; gap: 5px;">
                    <label style="font-weight: 600; color: #444;">Bài viết giới thiệu kinh nghiệm bản thân</label>
                    <textarea name="bio" rows="5" placeholder="Hãy viết mô tả chi tiết về kinh nghiệm và phương pháp dạy học của bạn để thu hút học viên đăng ký..." style="padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; font-family: Arial; resize: vertical;"><?php echo htmlspecialchars($myProfile['bio'] ?? ''); ?></textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="submit" style="flex: 1; background: #2e7d32; color: white; border: none; padding: 12px; border-radius: 25px; font-weight: bold; cursor: pointer; font-size: 15px; box-shadow: 0 4px 12px rgba(46,125,50,0.2);">
                        <i class="fas fa-save"></i> Lưu lại thay đổi
                    </button>
                    <button type="button" id="btn-cancel-edit" style="flex: 1; background: #f0f2f5; color: #333; border: 1px solid #ccc; padding: 12px; border-radius: 25px; font-weight: bold; cursor: pointer; font-size: 15px;">
                        Hủy bỏ
                    </button>
                </div>
            </form>
        </div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewMode = document.getElementById('profile-view-mode');
    const editMode = document.getElementById('profile-edit-mode');
    const btnEnableEdit = document.getElementById('btn-enable-edit');
    const btnCancelEdit = document.getElementById('btn-cancel-edit');

    // Khi click nút Sửa: Ẩn khung xem, Mở khung điền Form
    if(btnEnableEdit) {
        btnEnableEdit.addEventListener('click', function() {
            viewMode.style.display = 'none';
            editMode.style.display = 'block';
        });
    }

    // Khi click nút Hủy: Đóng khung Form, quay về giao diện xem ban đầu
    if(btnCancelEdit) {
        btnCancelEdit.addEventListener('click', function() {
            editMode.style.display = 'none';
            viewMode.style.display = 'flex';
        });
    }
});
</script>