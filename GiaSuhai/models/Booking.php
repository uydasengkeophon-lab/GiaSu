<?php
// models/Booking.php

class Booking
{
    private $conn;
    private $table_name = "bookings";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ✅ Kiểm tra học viên đã gửi đơn đăng ký với gia sư này chưa (Tránh spam đơn)
    public function isBooked($tutor_id, $student_id)
    {
        $sql = "SELECT id FROM bookings WHERE tutor_id=? AND student_id=? AND status != 'rejected'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$tutor_id, $student_id]);
        return $stmt->rowCount() > 0;
    }

    // ✅ Đếm số học sinh đã đăng ký với gia sư, trừ các đơn bị từ chối
    public function countSlots($tutor_id)
    {
        $sql = "SELECT COUNT(DISTINCT student_id) as total
                FROM bookings
                WHERE tutor_id=? AND (status IS NULL OR status != 'rejected')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$tutor_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // 🚀 Check trùng lịch chi tiết theo ngày
    public function checkConflict($tutor_id, $student_id, $study_date, $start_time, $end_time)
    {
        $sql = "SELECT * FROM bookings 
                WHERE study_date = ?
                AND (tutor_id = ? OR student_id = ?)
                AND (start_time < ? AND end_time > ?)
                AND status != 'rejected'";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$study_date, $tutor_id, $student_id, $end_time, $start_time]);
        return $stmt->rowCount() > 0;
    }

    // 🚀 Tạo đơn đăng ký mới (Mặc định khi sinh viên bấm chọn sẽ là: cho_thanh_toan)
    public function createWithSchedule($tutor_id, $student_id, $schedule_id, $amount = 0.00)
    {
        $sql = "INSERT INTO bookings (tutor_id, student_id, schedule_id, amount, status, created_at)
                VALUES (?, ?, ?, ?, 'cho_thanh_toan', NOW())";

        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute([$tutor_id, $student_id, $schedule_id, $amount])) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // ✅ Cập nhật trạng thái thanh toán (Xử lý luồng: cho_thanh_toan -> da_thanh_toan)
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE bookings SET status=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    // 📌 ADMIN DUYỆT ĐĂNG KÝ (Transaction bảo mật tuyệt đối)
    // Chuyển status sang 'approved' đồng thời kích hoạt gán sinh viên vào lịch cố định hàng tuần
    public function approveBooking($bookingId, $scheduleId, $studentUserId)
    {
        try {
            // Khởi động giao dịch Transaction an toàn dữ liệu
            $this->conn->beginTransaction();

            // 1. Cập nhật trạng thái đơn đặt lớp sang 'approved'
            $sqlBooking = "UPDATE bookings SET status = 'approved' WHERE id = ?";
            $stmtB = $this->conn->prepare($sqlBooking);
            $stmtB->execute([$bookingId]);

            // 2. Tìm chính xác ID của học viên trong bảng `students` thông qua student_id (khóa ngoại users)
            $sqlGetStudent = "SELECT id FROM students WHERE user_id = ? LIMIT 1";
            $stmtS = $this->conn->prepare($sqlGetStudent);
            $stmtS->execute([$studentUserId]);
            $studentProfileId = $stmtS->fetchColumn();

            if (!$studentProfileId) {
                throw new Exception("Tài khoản chưa khởi tạo hồ sơ học viên trong bảng students!");
            }

            // 3. Chính thức ghi danh, điền ID học viên vào khung lịch dạy cố định hàng tuần
            $sqlSchedule = "UPDATE lich_hoc_hang_tuan SET hoc_vien = ? WHERE id = ?";
            $stmtSch = $this->conn->prepare($sqlSchedule);
            $stmtSch->execute([$studentProfileId, $scheduleId]);

            // Mọi thứ chạy thành công -> Thực thi lưu vào bộ nhớ đệm
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Nếu có bất kỳ lỗi xung đột dữ liệu nào -> Rollback hủy bỏ toàn bộ luồng tránh rác DB
            $this->conn->rollBack();
            error_log("Lỗi hệ thống phê duyệt: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        $sql = "DELETE FROM bookings WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // ✅ Lấy 1 đơn đăng ký chi tiết kèm tên và thông tin lớp tuần lặp lại
    public function getById($id)
    {
        $sql = "SELECT b.id,
                       b.tutor_id,
                       b.student_id,
                       b.schedule_id,
                       b.amount,
                       b.study_date,
                       b.start_time,
                       b.end_time,
                       b.status,
                       b.created_at,
                       t.full_name AS tutor_name,
                       t.subjects AS tutor_subjects,
                       sp.full_name AS student_name,
                       r.thu_trong_tuan,
                       r.phien_hoc,
                       t.subjects AS recurring_subject
                FROM bookings b
                LEFT JOIN tutors t ON b.tutor_id = t.id
                LEFT JOIN students sp ON sp.user_id = b.student_id
                LEFT JOIN lich_hoc_hang_tuan r ON r.id = b.schedule_id
                WHERE b.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 📌 Lấy đơn chi tiết khớp thông tin bảo mật riêng cho Học viên
    public function getByIdForStudent($id, $studentUserId)
    {
        $sql = "SELECT b.id,
                       b.tutor_id,
                       b.student_id,
                       b.schedule_id,
                       b.amount,
                       b.study_date,
                       b.start_time,
                       b.end_time,
                       b.status,
                       b.created_at,
                       t.full_name AS tutor_name,
                       t.subjects AS tutor_subjects,
                       sp.id AS student_profile_id,
                       sp.full_name AS student_name,
                       r.thu_trong_tuan,
                       r.phien_hoc,
                       t.subjects AS recurring_subject
                FROM bookings b
                LEFT JOIN tutors t ON b.tutor_id = t.id
                LEFT JOIN students sp ON sp.user_id = b.student_id
                LEFT JOIN lich_hoc_hang_tuan r ON r.id = b.schedule_id
                WHERE b.id = :id AND b.student_id = :student_id
                LIMIT 1";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':id' => (int) $id,
            ':student_id' => (int) $studentUserId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 📌 ADMIN XEM TẤT CẢ ĐƠN ĐĂNG KÝ (Bốc đầy đủ Môn, Thứ, Ca học ra bảng Admin)
    public function getAll()
    {
        $sql = "SELECT b.id AS booking_id,
                       b.status AS booking_status,
                       b.created_at AS booking_date,
                       COALESCE(sp.full_name, student_user.username) AS student_name,
                       t.full_name AS tutor_name,
                       t.subjects AS subject_name,
                       r.thu_trong_tuan,
                       r.phien_hoc,
                       r.id AS schedule_id
                FROM bookings b
                LEFT JOIN tutors t ON b.tutor_id = t.id
                LEFT JOIN users student_user ON b.student_id = student_user.id
                LEFT JOIN students sp ON sp.user_id = student_user.id
                LEFT JOIN lich_hoc_hang_tuan r ON b.schedule_id = r.id
                ORDER BY b.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📌 CẬP NHẬT ĐỒNG BỘ: Lấy danh sách hóa đơn xử lý của sinh viên cụ thể
    public function getPendingPaymentsByStudent($studentUserId, $keyword = '')
    {
        // ✅ THAY ĐỔI: Chấp nhận hiển thị cả đơn mới tạo và đơn đã nộp học phí đang chờ duyệt lớp
        $where = [
            "b.student_id = :student_id", 
            "b.status IN ('cho_thanh_toan', 'pending', 'da_thanh_toan', 'paid')"
        ];
        $params = [':student_id' => (int) $studentUserId];

        if (trim($keyword) !== '') {
            $where[] = "(t.full_name LIKE :keyword OR t.subjects LIKE :keyword)";
            $params[':keyword'] = '%' . trim($keyword) . '%';
        }

        $sql = "SELECT b.id, b.amount, b.status, b.created_at, b.study_date, b.start_time, b.end_time,
                       t.full_name AS tutor_name, t.subjects AS tutor_subjects,
                       t.subjects AS recurring_subject, r.thu_trong_tuan, r.phien_hoc
                FROM bookings b
                LEFT JOIN tutors t ON b.tutor_id = t.id
                LEFT JOIN lich_hoc_hang_tuan r ON r.id = b.schedule_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY b.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy lịch học theo học viên
    public function getByStudent($student_id)
    {
        $sql = "SELECT b.*, tutor_user.username as tutor_name, student_user.username as student_name
                FROM bookings b
                LEFT JOIN tutors t ON b.tutor_id = t.id
                LEFT JOIN users tutor_user ON t.user_id = tutor_user.id
                LEFT JOIN users student_user ON b.student_id = student_user.id
                WHERE b.student_id = ? AND b.status = 'approved'
                ORDER BY b.study_date ASC, b.start_time ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
