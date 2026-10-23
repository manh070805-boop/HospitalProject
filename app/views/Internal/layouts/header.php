<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal nội bộ — Hệ thống quản lý bệnh viện</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/iconsax.css">
</head>

<body>
    <div class="admin-wrapper">
        <header class="admin-header">
            <div class="header-left">
                <h1>Portal nội bộ</h1>
                <p>Hệ thống vận hành điều dưỡng</p>
            </div>
            <div class="header-user">
                <div class="header-user-meta">
                    <strong class="header-user-name"><?= htmlspecialchars($user['full_name'] ?? 'Nhân viên') ?></strong>
                    <span class="header-user-role">
                        <?= $user['role_key'] === 'head_nurse' ? 'Điều dưỡng trưởng' : 'Điều dưỡng'; ?>
                    </span>
                </div>
                <a href="index.php?url=auth/logout" class="btn btn-secondary">Đăng xuất</a>
            </div>
        </header>

        <div class="admin-body">
