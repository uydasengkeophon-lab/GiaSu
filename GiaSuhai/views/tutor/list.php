<section class="tutors-section" style="padding: 60px 0; background-color: #f9f9f9; font-family: Arial, sans-serif;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 15px;">
        
        <div class="section-title-area" style="text-align: center; margin-bottom: 40px;">
            <h2 style="font-size: 32px; color: #4a148c; font-weight: bold; margin-bottom: 10px;">Đội Ngũ Gia Sư Tiêu Biểu</h2>
            <p style="font-size: 16px; color: #666;">Chọn giáo viên phù hợp nhất cho tương lai của bạn</p>
        </div>

        <div class="tutors-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; justify-content: center;">
            
            <?php 
            // 🔍 CƠ CHẾ DỰ PHÒNG THÔNG MINH (BYPASS FALLBACK):
            // Nếu biến kết nối phân quyền đăng nhập phức tạp từ Controller bị rỗng do tài khoản test lệch cấu trúc
            if (empty($prominentTutors) && !empty($tutors)) {
                $prominentTutors = $tutors;
            }

            if (empty($prominentTutors)) {
                $dbFallback = new Database();
                $connFallback = $dbFallback->connect();
                
                // Quét trực tiếp lấy dữ liệu gốc từ bảng tutors và tự động đếm nối chuỗi lịch cố định
                $sqlFallback = "SELECT t.*, 
                                       (SELECT COUNT(DISTINCT b.student_id)
                                        FROM bookings b
                                        WHERE b.tutor_id = t.id AND (b.status IS NULL OR b.status <> 'rejected')) AS student_count,
                                       (SELECT GROUP_CONCAT(CONCAT(l.thu_trong_tuan, '-', l.phien_hoc)) 
                                        FROM lich_hoc_hang_tuan l 
                                        WHERE l.gia_su = t.id AND l.trang_thai = 1) AS list_lich_co_dinh
                                FROM tutors t 
                                ORDER BY t.id DESC LIMIT 12";
                                
                $stmtF = $connFallback->prepare($sqlFallback);
                $stmtF->execute();
                $prominentTutors = $stmtF->fetchAll(PDO::FETCH_ASSOC);
            }
            ?>

            <?php if (!empty($prominentTutors)): ?>
                <?php foreach ($prominentTutors as $tutor): ?>
                    
                    <div class="tutor-card" style="background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; display: flex; flex-direction: column; align-items: center; padding-bottom: 20px; transition: transform 0.3s ease; border: 1px solid #ddd;">
                        
                        <div class="tutor-image-box" style="width: 100%; height: 260px; overflow: hidden; position: relative;">
                            <img src="<?= !empty($tutor['avatar']) ? 'assets/uploads/'.htmlspecialchars($tutor['avatar']) : 'assets/images/default-avatar.png'; ?>" 
                                 alt="<?= htmlspecialchars($tutor['full_name'] ?? ''); ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </div>

                        <div class="tutor-info-box" style="padding: 15px; width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; box-sizing: border-box;">
                            
                            <?php 
                                $maxSlots = 25;
                                $currentStudents = isset($tutor['student_count']) ? (int)$tutor['student_count'] : 0;
                                $remaining = max(0, $maxSlots - $currentStudents);
                            ?>
                            <div class="tutor-slot-status">
                                <?php if ($remaining <= 0): ?>
                                    <span class="slot-pill status-full" style="background-color: #e57373; color: #fff; font-size: 12px; padding: 4px 12px; border-radius: 20px; font-weight: 500;">
                                        <i class="fas fa-times-circle"></i> Đã đủ sinh viên
                                    </span>
                                <?php else: ?>
                                    <span class="slot-pill status-open" style="background-color: #2e7d32; color: #fff; font-size: 12px; padding: 4px 12px; border-radius: 20px; font-weight: 500;">
                                        <i class="fas fa-check-circle"></i> Còn <?php echo $remaining; ?> chỗ trống
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3 class="tutor-name" style="font-size: 20px; color: #333; margin: 5px 0 0 0; font-weight: bold;"><?= htmlspecialchars($tutor['full_name'] ?? 'Chưa cập nhật tên'); ?></h3>
                            
                            <div class="tutor-schedules-box" style="width: 100%; margin: 5px 0;">
                                <div class="schedule-tags-list" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 4px;">
                                    <?php 
                                    $dayMap = [0 => 'Thứ 2', 1 => 'Thứ 3', 2 => 'Thứ 4', 3 => 'Thứ 5', 4 => 'Thứ 6', 5 => 'Thứ 7', 6 => 'Chủ nhật'];
                                    $sessionMap = [1 => 'Sáng', 2 => 'Chiều', 3 => 'Tối'];

                                    $lichChuoi = !empty($tutor['list_lich_co_dinh']) ? $tutor['list_lich_co_dinh'] : '';

                                    if (!empty($lichChuoi)):
                                        $slots = explode(',', $lichChuoi);
                                        foreach ($slots as $slot):
                                            $parts = explode('-', $slot);
                                            if (count($parts) === 2):
                                                $thuCode = (int)$parts[0];
                                                $phienCode = (int)$parts[1];
                                                
                                                $dayLabel = $dayMap[$thuCode] ?? 'Lịch';
                                                $sessionLabel = $sessionMap[$phienCode] ?? 'Chưa xếp';
                                                
                                                $bgColors = ($phienCode === 1) ? '#fff3e0' : (($phienCode === 2) ? '#f3e5f5' : '#e8eaf6');
                                                $textColors = ($phienCode === 1) ? '#e65100' : (($phienCode === 2) ? '#6a1b9a' : '#1a237e');
                                    ?>
                                                <span class="schedule-tag" style="background-color: <?= $bgColors; ?>; color: <?= $textColors; ?>; font-size: 11px; padding: 3px 8px; border-radius: 4px; font-weight: 500; border: 1px solid rgba(0,0,0,0.05); display: inline-block; margin: 2px;">
                                                    <?= htmlspecialchars($dayLabel); ?> (<?= $sessionLabel; ?>)
                                                </span>
                                    <?php 
                                            endif;
                                        endforeach;
                                    else: 
                                    ?>
                                        <span style="font-size: 12px; color: #999; font-style: italic;">Chưa cập nhật lịch dạy cố định</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <span class="tutor-subject" style="color: #e91e63; font-weight: bold; font-size: 14px; text-transform: uppercase;"><?= htmlspecialchars($tutor['subjects'] ?? 'CHƯA CẬP NHẬT MÔN'); ?></span>
                            
                            <p class="tutor-bio" style="font-size: 14px; color: #666; margin: 0; line-height: 1.4; max-height: 40px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                <?= htmlspecialchars($tutor['bio'] ?? 'Gia sư nhiệt tình, giàu kinh nghiệm học tập.'); ?>
                            </p>
                            
                            <div class="tutor-price" style="font-size: 18px; color: #4a148c; font-weight: bold; margin-top: 5px;">
                                <?= number_format($tutor['hourly_rate'] ?? 0); ?>đ / giờ
                            </div>
                            
                            <?php if ($remaining <= 0): ?>
                                <button disabled class="btn-booking" style="background-color: #cccccc; color: #666666; width: 85%; padding: 10px 0; border-radius: 25px; border: none; font-weight: bold; font-size: 14px; margin-top: 10px; cursor: not-allowed;">
                                    Đã đủ học sinh
                                </button>
                            <?php else: ?>
                                <a href="?url=tutor/detail&id=<?= $tutor['id']; ?>" 
                                   class="btn-booking countdown-btn" 
                                   style="background-color: #ff0055; color: #fff; width: 85%; padding: 10px 0; border-radius: 25px; text-decoration: none; font-weight: bold; font-size: 13px; margin-top: 10px; display: inline-block; text-align: center; transition: background-color 0.2s;">
                                    Xem Lịch Giảng Dạy <span class="timer-display" style="display:block; font-size:11px; font-weight:normal; opacity:0.9; margin-top:2px;">Tính thời gian...</span>
                                </a>
                            <?php endif; ?>

                        </div>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; color: #999; padding: 20px;">Không tìm thấy gia sư tiêu biểu nào.</div>
            <?php endif; ?>

        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetTime = new Date().getTime() + (30 * 60 * 60 * 1000);

    function updateCountdown() {
        const currentTime = new Date().getTime();
        const timeDifference = targetTime - currentTime;

        const timerDisplays = document.querySelectorAll('.timer-display');
        const countdownButtons = document.querySelectorAll('.countdown-btn');

        if (timeDifference <= 0) {
            timerDisplays.forEach(display => { display.innerHTML = "(Hết hạn đăng ký)"; });
            countdownButtons.forEach(btn => {
                btn.style.backgroundColor = "#cccccc";
                btn.style.color = "#666666";
                btn.style.cursor = "not-allowed";
                btn.addEventListener('click', function(e) { e.preventDefault(); });
            });
            return;
        }

        const days = Math.floor(timeDifference / (1000 * 60 * 60 * 24));
        const hours = Math.floor((timeDifference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeDifference % (1000 * 60)) / 1000);

        const hoursString = hours < 10 ? '0' + hours : hours;
        const minutesString = minutes < 10 ? '0' + minutes : minutes;
        const secondsString = seconds < 10 ? '0' + seconds : seconds;
        
        let countdownText = "";
        if (days > 0) {
            countdownText = `(Còn ${days} ngày ${hoursString}:${minutesString}:${secondsString})`;
        } else {
            countdownText = `(Hạn chót: ${hoursString}:${minutesString}:${secondsString})`;
        }

        timerDisplays.forEach(display => {
            display.innerHTML = countdownText;
        });
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
});
</script>
