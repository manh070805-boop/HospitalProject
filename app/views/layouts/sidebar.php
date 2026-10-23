<?php
$currentUrl = $_GET['url'] ?? 'dashboard';
$currentModule = explode('/', trim($currentUrl, '/'))[0];
?>

<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <h2 class="sidebar-brand-title">
            <i class="iconsax" icon-name="hospital"></i>
            H-Admin
        </h2>
        <p>Bệnh viện Đa Khoa</p>
    </div>

    <div class="sidebar-section">
        <p class="sidebar-section-title">Tổng quan</p>
        <ul class="sidebar-menu">
            <li class="<?php echo ($currentModule === 'dashboard') ? 'active' : ''; ?>">
                <a href="index.php?url=dashboard">
                    <i class="iconsax" icon-name="grid-apps"></i>
                    Dashboard
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <p class="sidebar-section-title">Quản trị hệ thống</p>
        <ul class="sidebar-menu">
            <li class="<?php echo ($currentModule === 'users') ? 'active' : ''; ?>">
                <a href="index.php?url=users">
                    <i class="iconsax" icon-name="user-2"></i>
                    Người dùng
                </a>
            </li>
            <li class="<?php echo ($currentModule === 'departments') ? 'active' : ''; ?>">
                <a href="index.php?url=departments">
                    <i class="iconsax" icon-name="house-2"></i>
                    Khoa phòng
                </a>
            </li>
            <li class="<?php echo ($currentModule === 'roles') ? 'active' : ''; ?>">
                <a href="index.php?url=roles">
                    <i class="iconsax" icon-name="briefcase"></i>
                    Vai trò
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <p class="sidebar-section-title">Quản lý bệnh viện</p>
        <ul class="sidebar-menu">
            <li class="<?php echo ($currentModule === 'rooms') ? 'active' : ''; ?>">
                <a href="index.php?url=rooms">
                    <i class="iconsax" icon-name="house-2"></i>
                    Phòng bệnh
                </a>
            </li>
            <li class="<?php echo ($currentModule === 'patients') ? 'active' : ''; ?>">
                <a href="index.php?url=patients">
                    <i class="iconsax" icon-name="user-2"></i>
                    Bệnh nhân
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <p class="sidebar-section-title">Giám sát</p>
        <ul class="sidebar-menu">
            <li class="<?php echo ($currentModule === 'system-logs') ? 'active' : ''; ?>">
                <a href="index.php?url=system-logs">
                    <i class="iconsax" icon-name="note-text"></i>
                    Nhật ký hệ thống
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <p class="sidebar-section-title">Tài khoản</p>
        <ul class="sidebar-menu">
            <li>
                <a href="index.php?url=auth/logout">
                    <i class="iconsax" icon-name="logout-1"></i>
                    Đăng xuất
                </a>
            </li>
        </ul>
    </div>
</aside>

<main class="admin-content">
