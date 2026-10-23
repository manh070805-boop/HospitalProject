<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Dashboard quản trị</h2>
            <p>Tổng quan hệ thống và các chỉ số vận hành quan trọng</p>
        </div>
    </div>

    <div class="card dashboard-hero reveal-item delay-1">
        <div class="dashboard-hero-content">
            <h3 class="dashboard-hero-title">Chào mừng trở lại, Admin!
            </h3>
            <p class="dashboard-hero-text">Hệ thống đang hoạt động ổn định
                với hiệu năng tối ưu. Theo dõi các chỉ số bên dưới để nắm bắt tình hình.</p>
        </div>
        <i class="iconsax dashboard-hero-icon" icon-name="chart-square"></i>
    </div>

    <div class="stats-grid">
        <div class="stat-card stat-primary reveal-item delay-2">
            <div class="stat-head">
                <div>
                    <h3>Tổng người dùng</h3>
                    <p><?= $totalUsers; ?></p>
                </div>
                <div class="stat-icon-box is-primary">
                    <i class="iconsax" icon-name="users"></i>
                </div>
            </div>
            <div class="stat-watermark">U</div>
        </div>

        <div class="stat-card stat-success reveal-item delay-3">
            <div class="stat-head">
                <div>
                    <h3>Người dùng hoạt động</h3>
                    <p><?= $activeUsers; ?></p>
                </div>
                <div class="stat-icon-box is-success">
                    <i class="iconsax" icon-name="user-2-tick"></i>
                </div>
            </div>
            <div class="stat-watermark">A</div>
        </div>

        <div class="stat-card stat-warning reveal-item delay-4">
            <div class="stat-head">
                <div>
                    <h3>Không hoạt động</h3>
                    <p><?= $inactiveUsers; ?></p>
                </div>
                <div class="stat-icon-box is-warning">
                    <i class="iconsax" icon-name="user-2-minus"></i>
                </div>
            </div>
            <div class="stat-watermark">I</div>
        </div>

        <div class="stat-card stat-danger reveal-item delay-5">
            <div class="stat-head">
                <div>
                    <h3>Người dùng bị khóa</h3>
                    <p><?= $lockedUsers; ?></p>
                </div>
                <div class="stat-icon-box is-danger">
                    <i class="iconsax" icon-name="user-2-tag"></i>
                </div>
            </div>
            <div class="stat-watermark">L</div>
        </div>

        <div class="stat-card stat-purple reveal-item delay-6">
            <div class="stat-head">
                <div>
                    <h3>Tổng vai trò</h3>
                    <p><?= $totalRoles; ?></p>
                </div>
                <div class="stat-icon-box is-purple">
                    <i class="iconsax" icon-name="users"></i>
                </div>
            </div>
            <div class="stat-watermark">R</div>
        </div>

        <div class="stat-card stat-teal reveal-item delay-6">
            <div class="stat-head">
                <div>
                    <h3>Khoa/Phòng ban</h3>
                    <p><?= $totalDepartments; ?></p>
                </div>
                <div class="stat-icon-box is-teal">
                    <i class="iconsax" icon-name="hospital"></i>
                </div>
            </div>
            <div class="stat-watermark">D</div>
        </div>
    </div>

    <div class="dashboard-section reveal-item">
        <h3 class="dashboard-section-title">
            <i class="iconsax" icon-name="user-2-add"></i>
            Người dùng mới nhất
        </h3>
        <p class="dashboard-section-text">8 thành viên vừa tham gia hệ thống trong thời gian gần đây
        </p>
    </div>

    <div class="card dashboard-table-card reveal-item">
        <table class="table premium-table">
            <thead>
                <tr>
                    <th class="table-th-id">ID</th>
                    <th>Họ và tên</th>
                    <th>Tên đăng nhập</th>
                    <th>Vai trò / Chức vụ</th>
                    <th>Khoa</th>
                    <th>Trạng thái</th>
                    <th>Ngày tham gia</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentUsers)): ?>
                    <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td class="col-id">#<?= $user['user_id']; ?></td>
                            <td>
                                <div class="cell-strong">
                                    <?= htmlspecialchars($user['full_name']); ?>
                                </div>
                            </td>
                            <td class="dashboard-username"><?= htmlspecialchars($user['username']); ?>
                            </td>
                            <td>
                                <span class="role-badge">
                                    <?= htmlspecialchars($user['role_name']); ?>
                                </span>
                            </td>
                            <td class="table-text-muted"><?= htmlspecialchars($user['department_name']); ?></td>
                            <td>
                                <?php if ($user['status'] == 'active'): ?>
                                    <span class="badge badge-active"><span class="status-dot dot-active"></span>Hoạt động</span>
                                <?php elseif ($user['status'] == 'inactive'): ?>
                                    <span class="badge badge-inactive"><span class="status-dot dot-inactive"></span>Ngoại
                                        tuyến</span>
                                <?php elseif ($user['status'] == 'locked'): ?>
                                    <span class="badge badge-locked"><span class="status-dot dot-locked"></span>Bị khóa</span>
                                <?php endif; ?>
                            </td>
                            <td class="dashboard-date">
                                <?= date('d/m/Y', strtotime($user['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="iconsax empty-state-icon" icon-name="box"></i>
                            <p>Không có dữ liệu người dùng mới</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
