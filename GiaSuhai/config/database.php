<?php

class Database
{

    private $host = "localhost";
    private $db_name = "giasu";
    private $username = "root";
    private $password = "1234";

    public function connect()
    {

        $host = getenv('DB_HOST') ?: $this->host;
        $dbName = getenv('DB_NAME') ?: $this->db_name;
        $username = getenv('DB_USER') ?: $this->username;

        $envPassword = getenv('DB_PASS');
        $password = ($envPassword !== false && $envPassword !== '') ? $envPassword : $this->password;

        $conn = new PDO(
            "mysql:host=" . $host . ";dbname=" . $dbName . ";charset=utf8mb4",
            $username,
            $password
        );

        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->ensureBookingStatusEnum($conn, $dbName);
        $this->ensureBookingColumns($conn, $dbName);

        return $conn;
    }

    private function ensureBookingStatusEnum(PDO $conn, string $dbName)
    {
        try {
            $sql = "SELECT COLUMN_TYPE FROM information_schema.COLUMNS \
                    WHERE TABLE_SCHEMA = :schema \
                    AND TABLE_NAME = 'bookings' \
                    AND COLUMN_NAME = 'status'";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':schema' => $dbName]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || empty($row['COLUMN_TYPE'])) {
                return;
            }

            if (preg_match('/^enum\((.*)\)$/i', $row['COLUMN_TYPE'], $matches)) {
                $existing = array_map(function ($value) {
                    return trim($value, "'\"");
                }, explode(',', $matches[1]));

                $required = ['pending', 'paid', 'approved', 'rejected', 'cancelled'];
                $missing = array_diff($required, $existing);

                if (!empty($missing)) {
                    $newValues = array_unique(array_merge($existing, $required));
                    $enumList = implode(',', array_map(function ($value) {
                        return "'" . str_replace("'", "\\'", $value) . "'";
                    }, $newValues));

                    $alterSql = "ALTER TABLE bookings MODIFY COLUMN status ENUM($enumList) DEFAULT NULL";
                    $conn->exec($alterSql);
                }
            }
        } catch (PDOException $e) {
            // Không cần dừng ứng dụng nếu enum không tồn tại hoặc query thất bại.
        }
    }

    private function ensureBookingColumns(PDO $conn, string $dbName)
    {
        try {
            $sql = "SELECT COLUMN_NAME FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = :schema
                    AND TABLE_NAME = 'bookings'
                    AND COLUMN_NAME = 'schedule_id'";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':schema' => $dbName]);

            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $conn->exec("ALTER TABLE bookings ADD COLUMN schedule_id INT NULL AFTER student_id");
                $conn->exec("ALTER TABLE bookings ADD INDEX idx_bookings_schedule_id (schedule_id)");
            }
        } catch (PDOException $e) {
            // Không dừng ứng dụng nếu tài khoản DB không có quyền ALTER.
        }
    }

    public function getConnection()
    {
        return $this->connect();
    }
}

