<?php


// 🔥 Start session (chỉ chạy 1 lần)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


// 🔥 Lấy URL (mặc định về home/index)
$url = isset($_GET['url']) ? $_GET['url'] : 'home/index';

// 🔥 Tách URL
$urlParts = explode('/', $url);

// 🔥 Controller
$controllerName = ucfirst($urlParts[0]) . 'Controller';

// 🔥 Action
$actionName = isset($urlParts[1]) ? $urlParts[1] : 'index';

// 🔥 Đường dẫn controller
$controllerPath = "controllers/" . $controllerName . ".php";

// 🔥 Kiểm tra file controller
if (file_exists($controllerPath)) {

    require_once $controllerPath;

    // 🔥 Kiểm tra class tồn tại
    if (class_exists($controllerName)) {

        $controller = new $controllerName();

        // 🔥 Kiểm tra method (action)
        if (method_exists($controller, $actionName)) {

            $controller->$actionName();

        } else {
            echo "<h3 style='color:red'>404 - Action '$actionName' không tồn tại!</h3>";
        }

    } else {
        echo "<h3 style='color:red'>404 - Controller '$controllerName' không tồn tại!</h3>";
    }

} else {
    echo "<h3 style='color:red'>404 - File '$controllerPath' không tồn tại!</h3>";
}