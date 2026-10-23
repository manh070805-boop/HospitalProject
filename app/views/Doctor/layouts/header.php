<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Portal — Hệ thống quản lý bệnh viện</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/iconsax.css">
</head>

<body>
    <div class="admin-wrapper">
        <header class="admin-header">
            <div class="header-left">
                <h1>Doctor Portal</h1>
                <p>Không gian làm việc lâm sàng của bác sĩ</p>
            </div>

            <div class="header-user">
                <div class="header-user-meta">
                    <strong class="header-user-name"><?= htmlspecialchars($user['full_name'] ?? 'Bác sĩ') ?></strong>
                    <span class="header-user-role">
                        <?= htmlspecialchars($user['department_name'] ?? 'Chưa rõ khoa') ?>
                    </span>
                </div>
                <a href="index.php?url=auth/logout" class="btn btn-secondary">
                    Đăng xuất
                </a>
            </div>
        </header>

        <div class="admin-body">
