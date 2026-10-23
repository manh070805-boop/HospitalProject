<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<?php
$action = strtoupper($log['action'] ?? '');
$actionConfig = [
    'CREATE' => ['class' => 'is-create', 'icon' => 'add-circle', 'label' => 'Tạo mới'],
    'UPDATE' => ['class' => 'is-update', 'icon' => 'edit-2', 'label' => 'Cập nhật'],
    'DELETE' => ['class' => 'is-delete', 'icon' => 'trash', 'label' => 'Xóa'],
    'LOGIN' => ['class' => 'is-login', 'icon' => 'login', 'label' => 'Đăng nhập'],
    'LOGOUT' => ['class' => 'is-logout', 'icon' => 'logout-1', 'label' => 'Đăng xuất'],
];
$cfg = $actionConfig[$action] ?? ['class' => 'is-default', 'icon' => 'document-text-1', 'label' => $action];
?>

<div class="theme-logs">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Chi tiết nhật ký</h2>
            <p>Xem thông tin chi tiết bản ghi hoạt động <strong class="page-header-highlight">#<?= $log['log_id'] ?></strong>
            </p>
        </div>
        <a href="index.php?url=system-logs" class="btn btn-secondary reveal-item delay-1">
            <i class="iconsax" icon-name="arrow-left"></i>
            Quay lại danh sách
        </a>
    </div>

    <div class="logs-detail-grid reveal-item delay-2">

        <div class="card logs-detail-card logs-panel <?= $cfg['class'] ?>">

            <div class="logs-card-bar"></div>

            <div class="logs-card-body-lg">

                <div class="logs-detail-header">
                    <div class="logs-detail-meta">
                        <div class="logs-detail-icon <?= $cfg['class'] ?>">
                            <i class="iconsax" icon-name="<?= $cfg['icon'] ?>"></i>
                        </div>
                        <div>
                            <h3 class="logs-detail-title">Bản ghi
                                hoạt động</h3>
                            <p class="logs-detail-subtitle">Mã nhật ký:
                                #<?= $log['log_id'] ?></p>
                        </div>
                    </div>
                    <span class="logs-action-badge <?= $cfg['class'] ?>">
                        <i class="iconsax" icon-name="<?= $cfg['icon'] ?>"></i>
                        <?= $cfg['label'] ?>
                    </span>
                </div>

                <div class="logs-summary-grid">

                    <div class="logs-summary-box">
                        <div class="logs-summary-label">
                            <i class="iconsax" icon-name="user-2"></i>
                            <span>Người
                                thực hiện</span>
                        </div>
                        <p class="logs-summary-value">
                            <?= !empty($log['full_name']) ? htmlspecialchars($log['full_name']) : 'Hệ thống' ?>
                        </p>
                        <?php if (!empty($log['username'])): ?>
                            <p class="logs-summary-meta">
                                @<?= htmlspecialchars($log['username']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="logs-summary-box">
                        <div class="logs-summary-label">
                            <i class="iconsax" icon-name="clock"></i>
                            <span>Thời
                                gian</span>
                        </div>
                        <p class="logs-summary-value">
                            <?php
                            if (!empty($log['created_at'])) {
                                $dt = new DateTime($log['created_at']);
                                echo $dt->format('d/m/Y H:i:s');
                            } else {
                                echo '---';
                            }
                            ?>
                        </p>
                        <?php if (!empty($log['created_at'])): ?>
                            <p class="logs-summary-meta">
                                <?php
                                $now = new DateTime();
                                $diff = $now->diff($dt);
                                if ($diff->days == 0)
                                    echo 'Hôm nay';
                                elseif ($diff->days == 1)
                                    echo 'Hôm qua';
                                else
                                    echo $diff->days . ' ngày trước';
                                ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="logs-summary-box">
                        <div class="logs-summary-label">
                            <i class="iconsax" icon-name="flash-1"></i>
                            <span>Loại
                                hành động</span>
                        </div>
                        <p class="logs-summary-value logs-accent-value <?= $cfg['class'] ?>">
                            <?= $cfg['label'] ?> (<?= $action ?>)
                        </p>
                    </div>
                </div>

                <div class="logs-description-panel">
                    <div class="logs-panel-title-accent">
                        <i class="iconsax" icon-name="document-text-1"></i>
                        <span>Mô tả chi
                            tiết</span>
                    </div>
                    <p class="logs-description-text">
                        <?= nl2br(htmlspecialchars($log['description'] ?? 'Không có mô tả')) ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="card logs-sidebar-card">
            <div class="logs-card-bar-sm"></div>
            <div class="logs-card-body-sm">
                <h4 class="logs-sidebar-title">
                    <i class="iconsax" icon-name="clock"></i>
                    Hoạt động gần đây
                </h4>

                <div class="logs-sidebar-list">
                    <?php if (!empty($recentLogs)): ?>
                        <?php foreach ($recentLogs as $recent): ?>
                            <?php
                            $rAction = strtoupper($recent['action'] ?? '');
                            $rCfg = $actionConfig[$rAction] ?? ['class' => 'is-default', 'icon' => 'document-text-1', 'label' => $rAction];
                            $isActive = ($recent['log_id'] == $log['log_id']);
                            ?>
                            <a href="index.php?url=system-logs/show&id=<?= $recent['log_id'] ?>"
                                class="logs-sidebar-item <?= $isActive ? 'is-active' : '' ?>">
                                <div class="logs-sidebar-icon <?= $rCfg['class'] ?>">
                                    <i class="iconsax" icon-name="<?= $rCfg['icon'] ?>"></i>
                                </div>
                                <div class="logs-sidebar-content">
                                    <p class="logs-sidebar-item-title">
                                        <?= htmlspecialchars(mb_substr($recent['description'] ?? '', 0, 35)) ?>        <?= mb_strlen($recent['description'] ?? '') > 35 ? '...' : '' ?>
                                    </p>
                                    <p class="logs-sidebar-item-time">
                                        <?php
                                        if (!empty($recent['created_at'])) {
                                            $rDt = new DateTime($recent['created_at']);
                                            echo $rDt->format('d/m H:i');
                                        }
                                        ?>
                                    </p>
                                </div>
                                <?php if ($isActive): ?>
                                    <div class="logs-sidebar-current"></div>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <a href="index.php?url=system-logs" class="logs-sidebar-link">
                    <i class="iconsax" icon-name="document-text-1"></i>
                    Xem tất cả nhật ký
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
