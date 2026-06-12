<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trung tâm gia sư Appco</title>

    <link rel="stylesheet" href="/GiaSu/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>
        // Suppress errors from external scripts (gads-scraper, etc)
        window.addEventListener('error', function(event) {
            if (event.filename && event.filename.includes('gads-scraper')) {
                event.preventDefault();
            }
        });
    </script>
</head>

<body>

    <header class="header-area">
        <div class="container header-container">
            <div class="logo">
                <a href="index.php">appco</a>
            </div>

            <nav class="main-menu">
                <ul>
                    <li><a href="index.php">Trang Chủ</a></li>
                    <li><a href="index.php?url=tutor">Gia Sư</a></li>
                    <li><a href="index.php?url=news">Tin Khác</a></li>
                    <li><a href="index.php?url=contact">Liên Hệ</a></li>

                    <?php 
                    // Lấy vai trò (role) an toàn từ Session nếu user đã đăng nhập
                    $userRole = $_SESSION['role'] ?? null; 
                    ?>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        
                        <?php if ($userRole === 'tutor'): ?>
                            <li><a href="index.php?url=news/create"><i class="fas fa-edit"></i> Viết Thông Báo</a></li>
                            <li><a href="index.php?url=schedule" style="color: #2e7d32; font-weight: bold;"><i class="fas fa-chalkboard-teacher"></i> Lịch dạy</a></li>
                        
                        <?php elseif ($userRole === 'student'): ?>
                            <li><a href="index.php?url=schedule" style="color: #2196F3; font-weight: bold;"><i class="fas fa-calendar-alt"></i> Lịch học</a></li>
                            <li><a href="index.php?url=payment/index" style="color: #e91e63; font-weight: bold;"><i class="fas fa-qrcode"></i> Thanh toán</a></li>
                        
                        <?php elseif ($userRole === 'admin'): ?>
                            <li><a href="?url=admin/news">Quản lý tin</a></li>
                            <li><a href="index.php?url=schedule"><i class="fas fa-calendar-check"></i> Lịch tổng hợp</a></li>
                        <?php endif; ?>

                    <?php else: ?>
                        <li><a href="index.php?url=schedule">Lịch học</a></li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="user-info">
                            <a href="#">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown">
                                <?php if ($userRole === 'admin'): ?>
                                    <li>
                                        <a href="?url=admin/dashboard" style="color: red; font-weight: bold;">
                                            <i class="fas fa-cog"></i> Trang Quản Trị
                                        </a>
                                    </li>
                                    <li>
                                        <a href="?url=admin/contacts">
                                            <i class="fas fa-envelope"></i> Quản lý liên hệ
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if ($userRole === 'tutor'): ?>
                                    <li>
                                        <a href="?url=tutor/list">
                                            <i class="fas fa-id-card"></i> Hồ sơ của tôi
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <li><a href="?url=auth/logout"><i class="fas fa-sign-out-alt"></i> Đăng Xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li><a href="?url=auth/login" class="login-link">Đăng Nhập</a></li>
                        <li><a href="?url=auth/register" class="btn-signup">Đăng Ký</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
