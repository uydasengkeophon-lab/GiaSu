<?php
require_once "models/News.php";

class NewsController {

    // Hiển thị danh sách tin
    public function index(){
        $model = new News();
        $news = $model->getAll();
        require_once "views/news/index.php";
    }

    // Trang viết thông báo
    public function create(){
        require_once "views/news/create.php";
    }

    // Lưu tin vào database
    public function store(){

        // Lấy dữ liệu form
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        $image = "";

        // Kiểm tra upload ảnh
        if(isset($_FILES['image']) && $_FILES['image']['name'] != ""){

            $image = time()."_".$_FILES['image']['name'];

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                "assets/uploads/".$image
            );
        }

        // Kiểm tra session gia sư
        if(isset($_SESSION['tutor_id'])){
            $tutor_id = $_SESSION['tutor_id'];
        }else{
            $tutor_id = 1; // id mặc định tránh lỗi
        }

        // Lưu database
        $model = new News();
        $model->insert($title,$content,$image,$tutor_id);

        // Quay lại trang tin
        header("Location: index.php?url=news");
        exit();
    }

}