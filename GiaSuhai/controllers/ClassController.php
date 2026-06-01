<?php
require_once 'models/ClassModel.php';

class ClassController {
    private $db;
    private $classModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->classModel = new ClassModel($this->db);
    }

    public function list() {
        $stmt = $this->classModel->readAllActive();
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once 'views/layouts/header.php';
        require_once 'views/class/list.php';
        require_once 'views/layouts/footer.php';
    }

    public function detail() {
        $id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');
        $classItem = $this->classModel->getById($id);

        require_once 'views/layouts/header.php';
        require_once 'views/class/detail.php'; // Bạn cần tạo file này tương tự list
        require_once 'views/layouts/footer.php';
    }
}
?>