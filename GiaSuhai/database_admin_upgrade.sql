-- Bổ sung dữ liệu quản trị lịch học và tối ưu truy vấn dashboard.
-- Chạy thủ công nếu database chưa có các cột/index này.

ALTER TABLE lich_hoc_hang_tuan
  ADD COLUMN IF NOT EXISTS so_buoi INT NOT NULL DEFAULT 1 AFTER phien_hoc,
  ADD COLUMN IF NOT EXISTS admin_ghi_chu TEXT NULL AFTER trang_thai;

ALTER TABLE lich_hoc_hang_tuan
  ADD INDEX IF NOT EXISTS idx_recurring_filter (gia_su, hoc_vien, thu_trong_tuan, phien_hoc, trang_thai);

ALTER TABLE bookings
  ADD INDEX IF NOT EXISTS idx_booking_revenue_filter (status, study_date, tutor_id, amount);
