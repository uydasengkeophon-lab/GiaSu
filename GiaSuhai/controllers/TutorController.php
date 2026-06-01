<?php
require_once 'config/Database.php';
require_once 'models/Tutor.php';
require_once 'models/Booking.php';
require_once 'models/Schedule.php';
require_once 'helpers/SecurityHelper.php';

class TutorController
{

    private $db;
    private $tutorModel;
    private $bookingModel;
    private $scheduleModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();

        $this->tutorModel = new Tutor($this->db);
        $this->bookingModel = new Booking($this->db);
        $this->scheduleModel = new Schedule($this->db);
    }

    private function alertBack($message)
    {
        echo '<script>alert(' . json_encode($message, JSON_UNESCAPED_UNICODE) . ');history.back();</script>';
        exit;
    }

    // ================= DANH SÁCH =================
    public function index()
    {
        $this->list();
    }

    public function list()
    {

        if (isset($_SESSION['role']) && $_SESSION['role'] == 'tutor') {

            $user_id = $_SESSION['user_id'];
            $myProfile = $this->tutorModel->getTutorByUserId($user_id);

            require_once 'views/layouts/header.php';
            require_once 'views/tutor/profile.php';
            require_once 'views/layouts/footer.php';
        } else {

            $search = trim($_GET['q'] ?? '');
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $stmt = $this->tutorModel->getAllTutors($search, $page, 12);
            $tutors = $stmt->fetchAll(PDO::FETCH_ASSOC);

            require_once 'views/layouts/header.php';
            require_once 'views/tutor/list.php';
            require_once 'views/layouts/footer.php';
        }
    }

    // ================= CHI TIẾT =================
    public function detail()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // 1. Lấy thông tin hồ sơ gia sư
        $tutor = $this->tutorModel->getById($id); 
        if (!$tutor) {
            header("Location: ?url=tutor");
            exit();
        }

        $recurringSchedules = $this->tutorModel->getSchedulesByTutorId($id);

        // 3. Nhúng layout và truyền toàn bộ dữ liệu sang hiển thị
        require_once 'views/layouts/header.php';
        require_once 'views/tutor/detail.php'; // Gọi file giao diện chúng ta vừa sửa đổi ở trên
        require_once 'views/layouts/footer.php';
    }
    
    // ================= ĐĂNG KÝ HỌC =================
    public function book()
    {

        // ❌ chưa đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header("Location:index.php?url=auth/login");
            exit;
        }

        // ❌ không phải học sinh
        if ($_SESSION['role'] != 'student') {
            $this->alertBack('Chỉ học sinh mới được đăng ký!');
        }

        // ❌ thiếu dữ liệu
        if (!isset($_POST['tutor_id'])) {
            echo "Thiếu dữ liệu!";
            return;
        }

        $tutor_id  = $_POST['tutor_id'];
        $student_id = $_SESSION['user_id'];

        // ✅ lấy dữ liệu từ form
        $study_date = $_POST['study_date'] ?? null;
        $start_time = $_POST['start_time'] ?? null;
        $end_time   = $_POST['end_time'] ?? null;

        // ❌ thiếu ngày giờ
        if (!$study_date || !$start_time || !$end_time) {
            $this->alertBack('Vui lòng nhập đầy đủ ngày và giờ!');
        }

        // ❌ giờ sai
        if ($start_time >= $end_time) {
            $this->alertBack('Giờ không hợp lệ!');
        }

        // ❌ đã đăng ký
        if ($this->bookingModel->isBooked($tutor_id, $student_id)) {
            $this->alertBack('Bạn đã đăng ký với gia sư này rồi!');
        }

        if ($this->bookingModel->checkConflict($tutor_id, $student_id, $study_date, $start_time, $end_time)) {
            $this->alertBack('Trùng lịch đăng ký: bạn hoặc gia sư đã có lịch trong khung giờ này!');
        }

        if ($this->scheduleModel->checkConflictForParticipants($tutor_id, $student_id, $study_date, $start_time, $end_time)) {
            $this->alertBack('Trùng với lịch học chính thức hiện có, vui lòng chọn khung giờ khác!');
        }

        // ❌ đủ slot
        $current = $this->bookingModel->countSlots($tutor_id);
        if ($current >= 25) {
            $this->alertBack('Lớp đã đủ!');
        }

        // ✅ lấy tutor
        $tutor = $this->tutorModel->getById($tutor_id);

        if (!$tutor) {
            echo "Không tìm thấy gia sư!";
            return;
        }

        $amount = $tutor['hourly_rate'];

        // ✅ tạo booking
        $booking_id = $this->bookingModel->create(
            $tutor_id,
            $student_id,
            $amount,
            $study_date,
            $start_time,
            $end_time
        );

        if (!$booking_id) {
            $this->alertBack('Không thể tạo đăng ký. Vui lòng thử lại.');
        }

        // ✅ chuyển trang
        header("Location:index.php?url=payment/index&id=" . $booking_id);
        exit;
    }

    // ================= BỔ SUNG VÀO controllers/TutorController.php =================
    // Hàm xử lý nhận dữ liệu từ Form để tự cập nhật hồ sơ cá nhân của Gia sư
    public function updateProfilePost()
    {
        // 1. Kiểm tra bảo mật đăng nhập và quyền hạn
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tutor') {
            header("Location: index.php?url=auth/login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=tutor");
            exit;
        }

        $user_id = (int)$_SESSION['user_id'];
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subjects = trim($_POST['subjects'] ?? '');
        $hourly_rate = isset($_POST['hourly_rate']) ? (float)$_POST['hourly_rate'] : 0;
        $bio = trim($_POST['bio'] ?? '');
        $avatar = null;

        if (empty($full_name)) {
            echo "<script>alert('Họ và tên không được để trống!'); window.history.back();</script>";
            exit;
        }

        // 2. Xử lý Logic Upload Ảnh đại diện (Avatar) nếu Gia sư chọn file mới
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadCheck = SecurityHelper::validateAvatarUpload($_FILES['avatar']);
            if (!$uploadCheck['ok']) {
                echo "<script>alert(" . json_encode($uploadCheck['error'], JSON_UNESCAPED_UNICODE) . "); window.history.back();</script>";
                exit;
            }

            $uploadDir = 'assets/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $avatar = 'tutor_' . $user_id . '_' . $uploadCheck['filename'];
            $destination = $uploadDir . $avatar;

            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                echo "<script>alert('Không thể lưu ảnh đại diện!'); window.history.back();</script>";
                exit;
            }
        }

        // 3. Gọi hàm update đã có sẵn trong Model Tutor.php của bạn để lưu vào CSDL
        // Hàm update($user_id, $full_name, $phone, $subjects, $hourly_rate, $bio, $avatar) của bạn xử lý rất chuẩn rồi
        $result = $this->tutorModel->update($user_id, $full_name, $phone, $subjects, $hourly_rate, $bio, $avatar);

        if ($result) {
            // Cập nhật lại tên hiển thị trên Session nếu họ đổi họ tên
            $_SESSION['username'] = $full_name; 
            echo "<script>alert('Cập nhật hồ sơ cá nhân thành công!'); window.location.href='index.php?url=tutor';</script>";
        } else {
            echo "<script>alert('Có lỗi xảy ra trong quá trình lưu dữ liệu!'); window.history.back();</script>";
        }
        exit;
    }
}
