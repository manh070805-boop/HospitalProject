<?php
$currentUrl = $_GET['url'] ?? 'doctor-portal';
$activeModule = trim($currentUrl, '/');
?>

<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <h2 class="sidebar-brand-title">
            <i class="iconsax" icon-name="heart-circle"></i>
            H-Doctor
        </h2>
        <p>Điều trị & y lệnh</p>
    </div>

    <div class="sidebar-section">
        <p class="sidebar-section-title">Lâm sàng</p>
        <ul class="sidebar-menu">
            <li
                class="<?= ($activeModule === 'doctor-portal' || $activeModule === 'doctor-portal/dashboard') ? 'active' : '' ?>">
                <a href="index.php?url=doctor-portal">
                    <i class="iconsax" icon-name="home-2"></i>
                    Tổng quan
                </a>
            </li>

            <li
                class="<?= str_contains($activeModule, 'doctor-portal/admissions') ? 'active' : '' ?>">
                <a href="index.php?url=doctor-portal/admissions">
                    <i class="iconsax" icon-name="user-2"></i>
                    Bệnh nhân của tôi
                </a>
            </li>

            <li class="<?= str_contains($activeModule, 'doctor-portal/treatment-plans') ? 'active' : '' ?>">
                <a href="index.php?url=doctor-portal/treatment-plans">
                    <i class="iconsax" icon-name="clipboard-text-1"></i>
                    Kế hoạch điều trị
                </a>
            </li>

            <li class="<?= str_contains($activeModule, 'doctor-portal/alerts') ? 'active' : '' ?>">
                <a href="index.php?url=doctor-portal/alerts">
                    <i class="iconsax" icon-name="warning-triangle"></i>
                    Cảnh báo sinh tồn
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <p class="sidebar-section-title">Phối hợp</p>
        <ul class="sidebar-menu">
            <li class="<?= str_contains($activeModule, 'doctor-portal/care-plans') ? 'active' : '' ?>">
                <a href="index.php?url=doctor-portal/care-plans">
                    <i class="iconsax" icon-name="note-text"></i>
                    Kế hoạch chăm sóc
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
