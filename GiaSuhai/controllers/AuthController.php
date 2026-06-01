<?php
// controllers/AuthController.php
require_once 'models/User.php';
require_once 'helpers/SecurityHelper.php';

class AuthController
{
    private $db;
    private $userModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
        $this->userModel = new User($this->db);
    }

    // Hiển thị form đăng nhập
    public function login()
    {
        SecurityHelper::csrfToken();
        require_once 'views/layouts/header.php';
        require_once 'views/auth/login.php';
        require_once 'views/layouts/footer.php';
    }

    // Xử lý dữ liệu đăng nhập
    public function loginPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?url=auth/login");
            exit;
        }

        if (!SecurityHelper::verifyCsrf($_POST['_csrf_token'] ?? '')) {
            echo "<script>alert('Phiên bảo mật không hợp lệ. Vui lòng thử lại.'); window.history.back();</script>";
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
            echo "<script>alert('Email hoặc mật khẩu không đúng!'); window.history.back();</script>";
            exit;
        }

        SecurityHelper::regenerateSession();
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        header("Location: index.php");
        exit;
    }

    // Hiển thị form đăng ký
    public function register()
    {
        SecurityHelper::csrfToken();
        require_once 'views/layouts/header.php';
        require_once 'views/auth/register.php';
        require_once 'views/layouts/footer.php';
    }

    // Xử lý đăng ký
    public function registerPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?url=auth/register");
            exit;
        }

        if (!SecurityHelper::verifyCsrf($_POST['_csrf_token'] ?? '')) {
            echo "<script>alert('Phiên bảo mật không hợp lệ. Vui lòng thử lại.'); window.history.back();</script>";
            exit;
        }

        $this->userModel->full_name = trim($_POST['full_name'] ?? '');
        $this->userModel->username = trim($_POST['username'] ?? '');
        $this->userModel->password = $_POST['password'] ?? '';
        $this->userModel->email = trim($_POST['email'] ?? '');
        $this->userModel->role = $_POST['role'] ?? 'student';
        $this->userModel->subjects = isset($_POST['subjects']) ? trim($_POST['subjects']) : null;

        if ($this->userModel->register()) {
            echo "<script>alert('Đăng ký thành công! Hãy đăng nhập.'); window.location.href='?url=auth/login';</script>";
        } else {
            echo "<script>alert('Đăng ký thất bại. Vui lòng kiểm tra email, username hoặc mật khẩu tối thiểu 6 ký tự.'); window.history.back();</script>";
        }
        exit;
    }
    // Đăng xuất
    public function logout()
    {
        $_SESSION = [];
        session_destroy();
        header("Location: index.php");
        exit;
    }
}
