<?php
class Student
{
    private $conn;
    private $table_name = "students";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Lấy thông tin sinh viên theo user_id
    public function getStudentByUserId($user_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function ensureProfile($user_id, $fallback_full_name = '')
    {
        $existing = $this->getStudentByUserId($user_id);
        if ($existing) {
            return $existing;
        }

        $name = trim((string) $fallback_full_name);
        if ($name === '') {
            $name = 'Học sinh';
        }

        $query = "INSERT INTO " . $this->table_name . " (user_id, full_name) VALUES (:user_id, :full_name)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':full_name', $name);
        $stmt->execute();

        return $this->getStudentByUserId($user_id);
    }

    // Cập nhật thông tin sinh viên
    public function update($user_id, $full_name, $phone, $grade_level, $address)
    {
        $this->ensureProfile($user_id, $full_name);

        $query = "UPDATE " . $this->table_name . " 
                            SET full_name = :full_name, phone = :phone, grade_level = :grade_level, address = :address
                            WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':grade_level', $grade_level);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
    }
}
