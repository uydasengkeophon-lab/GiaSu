<?php

// index.php
require_once 'helpers/SecurityHelper.php';
SecurityHelper::hardenSession();

// Autoload đơn giản (hoặc require thủ công các file chính)
require_once 'config/database.php';

// Xử lý Routing
require_once 'routes/web.php';
?>

