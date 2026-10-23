<?php
$currentUrl = $_GET['url'] ?? 'internal';
$activeModule = trim($currentUrl, '/');
$userRole = PortalAccess::user()['role_key'] ?? 'nurse';
?>

<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <h2 class="sidebar-brand-title">
            <i class="iconsax" icon-name="clipboard-tick"></i>
            H-Internal
        </h2>
        <p>Điều dưỡng vận hành</p>
    </div>

    <div class="sidebar-section">
        <p class="sidebar-section-title">Quản lý bệnh nhân</p>
        <ul class="sidebar-menu">
            <li class="<?= ($activeModule === 'internal' || $activeModule === 'internal/dashboard') ? 'active' : '' ?>">
                <a href="index.php?url=internal">
                    <i class="iconsax" icon-name="home-2"></i>
                    Tổng quan
                </a>
            </li>
            <li class="<?= (strpos($activeModule, 'internal/patients') !== false) ? 'active' : '' ?>">
                <a href="index.php?url=internal/patients/search">
                    <i class="iconsax" icon-name="user-2-add"></i>
                    Tiếp nhận bệnh nhân
                </a>
            </li>
            <li class="<?= (strpos($activeModule, 'internal/monitoring') !== false || strpos($activeModule, 'internal/admissions') !== false) ? 'active' : '' ?>">
                <a href="index.php?url=internal/monitoring">
                    <i class="iconsax" icon-name="hospital"></i>
                    Bệnh nhân nội trú
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <p class="sidebar-section-title">Chăm sóc lâm sàng</p>
        <ul class="sidebar-menu">
            <li class="<?= (strpos($activeModule, 'internal/vitals') !== false) ? 'active' : '' ?>">
                <a href="index.php?url=internal/vitals">
                    <i class="iconsax" icon-name="heart"></i>
                    Ghi sinh tồn
                </a>
            </li>

            <li class="<?= (strpos($activeModule, 'internal/care-plans') !== false) ? 'active' : '' ?>">
                <a href="index.php?url=internal/care-plans">
                    <i class="iconsax" icon-name="note-text"></i>
                    Kế hoạch chăm sóc
                </a>
            </li>

            <li class="<?= (strpos($activeModule, 'internal/alerts') !== false) ? 'active' : '' ?>">
                <a href="index.php?url=internal/alerts">
                    <i class="iconsax" icon-name="info-circle"></i>
                    Cảnh báo
                </a>
            </li>

            <li class="<?= (strpos($activeModule, 'internal/tasks') !== false) ? 'active' : '' ?>">
                <a href="index.php?url=internal/tasks">
                    <i class="iconsax" icon-name="clipboard-tick"></i>
                    Task theo ca
                </a>
            </li>
        </ul>
    </div>

    <?php if ($userRole === 'head_nurse'): ?>
        <div class="sidebar-section">
            <p class="sidebar-section-title">Điều dưỡng trưởng</p>
            <ul class="sidebar-menu">
                <li class="<?= (strpos($activeModule, 'internal/approvals') !== false) ? 'active' : '' ?>">
                    <a href="index.php?url=internal/approvals">
                        <i class="iconsax" icon-name="shield-tick"></i>
                        Phê duyệt kế hoạch
                    </a>
                </li>
            </ul>
        </div>
    <?php endif; ?>

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
