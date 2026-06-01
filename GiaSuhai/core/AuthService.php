<?php

require_once 'models/User.php';
require_once 'helpers/SecurityHelper.php';

class AuthService
{
    private $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function login($email, $password)
    {
        $user = $this->userModel->authenticate($email, $password);
        if (!$user) {
            return false;
        }

        SecurityHelper::regenerateSession();
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        return true;
    }

    public function logout()
    {
        $_SESSION = [];
        session_destroy();
    }
}
