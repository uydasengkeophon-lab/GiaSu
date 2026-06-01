<?php
// models/Schedule.php

class Schedule
{
    private $conn;
    private $tableName = 'lich_hoc';

    public function __construct($db)
    {
        $this->conn = $db;
        $this->initializeScheduleStorage();
        $this->ensureScheduleColumns();
        $this->initializeRecurringScheduleStorage();
        $this->ensureRecurringScheduleColumns();
    }

    private function tableExists($table)
    {
        $stmt = $this->conn->prepare("SHOW TABLES LIKE :table_name");
        $stmt->execute([':table_name' => $table]);
        return (bool) $stmt->fetch(PDO::FETCH_NUM);
    }

    private function initializeScheduleStorage()
    {
        if ($this->tableExists('lich_hoc')) {
            $this->tableName = 'lich_hoc';
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS lich_hoc (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    gia_su INT NOT NULL,
                    hoc_vien INT NOT NULL,
                    mon_hoc VARCHAR(255) NOT NULL,
                    ngay DATE NOT NULL,
                    gio_bat_dau TIME NOT NULL,
                    gio_ket_thuc TIME NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_tutor_date (gia_su, ngay),
                    INDEX idx_student_date (hoc_vien, ngay),
                    INDEX idx_date_time (ngay, gio_bat_dau, gio_ket_thuc)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->conn->exec($sql);
        $this->tableName = 'lich_hoc';
    }

    private function ensureScheduleColumns()
    {
        // Có thể mở rộng nếu cần kiểm tra cột bảng lich_hoc lẻ
    }

    // 📌 LẤY TẤT CẢ LỊCH (BẢN SỬA ĐỔI CHUẨN CHO ADMIN)
    public function getAll()
    {
        $sql = "SELECT 
                    l.*, 
                    t.subjects AS mon_hoc,
                    t.full_name AS tutor_name,
                    IFNULL(s.full_name, 'Lớp chung') AS student_name
                FROM {$this->tableName} l
                LEFT JOIN tutors t ON l.gia_su = t.id
                LEFT JOIN students s ON l.hoc_vien = s.id
                ORDER BY l.ngay DESC, l.gio_bat_dau ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getForUser($role, $userId)
    {
        $scheduleRows = $this->getOfficialScheduleForUser($role, $userId);

        if ($role === 'admin') {
            return $scheduleRows;
        }

        $bookingRows = $this->getBookingScheduleForUser($role, $userId);
        $rows = array_merge($scheduleRows, $bookingRows);

        usort($rows, function ($left, $right) {
            $leftKey = ($left['ngay'] ?? '') . ' ' . ($left['gio_bat_dau'] ?? '');
            $rightKey = ($right['ngay'] ?? '') . ' ' . ($right['gio_bat_dau'] ?? '');
            return strcmp($rightKey, $leftKey);
        });

        return $rows;
    }
    
    private function getOfficialScheduleForUser($role, $userId)
    {
        $baseSql = "SELECT 
                        l.*, 
                        t.subjects AS mon_hoc, 
                        t.full_name AS tutor_name,
                        t.user_id AS tutor_user_id,
                        IFNULL(s.full_name, 'Lớp chung') AS student_name,
                        'schedule' AS source_type,
                        NULL AS booking_status,
                        'Lịch chính thức' AS display_status
                    FROM {$this->tableName} l
                    LEFT JOIN tutors t ON l.gia_su = t.id
                    LEFT JOIN students s ON l.hoc_vien = s.id";

        if ($role === 'admin') {
            $sql = $baseSql . " ORDER BY l.ngay DESC, l.gio_bat_dau ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($role === 'tutor') {
            $sql = $baseSql . " WHERE t.user_id = :user_id ORDER BY l.ngay DESC, l.gio_bat_dau ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql = $baseSql . " WHERE s.user_id = :user_id ORDER BY l.ngay DESC, l.gio_bat_dau ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📌 [CẬP NHẬT 1/3] SỬA HÀM LẤY ĐƠN BOOKING THÀNH VIÊN THEO LỊCH TUẦN GỐC
private function getBookingScheduleForUser($role, $userId)
{
    if (!in_array($role, ['tutor', 'student'], true)) {
        return [];
    }

    $sql = "SELECT 
                CONCAT('booking_', b.id) AS id,
                b.tutor_id AS gia_su,
                b.student_id AS hoc_vien,

                -- FIX: LẤY ĐÚ  MÔNS TỪ TUTORS
                t.subjects AS mon_hoc,

                b.study_date AS ngay,
                b.start_time AS gio_bat_dau,
                b.end_time AS gio_ket_thuc,

                COALESCE(t.full_name, tutor_user.username, 'Gia sư') AS tutor_name,
                COALESCE(student_profile.full_name, student_user.username, 'Học viên') AS student_name,

                'booking' AS source_type,
                b.status AS booking_status,

                CASE b.status
                    WHEN 'cho_thanh_toan' THEN 'Chờ thanh toán'
                    WHEN 'pending' THEN 'Chờ thanh toán'

                    WHEN 'da_thanh_toan' THEN 'Đã thanh toán'
                    WHEN 'paid' THEN 'Đã thanh toán'

                    WHEN 'approved' THEN 'Đã duyệt'
                    WHEN 'rejected' THEN 'Đã từ chối'

                    ELSE 'Đơn đăng ký'
                END AS display_status

            FROM bookings b

            LEFT JOIN lich_hoc_hang_tuan l 
                ON b.schedule_id = l.id

            LEFT JOIN tutors t 
                ON b.tutor_id = t.id

            LEFT JOIN users tutor_user 
                ON t.user_id = tutor_user.id

            LEFT JOIN users student_user 
                ON b.student_id = student_user.id

            LEFT JOIN students student_profile 
                ON student_profile.user_id = b.student_id";

    if ($role === 'tutor') {

        $sql .= " WHERE t.user_id = :user_id";

    } else {

        $sql .= " WHERE b.student_id = :user_id
                  AND b.status IN (
                        'cho_thanh_toan',
                        'pending',
                        'da_thanh_toan',
                        'paid',
                        'approved'
                  )";
    }

    $sql .= " ORDER BY b.study_date DESC, b.start_time ASC";

    $stmt = $this->conn->prepare($sql);

    $stmt->execute([
        ':user_id' => $userId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByIdWithNames($id)
    {
        $sql = "SELECT 
                    l.*, 
                    t.subjects AS mon_hoc,
                    t.full_name AS tutor_name,
                    t.user_id AS tutor_user_id,
                    s.full_name AS student_name
                FROM {$this->tableName} l
                LEFT JOIN tutors t ON l.gia_su = t.id
                LEFT JOIN students s ON l.hoc_vien = s.id
                WHERE l.id = :id
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTutorOptions()
    {
        $sql = "SELECT id, full_name FROM tutors ORDER BY full_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentOptions()
    {
        $sql = "SELECT u.id, COALESCE(s.full_name, u.username) AS full_name
                FROM users u
                LEFT JOIN students s ON s.user_id = u.id
                WHERE u.role = 'student'
                ORDER BY full_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentProfileOptions()
    {
        $sql = "SELECT s.id, COALESCE(s.full_name, u.username) AS full_name
                FROM students s
                LEFT JOIN users u ON s.user_id = u.id
                ORDER BY full_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $gia_su, $hoc_vien, $mon, $ngay, $bat_dau, $ket_thuc)
    {
        $sql = "UPDATE {$this->tableName} 
                SET gia_su = :gia_su,
                    hoc_vien = :hoc_vien,
                    mon_hoc = :mon,
                    ngay = :ngay,
                    gio_bat_dau = :bat_dau,
                    gio_ket_thuc = :ket_thuc
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':gia_su' => $gia_su,
            ':hoc_vien' => $hoc_vien,
            ':mon' => $mon,
            ':ngay' => $ngay,
            ':bat_dau' => $bat_dau,
            ':ket_thuc' => $ket_thuc
        ]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }

    public function isOwnedByTutorUser($scheduleId, $userId)
    {
        $sql = "SELECT l.id
            FROM {$this->tableName} l
                INNER JOIN tutors t ON l.gia_su = t.id
                WHERE l.id = :schedule_id AND t.user_id = :user_id
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':schedule_id' => $scheduleId,
            ':user_id' => $userId
        ]);

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function checkConflict($gia_su, $ngay, $bat_dau, $ket_thuc)
    {
        return $this->checkConflictForParticipants($gia_su, null, $ngay, $bat_dau, $ket_thuc);
    }

    public function checkConflictExcludingId($gia_su, $ngay, $bat_dau, $ket_thuc, $excludeId)
    {
        return $this->checkConflictForParticipants($gia_su, null, $ngay, $bat_dau, $ket_thuc, $excludeId);
    }

    public function checkConflictForParticipants($gia_su, $hoc_vien, $ngay, $bat_dau, $ket_thuc, $excludeId = null)
    {
        $sql = "SELECT id FROM {$this->tableName}
                WHERE ngay = :ngay
                AND gio_bat_dau < :ket_thuc
                AND gio_ket_thuc > :bat_dau
                AND (gia_su = :gia_su";

        $params = [
            ':ngay' => $ngay,
            ':ket_thuc' => $ket_thuc,
            ':bat_dau' => $bat_dau,
            ':gia_su' => $gia_su
        ];

        if ($hoc_vien !== null) {
            $sql .= " OR hoc_vien = :hoc_vien";
            $params[':hoc_vien'] = $hoc_vien;
        }

        $sql .= ")";

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($gia_su, $hoc_vien, $mon, $ngay, $bat_dau, $ket_thuc)
    {
        $sql = "INSERT INTO {$this->tableName}(gia_su, hoc_vien, mon_hoc, ngay, gio_bat_dau, gio_ket_thuc)
                VALUES (:gia_su, :hoc_vien, :mon, :ngay, :bat_dau, :ket_thuc)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':gia_su' => $gia_su,
            ':hoc_vien' => $hoc_vien,
            ':mon' => $mon,
            ':ngay' => $ngay,
            ':bat_dau' => $bat_dau,
            ':ket_thuc' => $ket_thuc
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // LỊCH HỌC CỐ ĐỊNH THEO TUẦN (RECURRING SCHEDULES)
    // ═══════════════════════════════════════════════════════════════

    private function initializeRecurringScheduleStorage()
    {
        if ($this->tableExists('lich_hoc_hang_tuan')) {
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS lich_hoc_hang_tuan (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    gia_su INT NOT NULL,
                    hoc_vien INT NOT NULL,
                    mon_hoc VARCHAR(255) NOT NULL,
                    thu_trong_tuan INT NOT NULL COMMENT '0=Thứ 2, 1=Thứ 3, ..., 6=Chủ nhật',
                    phien_hoc INT NOT NULL COMMENT '1=Buổi sáng (7-11), 2=Buổi chiều (14-17)',
                    trang_thai TINYINT DEFAULT 1,
                    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_tutor (gia_su),
                    INDEX idx_student (hoc_vien),
                    INDEX idx_tutor_student (gia_su, hoc_vien)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->conn->exec($sql);
    }

    private function ensureRecurringScheduleColumns()
    {
        $columns = $this->getTableColumns('lich_hoc_hang_tuan');

        if (!isset($columns['so_buoi'])) {
            $this->conn->exec("ALTER TABLE lich_hoc_hang_tuan ADD COLUMN so_buoi INT NOT NULL DEFAULT 1 AFTER phien_hoc");
        }

        if (!isset($columns['admin_ghi_chu'])) {
            $this->conn->exec("ALTER TABLE lich_hoc_hang_tuan ADD COLUMN admin_ghi_chu TEXT NULL AFTER trang_thai");
        }

        if (!$this->indexExists('lich_hoc_hang_tuan', 'idx_recurring_filter')) {
            $this->conn->exec("ALTER TABLE lich_hoc_hang_tuan ADD INDEX idx_recurring_filter (gia_su, hoc_vien, thu_trong_tuan, phien_hoc, trang_thai)");
        }

        if (!$this->indexExists('lich_hoc_hang_tuan', 'idx_recurring_tutor_slot')) {
            $this->conn->exec("ALTER TABLE lich_hoc_hang_tuan ADD INDEX idx_recurring_tutor_slot (gia_su, thu_trong_tuan, phien_hoc, trang_thai)");
        }

        if (!$this->indexExists('lich_hoc_hang_tuan', 'idx_recurring_student_slot')) {
            $this->conn->exec("ALTER TABLE lich_hoc_hang_tuan ADD INDEX idx_recurring_student_slot (hoc_vien, thu_trong_tuan, phien_hoc, trang_thai)");
        }

        if ($this->tableExists('bookings') && !$this->indexExists('bookings', 'idx_booking_schedule_status_student')) {
            $this->conn->exec("ALTER TABLE bookings ADD INDEX idx_booking_schedule_status_student (schedule_id, status, student_id)");
        }
    }

    private function getTableColumns($table)
    {
        $stmt = $this->conn->prepare("SHOW COLUMNS FROM {$table}");
        $stmt->execute();
        $columns = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
            $columns[$column['Field']] = true;
        }

        return $columns;
    }

    private function indexExists($table, $indexName)
    {
        $stmt = $this->conn->prepare("SHOW INDEX FROM {$table} WHERE Key_name = :index_name");
        $stmt->execute([':index_name' => $indexName]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createRecurringSchedule($gia_su, $hoc_vien, $mon, $thu_trong_tuan, $phien_hoc, $so_buoi = 1, $admin_ghi_chu = '')
    {
        if (!in_array((int)$thu_trong_tuan, range(0, 6))) {
            return false;
        }
        if (!in_array((int)$phien_hoc, [1, 2, 3])) {
            return false;
        }

        $sql = "INSERT INTO lich_hoc_hang_tuan (gia_su, hoc_vien, mon_hoc, thu_trong_tuan, phien_hoc, so_buoi, trang_thai, admin_ghi_chu)
                VALUES (:gia_su, :hoc_vien, :mon, :thu_trong_tuan, :phien_hoc, :so_buoi, 1, :admin_ghi_chu)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':gia_su' => $gia_su,
            ':hoc_vien' => $hoc_vien,
            ':mon' => $mon,
            ':thu_trong_tuan' => $thu_trong_tuan,
            ':phien_hoc' => $phien_hoc,
            ':so_buoi' => max(1, (int) $so_buoi),
            ':admin_ghi_chu' => $admin_ghi_chu
        ]);
    }

    private function getSessionTimes($phien_hoc)
    {
        if ($phien_hoc == 1) {
            return ['start' => '07:00:00', 'end' => '11:00:00'];
        } elseif ($phien_hoc == 2) {
            return ['start' => '14:00:00', 'end' => '17:00:00'];
        } elseif ($phien_hoc == 3) {
            return ['start' => '18:00:00', 'end' => '21:00:00'];
        }
        return null;
    }

    private function getDayName($dayOfWeek)
    {
        $days = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
        return $days[$dayOfWeek] ?? '';
    }

    private function getStudentProfileIdByUserId($userId)
    {
        $sql = "SELECT id FROM students WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => (int) $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function getStudentUserIdByProfileId($studentProfileId)
    {
        $sql = "SELECT user_id FROM students WHERE id = :student_profile_id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':student_profile_id' => (int) $studentProfileId]);
        return (int) $stmt->fetchColumn();
    }

    public function hasStudentRecurringConflictByProfileId($studentProfileId, $thuTrongTuan, $phienHoc, $excludeScheduleId = null)
    {
        $studentUserId = $this->getStudentUserIdByProfileId($studentProfileId);
        if ($studentUserId <= 0) {
            return true;
        }

        return $this->hasStudentRecurringConflict($studentUserId, $thuTrongTuan, $phienHoc, $excludeScheduleId);
    }

    public function getRecurringSlotForRegistration($scheduleId)
    {
        $sql = "SELECT l.*, t.full_name AS tutor_name, t.subjects AS tutor_subjects, t.hourly_rate
                FROM lich_hoc_hang_tuan l
                INNER JOIN tutors t ON l.gia_su = t.id
                WHERE l.id = :id AND l.trang_thai = 1
                LIMIT 1";
        if ($this->conn->inTransaction()) {
            $sql .= " FOR UPDATE";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => (int) $scheduleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🚀 CHỈ BÁO TRÙNG LỊCH KHI ĐƠN ĐÓ THỰC SỰ ĐÃ ĐƯỢC CHẤP NHẬN HOẶC ĐÃ ĐÓNG TIỀN (LỚP HỌC ĐẠI TRÀ)
    public function hasStudentRecurringConflict($studentUserId, $thuTrongTuan, $phienHoc, $excludeScheduleId = null)
    {
        $studentProfileId = $this->getStudentProfileIdByUserId($studentUserId);
        if ($studentProfileId <= 0) {
            return true;
        }

        // Kiểm tra xung đột chéo dựa trên đơn đặt lớp tuần trong bookings
        $sql = "SELECT l.id
                FROM bookings b
                INNER JOIN lich_hoc_hang_tuan l ON l.id = b.schedule_id
                WHERE b.student_id = :student_user_id
                AND b.status IN ('cho_thanh_toan', 'pending', 'da_thanh_toan', 'paid', 'approved')
                AND l.trang_thai = 1
                AND l.thu_trong_tuan = :thu_trong_tuan
                AND l.phien_hoc = :phien_hoc";

        $params = [
            ':student_user_id' => (int) $studentUserId,
            ':thu_trong_tuan' => (int) $thuTrongTuan,
            ':phien_hoc' => (int) $phienHoc
        ];

        if ($excludeScheduleId !== null) {
            $sql .= " AND l.id != :exclude_schedule_id";
            $params[':exclude_schedule_id'] = (int) $excludeScheduleId;
        }

        $sql .= " LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            return true;
        }

        // Kiểm tra dựa trên gán ID trực tiếp thô trong bảng lịch học gốc
        $sqlAssigned = "SELECT id FROM lich_hoc_hang_tuan
                        WHERE hoc_vien = :student_profile_id
                        AND trang_thai = 1
                        AND thu_trong_tuan = :thu_trong_tuan
                        AND phien_hoc = :phien_hoc";

        if ($excludeScheduleId !== null) {
            $sqlAssigned .= " AND id != :exclude_schedule_id";
        }

        $sqlAssigned .= " LIMIT 1";
        $stmtAssigned = $this->conn->prepare($sqlAssigned);
        $assignedParams = [
            ':student_profile_id' => $studentProfileId,
            ':thu_trong_tuan' => (int) $thuTrongTuan,
            ':phien_hoc' => (int) $phienHoc
        ];
        if ($excludeScheduleId !== null) {
            $assignedParams[':exclude_schedule_id'] = (int) $excludeScheduleId;
        }
        $stmtAssigned->execute($assignedParams);

        return (bool) $stmtAssigned->fetch(PDO::FETCH_ASSOC);
    }

    public function hasTutorRecurringConflict($tutorId, $thuTrongTuan, $phienHoc, $excludeScheduleId = null)
    {
        $sql = "SELECT id FROM lich_hoc_hang_tuan
                WHERE gia_su = :tutor_id
                AND thu_trong_tuan = :thu_trong_tuan
                AND phien_hoc = :phien_hoc
                AND trang_thai = 1";
        $params = [
            ':tutor_id' => (int) $tutorId,
            ':thu_trong_tuan' => (int) $thuTrongTuan,
            ':phien_hoc' => (int) $phienHoc
        ];

        if ($excludeScheduleId !== null) {
            $sql .= " AND id != :exclude_schedule_id";
            $params[':exclude_schedule_id'] = (int) $excludeScheduleId;
        }

        $sql .= " LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isRecurringSlotReserved($scheduleId, $excludeBookingId = null)
    {
        // LỚP HỌC ĐẠI TRÀ: Hàm này luôn trả về false vì nhiều người được phép đặt cùng 1 ca của thầy
        return false;
    }

    public function canStudentRegisterRecurring($studentUserId, $scheduleId, &$reason = '')
    {
        $slot = $this->getRecurringSlotForRegistration($scheduleId);
        if (!$slot) {
            $reason = 'Khung giờ này không tồn tại hoặc đã bị gỡ bỏ.';
            return false;
        }

        if ($this->hasStudentRecurringConflict($studentUserId, (int) $slot['thu_trong_tuan'], (int) $slot['phien_hoc'], $scheduleId)) {
            $reason = 'Bạn đã có lớp khác trong cùng thứ và ca học này.';
            return false;
        }

        $reason = '';
        return true;
    }

    public function createRecurringBooking($studentUserId, $scheduleId)
    {
        $slot = $this->getRecurringSlotForRegistration($scheduleId);
        if (!$slot) {
            return false;
        }

        $amount = isset($slot['hourly_rate']) ? (float) $slot['hourly_rate'] : 0;
        $sql = "INSERT INTO bookings (tutor_id, student_id, schedule_id, status, amount, created_at)
                VALUES (:tutor_id, :student_id, :schedule_id, 'cho_thanh_toan', :amount, NOW())";
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute([
            ':tutor_id' => (int) $slot['gia_su'],
            ':student_id' => (int) $studentUserId,
            ':schedule_id' => (int) $scheduleId,
            ':amount' => $amount
        ])) {
            return (int) $this->conn->lastInsertId();
        }

        return false;
    }

    public function getStudentBookingForRecurringSlot($studentUserId, $scheduleId)
    {
        $sql = "SELECT id, status FROM bookings
                WHERE student_id = :student_id
                AND schedule_id = :schedule_id
                AND status IN ('cho_thanh_toan', 'pending', 'da_thanh_toan', 'paid', 'approved')
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':student_id' => (int) $studentUserId,
            ':schedule_id' => (int) $scheduleId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function activateRecurringBooking($booking)
    {
        $scheduleId = (int) ($booking['schedule_id'] ?? 0);
        $studentProfileId = (int) ($booking['student_profile_id'] ?? 0);

        if ($scheduleId <= 0 || $studentProfileId <= 0) {
            return false;
        }

        $slot = $this->getRecurringSlotForRegistration($scheduleId);
        if (!$slot) {
            return false;
        }

        if ($this->hasStudentRecurringConflict((int) $booking['student_id'], (int) $slot['thu_trong_tuan'], (int) $slot['phien_hoc'], $scheduleId)) {
            return false;
        }

        $sql = "UPDATE lich_hoc_hang_tuan
                SET hoc_vien = :student_profile_id
                WHERE id = :schedule_id AND trang_thai = 1";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':student_profile_id' => $studentProfileId,
            ':schedule_id' => $scheduleId
        ]);
    }

   // 📌 [CẬP NHẬT 2/3] SỬA HÀM LẤY LỊCH TUẦN
public function getRecurringSchedulesForUser($role, $userId)
{
    if ($role === 'admin') {

        $sql = "SELECT 
                    l.*,
                    t.subjects AS mon_hoc,
                    t.full_name AS tutor_name,
                    s.full_name AS student_name

                FROM lich_hoc_hang_tuan l

                LEFT JOIN tutors t 
                    ON l.gia_su = t.id

                LEFT JOIN students s 
                    ON l.hoc_vien = s.id

                WHERE l.trang_thai = 1

                ORDER BY l.thu_trong_tuan ASC, l.phien_hoc ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    elseif ($role === 'tutor') {

        $sql = "SELECT 
                    l.*,
                    t.subjects AS mon_hoc,
                    t.full_name AS tutor_name,
                    s.full_name AS student_name

                FROM lich_hoc_hang_tuan l

                JOIN tutors t 
                    ON l.gia_su = t.id

                LEFT JOIN students s 
                    ON l.hoc_vien = s.id

                WHERE t.user_id = :user_id
                AND l.trang_thai = 1

                ORDER BY l.thu_trong_tuan ASC, l.phien_hoc ASC";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    elseif ($role === 'student') {

        $sql = "SELECT 
                    l.*,

                    -- FIX lấy đúng môn học thật
                    t.subjects AS mon_hoc,

                    t.full_name AS tutor_name,

                    COALESCE(s.full_name, u.username) AS student_name,

                    b.status AS booking_status,
                    b.id AS booking_id

                FROM bookings b

                INNER JOIN lich_hoc_hang_tuan l
                    ON b.schedule_id = l.id

                LEFT JOIN tutors t
                    ON l.gia_su = t.id

                LEFT JOIN users u
                    ON b.student_id = u.id

                LEFT JOIN students s
                    ON s.user_id = u.id

                WHERE b.student_id = :user_id
                AND l.trang_thai = 1
                AND b.status IN (
                    'cho_thanh_toan',
                    'pending',
                    'da_thanh_toan',
                    'paid',
                    'approved'
                )

                ORDER BY l.thu_trong_tuan ASC, l.phien_hoc ASC";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return [];
}

    /// 📌 [CẬP NHẬT 3/3] ĐỒNG BỘ MÔN HỌC CHO THỜI KHÓA BIỂU
public function getWeeklyTimetableForUser($role, $userId)
{
    if ($role === 'tutor') {

        $sql = "SELECT 
                    l.id AS schedule_id,
                    t.subjects AS mon_hoc,
                    l.thu_trong_tuan,
                    l.phien_hoc,
                    l.hoc_vien,

                    COALESCE(s.full_name, su.username, 'Chưa có học viên') AS student_name,

                    b.status AS booking_status,
                    b.id AS booking_id

                FROM lich_hoc_hang_tuan l

                INNER JOIN tutors t 
                    ON t.id = l.gia_su

                LEFT JOIN bookings b 
                    ON b.schedule_id = l.id
                    AND b.status IN (
                        'cho_thanh_toan',
                        'pending',
                        'da_thanh_toan',
                        'paid',
                        'approved'
                    )

                LEFT JOIN users su 
                    ON su.id = b.student_id

                LEFT JOIN students s 
                    ON s.user_id = su.id

                WHERE t.user_id = :user_id
                AND l.trang_thai = 1

                ORDER BY l.thu_trong_tuan ASC, l.phien_hoc ASC";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ':user_id' => (int) $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($role === 'student') {

        $sql = "SELECT 
                    l.id AS schedule_id,

                    -- FIX lấy đúng môn học thật
                    t.subjects AS mon_hoc,

                    l.thu_trong_tuan,
                    l.phien_hoc,

                    t.full_name AS tutor_name,

                    b.status AS booking_status,
                    b.id AS booking_id

                FROM bookings b

                INNER JOIN lich_hoc_hang_tuan l 
                    ON l.id = b.schedule_id

                INNER JOIN tutors t 
                    ON t.id = l.gia_su

                WHERE b.student_id = :user_id

                AND b.status IN (
                    'cho_thanh_toan',
                    'pending',
                    'da_thanh_toan',
                    'paid',
                    'approved'
                )

                AND l.trang_thai = 1

                ORDER BY l.thu_trong_tuan ASC, l.phien_hoc ASC";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ':user_id' => (int) $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return [];
}

    public function getAllRecurringSchedules()
    {
        $sql = "SELECT 
                    r.*, 
                    t.full_name AS tutor_name,
                    s.full_name AS student_name
                FROM lich_hoc_hang_tuan r
                LEFT JOIN tutors t ON r.gia_su = t.id
                LEFT JOIN students s ON r.hoc_vien = s.id
                WHERE r.trang_thai = 1
                ORDER BY r.thu_trong_tuan ASC, r.phien_hoc ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteRecurringSchedule($id)
    {
        $sql = "UPDATE lich_hoc_hang_tuan SET trang_thai = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }

    public function updateRecurringSchedule($id, $gia_su, $hoc_vien, $mon, $thu_trong_tuan, $phien_hoc, $so_buoi = null, $admin_ghi_chu = null)
    {
        $sql = "UPDATE lich_hoc_hang_tuan 
                SET gia_su = :gia_su,
                    hoc_vien = :hoc_vien,
                    mon_hoc = :mon,
                    thu_trong_tuan = :thu_trong_tuan,
                    phien_hoc = :phien_hoc";

        if ($so_buoi !== null) {
            $sql .= ", so_buoi = :so_buoi";
        }

        if ($admin_ghi_chu !== null) {
            $sql .= ", admin_ghi_chu = :admin_ghi_chu";
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $params = [
            ':id' => $id,
            ':gia_su' => $gia_su,
            ':hoc_vien' => $hoc_vien,
            ':mon' => $mon,
            ':thu_trong_tuan' => $thu_trong_tuan,
            ':phien_hoc' => $phien_hoc
        ];

        if ($so_buoi !== null) {
            $params[':so_buoi'] = max(1, (int) $so_buoi);
        }

        if ($admin_ghi_chu !== null) {
            $params[':admin_ghi_chu'] = $admin_ghi_chu;
        }

        return $stmt->execute($params);
    }

    public function canRegisterNow()
    {
        return true;
    }

    public function generateMonthSchedulesFromRecurring($month = null, $year = null)
    {
        if ($month === null) {
            $month = (int)date('m');
        }
        if ($year === null) {
            $year = (int)date('Y');
        }

        $recurringSchedules = $this->getAllRecurringSchedules();
        $generatedSchedules = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        foreach ($recurringSchedules as $recurring) {
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
                
                if ($date === false) continue;

                $phpDayOfWeek = (int)$date->format('w');
                $myDayOfWeek = ($phpDayOfWeek + 6) % 7;

                if ($myDayOfWeek == (int)$recurring['thu_trong_tuan']) {
                    $sessionTimes = $this->getSessionTimes($recurring['phien_hoc']);
                    
                    $generatedSchedules[] = [
                        'recurring_id' => $recurring['id'],
                        'gia_su' => $recurring['gia_su'],
                        'hoc_vien' => $recurring['hoc_vien'],
                        'mon_hoc' => $recurring['mon_hoc'],
                        'ngay' => $date->format('Y-m-d'),
                        'gio_bat_dau' => $sessionTimes['start'],
                        'gio_ket_thuc' => $sessionTimes['end'],
                        'tutor_name' => $recurring['tutor_name'],
                        'student_name' => $recurring['student_name'],
                        'phien_hoc' => $recurring['phien_hoc']
                    ];
                }
            }
        }

        return $generatedSchedules;
    }

    public function checkRecurringConflict($gia_su, $thu_trong_tuan, $phien_hoc, $excludeId = null, $hoc_vien = null)
    {
        $sql = "SELECT id FROM lich_hoc_hang_tuan 
                WHERE thu_trong_tuan = :thu_trong_tuan 
                AND phien_hoc = :phien_hoc
                AND trang_thai = 1
                AND (gia_su = :gia_su";
        $params = [
            ':gia_su' => $gia_su,
            ':thu_trong_tuan' => $thu_trong_tuan,
            ':phien_hoc' => $phien_hoc
        ];

        if ($hoc_vien !== null && (int) $hoc_vien > 0) {
            $sql .= " OR hoc_vien = :hoc_vien";
            $params[':hoc_vien'] = (int) $hoc_vien;
        }

        $sql .= ")";

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRecurringScheduleById($id)
    {
        $sql = "SELECT 
                    r.*, 
                    t.full_name AS tutor_name,
                    t.user_id AS tutor_user_id,
                    s.full_name AS student_name
                FROM lich_hoc_hang_tuan r
                LEFT JOIN tutors t ON r.gia_su = t.id
                LEFT JOIN students s ON r.hoc_vien = s.id
                WHERE r.id = :id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isRecurringOwnedByTutorUser($recurringId, $userId)
    {
        $sql = "SELECT r.id
                FROM lich_hoc_hang_tuan r
                INNER JOIN tutors t ON r.gia_su = t.id
                WHERE r.id = :recurring_id AND t.user_id = :user_id AND r.trang_thai = 1
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':recurring_id' => $recurringId,
            ':user_id' => $userId
        ]);

        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAdminRecurringSchedules($filters = [], $page = 1, $perPage = 12)
    {
        $where = [];
        $params = [];
        $this->buildAdminRecurringWhere($filters, $where, $params);

        $limit = max(1, (int) $perPage);
        $offset = max(0, ((int) $page - 1) * $limit);
        $whereSql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT 
                    l.*,
                    t.full_name AS tutor_name,
                    t.subjects AS tutor_subjects,
                    COALESCE(s.full_name, su.username, 'Chưa có học viên') AS student_name,
                    CASE
                        WHEN l.trang_thai = 0 THEN 'cancelled'
                        WHEN l.trang_thai = 2 THEN 'pending'
                        WHEN l.hoc_vien IS NULL OR l.hoc_vien = 0 THEN 'open'
                        ELSE 'approved'
                    END AS status_key,
                    CASE
                        WHEN l.trang_thai = 0 THEN 'Đã huỷ'
                        WHEN l.trang_thai = 2 THEN 'Chờ duyệt'
                        WHEN l.hoc_vien IS NULL OR l.hoc_vien = 0 THEN 'Còn trống'
                        ELSE 'Đã duyệt'
                    END AS status_label
                FROM lich_hoc_hang_tuan l
                LEFT JOIN tutors t ON l.gia_su = t.id
                LEFT JOIN students s ON l.hoc_vien = s.id
                LEFT JOIN users su ON s.user_id = su.id
                {$whereSql}
                ORDER BY l.thu_trong_tuan ASC, l.phien_hoc ASC, l.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAdminRecurringSchedules($filters = [])
    {
        $where = [];
        $params = [];
        $this->buildAdminRecurringWhere($filters, $where, $params);
        $whereSql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT COUNT(*)
                FROM lich_hoc_hang_tuan l
                LEFT JOIN tutors t ON l.gia_su = t.id
                LEFT JOIN students s ON l.hoc_vien = s.id
                LEFT JOIN users su ON s.user_id = su.id
                {$whereSql}";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function buildAdminRecurringWhere($filters, &$where, &$params)
    {
        if (!empty($filters['tutor_id'])) {
            $where[] = 'l.gia_su = :tutor_id';
            $params[':tutor_id'] = (int) $filters['tutor_id'];
        }

        if (!empty($filters['student_id'])) {
            $where[] = 'l.hoc_vien = :student_id';
            $params[':student_id'] = (int) $filters['student_id'];
        }

        if ($filters['day'] !== '' && $filters['day'] !== null) {
            $where[] = 'l.thu_trong_tuan = :day';
            $params[':day'] = (int) $filters['day'];
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'cancelled') {
                $where[] = 'l.trang_thai = 0';
            } elseif ($filters['status'] === 'pending') {
                $where[] = 'l.trang_thai = 2';
            } elseif ($filters['status'] === 'open') {
                $where[] = 'l.trang_thai = 1 AND (l.hoc_vien IS NULL OR l.hoc_vien = 0)';
            } elseif ($filters['status'] === 'approved') {
                $where[] = 'l.trang_thai = 1 AND l.hoc_vien > 0';
            }
        }

        if (!empty($filters['q'])) {
            $where[] = "(t.subjects LIKE :keyword OR t.full_name LIKE :keyword OR s.full_name LIKE :keyword OR su.username LIKE :keyword OR l.admin_ghi_chu LIKE :keyword)";
            $params[':keyword'] = '%' . $filters['q'] . '%';
        }
    }

    public function updateRecurringAdmin($id, $gia_su, $hoc_vien, $mon, $thu_trong_tuan, $phien_hoc, $so_buoi, $admin_ghi_chu, $trang_thai)
    {
        $sql = "UPDATE lich_hoc_hang_tuan
                SET gia_su = :gia_su,
                    hoc_vien = :hoc_vien,
                    mon_hoc = :mon,
                    thu_trong_tuan = :thu_trong_tuan,
                    phien_hoc = :phien_hoc,
                    so_buoi = :so_buoi,
                    admin_ghi_chu = :admin_ghi_chu,
                    trang_thai = :trang_thai
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':gia_su' => $gia_su,
            ':hoc_vien' => $hoc_vien,
            ':mon' => $mon,
            ':thu_trong_tuan' => $thu_trong_tuan,
            ':phien_hoc' => $phien_hoc,
            ':so_buoi' => max(1, (int) $so_buoi),
            ':admin_ghi_chu' => $admin_ghi_chu,
            ':trang_thai' => $trang_thai
        ]);
    }

    public function updateRecurringStatus($id, $status)
    {
        $allowed = [0, 1, 2];
        if (!in_array((int) $status, $allowed, true)) {
            return false;
        }

        $sql = "UPDATE lich_hoc_hang_tuan SET trang_thai = :status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':status' => (int) $status
        ]);
    }
}