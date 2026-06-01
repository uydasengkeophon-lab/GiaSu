<?php
$currentUrl = $_GET['url'] ?? 'admin/dashboard';
$isActive = function ($prefixes) use ($currentUrl) {
   foreach ($prefixes as $prefix) {
      if (strpos($currentUrl, $prefix) === 0) {
         return true;
      }
   }
   return false;
};
?>
<!DOCTYPE html>
<html lang="vi">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Quản trị hệ thống</title>

   <link rel="stylesheet" href="/GiaSu/assets/css/style.css">
   <link rel="stylesheet" href="/GiaSu/assets/css/admin.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="admin-body">
   <div class="admin-shell">
      <aside class="admin-sidebar" id="adminSidebar">
         <div class="admin-brand">
            <a href="?url=admin/dashboard">APPCO ADMIN</a>
         </div>

         <nav class="admin-menu">
            <a href="?url=admin/dashboard" class="<?php echo $isActive(['admin/dashboard']) ? 'active' : ''; ?>">
               <i class="fas fa-chart-line"></i> Tổng quan
            </a>
            <a href="?url=admin/schedules" class="<?php echo $isActive(['admin/schedules']) ? 'active' : ''; ?>">
               <i class="fas fa-calendar-alt"></i> Quản lý lịch học
            </a>
            <a href="?url=admin/revenue" class="<?php echo $isActive(['admin/revenue']) ? 'active' : ''; ?>">
               <i class="fas fa-coins"></i> Doanh thu
            </a>
            <a href="?url=admin/bookings" class="<?php echo $isActive(['admin/bookings']) ? 'active' : ''; ?>">
               <i class="fas fa-clipboard-check"></i> Duyệt đăng ký
            </a>
            <a href="?url=admin/news" class="<?php echo $isActive(['admin/news', 'admin/editNews']) ? 'active' : ''; ?>">
               <i class="fas fa-newspaper"></i> Quản lý tin tức
            </a>
            <a href="?url=admin/contacts" class="<?php echo $isActive(['admin/contacts']) ? 'active' : ''; ?>">
               <i class="fas fa-envelope"></i> Quản lý liên hệ
            </a>
            <a href="index.php">
               <i class="fas fa-home"></i> Về trang người dùng
            </a>
            <a href="?url=auth/logout">
               <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </a>
         </nav>
      </aside>

      <div class="admin-main">
         <header class="admin-topbar">
            <button type="button" id="adminSidebarToggle" class="admin-sidebar-toggle" aria-label="Mở menu quản trị">
               <i class="fas fa-bars"></i>
            </button>
            <h1>Bảng điều khiển quản trị viên</h1>
            <div class="admin-topbar-user">
               <button type="button" id="adminThemeToggle" class="admin-theme-toggle" aria-label="Đổi giao diện sáng tối">
                  <i class="fas fa-moon"></i>
               </button>
               <i class="fas fa-user-shield"></i>
               <?php echo htmlspecialchars($_SESSION['username'] ?? 'admin'); ?>
            </div>
         </header>

         <main class="admin-content">
