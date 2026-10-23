<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="theme-logs">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Thống kê hoạt động</h2>
            <p>Báo cáo tổng quan về các hoạt động trong hệ thống</p>
        </div>
        <a href="index.php?url=system-logs" class="btn btn-secondary reveal-item delay-1">
            <i class="iconsax" icon-name="arrow-left"></i>
            Quay lại danh sách
        </a>
    </div>

    <div class="stats-grid stats-grid-compact stats-grid-three reveal-item delay-1">
        <div class="stat-card metric-card-border-purple">
            <div class="metric-row">
                <div>
                    <h3>Tổng hoạt động</h3>
                    <p class="metric-value-xl metric-value-purple"><?= $totalAll ?></p>
                </div>
                <div class="metric-icon-box metric-icon-box-lg is-purple">
                    <i class="iconsax" icon-name="chart-square"></i>
                </div>
            </div>
        </div>
        <div class="stat-card metric-card-border-blue">
            <div class="metric-row">
                <div>
                    <h3>Đăng nhập</h3>
                    <p class="metric-value-xl metric-value-blue"><?= $countLogin ?></p>
                </div>
                <div class="metric-icon-box metric-icon-box-lg is-blue">
                    <i class="iconsax" icon-name="login"></i>
                </div>
            </div>
        </div>
        <div class="stat-card metric-card-border-slate">
            <div class="metric-row">
                <div>
                    <h3>Đăng xuất</h3>
                    <p class="metric-value-xl metric-value-slate"><?= $countLogout ?></p>
                </div>
                <div class="metric-icon-box metric-icon-box-lg is-slate">
                    <i class="iconsax" icon-name="logout-1"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="logs-stats-grid reveal-item delay-2">

        <div class="card logs-stats-card">
            <div class="logs-card-bar-sm"></div>
            <div class="logs-card-body-md">
                <h4 class="logs-sidebar-title">
                    <i class="iconsax" icon-name="chart-square"></i>
                    Phân bổ hành động
                </h4>

                <div class="logs-distribution-list">

                    <div class="logs-distribution-row">
                        <div class="logs-distribution-head">
                            <span class="logs-distribution-label">
                                <span class="logs-distribution-dot is-create"></span>
                                Tạo mới (CREATE)
                            </span>
                            <span class="logs-distribution-value is-create"><?= $countCreate ?> <span class="logs-percent">(<?= $pctCreate ?>%)</span></span>
                        </div>
                        <progress class="logs-progress is-create" max="100" value="<?= $pctCreate ?>"></progress>
                    </div>

                    <div class="logs-distribution-row">
                        <div class="logs-distribution-head">
                            <span class="logs-distribution-label">
                                <span class="logs-distribution-dot is-update"></span>
                                Cập nhật (UPDATE)
                            </span>
                            <span class="logs-distribution-value is-update"><?= $countUpdate ?> <span class="logs-percent">(<?= $pctUpdate ?>%)</span></span>
                        </div>
                        <progress class="logs-progress is-update" max="100" value="<?= $pctUpdate ?>"></progress>
                    </div>

                    <div class="logs-distribution-row">
                        <div class="logs-distribution-head">
                            <span class="logs-distribution-label">
                                <span class="logs-distribution-dot is-delete"></span>
                                Xóa (DELETE)
                            </span>
                            <span class="logs-distribution-value is-delete"><?= $countDelete ?> <span class="logs-percent">(<?= $pctDelete ?>%)</span></span>
                        </div>
                        <progress class="logs-progress is-delete" max="100" value="<?= $pctDelete ?>"></progress>
                    </div>

                    <div class="logs-distribution-row">
                        <div class="logs-distribution-head">
                            <span class="logs-distribution-label">
                                <span class="logs-distribution-dot is-login"></span>
                                Đăng nhập (LOGIN)
                            </span>
                            <span class="logs-distribution-value is-login"><?= $countLogin ?> <span class="logs-percent">(<?= $pctLogin ?>%)</span></span>
                        </div>
                        <progress class="logs-progress is-login" max="100" value="<?= $pctLogin ?>"></progress>
                    </div>

                    <div class="logs-distribution-row">
                        <div class="logs-distribution-head">
                            <span class="logs-distribution-label">
                                <span class="logs-distribution-dot is-logout"></span>
                                Đăng xuất (LOGOUT)
                            </span>
                            <span class="logs-distribution-value is-logout"><?= $countLogout ?> <span class="logs-percent">(<?= $pctLogout ?>%)</span></span>
                        </div>
                        <progress class="logs-progress is-logout" max="100" value="<?= $pctLogout ?>"></progress>
                    </div>
                </div>

                <div class="logs-summary-chips">
                    <span class="logs-summary-chip">CREATE: <?= $pctCreate ?>%</span>
                    <span class="logs-summary-chip">UPDATE: <?= $pctUpdate ?>%</span>
                    <span class="logs-summary-chip">DELETE: <?= $pctDelete ?>%</span>
                    <span class="logs-summary-chip">LOGIN: <?= $pctLogin ?>%</span>
                    <span class="logs-summary-chip">LOGOUT: <?= $pctLogout ?>%</span>
                    <?php if ($pctOther > 0): ?>
                        <span class="logs-summary-chip">Khác: <?= $pctOther ?>%</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card logs-stats-card">
            <div class="logs-card-bar-alt"></div>
            <div class="logs-card-body-md">
                <h4 class="logs-sidebar-title">
                    <i class="iconsax" icon-name="clock"></i>
                    Dòng thời gian hoạt động
                </h4>

                <div class="logs-timeline">

                    <div class="logs-timeline-line"></div>

                    <?php if (!empty($recentLogs)): ?>
                        <?php
                        $actionConfig = [
                            'CREATE' => ['class' => 'is-create', 'icon' => 'add-circle', 'label' => 'Tạo mới'],
                            'UPDATE' => ['class' => 'is-update', 'icon' => 'edit-2', 'label' => 'Cập nhật'],
                            'DELETE' => ['class' => 'is-delete', 'icon' => 'trash', 'label' => 'Xóa'],
                            'LOGIN' => ['class' => 'is-login', 'icon' => 'login', 'label' => 'Đăng nhập'],
                            'LOGOUT' => ['class' => 'is-logout', 'icon' => 'logout-1', 'label' => 'Đăng xuất'],
                        ];
                        ?>
                        <?php foreach ($recentLogs as $idx => $rLog): ?>
                            <?php
                            $rAction = strtoupper($rLog['action'] ?? '');
                            $rCfg = $actionConfig[$rAction] ?? ['class' => 'is-default', 'icon' => 'document-text-1', 'label' => $rAction];
                            ?>
                            <div class="logs-timeline-item <?= $idx < count($recentLogs) - 1 ? '' : 'is-last' ?>">

                                <div class="logs-timeline-dot <?= $rCfg['class'] ?>"></div>

                                <a href="index.php?url=system-logs/show&id=<?= $rLog['log_id'] ?>"
                                    class="logs-timeline-link">
                                    <div class="logs-timeline-header">
                                        <span class="logs-mini-badge <?= $rCfg['class'] ?>">
                                            <i class="iconsax" icon-name="<?= $rCfg['icon'] ?>"></i>
                                            <?= $rCfg['label'] ?>
                                        </span>
                                        <span class="logs-timeline-time">
                                            <?php
                                            if (!empty($rLog['created_at'])) {
                                                $rDt = new DateTime($rLog['created_at']);
                                                echo $rDt->format('d/m H:i');
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <p class="logs-timeline-text">
                                        <?= htmlspecialchars(mb_substr($rLog['description'] ?? '', 0, 50)) ?>        <?= mb_strlen($rLog['description'] ?? '') > 50 ? '...' : '' ?>
                                    </p>
                                    <p class="logs-timeline-user">
                                        <?= !empty($rLog['full_name']) ? htmlspecialchars($rLog['full_name']) : 'Hệ thống' ?>
                                    </p>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="logs-timeline-empty">
                            <p>Chưa có hoạt động nào được ghi nhận.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
