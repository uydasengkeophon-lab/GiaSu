<?php
// controllers/AdminController.php
require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/Tutor.php';
require_once 'models/Student.php';
require_once 'models/contact.php';
require_once 'models/News.php';
require_once 'models/Booking.php';
require_once 'models/Schedule.php';
require_once 'models/Revenue.php';
require_once 'helpers/SecurityHelper.php';

class AdminController
{
    private $db;
    private $userModel;
    private $tutorModel;
    private $studentModel;
    private $contactModel;
    private $scheduleModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();

        $this->userModel = new User($this->db);
        $this->tutorModel = new Tutor($this->db);
        $this->studentModel = new Student($this->db);
        $this->contactModel = new Contact();
        $this->scheduleModel = new Schedule($this->db);
    }

    private function ensureAdmin()
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: index.php");
            exit;
        }
    }

    private function redirect($url)
    {
        header("Location: " . $url);
        exit;
    }

    private function getRoleInfoWithFallback($user)
    {
        $userId = (int) ($user['id'] ?? 0);
        $username = trim((string) ($user['username'] ?? ''));
        $role = $user['role'] ?? '';

        if ($userId <= 0) {
            return [];
        }

        if ($role === 'tutor') {
            $info = $this->tutorModel->getTutorByUserId($userId);
            if (!$info) {
                $info = $this->tutorModel->ensureProfile($userId, $username);
            }
            if (!$info) {
                return ['full_name' => $username];
            }
            return $info;
        }

        if ($role === 'student') {
            $info = $this->studentModel->getStudentByUserId($userId);
            if (!$info) {
                $info = $this->studentModel->ensureProfile($userId, $username);
            }
            if (!$info) {
                return ['full_name' => $username];
            }
            return $info;
        }

        return [];
    }

    private function getEditRoleProfiles($user, $info)
    {
        $userId = (int) ($user['id'] ?? 0);
        $username = trim((string) ($user['username'] ?? ''));

        $baseFullName = trim((string) ($info['full_name'] ?? $username));
        $basePhone = trim((string) ($info['phone'] ?? ''));

        $tutorInfo = $this->tutorModel->getTutorByUserId($userId);
        if (!$tutorInfo) {
            $tutorInfo = [
                'full_name' => $baseFullName,
                'phone' => $basePhone,
                'subjects' => '',
                'hourly_rate' => '',
                'bio' => '',
                'avatar' => ''
            ];
        }

        $studentInfo = $this->studentModel->getStudentByUserId($userId);
        if (!$studentInfo) {
            $studentInfo = [
                'full_name' => $baseFullName,
                'phone' => $basePhone,
                'grade_level' => '',
                'address' => ''
            ];
        }

        return [$tutorInfo, $studentInfo];
    }

    /* ================= CONTACT ================= */

    public function contacts()
    {
        $this->ensureAdmin();
        $contacts = $this->contactModel->getAll();

        require_once 'views/layouts/admin_header.php';
        require_once 'views/admin/contacts.php';
        require_once 'views/layouts/admin_footer.php';
    }

    public function deleteContact()
    {
        $this->ensureAdmin();

        if (isset($_GET['id'])) {
            $this->contactModel->delete((int) $_GET['id']);
        }

        $this->redirect("index.php?url=admin/contacts");
    }

    /* ================= DASHBOARD ================= */

    public function dashboard()
    {
        $this->ensureAdmin();

        $searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
        $roleFilter = isset($_GET['role']) ? trim($_GET['role']) : '';

        $stmt = $this->userModel->getAllUsers($searchQuery, $roleFilter);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once 'views/layouts/admin_header.php';
        require_once 'views/admin/dashboard.php';
        require_once 'views/layouts/admin_footer.php';
    }

    public function delete()
    {
        $this->ensureAdmin();

        if (isset($_GET['id'])) {
            $this->userModel->delete((int) $_GET['id']);
            $this->redirect("?url=admin/dashboard");
        }
    }

    /* ================= EDIT ================= */

    public function edit()
    {
        $this->ensureAdmin();

        $id = $_GET['id'] ?? null;
        if (!$id) die("Thiếu ID");

        $user = $this->userModel->getUserById($id);
        if (!$user) die("Không tồn tại");

        if (!in_array($user['role'], ['tutor', 'student'], true)) {
            $this->redirect("?url=admin/dashboard");
        }

        $info = $this->getRoleInfoWithFallback($user);
        [$tutorInfo, $studentInfo] = $this->getEditRoleProfiles($user, $info);

        require_once 'views/layouts/admin_header.php';
        require_once 'views/admin/edit.php';
        require_once 'views/layouts/admin_footer.php';
    }

    public function updatePost()
    {
        $this->ensureAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("?url=admin/dashboard");
        }

        $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $role = $_POST['role'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if ($userId <= 0 || $fullName === '' || !in_array($role, ['tutor', 'student'], true)) {
            $this->redirect("?url=admin/dashboard");
        }

        $existingUser = $this->userModel->getUserById($userId);
        if (!$existingUser || !in_array($existingUser['role'], ['tutor', 'student'], true)) {
            $this->redirect("?url=admin/dashboard");
        }

        if ($existingUser['role'] !== $role) {
            $this->userModel->updateRole($userId, $role);
        }

        if ($role === 'tutor') {
            $subjects = trim($_POST['subjects'] ?? '');
            $hourlyRate = isset($_POST['hourly_rate']) ? (float) $_POST['hourly_rate'] : 0;
            $bio = trim($_POST['bio'] ?? '');
            $avatar = null;

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadCheck = SecurityHelper::validateAvatarUpload($_FILES['avatar']);
                if (!$uploadCheck['ok']) {
                    $this->redirect("?url=admin/edit&id=" . $userId . "&error=" . urlencode($uploadCheck['error']));
                }

                $uploadDir = __DIR__ . '/../assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $avatar = 'tutor_' . $userId . '_' . $uploadCheck['filename'];
                $destination = $uploadDir . $avatar;
                if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                    $avatar = null;
                }
            }

            $this->tutorModel->update($userId, $fullName, $phone, $subjects, $hourlyRate, $bio, $avatar);
        }

        if ($role === 'student') {
            $gradeLevel = trim($_POST['grade_level'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $this->studentModel->update($userId, $fullName, $phone, $gradeLevel, $address);
        }

        $this->redirect("?url=admin/dashboard");
    }

    /* ================= DETAIL ================= */

    public function detail()
    {
        $this->ensureAdmin();

        $id = $_GET['id'] ?? null;
        if (!$id) die("Thiếu ID");

        $user = $this->userModel->getUserById($id);
        if (!$user) die("Không tồn tại");

        if (!in_array($user['role'], ['tutor', 'student'], true)) {
            $this->redirect("?url=admin/dashboard");
        }

        $info = $this->getRoleInfoWithFallback($user);

        require_once 'views/layouts/admin_header.php';
        require_once 'views/admin/detail.php';
        require_once 'views/layouts/admin_footer.php';
    }

    /* ================= NEWS ================= */

    public function approveNews()
    {
        $this->ensureAdmin();
        if (isset($_GET['id'])) {
            $model = new News();
            $model->approve((int) $_GET['id']);
        }
        $this->redirect("index.php?url=admin/news");
    }

    public function news()
    {
        $this->ensureAdmin();
        $model = new News();
        $news = $model->getAllAdmin();

        require_once "views/layouts/admin_header.php";
        require_once "views/admin/news.php";
        require_once "views/layouts/admin_footer.php";
    }

    public function deleteNews()
    {
        $this->ensureAdmin();
        $model = new News();
        $model->delete((int) $_GET['id']);
        $this->redirect("?url=admin/news");
    }

    public function editNews()
    {
        $this->ensureAdmin();
        $model = new News();
        $news = $model->getById((int) $_GET['id']);

        require_once "views/layouts/admin_header.php";
        require_once "views/admin/edit_news.php";
        require_once "views/layouts/admin_footer.php";
    }

    public function updateNews()
    {
        $this->ensureAdmin();
        $model = new News();
        $model->update((int) $_POST['id'], $_POST['title'], $_POST['content']);

        $this->redirect("?url=admin/news");
    }

    /* ================= BOOKINGS (QUẢN LÝ DUYỆT ĐĂNG KÝ HỌC CHUẨN) ================= */

    // 📌 Hiển thị bảng kiểm duyệt đăng ký học cố định của Admin
    public function bookings()
    {
        $this->ensureAdmin();

        $model = new Booking($this->db);
        // Gọi hàm getAll() phiên bản mới bốc đầy đủ Môn, Thứ, Ca học cố định lặp lại ra bảng
        $bookings = $model->getAll();

        require_once "views/layouts/admin_header.php";
        require_once "views/admin/bookings.php";
        require_once "views/layouts/admin_footer.php";
    }

    // Sửa form thông tin đơn đặt lịch chi tiết
    public function editBooking()
    {
        $this->ensureAdmin();

        if (!isset($_GET['id'])) {
            $this->redirect("?url=admin/bookings");
        }

        $model = new Booking($this->db);
        $booking = $model->getById((int) $_GET['id']);
        if (!$booking) {
            $this->redirect("?url=admin/bookings");
        }

        $tutorOptions = $this->scheduleModel->getTutorOptions();
        $studentOptions = $this->scheduleModel->getStudentOptions();

        require_once "views/layouts/admin_header.php";
        require_once "views/admin/edit_booking.php";
        require_once "views/layouts/admin_footer.php";
    }

    public function updateBooking()
    {
        $this->ensureAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("?url=admin/bookings");
        }

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $tutor_id = isset($_POST['tutor_id']) ? (int) $_POST['tutor_id'] : 0;
        $student_id = isset($_POST['student_id']) ? (int) $_POST['student_id'] : 0;
        $amount = isset($_POST['amount']) ? (int) $_POST['amount'] : 0;
        $study_date = $_POST['study_date'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';

        if ($id <= 0 || $tutor_id <= 0 || $student_id <= 0 || $amount < 0 || !$study_date || !$start_time || !$end_time) {
            $this->redirect("?url=admin/bookings");
        }

        if ($start_time >= $end_time) {
            $this->redirect("?url=admin/bookings");
        }

        $model = new Booking($this->db);
        $booking = $model->getById($id);
        if (!$booking) {
            $this->redirect("?url=admin/bookings");
        }

        $conflict = $model->checkConflictExcludingId($tutor_id, $student_id, $study_date, $start_time, $end_time, $id);
        if ($conflict) {
            $this->redirect("?url=admin/bookings&error=" . urlencode('Trùng lịch với booking khác.'));
        }

        $model->update($id, $tutor_id, $student_id, $amount, $study_date, $start_time, $end_time);
        $this->redirect("?url=admin/bookings&msg=" . urlencode('Cập nhật booking thành công.'));
    }

    public function deleteBooking()
    {
        $this->ensureAdmin();

        if (!isset($_GET['id'])) {
            $this->redirect("?url=admin/bookings");
        }

        $model = new Booking($this->db);
        $model->delete((int) $_GET['id']);
        $this->redirect("?url=admin/bookings&msg=" . urlencode('Xoá booking thành công.'));
    }

    // 📌 [HÀM MỚI TÍCH HỢP] XÁC NHẬN ĐÓNG TIỀN (cho_thanh_toan -> da_thanh_toan)
    public function updatePaymentStatus()
    {
        $this->ensureAdmin();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';

        if ($id > 0 && in_array($status, ['cho_thanh_toan', 'da_thanh_toan', 'rejected'])) {
            $bookingModel = new Booking($this->db);
            $bookingModel->updateStatus($id, $status);
            $this->redirect("?url=admin/bookings&msg=" . urlencode('Xác nhận đóng học phí thành công! Trạng thái chuyển sang Đã thanh toán.'));
        } else {
            $this->redirect("?url=admin/bookings&error=" . urlencode('Thông tin yêu cầu thay đổi không hợp lệ.'));
        }
    }

    // 📌 [HÀM MỚI NÂNG CẤP CHUẨN] DUYỆT ĐĂNG KÝ HỌC LỊCH CỐ ĐỊNH (BẮT BUỘC CHECK THANH TOÁN)
    public function approveBooking()
    {
        $this->ensureAdmin();

        $bookingId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($bookingId <= 0) {
            $this->redirect("?url=admin/bookings");
        }

        $model = new Booking($this->db);
        $booking = $model->getById($bookingId);
        if (!$booking) {
            $this->redirect("?url=admin/bookings");
        }

        // 🛡️ KIỂM TRA ĐIỀU KIỆN ĐẶC BIỆT: Nếu sinh viên chưa thanh toán (status = cho_thanh_toan) -> CHẶN TUYỆT ĐỐI không cho Duyệt
        if ($booking['status'] === 'cho_thanh_toan') {
            $this->redirect("?url=admin/bookings&error=" . urlencode('Thất bại! Sinh viên này chưa thanh toán học phí, vui lòng bấm nút xác nhận đóng tiền trước khi duyệt lớp!'));
        }

        if ($booking['status'] === 'approved') {
            $this->redirect("?url=admin/bookings&msg=" . urlencode('Đơn đăng ký học này đã được kích hoạt từ trước.'));
        }

        // Thực thi phê duyệt an toàn thông qua hàm approveBooking kép của Model Booking
        if (!empty($booking['schedule_id'])) {
            // Thực thi ghi danh lớp cố định lặp lại dài hạn
            $result = $model->approveBooking((int)$booking['id'], (int)$booking['schedule_id'], (int)$booking['student_id']);
            
            if ($result) {
                $this->redirect("?url=admin/bookings&msg=" . urlencode('Duyệt thành công! Sinh viên đã chính thức được gán tên vào danh sách lớp cố định.'));
            } else {
                $this->redirect("?url=admin/bookings&error=" . urlencode('Có lỗi xảy ra trong quá trình cập nhật cơ sở dữ liệu.'));
            }
        }

        // Luồng xử lý dự phòng đối với đơn đăng ký lịch đơn lẻ theo ngày cụ thể (Bản cũ của bạn)
        $studyDate = trim((string) ($booking['study_date'] ?? ''));
        $startTime = trim((string) ($booking['start_time'] ?? ''));
        $endTime = trim((string) ($booking['end_time'] ?? ''));

        if ($studyDate === '' || $startTime === '' || $endTime === '') {
            $this->redirect("?url=admin/bookings&error=" . urlencode('Booking thiếu ngày/giờ. Vui lòng cập nhật thông tin trước khi duyệt.'));
        }

        $conflict = $this->scheduleModel->checkConflictForParticipants(
            (int) $booking['tutor_id'], 
            (int) $booking['student_id'], 
            $studyDate, 
            $startTime, 
            $endTime
        );

        if ($conflict) {
            $this->redirect("?url=admin/bookings&error=" . urlencode('Không thể duyệt do trùng lịch học.'));
        }

        $subject = trim((string) ($booking['tutor_subjects'] ?? '')) ?: 'Lớp đã đăng ký';

        try {
            $this->db->beginTransaction();

            $inserted = $this->scheduleModel->insert(
                (int) $booking['tutor_id'],
                (int) $booking['student_id'],
                $subject,
                $studyDate,
                $startTime,
                $endTime
            );

            if (!$inserted) {
                throw new Exception('Không thể tạo lịch học khi duyệt booking.');
            }

            $updated = $model->updateStatus((int) $_GET['id'], 'approved');
            if (!$updated) {
                throw new Exception('Không thể cập nhật trạng thái booking sang đã duyệt.');
            }

            $this->db->commit();
            $this->redirect("?url=admin/bookings&msg=" . urlencode('Duyệt booking thành công và đã tạo lịch học chi tiết.'));
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->redirect("?url=admin/bookings&error=" . urlencode($e->getMessage()));
        }
    }

    /* ================= ADMIN SCHEDULES ================= */

    public function schedules()
    {
        $this->ensureAdmin();

        $filters = [
            'tutor_id' => isset($_GET['tutor_id']) ? (int) $_GET['tutor_id'] : 0,
            'student_id' => isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0,
            'day' => isset($_GET['day']) ? $_GET['day'] : '',
            'status' => trim($_GET['status'] ?? ''),
            'q' => trim($_GET['q'] ?? '')
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 12;
        $totalRows = $this->scheduleModel->countAdminRecurringSchedules($filters);
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        $page = min($page, $totalPages);

        $schedules = $this->scheduleModel->getAdminRecurringSchedules($filters, $page, $perPage);
        $tutorOptions = $this->scheduleModel->getTutorOptions();
        $studentOptions = $this->scheduleModel->getStudentProfileOptions();

        require_once 'views/layouts/admin_header.php';
        require_once 'views/admin/schedules.php';
        require_once 'views/layouts/admin_footer.php';
    }

    public function storeSchedule()
    {
        $this->ensureAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("?url=admin/schedules");
        }

        $gia_su = (int) ($_POST['gia_su_id'] ?? 0);
        $hoc_vien = (int) ($_POST['hoc_vien_id'] ?? 0);
        $mon = trim($_POST['mon_hoc'] ?? '');
        $thu = (int) ($_POST['thu_trong_tuan'] ?? -1);
        $phien = (int) ($_POST['phien_hoc'] ?? 0);
        $so_buoi = max(1, (int) ($_POST['so_buoi'] ?? 1));
        $ghi_chu = trim($_POST['admin_ghi_chu'] ?? '');

        if ($gia_su <= 0 || $mon === '' || !in_array($thu, range(0, 6), true) || !in_array($phien, [1, 2, 3], true)) {
            $this->redirect("?url=admin/schedules&error=" . urlencode('Thông tin lịch học không hợp lệ.'));
        }

        if ($this->scheduleModel->hasTutorRecurringConflict($gia_su, $thu, $phien)) {
            $this->redirect("?url=admin/schedules&error=" . urlencode('Trùng ca: gia sư đã có lịch ở khung này.'));
        }

        if ($hoc_vien > 0 && $this->scheduleModel->hasStudentRecurringConflictByProfileId($hoc_vien, $thu, $phien)) {
            $this->redirect("?url=admin/schedules&error=" . urlencode('Trùng ca: học viên đã có lớp khác ở khung này.'));
        }

        $this->scheduleModel->createRecurringSchedule($gia_su, $hoc_vien, $mon, $thu, $phien, $so_buoi, $ghi_chu);
        $this->redirect("?url=admin/schedules&msg=" . urlencode('Tạo lịch học thành công.'));
    }

    public function updateSchedule()
    {
        $this->ensureAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("?url=admin/schedules");
        }

        $id = (int) ($_POST['id'] ?? 0);
        $gia_su = (int) ($_POST['gia_su_id'] ?? 0);
        $hoc_vien = (int) ($_POST['hoc_vien_id'] ?? 0);
        $mon = trim($_POST['mon_hoc'] ?? '');
        $thu = (int) ($_POST['thu_trong_tuan'] ?? -1);
        $phien = (int) ($_POST['phien_hoc'] ?? 0);
        $so_buoi = max(1, (int) ($_POST['so_buoi'] ?? 1));
        $ghi_chu = trim($_POST['admin_ghi_chu'] ?? '');
        $trang_thai = (int) ($_POST['trang_thai'] ?? 1);

        if ($id <= 0 || $gia_su <= 0 || $mon === '' || !in_array($thu, range(0, 6), true) || !in_array($phien, [1, 2, 3], true) || !in_array($trang_thai, [0, 1, 2], true)) {
            $this->redirect("?url=admin/schedules&error=" . urlencode('Thông tin cập nhật không hợp lệ.'));
        }

        if ($trang_thai === 1 && $this->scheduleModel->hasTutorRecurringConflict($gia_su, $thu, $phien, $id)) {
            $this->redirect("?url=admin/schedules&error=" . urlencode('Không thể cập nhật vì gia sư bị trùng ca.'));
        }

        if ($trang_thai === 1 && $hoc_vien > 0 && $this->scheduleModel->hasStudentRecurringConflictByProfileId($hoc_vien, $thu, $phien, $id)) {
            $this->redirect("?url=admin/schedules&error=" . urlencode('Không thể cập nhật vì học viên bị trùng ca.'));
        }

        $this->scheduleModel->updateRecurringAdmin($id, $gia_su, $hoc_vien, $mon, $thu, $phien, $so_buoi, $ghi_chu, $trang_thai);
        $this->redirect("?url=admin/schedules&msg=" . urlencode('Cập nhật lịch học thành công.'));
    }

    public function approveSchedule()
    {
        $this->ensureAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->scheduleModel->updateRecurringStatus($id, 1);
        }
        $this->redirect("?url=admin/schedules&msg=" . urlencode('Đã duyệt lịch học.'));
    }

    public function cancelSchedule()
    {
        $this->ensureAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->scheduleModel->updateRecurringStatus($id, 0);
        }
        $this->redirect("?url=admin/schedules&msg=" . urlencode('Đã huỷ lịch học.'));
    }

    /* ================= REVENUE DASHBOARD ================= */

    public function revenue()
    {
        $this->ensureAdmin();

        $filters = [
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to' => trim($_GET['date_to'] ?? ''),
            'tutor_id' => isset($_GET['tutor_id']) ? (int) $_GET['tutor_id'] : 0,
            'subject' => trim($_GET['subject'] ?? '')
        ];

        $revenueModel = new Revenue($this->db);
        $summary = $revenueModel->getSummary($filters);
        $systemStats = $revenueModel->getSystemStats();
        $periodRevenue = $revenueModel->getRevenueByPeriod($filters);
        $monthlyRevenue = $revenueModel->getMonthlyRevenue($filters);
        $topTutorsRevenue = $revenueModel->getTopTutorsByRevenue($filters);
        $topTutorsStudents = $revenueModel->getTopTutorsByStudents($filters);
        $popularSubjects = $revenueModel->getPopularSubjects($filters);
        $tutorOptions = $this->scheduleModel->getTutorOptions();

        require_once 'views/layouts/admin_header.php';
        require_once 'views/admin/revenue.php';
        require_once 'views/layouts/admin_footer.php';
    }
}