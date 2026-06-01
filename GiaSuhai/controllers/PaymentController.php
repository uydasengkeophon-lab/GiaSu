<?php
require_once 'config/Database.php';
require_once 'models/Booking.php';
require_once 'models/Schedule.php';

class PaymentController
{

    private $bookingModel;
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
        $this->bookingModel = new Booking($this->conn);
    }

    // ✅ Trang thanh toán
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location:index.php?url=auth/login");
            exit;
        }

        if (($_SESSION['role'] ?? '') !== 'student') {
            header("Location:index.php");
            exit;
        }

        $keyword = trim($_GET['q'] ?? '');
        $paymentList = [];
        $booking = null;

        if (isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            $booking = $this->bookingModel->getByIdForStudent($id, (int) $_SESSION['user_id']);

            if (!$booking) {
                header("Location:index.php?url=payment/index&error=" . urlencode('Không tìm thấy đơn thanh toán của bạn.'));
                exit;
            }
        } else {
            $paymentList = $this->bookingModel->getPendingPaymentsByStudent((int) $_SESSION['user_id'], $keyword);
        }

        require_once 'views/layouts/header.php';
        require_once 'views/payment/index.php';
        require_once 'views/layouts/footer.php';
    }

    // ✅ Thanh toán + auto tạo lịch
    public function pay()
    {

        if (!isset($_GET['id'])) {
            echo "Không có đơn!";
            return;
        }

        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
            header("Location:index.php?url=auth/login");
            exit;
        }

        $id = $_GET['id'];

        $booking = $this->bookingModel->getByIdForStudent($id, (int) $_SESSION['user_id']);

        if (!$booking) {
            echo "Không tìm thấy đơn!";
            return;
        }

        if (in_array($booking['status'] ?? '', ['paid', 'approved'], true)) {
            header("Location:index.php?url=schedule/index&msg=" . urlencode('Đơn này đã được thanh toán trước đó.'));
            exit;
        }

        $scheduleModel = new Schedule($this->conn);

        if (!empty($booking['schedule_id'])) {
            try {
                $this->conn->beginTransaction();

                if (!$scheduleModel->activateRecurringBooking($booking)) {
                    throw new Exception('Không thể kích hoạt lịch cố định do khung giờ đã bị chiếm hoặc bị trùng lịch.');
                }

                if (!$this->bookingModel->updateStatus($id, 'paid')) {
                    throw new Exception('Không thể cập nhật trạng thái thanh toán.');
                }

                $this->conn->commit();
                header("Location:index.php?url=schedule/index&msg=" . urlencode('Thanh toán thành công, lịch học cố định đã được cập nhật.'));
                exit;
            } catch (Exception $e) {
                if ($this->conn->inTransaction()) {
                    $this->conn->rollBack();
                }
                header("Location:index.php?url=schedule/index&error=" . urlencode($e->getMessage()));
                exit;
            }
        }

        $isConflict = $scheduleModel->checkConflictForParticipants(
            $booking['tutor_id'],
            $booking['student_id'],
            $booking['study_date'],
            $booking['start_time'],
            $booking['end_time']
        );

        if ($isConflict) {
            header("Location:index.php?url=schedule/index&error=" . urlencode('Trùng lịch: gia sư hoặc học viên đã có lịch trong khung giờ này.'));
            exit;
        }

        // update trạng thái
        $this->bookingModel->updateStatus($id, 'paid');

        // ✅ tạo lịch
        $created = $scheduleModel->insert(
            $booking['tutor_id'],
            $booking['student_id'],
            "Môn học",
            $booking['study_date'],
            $booking['start_time'],
            $booking['end_time']
        );

        if (!$created) {
            header("Location:index.php?url=schedule/index&error=" . urlencode('Không thể tạo lịch học sau khi thanh toán.'));
            exit;
        }

        // chuyển trang
        header("Location:index.php?url=schedule/index&msg=" . urlencode('Thanh toán thành công, lịch học đã được cập nhật.'));
        exit;
    }
}
