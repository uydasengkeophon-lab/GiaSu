<?php
require_once "config/Database.php";

class News {

    private $conn;

    public function __construct(){
        $db = new Database();
        $this->conn = $db->connect();
    }

    // Lấy tất cả (admin)
    public function getAllAdmin(){
        $sql = "SELECT * FROM news ORDER BY id DESC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy bài đã duyệt (user)
    public function getAll(){
        $sql = "SELECT n.*,
                       COALESCE(t.full_name, u.username, n.author, 'Gia sư') AS tutor_name
                FROM news n
                LEFT JOIN tutors t ON n.tutor_id = t.id
                LEFT JOIN users u ON t.user_id = u.id
                WHERE n.status = 1
                ORDER BY n.id DESC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thêm
    public function insert($title,$content,$image,$tutor_id){
        $sql = "INSERT INTO news(title,content,image,tutor_id,status)
                VALUES(?,?,?,?,0)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$title,$content,$image,$tutor_id]);
    }

    // Duyệt
    public function approve($id){
        $sql = "UPDATE news SET status=1 WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Xóa
    public function delete($id){
        $sql = "DELETE FROM news WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Lấy 1 bài
    public function getById($id){
        $sql = "SELECT * FROM news WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật
    public function update($id,$title,$content){
        $sql = "UPDATE news SET title=?, content=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$title,$content,$id]);
    }
}
