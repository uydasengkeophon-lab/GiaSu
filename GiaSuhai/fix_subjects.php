<?php
// Fix: Cập nhật môn học từ tutors.subjects vào lich_hoc_hang_tuan

require 'config/database.php';
$db = new Database();
$conn = $db->connect();

// 1. Kiểm tra dữ liệu lỗi
echo "=== KIỂM TRA DỮ LIỆU HIỆN TẠI ===\n\n";

$sql = "SELECT DISTINCT gia_su, GROUP_CONCAT(DISTINCT mon_hoc) AS mon_hoc
        FROM lich_hoc_hang_tuan
        GROUP BY gia_su
        ORDER BY gia_su";

$stmt = $conn->query($sql);
$scheduledSubjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Dữ liệu hiện tại trong lich_hoc_hang_tuan:\n";
foreach ($scheduledSubjects as $row) {
    $tutorId = $row['gia_su'];
    $tutorSql = "SELECT full_name, subjects FROM tutors WHERE id = ?";
    $tutorStmt = $conn->prepare($tutorSql);
    $tutorStmt->execute([$tutorId]);
    $tutor = $tutorStmt->fetch(PDO::FETCH_ASSOC);
    
    echo "ID: $tutorId | Tên: " . $tutor['full_name'] . " | Môn (tutors): " . $tutor['subjects'] . " | Môn (schedule): " . $row['mon_hoc'] . "\n";
}

echo "\n=== BẮT ĐẦU CẬP NHẬT ===\n\n";

// 2. Cập nhật dữ liệu
$tutorSql = "SELECT id, subjects FROM tutors";
$tutorStmt = $conn->query($tutorSql);
$tutors = $tutorStmt->fetchAll(PDO::FETCH_ASSOC);

$updateCount = 0;
foreach ($tutors as $tutor) {
    $updateSql = "UPDATE lich_hoc_hang_tuan SET mon_hoc = :subject WHERE gia_su = :tutor_id";
    $updateStmt = $conn->prepare($updateSql);
    
    if ($updateStmt->execute([
        ':subject' => $tutor['subjects'],
        ':tutor_id' => $tutor['id']
    ])) {
        $affected = $updateStmt->rowCount();
        if ($affected > 0) {
            echo "✓ Cập nhật " . $tutor['subjects'] . " cho " . $tutor['id'] . " (" . $affected . " record)\n";
            $updateCount += $affected;
        }
    }
}

echo "\n=== KẾT QUẢ ===\n";
echo "Tổng cộng cập nhật: " . $updateCount . " record\n\n";

// 3. Kiểm tra kết quả
echo "=== KIỂM TRA SAU CẬP NHẬT ===\n\n";

$sql = "SELECT gia_su, GROUP_CONCAT(DISTINCT mon_hoc) AS mon_hoc
        FROM lich_hoc_hang_tuan
        GROUP BY gia_su
        HAVING COUNT(DISTINCT mon_hoc) > 1";

$stmt = $conn->query($sql);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($result)) {
    echo "✓ Dữ liệu đã được sửa! Không còn gia sư nào bị nhiều môn học.\n";
} else {
    echo "✗ Vẫn còn lỗi:\n";
    foreach ($result as $row) {
        echo "Gia sư ID: " . $row['gia_su'] . " | Môn: " . $row['mon_hoc'] . "\n";
    }
}

// 4. Hiển thị dữ liệu cuối cùng
echo "\n=== DỮ LIỆU CUỐI CÙNG ===\n\n";

$finalSql = "SELECT l.id, l.gia_su, t.full_name, t.subjects, l.mon_hoc, l.thu_trong_tuan, l.phien_hoc
             FROM lich_hoc_hang_tuan l
             JOIN tutors t ON l.gia_su = t.id
             ORDER BY t.full_name, l.thu_trong_tuan, l.phien_hoc";

$finalStmt = $conn->query($finalSql);
$finalData = $finalStmt->fetchAll(PDO::FETCH_ASSOC);

$currentTutor = '';
foreach ($finalData as $row) {
    if ($currentTutor != $row['full_name']) {
        echo "\n" . $row['full_name'] . " (" . $row['subjects'] . "):\n";
        $currentTutor = $row['full_name'];
    }
    
    $dayName = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'CN'];
    $sessionName = ['Sáng (7-11)', 'Chiều (14-17)'];
    
    echo "  - " . $dayName[$row['thu_trong_tuan']] . ", " . $sessionName[$row['phien_hoc'] - 1] . ", Môn: " . $row['mon_hoc'] . "\n";
}

echo "\n✓ Hoàn thành!\n";
?>
