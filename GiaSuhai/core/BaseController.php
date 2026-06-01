<?php

require_once 'helpers/SecurityHelper.php';

abstract class BaseController
{
    protected function view($view, $data = [], $layout = 'layouts/header', $footer = 'layouts/footer')
    {
        extract($data);
        require_once 'views/' . $layout . '.php';
        require_once 'views/' . $view . '.php';
        require_once 'views/' . $footer . '.php';
    }

    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    protected function requirePost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php');
        }
    }

    protected function requireCsrf()
    {
        if (!SecurityHelper::verifyCsrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            exit('CSRF token không hợp lệ.');
        }
    }
}
