<?php
require_once 'models/Booking.php';

class BookingController {

    private $bookingModel;

    public function __construct(){
        require_once 'config/database.php';
        $db = (new Database())->getConnection();
        $this->bookingModel = new Booking($db);
    }

    public function store(){

        session_start();

        $tutor_id = $_POST['tutor_id'];
        $student_id = $_SESSION['user_id'];
        $amount = $_POST['amount'];
        $study_date = $_POST['study_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        if($start_time >= $end_time){
        echo "<script>alert('Giờ không hợp lệ');history.back();</script>";
        return;
    }


        $result = $this->bookingModel->create(
            $tutor_id,
            $student_id,
            $amount,
            $study_date,
            $start_time,
            $end_time
        );

        if(!$result){
            echo "<script>alert('Trùng lịch hoặc lỗi!'); history.back();</script>";
        } else {
            echo "<script>alert('Đặt lịch thành công'); window.location='?route=schedule';</script>";
        }
    }
}