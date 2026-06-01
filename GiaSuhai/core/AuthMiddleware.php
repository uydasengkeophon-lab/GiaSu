<?php

class AuthMiddleware
{
    public static function requireLogin()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ?url=auth/login');
            exit;
        }
    }

    public static function requireRole($roles)
    {
        self::requireLogin();

        $allowedRoles = (array) $roles;
        if (!in_array($_SESSION['role'] ?? '', $allowedRoles, true)) {
            http_response_code(403);
            exit('Bạn không có quyền truy cập chức năng này.');
        }
    }
}
