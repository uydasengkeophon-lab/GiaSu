<?php

class Revenue
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->ensureBookingIndexes();
    }

    private function indexExists($table, $indexName)
    {
        $stmt = $this->conn->prepare("SHOW INDEX FROM {$table} WHERE Key_name = :index_name");
        $stmt->execute([':index_name' => $indexName]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function ensureBookingIndexes()
    {
        // Index phục vụ dashboard doanh thu và lọc booking theo thời gian.
        if (!$this->indexExists('bookings', 'idx_booking_revenue_filter')) {
            $this->conn->exec("ALTER TABLE bookings ADD INDEX idx_booking_revenue_filter (status, study_date, tutor_id, amount)");
        }
    }

    private function buildFilterWhere($filters, &$params)
    {
        $where = ["b.status IN ('paid', 'approved')"];

        if (!empty($filters['date_from'])) {
            $where[] = 'b.study_date >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'b.study_date <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['tutor_id'])) {
            $where[] = 'b.tutor_id = :tutor_id';
            $params[':tutor_id'] = (int) $filters['tutor_id'];
        }

        if (!empty($filters['subject'])) {
            $where[] = 't.subjects LIKE :subject';
            $params[':subject'] = '%' . $filters['subject'] . '%';
        }

        return 'WHERE ' . implode(' AND ', $where);
    }

    public function getSummary($filters = [])
    {
        $params = [];
        $whereSql = $this->buildFilterWhere($filters, $params);

        $sql = "SELECT 
                    COALESCE(SUM(b.amount), 0) AS total_revenue,
                    COUNT(DISTINCT b.id) AS total_bookings,
                    COUNT(DISTINCT b.tutor_id) AS tutor_with_revenue,
                    COUNT(DISTINCT b.student_id) AS student_with_revenue
                FROM bookings b
                LEFT JOIN tutors t ON b.tutor_id = t.id
                {$whereSql}";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSystemStats()
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM lich_hoc_hang_tuan WHERE trang_thai = 1) AS total_classes,
                    (SELECT COUNT(*) FROM users WHERE role = 'student') AS total_students,
                    (SELECT COUNT(*) FROM tutors) AS total_tutors,
                    (SELECT COUNT(*) FROM bookings) AS total_bookings";

        return $this->conn->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    public function getRevenueByPeriod($filters = [])
    {
        $params = [];
        $whereSql = $this->buildFilterWhere($filters, $params);

        $sql = "SELECT
                    COALESCE(SUM(CASE WHEN b.study_date = CURDATE() THEN b.amount ELSE 0 END), 0) AS today,
                    COALESCE(SUM(CASE WHEN YEARWEEK(b.study_date, 1) = YEARWEEK(CURDATE(), 1) THEN b.amount ELSE 0 END), 0) AS this_week,
                    COALESCE(SUM(CASE WHEN YEAR(b.study_date) = YEAR(CURDATE()) AND MONTH(b.study_date) = MONTH(CURDATE()) THEN b.amount ELSE 0 END), 0) AS this_month,
                    COALESCE(SUM(CASE WHEN YEAR(b.study_date) = YEAR(CURDATE()) THEN b.amount ELSE 0 END), 0) AS this_year
                FROM bookings b
                LEFT JOIN tutors t ON b.tutor_id = t.id
                {$whereSql}";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMonthlyRevenue($filters = [])
    {
        $params = [];
        $whereSql = $this->buildFilterWhere($filters, $params);

        $sql = "SELECT DATE_FORMAT(b.study_date, '%Y-%m') AS period_label,
                       COALESCE(SUM(b.amount), 0) AS revenue
                FROM bookings b
                LEFT JOIN tutors t ON b.tutor_id = t.id
                {$whereSql}
                GROUP BY DATE_FORMAT(b.study_date, '%Y-%m')
                ORDER BY period_label ASC
                LIMIT 12";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopTutorsByRevenue($filters = [], $limit = 5)
    {
        $params = [];
        $whereSql = $this->buildFilterWhere($filters, $params);
        $limit = max(1, (int) $limit);

        $sql = "SELECT t.id, COALESCE(t.full_name, u.username, 'Gia sư') AS tutor_name,
                       COALESCE(SUM(b.amount), 0) AS revenue
                FROM bookings b
                LEFT JOIN tutors t ON b.tutor_id = t.id
                LEFT JOIN users u ON t.user_id = u.id
                {$whereSql}
                GROUP BY t.id, tutor_name
                ORDER BY revenue DESC
                LIMIT {$limit}";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopTutorsByStudents($filters = [], $limit = 5)
    {
        $params = [];
        $whereSql = $this->buildFilterWhere($filters, $params);
        $limit = max(1, (int) $limit);

        $sql = "SELECT t.id, COALESCE(t.full_name, u.username, 'Gia sư') AS tutor_name,
                       COUNT(DISTINCT b.student_id) AS student_count
                FROM bookings b
                LEFT JOIN tutors t ON b.tutor_id = t.id
                LEFT JOIN users u ON t.user_id = u.id
                {$whereSql}
                GROUP BY t.id, tutor_name
                ORDER BY student_count DESC
                LIMIT {$limit}";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPopularSubjects($filters = [], $limit = 6)
    {
        $params = [];
        $whereSql = $this->buildFilterWhere($filters, $params);
        $limit = max(1, (int) $limit);

        $sql = "SELECT COALESCE(NULLIF(TRIM(t.subjects), ''), 'Chưa phân môn') AS subject_name,
                       COUNT(b.id) AS booking_count
                FROM bookings b
                LEFT JOIN tutors t ON b.tutor_id = t.id
                {$whereSql}
                GROUP BY subject_name
                ORDER BY booking_count DESC
                LIMIT {$limit}";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
