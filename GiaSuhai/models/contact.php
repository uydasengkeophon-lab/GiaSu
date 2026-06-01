<?php

class Contact {

    private $conn;

    public function __construct() {
        $db = new Database();
       $this->conn = $db->connect(); // ✅ FIX
    }

    // Lấy tất cả liên hệ
    public function getAll(){
        $stmt = $this->conn->prepare("SELECT * FROM contacts ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thêm liên hệ
    public function insert($name, $email, $message){
        $sql = "INSERT INTO contacts(name,email,message) VALUES(?,?,?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$name, $email, $message]);
    }

    // Xóa liên hệ
    public function delete($id){
        $stmt = $this->conn->prepare("DELETE FROM contacts WHERE id=?");
        return $stmt->execute([$id]);
    }
}
