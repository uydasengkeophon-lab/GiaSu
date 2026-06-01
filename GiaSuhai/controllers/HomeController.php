<?php
// controllers/HomeController.php

class HomeController {
    public function index() {
        require_once 'models/Tutor.php';

        $database = new Database();
        $dbConn = $database->connect();
        $tutorModel = new Tutor($dbConn);

        // Lấy danh sách gia sư
        $prominentTutors = $tutorModel->getProminentTutors(); 

        // Đính kèm lịch dạy cố định hàng tuần dạng số (0 -> 6)
        if (!empty($prominentTutors)) {
            foreach ($prominentTutors as &$tutor) {
                $tutor['schedules'] = $tutorModel->getSchedulesByTutorId($tutor['id']);
            }
            unset($tutor);
        }

        // Mảng dịch dữ liệu số nguyên từ Database sang Tiếng Việt hiển thị
        $dayMap = [
            0 => 'Thứ 2',
            1 => 'Thứ 3',
            2 => 'Thứ 4',
            3 => 'Thứ 5',
            4 => 'Thứ 6',
            5 => 'Thứ 7',
            6 => 'Chủ Nhật'
        ];

        require_once 'views/layouts/header.php';
        require_once 'views/home/index.php';
        require_once 'views/layouts/footer.php';
    }
}
?>