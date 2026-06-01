-- Nâng cấp bảo mật và hiệu năng cho hệ thống gia sư.
-- Trước khi thêm UNIQUE, hãy xử lý dữ liệu trùng nếu database cũ đã có trùng username/email.

ALTER TABLE users
  MODIFY username VARCHAR(50) NOT NULL,
  MODIFY email VARCHAR(100) NOT NULL,
  MODIFY password VARCHAR(255) NOT NULL,
  MODIFY role ENUM('admin','tutor','student') NOT NULL,
  ADD UNIQUE KEY IF NOT EXISTS uq_users_username (username),
  ADD UNIQUE KEY IF NOT EXISTS uq_users_email (email),
  ADD INDEX IF NOT EXISTS idx_users_role_created (role, created_at);

ALTER TABLE tutors
  MODIFY user_id INT NOT NULL,
  MODIFY full_name VARCHAR(100) NOT NULL,
  MODIFY hourly_rate DECIMAL(10,2) DEFAULT 0,
  ADD UNIQUE KEY IF NOT EXISTS uq_tutors_user_id (user_id),
  ADD INDEX IF NOT EXISTS idx_tutors_subject_rate (subjects(100), hourly_rate);

ALTER TABLE students
  MODIFY user_id INT NOT NULL,
  ADD UNIQUE KEY IF NOT EXISTS uq_students_user_id (user_id);

ALTER TABLE bookings
  MODIFY status ENUM('pending','paid','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  MODIFY amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  ADD INDEX IF NOT EXISTS idx_bookings_tutor_status_date (tutor_id, status, study_date),
  ADD INDEX IF NOT EXISTS idx_bookings_student_status_date (student_id, status, study_date);

ALTER TABLE lich_hoc_hang_tuan
  ADD INDEX IF NOT EXISTS idx_lhht_tutor_day_session_status (gia_su, thu_trong_tuan, phien_hoc, trang_thai),
  ADD INDEX IF NOT EXISTS idx_lhht_student_day_session_status (hoc_vien, thu_trong_tuan, phien_hoc, trang_thai);

-- Khóa ngoại đề xuất nếu dữ liệu hiện tại đã sạch.
-- ALTER TABLE tutors
--   ADD CONSTRAINT fk_tutors_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;
-- ALTER TABLE students
--   ADD CONSTRAINT fk_students_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;
-- ALTER TABLE bookings
--   ADD CONSTRAINT fk_bookings_tutor FOREIGN KEY (tutor_id) REFERENCES tutors(id) ON DELETE SET NULL ON UPDATE CASCADE,
--   ADD CONSTRAINT fk_bookings_student_user FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;
-- ALTER TABLE lich_hoc_hang_tuan
--   ADD CONSTRAINT fk_lhht_tutor FOREIGN KEY (gia_su) REFERENCES tutors(id) ON DELETE CASCADE ON UPDATE CASCADE,
--   ADD CONSTRAINT fk_lhht_student FOREIGN KEY (hoc_vien) REFERENCES students(id) ON DELETE SET NULL ON UPDATE CASCADE;
