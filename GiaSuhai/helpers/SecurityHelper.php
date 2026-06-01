<?php

class SecurityHelper
{
    public static function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function csrfToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    public static function csrfField()
    {
        return '<input type="hidden" name="_csrf_token" value="' . self::e(self::csrfToken()) . '">';
    }

    public static function verifyCsrf($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['_csrf_token'])
            && is_string($token)
            && hash_equals($_SESSION['_csrf_token'], $token);
    }

    public static function hardenSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
            ]);
            session_start();
        }
    }

    public static function regenerateSession()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function validateAvatarUpload($file)
    {
        if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'filename' => null, 'error' => null];
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'filename' => null, 'error' => 'Upload ảnh thất bại.'];
        }

        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            return ['ok' => false, 'filename' => null, 'error' => 'Ảnh đại diện tối đa 2MB.'];
        }

        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif'
        ];

        $mime = mime_content_type($file['tmp_name']);
        if (!isset($allowedMime[$mime])) {
            return ['ok' => false, 'filename' => null, 'error' => 'Định dạng ảnh không hợp lệ.'];
        }

        return [
            'ok' => true,
            'filename' => bin2hex(random_bytes(12)) . '.' . $allowedMime[$mime],
            'error' => null
        ];
    }
}
