<?php
class ClassModel {
    private $conn;
    private $table = "classes";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách lớp đang tìm gia sư (status = pending)
    public function readAllActive() {
        $query = "SELECT c.*, s.full_name as student_name 
                  FROM " . $this->table . " c 
                  JOIN students s ON c.student_id = s.id 
                  WHERE c.status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Lấy chi tiết 1 lớp
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>