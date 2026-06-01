#!/usr/bin/env php
<?php
/**
 * Setup Script for Recurring Schedule Feature
 * 
 * Chạy script này để khởi tạo bảng lich_hoc_hang_tuan
 * Usage: php setup_recurring_schedules.php
 */

require_once __DIR__ . '/config/database.php';

echo "=== Initializing Recurring Schedule Feature ===\n\n";

try {
    $database = new Database();
    $conn = $database->connect();

    // Kiểm tra bảng đã tồn tại
    $stmt = $conn->prepare("SHOW TABLES LIKE 'lich_hoc_hang_tuan'");
    $stmt->execute();
    $tableExists = (bool)$stmt->fetch(PDO::FETCH_NUM);

    if ($tableExists) {
        echo "✅ Bảng 'lich_hoc_hang_tuan' đã tồn tại.\n\n";
    } else {
        echo "📝 Tạo bảng 'lich_hoc_hang_tuan'...\n";

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
                    INDEX idx_tutor_student (gia_su, hoc_vien),
                    FOREIGN KEY (gia_su) REFERENCES tutors(id) ON DELETE CASCADE,
                    FOREIGN KEY (hoc_vien) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $conn->exec($sql);
        echo "✅ Bảng 'lich_hoc_hang_tuan' đã được tạo thành công!\n\n";
    }

    // Kiểm tra các cột
    echo "📋 Kiểm tra cấu trúc bảng...\n";
    $stmt = $conn->prepare("DESCRIBE lich_hoc_hang_tuan");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    $requiredColumns = ['id', 'gia_su', 'hoc_vien', 'mon_hoc', 'thu_trong_tuan', 'phien_hoc', 'trang_thai', 'ngay_tao', 'ngay_cap_nhat'];
    $missingColumns = array_diff($requiredColumns, $columns);

    if (empty($missingColumns)) {
        echo "✅ Tất cả các cột bắt buộc đã tồn tại.\n";
    } else {
        echo "⚠️  Các cột thiếu: " . implode(', ', $missingColumns) . "\n";
    }

    echo "\n✅ Cài đặt hoàn tất!\n";
    echo "\n📌 Thông tin bảng:\n";
    foreach ($columns as $column) {
        echo "   - $column\n";
    }

    echo "\n📊 Số lượng bản ghi hiện tại: ";
    $stmt = $conn->prepare("SELECT COUNT(*) FROM lich_hoc_hang_tuan WHERE trang_thai = 1");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    echo "$count\n\n";

} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    exit(1);
}
