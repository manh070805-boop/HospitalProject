<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Dashboard Nội Bộ</h2>
            <p>Chào mừng <strong><?= htmlspecialchars($user['full_name']); ?></strong>
                (<?= $role === 'head_nurse' ? 'Điều dưỡng trưởng' : 'Điều dưỡng'; ?>)</p>
        </div>
    </div>
    <div class="stats-grid">
        <div class="stat-card stat-primary reveal-item delay-1">
            <div class="stat-head">
                <div>
                    <h3>Ca nội trú hiện tại</h3>
                    <p><?= $activeAdmissions; ?></p>
                </div>
                <div class="stat-icon-box is-primary">
                    <i class="iconsax" icon-name="hospital"></i>
                </div>
            </div>

        </div>

        <div class="stat-card stat-danger reveal-item delay-2">
            <div class="stat-head">
                <div>
                    <h3>Cảnh báo (Alerts)</h3>
                    <p><?= $activeAlerts; ?></p>
                </div>
                <div class="stat-icon-box is-danger">
                    <i class="iconsax" icon-name="warning-triangle"></i>
                </div>
            </div>
        </div>

        <div class="stat-card stat-warning reveal-item delay-3">
            <div class="stat-head">
                <div>
                    <h3>Công việc chờ xử lý</h3>
                    <p><?= (int) $pendingTasks; ?></p>
                </div>
                <div class="stat-icon-box is-warning">
                    <i class="iconsax" icon-name="task-list"></i>
                </div>
            </div>
        </div>

        <?php if ($role === 'head_nurse' && $bedStats): ?>
            <div class="stat-card stat-success reveal-item delay-4">
                <div class="stat-head">
                    <div>
                        <h3>Giường trống trong khoa</h3>
                        <p><?= $bedStats['available_beds']; ?> / <?= $bedStats['total_beds']; ?></p>
                    </div>
                    <div class="stat-icon-box is-success">
                        <i class="iconsax" icon-name="hospital"></i>
                    </div>
                </div>

            </div>
            <div class="stat-card stat-info reveal-item delay-5">
                <div class="stat-head">
                    <div>
                        <h3>Tổng số kế hoạch</h3>
                        <p><?= (int) ($carePlanStats['total'] ?? 0); ?></p>
                    </div>
                    <div class="stat-icon-box is-info">
                        <i class="iconsax" icon-name="document-text-1"></i>
                    </div>
                </div>

            </div>
            <div class="stat-card stat-info reveal-item delay-5">
                <div class="stat-head">
                    <div>
                        <h3>Số kế hoạch đã duyệt</h3>
                        <p><?= (int) ($carePlanStats['approved'] ?? 0); ?></p>
                    </div>
                    <div class="stat-icon-box is-info">
                        <i class="iconsax" icon-name="document-text-1"></i>
                    </div>
                </div>

            </div>
            <div class="stat-card stat-info reveal-item delay-5">
                <div class="stat-head">
                    <div>
                        <h3>Số kế hoạch chưa duyệt</h3>
                        <p><?= (int) ($carePlanStats['not_approved'] ?? 0); ?></p>
                    </div>
                    <div class="stat-icon-box is-info">
                        <i class="iconsax" icon-name="document-text-1"></i>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </div>

    <section class="attention-panel reveal-item delay-4">
        <div class="attention-panel-header">
            <div class="attention-panel-title">
                <span class="attention-panel-icon">
                    <i class="iconsax" icon-name="warning-triangle"></i>
                </span>
                <div>
                    <h3>Bệnh nhân cần chú ý</h3>
                    <p>Dữ liệu đồng bộ từ cảnh báo sinh tồn, task quá hạn và chỉ số mới nhất trong ca trực.</p>
                </div>
            </div>
            <span class="attention-panel-badge">
                <?= count($attentionPatients ?? []); ?> ca đang theo dõi
            </span>
        </div>

        <?php if (!empty($attentionPatients)): ?>
            <div class="attention-patient-list">
                <?php foreach ($attentionPatients as $patient): ?>
                    <?php
                    $toneClass = in_array($patient['tone'] ?? '', ['danger', 'warning', 'info'], true)
                        ? $patient['tone']
                        : 'info';
                    ?>
                    <article class="attention-patient-card is-<?= htmlspecialchars($toneClass); ?>">
                        <div class="attention-patient-main">
                            <span class="attention-patient-icon">
                                <i class="iconsax"
                                    icon-name="<?= htmlspecialchars($patient['icon'] ?? 'warning-triangle'); ?>"></i>
                            </span>
                            <div class="attention-patient-info">
                                <div class="attention-patient-name">
                                    <?= htmlspecialchars($patient['patient_name']); ?>
                                </div>
                                <div class="attention-patient-meta">
                                    <?= htmlspecialchars($patient['patient_code']); ?> ·
                                    <?= htmlspecialchars($patient['location']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="attention-patient-footer">
                            <span class="attention-patient-signal">
                                <?= htmlspecialchars($patient['signal']); ?>
                            </span>
                            <span class="attention-patient-priority">
                                <?= htmlspecialchars($patient['priority']); ?>
                            </span>
                        </div>
                        <p class="attention-patient-reason">
                            <?= htmlspecialchars($patient['reason']); ?>
                        </p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="attention-patient-empty">
                Chưa có bệnh nhân nào trong danh sách cần chú ý.
            </div>
        <?php endif; ?>
    </section>

    <div class="dashboard-section reveal-item">
        <h3 class="dashboard-section-title">
            <i class="iconsax" icon-name="clipboard-text-1"></i>
            Thông báo & Nhắc việc
        </h3>
        <p class="dashboard-section-text">Danh sách các hoạt động cần lưu ý trong ca trực của bạn</p>
    </div>

    <div class="card dashboard-notice-card reveal-item">
        <?php if (!empty($dashboardActivities)): ?>
            <div class="dashboard-notice-list">
                <?php foreach ($dashboardActivities as $activity): ?>
                    <?php
                    $toneClass = in_array($activity['tone'] ?? '', ['danger', 'warning', 'info'], true)
                        ? $activity['tone']
                        : 'info';
                    $activityAt = !empty($activity['activity_at']) ? strtotime($activity['activity_at']) : false;
                    ?>
                    <a class="dashboard-notice-item is-<?= htmlspecialchars($toneClass); ?>"
                        href="<?= htmlspecialchars($activity['href'] ?? 'index.php?url=internal/dashboard'); ?>">
                        <span class="dashboard-notice-icon">
                            <i class="iconsax" icon-name="<?= htmlspecialchars($activity['icon'] ?? 'bell-1'); ?>"></i>
                        </span>
                        <span class="dashboard-notice-content">
                            <strong><?= htmlspecialchars($activity['title'] ?? 'Thông báo'); ?></strong>
                            <span>
                                <?= htmlspecialchars($activity['patient_name'] ?? ''); ?>
                                <?php if (!empty($activity['patient_code'])): ?>
                                    · <?= htmlspecialchars($activity['patient_code']); ?>
                                <?php endif; ?>
                                <?php if (!empty($activity['location'])): ?>
                                    · <?= htmlspecialchars($activity['location']); ?>
                                <?php endif; ?>
                            </span>
                            <?php if (!empty($activity['description'])): ?>
                                <small><?= htmlspecialchars($activity['description']); ?></small>
                            <?php endif; ?>
                        </span>
                        <span class="dashboard-notice-meta">
                            <span><?= htmlspecialchars($activity['status_label'] ?? 'Đang mở'); ?></span>
                            <time><?= $activityAt ? date('H:i d/m', $activityAt) : '---'; ?></time>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="iconsax empty-state-icon" icon-name="tick-circle"></i>
                <p>Không có thông báo hoặc nhắc việc đang mở.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>