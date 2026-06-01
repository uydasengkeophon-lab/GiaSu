<?php

class Tutor
{
    private $conn;
    private $tableName = 'tutors';

    public $id;
    public $full_name;
    public $subjects;
    public $hourly_rate;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function cleanText($value)
    {
        return trim((string) $value);
    }

    public function read()
    {
        $query = "SELECT * FROM {$this->tableName} ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getTutorByUserId($userId)
    {
        $query = "SELECT * FROM {$this->tableName} WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => (int) $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function ensureProfile($userId, $fallbackFullName = '')
    {
        $existing = $this->getTutorByUserId($userId);
        if ($existing) {
            return $existing;
        }

        $name = $this->cleanText($fallbackFullName);
        if ($name === '') {
            $name = 'Gia sư';
        }

        $query = "INSERT INTO {$this->tableName} (user_id, full_name) VALUES (:user_id, :full_name)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':user_id' => (int) $userId,
            ':full_name' => $name
        ]);

        return $this->getTutorByUserId($userId);
    }

    public function create()
    {
        $fullName = $this->cleanText($this->full_name);
        $subjects = $this->cleanText($this->subjects);
        $hourlyRate = max(0, (float) $this->hourly_rate);

        if ($fullName === '') {
            return false;
        }

        $query = "INSERT INTO {$this->tableName} (full_name, subjects, hourly_rate, user_id)
                  VALUES (:full_name, :subjects, :hourly_rate, :user_id)";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':full_name' => $fullName,
            ':subjects' => $subjects,
            ':hourly_rate' => $hourlyRate,
            ':user_id' => 1
        ]);
    }

    public function update($userId, $fullName, $phone, $subjects, $hourlyRate, $bio, $avatar = null)
    {
        $this->ensureProfile($userId, $fullName);

        $fields = [
            'full_name = :full_name',
            'phone = :phone',
            'subjects = :subjects',
            'hourly_rate = :hourly_rate',
            'bio = :bio'
        ];

        $params = [
            ':full_name' => $this->cleanText($fullName),
            ':phone' => $this->cleanText($phone),
            ':subjects' => $this->cleanText($subjects),
            ':hourly_rate' => max(0, (float) $hourlyRate),
            ':bio' => $this->cleanText($bio),
            ':user_id' => (int) $userId
        ];

        if ($avatar !== null && $avatar !== '') {
            $fields[] = 'avatar = :avatar';
            $params[':avatar'] = basename($avatar);
        }

        $query = "UPDATE {$this->tableName}
                  SET " . implode(', ', $fields) . "
                  WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute($params);
    }

    public function getById($id)
    {
        $query = "SELECT t.*, u.username, u.email
                  FROM {$this->tableName} t
                  LEFT JOIN users u ON t.user_id = u.id
                  WHERE t.id = :id
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => (int) $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllTutors($search = '', $page = 1, $perPage = null)
    {
        $params = [];
        $where = ["u.role = 'tutor'"];

        if ($search !== '') {
            $where[] = "(t.full_name LIKE :search OR t.subjects LIKE :search OR u.email LIKE :search OR u.username LIKE :search)";
            $params[':search'] = '%' . trim($search) . '%';
        }

        $limitSql = '';
        if ($perPage !== null) {
            $limit = max(1, (int) $perPage);
            $offset = max(0, ((int) $page - 1) * $limit);
            $limitSql = " LIMIT {$limit} OFFSET {$offset}";
        }

        $query = "SELECT t.*, u.username, u.email,
                         COUNT(DISTINCT b.student_id) AS student_count,
                         COALESCE(SUM(CASE WHEN b.status IN ('paid', 'approved') THEN b.amount ELSE 0 END), 0) AS total_revenue
                  FROM {$this->tableName} t
                  INNER JOIN users u ON t.user_id = u.id
                  LEFT JOIN bookings b ON b.tutor_id = t.id
                  WHERE " . implode(' AND ', $where) . "
                  GROUP BY t.id, u.username, u.email
                  ORDER BY t.id DESC{$limitSql}";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public function countTutors($search = '')
    {
        $params = [];
        $where = ["u.role = 'tutor'"];

        if ($search !== '') {
            $where[] = "(t.full_name LIKE :search OR t.subjects LIKE :search OR u.email LIKE :search OR u.username LIKE :search)";
            $params[':search'] = '%' . trim($search) . '%';
        }

        $query = "SELECT COUNT(*)
                  FROM {$this->tableName} t
                  INNER JOIN users u ON t.user_id = u.id
                  WHERE " . implode(' AND ', $where);
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function getSchedulesByTutorId($tutorId)
    {
        $query = "SELECT id, thu_trong_tuan, phien_hoc, hoc_vien, mon_hoc, trang_thai
                  FROM lich_hoc_hang_tuan
                  WHERE gia_su = :tutor_id
                  ORDER BY thu_trong_tuan ASC, phien_hoc ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':tutor_id' => (int) $tutorId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProminentTutors($limit = 12)
    {
        $limit = max(1, (int) $limit);

        $query = "SELECT t.*, u.username, u.email,
                         COUNT(DISTINCT b.student_id) AS student_count,
                         GROUP_CONCAT(DISTINCT CONCAT(l.thu_trong_tuan, '-', l.phien_hoc) ORDER BY l.thu_trong_tuan, l.phien_hoc) AS list_lich_co_dinh
                  FROM {$this->tableName} t
                  INNER JOIN users u ON t.user_id = u.id AND u.role = 'tutor'
                  LEFT JOIN bookings b ON b.tutor_id = t.id AND b.status IN ('paid', 'approved')
                  LEFT JOIN lich_hoc_hang_tuan l ON l.gia_su = t.id AND l.trang_thai = 1
                  GROUP BY t.id, u.username, u.email
                  ORDER BY student_count DESC, t.id DESC
                  LIMIT {$limit}";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
