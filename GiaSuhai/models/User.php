<?php

class User
{
    private $conn;
    private $table = 'users';

    public $id;
    public $username;
    public $password;
    public $email;
    public $role;
    public $full_name;
    public $subjects;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function normalizeEmail($email)
    {
        return strtolower(trim((string) $email));
    }

    private function normalizeUsername($username)
    {
        return trim((string) $username);
    }

    private function isValidRole($role)
    {
        return in_array($role, ['student', 'tutor'], true);
    }

    public function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT id FROM {$this->table} WHERE email = :email";
        $params = [':email' => $this->normalizeEmail($email)];

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = (int) $excludeId;
        }

        $sql .= " LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function usernameExists($username, $excludeId = null)
    {
        $sql = "SELECT id FROM {$this->table} WHERE username = :username";
        $params = [':username' => $this->normalizeUsername($username)];

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = (int) $excludeId;
        }

        $sql .= " LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function register()
    {
        $username = $this->normalizeUsername($this->username);
        $email = $this->normalizeEmail($this->email);
        $password = (string) $this->password;
        $role = (string) $this->role;
        $fullName = trim((string) $this->full_name);
        $subjects = trim((string) ($this->subjects ?? ''));

        // Validate đầu vào tại model để controller nào gọi cũng được bảo vệ.
        if ($username === '' || $fullName === '' || strlen($password) < 6) {
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$this->isValidRole($role)) {
            return false;
        }

        if ($this->emailExists($email) || $this->usernameExists($username)) {
            return false;
        }

        try {
            $this->conn->beginTransaction();

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO {$this->table} (username, password, email, role)
                      VALUES (:username, :password, :email, :role)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':username' => $username,
                ':password' => $passwordHash,
                ':email' => $email,
                ':role' => $role
            ]);

            $userId = (int) $this->conn->lastInsertId();

            if ($role === 'student') {
                $profileSql = "INSERT INTO students (user_id, full_name) VALUES (:user_id, :full_name)";
                $profileStmt = $this->conn->prepare($profileSql);
                $profileStmt->execute([
                    ':user_id' => $userId,
                    ':full_name' => $fullName
                ]);
            }

            if ($role === 'tutor') {
                $profileSql = "INSERT INTO tutors (user_id, full_name, subjects) VALUES (:user_id, :full_name, :subjects)";
                $profileStmt = $this->conn->prepare($profileSql);
                $profileStmt->execute([
                    ':user_id' => $userId,
                    ':full_name' => $fullName,
                    ':subjects' => $subjects !== '' ? $subjects : null
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    public function login()
    {
        $query = "SELECT id, username, password, role FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':email' => $this->normalizeEmail($this->email)]);
        return $stmt;
    }

    public function authenticate($email, $password)
    {
        $this->email = $email;
        $stmt = $this->login();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        $storedPassword = (string) $user['password'];
        $isValid = password_verify((string) $password, $storedPassword);

        // Hỗ trợ tài khoản cũ đang lưu plaintext, sau khi đăng nhập sẽ tự nâng cấp hash.
        if (!$isValid && hash_equals($storedPassword, (string) $password)) {
            $isValid = true;
            $this->updatePasswordHash((int) $user['id'], (string) $password);
        }

        if (!$isValid) {
            return false;
        }

        unset($user['password']);
        return $user;
    }

    public function updatePasswordHash($id, $plainPassword)
    {
        $sql = "UPDATE {$this->table} SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => (int) $id,
            ':password' => password_hash($plainPassword, PASSWORD_DEFAULT)
        ]);
    }

    public function getUserById($id)
    {
        $query = "SELECT id, username, email, role, created_at FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => (int) $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateRole($id, $role)
    {
        if (!in_array($role, ['tutor', 'student'], true)) {
            return false;
        }

        $query = "UPDATE {$this->table} SET role = :role WHERE id = :id AND role != 'admin'";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':role' => $role,
            ':id' => (int) $id
        ]);
    }

    public function getAllUsers($search = '', $role = '', $page = 1, $perPage = null)
    {
        $conditions = ["u.role != 'admin'"];
        $params = [];

        if ($search !== '') {
            $conditions[] = "(
                u.username LIKE :search
                OR u.email LIKE :search
                OR CAST(u.id AS CHAR) = :search_exact
                OR s.full_name LIKE :search
                OR t.full_name LIKE :search
            )";
            $params[':search'] = '%' . trim($search) . '%';
            $params[':search_exact'] = trim($search);
        }

        if ($role === 'tutor' || $role === 'student') {
            $conditions[] = "u.role = :role";
            $params[':role'] = $role;
        }

        $limitSql = '';
        if ($perPage !== null) {
            $limit = max(1, (int) $perPage);
            $offset = max(0, ((int) $page - 1) * $limit);
            $limitSql = " LIMIT {$limit} OFFSET {$offset}";
        }

        $query = "SELECT u.id, u.username, u.email, u.role, u.created_at,
                         COALESCE(t.full_name, s.full_name, u.username) AS full_name
                  FROM {$this->table} u
                  LEFT JOIN tutors t ON t.user_id = u.id
                  LEFT JOIN students s ON s.user_id = u.id
                  WHERE " . implode(' AND ', $conditions) . "
                  ORDER BY u.id DESC{$limitSql}";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt;
    }

    public function countUsers($search = '', $role = '')
    {
        $conditions = ["u.role != 'admin'"];
        $params = [];

        if ($search !== '') {
            $conditions[] = "(u.username LIKE :search OR u.email LIKE :search OR CAST(u.id AS CHAR) = :search_exact)";
            $params[':search'] = '%' . trim($search) . '%';
            $params[':search_exact'] = trim($search);
        }

        if ($role === 'tutor' || $role === 'student') {
            $conditions[] = "u.role = :role";
            $params[':role'] = $role;
        }

        $query = "SELECT COUNT(*) FROM {$this->table} u WHERE " . implode(' AND ', $conditions);
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id AND role != 'admin'";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => (int) $id]);
    }
}
