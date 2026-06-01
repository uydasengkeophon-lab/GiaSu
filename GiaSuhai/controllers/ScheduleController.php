<?php
// controllers/ScheduleController.php
require_once 'config/database.php';
require_once 'models/Schedule.php';

class ScheduleController
{
    private $db;
    private $scheduleModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
        $this->scheduleModel = new Schedule($this->db);
    }

    private function redirectWithMessage($message, $type = 'msg')
    {
        $query = http_build_query([$type => $message]);
        header("Location: ?url=schedule/index&" . $query);
        exit();
    }

    private function ensureLoggedIn()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: ?url=auth/login");
            exit();
        }
    }

    private function isAdmin()
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    private function isTutor()
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'tutor';
    }

    private function canTutorEditSchedule($scheduleId)
    {
        if (!$this->isTutor()) {
            return false;
        }
        return $this->scheduleModel->isOwnedByTutorUser($scheduleId, $_SESSION['user_id']);
    }

    // 📌 1. HIỂN THỊ LỊCH THƯỜNG / CHI TIẾT THEO NGÀY
    public function index()
    {
        $this->ensureLoggedIn();

        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin', 'tutor', 'student'], true)) {
            $this->redirectWithMessage('Bạn không có quyền truy cập lịch học!', 'error');
        }

        $data = $this->scheduleModel->getForUser($role, $_SESSION['user_id']);
        $weeklyTimetable = $this->scheduleModel->getWeeklyTimetableForUser($role, $_SESSION['user_id']);
        $canCreate = ($role === 'admin');
        $canEdit = ($role === 'admin' || $role === 'tutor');
        $canDelete = ($role === 'admin');
        $currentRole = $role;
        $currentUserId = (int) $_SESSION['user_id'];
        
        $tutorOptions = $canCreate ? $this->scheduleModel->getTutorOptions() : [];
        $studentOptions = $canCreate ? $this->scheduleModel->getStudentOptions() : [];

        if ($role === 'admin') {
            require_once 'views/layouts/admin_header.php';
        } else {
            require_once 'views/layouts/header.php';
        }
        require_once 'views/schedule/index.php';
        if ($role === 'admin') {
            require_once 'views/layouts/admin_footer.php';
        } else {
            require_once 'views/layouts/footer.php';
        }
    }

    public function edit()
    {
        $this->ensureLoggedIn();

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            $this->redirectWithMessage('ID lịch học không hợp lệ!', 'error');
        }

        $data = $this->scheduleModel->getByIdWithNames($id);
        if (!$data) {
            $this->redirectWithMessage('Không tìm thấy lịch học!', 'error');
        }

        if (!$this->isAdmin() && !$this->canTutorEditSchedule($id)) {
            $this->redirectWithMessage('Bạn không được sửa lịch học này!', 'error');
        }

        $isAdminEdit = $this->isAdmin();
        $tutorOptions = $isAdminEdit ? $this->scheduleModel->getTutorOptions() : [];
        $studentOptions = $isAdminEdit ? $this->scheduleModel->getStudentOptions() : [];

        if ($isAdminEdit) {
            require_once 'views/layouts/admin_header.php';
        } else {
            require_once 'views/layouts/header.php';
        }
        require_once 'views/schedule/edit.php';
        if ($isAdminEdit) {
            require_once 'views/layouts/admin_footer.php';
        } else {
            require_once 'views/layouts/footer.php';
        }
    }

    public function update()
    {
        $this->ensureLoggedIn();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithMessage('Phương thức không hợp lệ!', 'error');
        }

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            $this->redirectWithMessage('ID lịch học không hợp lệ!', 'error');
        }

        $current = $this->scheduleModel->getById($id);
        if (!$current) {
            $this->redirectWithMessage('Lịch học không tồn tại!', 'error');
        }

        if (!$this->isAdmin() && !$this->canTutorEditSchedule($id)) {
            $this->redirectWithMessage('Bạn không có quyền cập nhật lịch học này!', 'error');
        }

        $mon = trim($_POST['mon_hoc'] ?? '');
        $ngay = $_POST['ngay'] ?? '';
        $bat_dau = $_POST['gio_bat_dau'] ?? '';
        $ket_thuc = $_POST['gio_ket_thuc'] ?? '';

        if ($mon === '' || $ngay === '' || $bat_dau === '' || $ket_thuc === '') {
            $this->redirectWithMessage('Vui lòng nhập đầy đủ thông tin!', 'error');
        }

        if ($bat_dau >= $ket_thuc) {
            $this->redirectWithMessage('Giờ học không hợp lệ!', 'error');
        }

        if ($this->isAdmin()) {
            $gia_su = isset($_POST['gia_su_id']) ? (int) $_POST['gia_su_id'] : 0;
            $hoc_vien = isset($_POST['hoc_vien_id']) ? (int) $_POST['hoc_vien_id'] : 0;
            if ($gia_su <= 0 || $hoc_vien <= 0) {
                $this->redirectWithMessage('Gia sư hoặc học viên không hợp lệ!', 'error');
            }
        } else {
            $gia_su = (int) ($current['gia_su_id'] ?? $current['gia_su'] ?? 0);
            $hoc_vien = (int) ($current['hoc_vien_id'] ?? $current['hoc_vien'] ?? 0);
        }

        $isConflict = $this->scheduleModel->checkConflictForParticipants($gia_su, $hoc_vien, $ngay, $bat_dau, $ket_thuc, $id);
        if ($isConflict) {
            $this->redirectWithMessage('Trùng lịch: gia sư hoặc học viên đã có lịch trong khung giờ này!', 'error');
        }

        $this->scheduleModel->update($id, $gia_su, $hoc_vien, $mon, $ngay, $bat_dau, $ket_thuc);
        $this->redirectWithMessage('Cập nhật lịch học thành công!');
    }

    public function store()
    {
        $this->ensureLoggedIn();

        if (!$this->isAdmin()) {
            $this->redirectWithMessage('Chỉ quản trị viên được tạo lịch học!', 'error');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $gia_su = isset($_POST['gia_su_id']) ? (int) $_POST['gia_su_id'] : 0;
            $mon = trim($_POST['mon_hoc'] ?? '');
            $ngay = $_POST['ngay'] ?? '';
            $bat_dau = $_POST['gio_bat_dau'] ?? '';
            $ket_thuc = $_POST['gio_ket_thuc'] ?? '';

            if ($gia_su <= 0 || $mon === '' || $ngay === '' || $bat_dau === '' || $ket_thuc === '') {
                $this->redirectWithMessage('Vui lòng nhập đầy đủ thông tin hợp lệ!', 'error');
            }

            if ($bat_dau >= $ket_thuc) {
                $this->redirectWithMessage('Giờ kết thúc phải lớn hơn giờ bắt đầu!', 'error');
            }

            $sqlFindStudents = "SELECT DISTINCT hoc_vien FROM lich_hoc_hang_tuan WHERE gia_su = :gia_su AND mon_hoc = :mon_hoc AND hoc_vien > 0";
            $stmtFind = $this->db->prepare($sqlFindStudents);
            $stmtFind->execute([
                ':gia_su' => $gia_su,
                ':mon_hoc' => $mon
            ]);
            $studentsList = $stmtFind->fetchAll(PDO::FETCH_ASSOC);

            if (empty($studentsList)) {
                $this->redirectWithMessage('Không tìm thấy học viên nào đăng ký học cố định môn này với gia sư hiện tại để phân phối lịch!', 'error');
            }

            $successCount = 0;
            $conflictCount = 0;

            foreach ($studentsList as $studentRow) {
                $hoc_vien_id = (int)$studentRow['hoc_vien'];

                $isConflict = $this->scheduleModel->checkConflictForParticipants($gia_su, $hoc_vien_id, $ngay, $bat_dau, $ket_thuc);

                if ($isConflict) {
                    $conflictCount++;
                    continue; 
                }

                $this->scheduleModel->insert($gia_su, $hoc_vien_id, $mon, $ngay, $bat_dau, $ket_thuc);
                $successCount++;
            }

            if ($successCount > 0) {
                $msgStr = "Đã phát hành lịch học thành công cho lớp học! Tổng số: thêm thành công {$successCount} học viên.";
                if ($conflictCount > 0) {
                    $msgStr .= " (Bỏ qua {$conflictCount} học viên do bị trùng lịch cá nhân khác).";
                }
                $this->redirectWithMessage($msgStr);
            } else {
                $this->redirectWithMessage('Phát hành lịch thất bại! Tất cả học viên trong lớp đều đã bị trùng khung lịch khác.', 'error');
            }
        }

        $this->redirectWithMessage('Phương thức xử lý không hợp lệ!', 'error');
    }

    public function delete()
    {
        $this->ensureLoggedIn();

        if (!$this->isAdmin()) {
            $this->redirectWithMessage('Chỉ quản trị viên được xoá lịch học!', 'error');
        }

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            $this->redirectWithMessage('ID lịch học không hợp lệ!', 'error');
        }

        $this->scheduleModel->delete($id);
        $this->redirectWithMessage('Xoá lịch học thành công!');
    }

    // ═══════════════════════════════════════════════════════════════
    // QUẢN LÝ LỊCH HỌC CỐ ĐỊNH HÀNG TUẦN
    // ═══════════════════════════════════════════════════════════════

    // 📌 2. HIỂN THỊ TRANG QUẢN LÝ LỊCH CỐ ĐỊNH
    public function recurring()
    {
        $this->ensureLoggedIn();

        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin', 'tutor', 'student'], true)) {
            $this->redirectWithMessage('Bạn không có quyền truy cập!', 'error');
        }

        $recurringSchedules = $this->scheduleModel->getRecurringSchedulesForUser($role, $_SESSION['user_id']);
        
        if (!empty($recurringSchedules)) {
            foreach ($recurringSchedules as &$schedule) {
                $dayNames = ['Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy', 'Chủ nhật'];
                $schedule['day_name'] = $dayNames[$schedule['thu_trong_tuan']] ?? 'Hàng tuần';
                
                $sessionNames = [1 => 'Buổi Sáng (08:00 - 11:30)', 2 => 'Buổi Chiều (14:00 - 17:30)', 3 => 'Buổi Tối (18:00 - 21:30)'];
                $schedule['session_name'] = $sessionNames[$schedule['phien_hoc']] ?? 'Chưa xếp buổi';
            }
            unset($schedule);
        }

        $canCreate = ($role === 'admin');
        $canEdit = ($role === 'admin' || $role === 'tutor');
        $canDelete = ($role === 'admin');
        $canRegister = $this->scheduleModel->canRegisterNow();
        
        $tutorOptions = $canCreate ? $this->scheduleModel->getTutorOptions() : [];
        $studentOptions = $canCreate ? $this->scheduleModel->getStudentOptions() : [];

        if ($role === 'admin') {
            require_once 'views/layouts/admin_header.php';
        } else {
            require_once 'views/layouts/header.php';
        }

        require_once 'views/schedule/recurring.php';

        if ($role === 'admin') {
            require_once 'views/layouts/admin_footer.php';
        } else {
            require_once 'views/layouts/footer.php';
        }
    }

    // 📌 3. TẠO LỊCH HỌC CỐ ĐỊNH MỚI (TỪ FORM ADMIN ĐẶT LỊCH)
    public function storeRecurring()
    {
        $this->ensureLoggedIn();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithMessage('Phương thức không hợp lệ!', 'error');
        }

        if (!$this->isAdmin()) {
            $this->redirectWithMessage('Chỉ quản trị viên được tạo lịch cố định!', 'error');
        }

        if (!$this->scheduleModel->canRegisterNow()) {
            $this->redirectWithMessage('Chỉ được phép đăng ký lịch cố định từ ngày 01-07 của tháng!', 'error');
        }

        $gia_su = isset($_POST['gia_su_id']) ? (int)$_POST['gia_su_id'] : 0;
        $hoc_vien = isset($_POST['hoc_vien_id']) ? (int)$_POST['hoc_vien_id'] : 0; 
        $mon = trim($_POST['mon_hoc'] ?? '');
        $thu_trong_tuan = isset($_POST['thu_trong_tuan']) ? (int)$_POST['thu_trong_tuan'] : -1;
        $phien_hoc = isset($_POST['phien_hoc']) ? (int)$_POST['phien_hoc'] : 0;

        if ($gia_su <= 0 || $mon === '') {
            $this->redirectWithMessage('Vui lòng nhập đầy đủ thông tin!', 'error');
        }

        if (!in_array($thu_trong_tuan, range(0, 6))) {
            $this->redirectWithMessage('Ngày trong tuần không hợp lệ!', 'error');
        }

        if (!in_array($phien_hoc, [1, 2, 3])) { 
            $this->redirectWithMessage('Buổi học không hợp lệ!', 'error');
        }

        if ($this->scheduleModel->hasTutorRecurringConflict($gia_su, $thu_trong_tuan, $phien_hoc)) {
            $this->redirectWithMessage('Gia sư đã có lịch cố định vào thời gian này!', 'error');
        }

        if ($hoc_vien > 0 && $this->scheduleModel->hasStudentRecurringConflictByProfileId($hoc_vien, $thu_trong_tuan, $phien_hoc)) {
            $this->redirectWithMessage('Học viên đã có lớp khác trong thời gian này!', 'error');
        }

        if ($this->scheduleModel->createRecurringSchedule($gia_su, $hoc_vien, $mon, $thu_trong_tuan, $phien_hoc)) {
            header("Location: ?url=schedule/recurring&msg=" . urlencode('Tạo lịch cố định hàng tuần thành công!'));
            exit();
        } else {
            header("Location: ?url=schedule/recurring&error=" . urlencode('Lỗi khi tạo lịch cố định!'));
            exit();
        }
    }

    // 📌 4. CHỈNH SỬA LỊCH HỌC CỐ ĐỊNH
    public function editRecurring()
    {
        $this->ensureLoggedIn();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header("Location: ?url=schedule/recurring&error=" . urlencode('ID lịch cố định không hợp lệ!'));
            exit();
        }

        $data = $this->scheduleModel->getRecurringScheduleById($id);
        if (!$data) {
            header("Location: ?url=schedule/recurring&error=" . urlencode('Không tìm thấy lịch cố định!'));
            exit();
        }

        if (!$this->isAdmin() && !$this->scheduleModel->isRecurringOwnedByTutorUser($id, $_SESSION['user_id'])) {
            header("Location: ?url=schedule/recurring&error=" . urlencode('Bạn không được sửa lịch cố định này!'));
            exit();
        }

        $dayNames = ['Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy', 'Chủ nhật'];
        $data['day_name'] = $dayNames[$data['thu_trong_tuan']] ?? '';
        
        $sessionNames = [1 => 'Buổi Sáng', 2 => 'Buổi Chiều', 3 => 'Buổi Tối'];
        $data['session_name'] = $sessionNames[$data['phien_hoc']] ?? '';

        $isAdminEdit = $this->isAdmin();
        $tutorOptions = $isAdminEdit ? $this->scheduleModel->getTutorOptions() : [];
        $studentOptions = $isAdminEdit ? $this->scheduleModel->getStudentOptions() : [];

        if ($isAdminEdit) {
            require_once 'views/layouts/admin_header.php';
        } else {
            require_once 'views/layouts/header.php';
        }

        require_once 'views/schedule/edit_recurring.php';

        if ($isAdminEdit) {
            require_once 'views/layouts/admin_footer.php';
        } else {
            require_once 'views/layouts/footer.php';
        }
    }

    // 📌 5. CẬP NHẬT LỊCH HỌC CỐ ĐỊNH
    public function updateRecurring()
    {
        $this->ensureLoggedIn();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?url=schedule/recurring&error=" . urlencode('Phương thức không hợp lệ!'));
            exit();
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            header("Location: ?url=schedule/recurring&error=" . urlencode('ID lịch cố định không hợp lệ!'));
            exit();
        }

        $current = $this->scheduleModel->getRecurringScheduleById($id);
        if (!$current) {
            header("Location: ?url=schedule/recurring&error=" . urlencode('Lịch cố định không tồn tại!'));
            exit();
        }

        if (!$this->isAdmin() && !$this->scheduleModel->isRecurringOwnedByTutorUser($id, $_SESSION['user_id'])) {
            header("Location: ?url=schedule/recurring&error=" . urlencode('Bạn không có quyền cập nhật lịch cố định này!'));
            exit();
        }

        $mon = trim($_POST['mon_hoc'] ?? '');
        $thu_trong_tuan = isset($_POST['thu_trong_tuan']) ? (int)$_POST['thu_trong_tuan'] : -1;
        $phien_hoc = isset($_POST['phien_hoc']) ? (int)$_POST['phien_hoc'] : 0;

        if ($mon === '') {
            header("Location: ?url=schedule/recurring&error=" . urlencode('Vui lòng nhập đầy đủ thông tin!'));
            exit();
        }

        if (!in_array($thu_trong_tuan, range(0, 6)) || !in_array($phien_hoc, [1, 2, 3])) {
            header("Location: ?url=schedule/recurring&error=" . urlencode('Thông tin không hợp lệ!'));
            exit();
        }

        $gia_su_id = (int)($current['gia_su_id'] ?? $current['gia_su'] ?? 0);

        if ($this->isAdmin()) {
            $gia_su = isset($_POST['gia_su_id']) ? (int)$_POST['gia_su_id'] : 0;
            $hoc_vien = isset($_POST['hoc_vien_id']) ? (int)$_POST['hoc_vien_id'] : 0;
            if ($gia_su <= 0) {
                header("Location: ?url=schedule/recurring&error=" . urlencode('Gia sư không hợp lệ!'));
                exit();
            }
        } else {
            $gia_su = $gia_su_id;
            $hoc_vien = (int)($current['hoc_vien_id'] ?? $current['hoc_vien'] ?? 0);
        }

        if ($this->scheduleModel->hasTutorRecurringConflict($gia_su, $thu_trong_tuan, $phien_hoc, $id)) {
            header("Location: ?url=schedule/recurring&error=" . urlencode('Gia sư đã có lịch cố định vào thời gian này!'));
            exit();
        }

        if ($hoc_vien > 0 && $this->scheduleModel->hasStudentRecurringConflictByProfileId($hoc_vien, $thu_trong_tuan, $phien_hoc, $id)) {
            header("Location: ?url=schedule/recurring&error=" . urlencode('Học viên đã có lớp khác trong thời gian này!'));
            exit();
        }

        $this->scheduleModel->updateRecurringSchedule($id, $gia_su, $hoc_vien, $mon, $thu_trong_tuan, $phien_hoc);
        header("Location: ?url=schedule/recurring&msg=" . urlencode('Cập nhật lịch cố định thành công!'));
        exit();
    }

    // 📌 6. XOÁ LỊCH HỌC CỐ ĐỊNH
    public function deleteRecurring()
    {
        $this->ensureLoggedIn();

        if (!$this->isAdmin()) {
            header("Location: ?url=schedule/recurring&error=" . urlencode('Chỉ quản trị viên được xoá lịch cố định!'));
            exit();
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header("Location: ?url=schedule/recurring&error=" . urlencode('ID lịch cố định không hợp lệ!'));
            exit();
        }

        if ($this->scheduleModel->deleteRecurringSchedule($id)) {
            header("Location: ?url=schedule/recurring&msg=" . urlencode('Xoá lịch cố định thành công!'));
            exit();
        } else {
            header("Location: ?url=schedule/recurring&error=" . urlencode('Lỗi khi xoá lịch cố định!'));
            exit();
        }
    }
    
    // 📌 7. ĐĂNG KÝ KHUNG GIỜ CỐ ĐỊNH
    public function register_recurring()
    {
        $this->registerRecurring();
    }

    public function registerRecurring()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
        $userId = $_SESSION['user_id'] ?? 0;

        if ($userId <= 0 || $role !== 'student') {
            header("Location: ?url=auth/login&error=" . urlencode('Chỉ tài khoản Học viên mới có quyền đăng ký học!'));
            exit();
        }

        $recurringId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($recurringId <= 0) {
            header("Location: ?url=schedule/recurring&error=" . urlencode('Khung giờ đăng ký không hợp lệ!'));
            exit();
        }

        require_once 'models/Booking.php';
        $bookingModel = new Booking($this->db);

        try {
            $this->db->beginTransaction();

            // 🛡️ BƯỚC 1: Chỉ chặn nếu CHÍNH học viên này gửi đơn đặt lớp tuần này trùng lặp
            $existingBooking = $this->scheduleModel->getStudentBookingForRecurringSlot((int)$userId, $recurringId);
            if ($existingBooking) {
                $this->db->commit();
                if ($existingBooking['status'] === 'cho_thanh_toan' || $existingBooking['status'] === 'pending') {
                    header("Location: ?url=payment/index&id=" . (int)$existingBooking['id']);
                } else {
                    header("Location: ?url=schedule/recurring&msg=" . urlencode('Bạn đã gửi đơn nộp học phí hoặc đã tham gia lớp này trước đó rồi!'));
                }
                exit();
            }

            // Bốc thông tin Thứ và Ca học của ô lịch tuần mục tiêu
            $sqlGetTarget = "SELECT gia_su, thu_trong_tuan, phien_hoc, mon_hoc FROM lich_hoc_hang_tuan WHERE id = ? LIMIT 1";
            $stmtT = $this->db->prepare($sqlGetTarget);
            $stmtT->execute([$recurringId]);
            $targetSlot = $stmtT->fetch(PDO::FETCH_ASSOC);

            if (!$targetSlot) {
                throw new Exception('Khung giờ này không tồn tại hoặc đã bị gỡ bỏ.');
            }

            $tutorId = (int)$targetSlot['gia_su'];
            $thuInt = (int)$targetSlot['thu_trong_tuan'];
            $phienInt = (int)$targetSlot['phien_hoc'];

            // 🛡️ BƯỚC 2: !!! ĐÃ FIX LỖI CHỐT CỦA BẠN !!!
            // Thay vì truyền trực tiếp ID User thô vào hàm check Profile, chúng ta sử dụng hàm gốc check bằng User ID: `hasStudentRecurringConflict`
            if ($this->scheduleModel->hasStudentRecurringConflict((int)$userId, $thuInt, $phienInt)) {
                $dayNames = ['Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy', 'Chủ nhật'];
                $sessionNames = [1 => 'Buổi Sáng', 2 => 'Buổi Chiều', 3 => 'Buổi Tối'];
                $dayStr = $dayNames[$thuInt] ?? 'Lịch tuần';
                $sessStr = $sessionNames[$phienInt] ?? 'Ca học';
                throw new Exception("Thất bại! Bạn đã bị trùng lịch cá nhân vào khung giờ {$dayStr} ({$sessStr}) với một môn học khác.");
            }

            // Bốc đơn giá học phí của gia sư để tạo hóa đơn
            $sqlPrice = "SELECT hourly_rate FROM tutors WHERE id = ? LIMIT 1";
            $stmtP = $this->db->prepare($sqlPrice);
            $stmtP->execute([$tutorId]);
            $hourlyRate = (float)$stmtP->fetchColumn() ?: 100000;

            // 🎯 BƯỚC 3: Tạo hóa đơn đăng ký trực tiếp sang bảng bookings (Mặc định: cho_thanh_toan)
            $bookingId = $bookingModel->createWithSchedule($tutorId, (int)$userId, $recurringId, $hourlyRate);
            if (!$bookingId) {
                throw new Exception('Hệ thống không thể khởi tạo hóa đơn đăng ký. Vui lòng thử lại!');
            }

            $this->db->commit();
            
            // 🚀 CHUYỂN HƯỚNG THẲNG SANG TRANG THANH TOÁN HÓA ĐƠN LUÔN
            header("Location: ?url=payment/index&id=" . (int)$bookingId);
            exit();

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            header("Location: ?url=schedule/recurring&error=" . urlencode($e->getMessage()));
            exit();
        }
    }

    // 📌 8. TRANG XEM CHI TIẾT BUỔI HỌC
    public function detail()
    {
        $this->ensureLoggedIn();
        $role = $_SESSION['role'] ?? '';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header("Location: ?url=schedule/index&error=" . urlencode('ID lịch học không hợp lệ!'));
            exit();
        }

        $scheduleDetail = $this->scheduleModel->getByIdWithNames($id);
        if (!$scheduleDetail) {
            header("Location: ?url=schedule/index&error=" . urlencode('Không tìm thấy dữ liệu chi tiết của buổi học này!'));
            exit();
        }

        if ($role === 'admin') {
            require_once 'views/layouts/admin_header.php';
        } else {
            require_once 'views/layouts/header.php';
        }

        require_once 'views/schedule/detail.php';

        if ($role === 'admin') {
            require_once 'views/layouts/admin_footer.php';
        } else {
            require_once 'views/layouts/footer.php';
        }
    }
}