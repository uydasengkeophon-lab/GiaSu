-- Thiết kế database đề xuất cho logic lịch học chuyên sâu.
-- Quy ước:
-- - bookings.student_id trỏ users.id của sinh viên.
-- - lich_hoc_hang_tuan.hoc_vien trỏ students.id, chỉ gán khi paid/approved.
-- - pending chỉ nằm ở bookings, chưa hiện trong lịch học sinh viên.

CREATE TABLE IF NOT EXISTS lich_hoc_hang_tuan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  gia_su INT NOT NULL,
  hoc_vien INT NULL DEFAULT NULL,
  mon_hoc VARCHAR(255) NOT NULL,
  thu_trong_tuan TINYINT NOT NULL COMMENT '0=Thứ Hai ... 6=Chủ Nhật',
  phien_hoc TINYINT NOT NULL COMMENT '1=Sáng, 2=Chiều, 3=Tối',
  so_buoi INT NOT NULL DEFAULT 1,
  trang_thai TINYINT NOT NULL DEFAULT 1 COMMENT '0=Huỷ, 1=Hoạt động, 2=Chờ duyệt',
  admin_ghi_chu TEXT NULL,
  ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_lhht_tutor_slot (gia_su, thu_trong_tuan, phien_hoc, trang_thai),
  INDEX idx_lhht_student_slot (hoc_vien, thu_trong_tuan, phien_hoc, trang_thai),
  CONSTRAINT fk_lhht_tutor FOREIGN KEY (gia_su) REFERENCES tutors(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_lhht_student FOREIGN KEY (hoc_vien) REFERENCES students(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tutor_id INT NOT NULL,
  student_id INT NOT NULL COMMENT 'users.id của sinh viên',
  schedule_id INT NULL COMMENT 'lich_hoc_hang_tuan.id nếu đăng ký lịch cố định',
  status ENUM('pending','paid','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  study_date DATE NULL,
  start_time TIME NULL,
  end_time TIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  paid_at DATETIME NULL,
  INDEX idx_booking_student_status (student_id, status),
  INDEX idx_booking_tutor_status (tutor_id, status),
  INDEX idx_booking_schedule_status (schedule_id, status, student_id),
  CONSTRAINT fk_booking_tutor FOREIGN KEY (tutor_id) REFERENCES tutors(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_booking_student_user FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_booking_schedule FOREIGN KEY (schedule_id) REFERENCES lich_hoc_hang_tuan(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- MySQL/MariaDB không hỗ trợ UNIQUE có điều kiện theo status.
-- Vì vậy chống trùng lịch chính nằm ở transaction + SELECT FOR UPDATE trong PHP,
-- và các index trên giúp truy vấn conflict nhanh.
