<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php
$formatLocation = static function (array $row): string {
    return trim(($row['room_name'] ?? 'Chưa xếp phòng') . ' · Giường ' . ($row['bed_code'] ?? '---'));
};

$formatSeverityLabel = static function (?string $severity): string {
    return match ($severity) {
        'critical' => 'Nguy hiểm',
        'high' => 'Cao',
        'medium' => 'Theo dõi',
        'low' => 'Thấp',
        default => 'Theo dõi',
    };
};

$formatStatusLabel = static function (?string $status): string {
    return match ($status) {
        'active' => 'Active',
        'updated' => 'Đã cập nhật',
        'stopped' => 'Đã dừng',
        'completed' => 'Hoàn tất',
        'acknowledged' => 'Đã xác nhận',
        'new' => 'Mới',
        default => $status ? ucfirst((string) $status) : 'Đang mở',
    };
};

$attentionCards = [];
foreach ($attentionPatients ?? [] as $row) {
    $hasAlert = !empty($row['alert_type']);
    $hasPlan = !empty($row['treatment_plan_id']);
    $severity = $row['severity'] ?? 'medium';
    $tone = $hasAlert
        ? (in_array($severity, ['critical', 'high'], true) ? 'danger' : ($severity === 'medium' ? 'warning' : 'info'))
        : ($hasPlan ? 'info' : 'warning');

    $vitalSignal = 'Theo dõi';
    if (!empty($row['spo2'])) {
        $vitalSignal = 'SpO2 ' . $row['spo2'] . '%';
    } elseif (!empty($row['blood_pressure_systolic']) || !empty($row['blood_pressure_diastolic'])) {
        $vitalSignal = 'HA ' . ($row['blood_pressure_systolic'] ?? '---') . '/' . ($row['blood_pressure_diastolic'] ?? '---');
    }

    $attentionCards[] = [
        'patient_name' => $row['patient_name'] ?? '---',
        'patient_code' => $row['patient_code'] ?? '---',
        'location' => $formatLocation($row),
        'signal' => $hasAlert ? ($row['alert_type'] ?? 'Cảnh báo') : $vitalSignal,
        'priority' => $hasAlert ? $formatSeverityLabel($severity) : ($hasPlan ? 'Theo dõi' : 'Chưa có kế hoạch điều trị'),
        'reason' => $hasAlert
            ? ($row['alert_message'] ?? 'Cần bác sĩ đánh giá cảnh báo sinh tồn.')
            : ($hasPlan
                ? ('Kế hoạch điều trị active: ' . mb_substr($row['diagnosis'] ?? 'Đang theo dõi', 0, 90))
                : 'Bệnh nhân chưa có kế hoạch điều trị active, cần rà soát y lệnh.'),
        'tone' => $tone,
        'icon' => $hasAlert ? 'warning-triangle' : ($hasPlan ? 'activity-square' : 'clipboard-text-1'),
    ];
}

$dashboardActivities = [];
foreach ($recentOrders ?? [] as $order) {
    $status = $order['status'] ?? 'active';
    $dashboardActivities[] = [
        'tone' => $status === 'active' ? 'info' : 'warning',
        'icon' => 'clipboard-text-1',
        'title' => 'Y lệnh: ' . mb_substr($order['diagnosis'] ?? 'Chưa ghi chẩn đoán', 0, 70),
        'description' => mb_substr($order['medical_order'] ?? '', 0, 130),
        'patient_name' => $order['patient_name'] ?? '',
        'patient_code' => $order['patient_code'] ?? '',
        'location' => $formatLocation($order),
        'status_label' => $formatStatusLabel($status),
        'activity_at' => $order['created_at'] ?? null,
        'href' => 'index.php?url=doctor-portal/treatment-plans/show&id=' . (int) ($order['treatment_plan_id'] ?? 0),
        'sort_rank' => $status === 'active' ? 60 : 45,
    ];
}

usort($dashboardActivities, static function (array $a, array $b): int {
    $rankCompare = ($b['sort_rank'] ?? 0) <=> ($a['sort_rank'] ?? 0);
    if ($rankCompare !== 0) {
        return $rankCompare;
    }

    return strtotime($b['activity_at'] ?? '1970-01-01') <=> strtotime($a['activity_at'] ?? '1970-01-01');
});
$dashboardActivities = array_slice($dashboardActivities, 0, 8);
?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Dashboard lâm sàng</h2>
            <p>Theo dõi bệnh nhân đang được phân công và các cảnh báo cần xử trí</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card stat-primary reveal-item delay-1">
            <div class="stat-head">
                <div>
                    <h3>Đang phụ trách</h3>
                    <p><?= (int) $stats['active_admissions'] ?></p>
                </div>
                <div class="stat-icon-box is-primary">
                    <i class="iconsax" icon-name="hospital"></i>
                </div>
            </div>
        </div>

        <div class="stat-card stat-danger reveal-item delay-2">
            <div class="stat-head">
                <div>
                    <h3>Cảnh báo mở</h3>
                    <p><?= (int) $stats['open_alerts'] ?></p>
                </div>
                <div class="stat-icon-box is-danger">
                    <i class="iconsax" icon-name="warning-triangle"></i>
                </div>
            </div>
        </div>

        <div class="stat-card stat-success reveal-item delay-3">
            <div class="stat-head">
                <div>
                    <h3>Kế hoạch điều trị</h3>
                    <p><?= (int) $stats['active_plans'] ?></p>
                </div>
                <div class="stat-icon-box is-success">
                    <i class="iconsax" icon-name="clipboard-text-1"></i>
                </div>
            </div>
        </div>


    </div>

    <section class="attention-panel reveal-item delay-4">
        <div class="attention-panel-header">
            <div class="attention-panel-title">
                <span class="attention-panel-icon">
                    <i class="iconsax" icon-name="warning-triangle"></i>
                </span>
                <div>
                    <h3>Bệnh nhân cần chú ý</h3>
                    <p>Dữ liệu đồng bộ từ cảnh báo sinh tồn, chỉ số mới nhất và tình trạng kế hoạch điều trị.</p>
                </div>
            </div>
            <span class="attention-panel-badge">
                <?= count($attentionCards); ?> ca đang theo dõi
            </span>
        </div>

        <?php if (!empty($attentionCards)): ?>
            <div class="attention-patient-list">
                <?php foreach ($attentionCards as $patient): ?>
                    <article class="attention-patient-card is-<?= htmlspecialchars($patient['tone']); ?>">
                        <div class="attention-patient-main">
                            <span class="attention-patient-icon">
                                <i class="iconsax" icon-name="<?= htmlspecialchars($patient['icon']); ?>"></i>
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

    <section class="orders-panel reveal-item delay-5">
        <div class="orders-panel-header">
            <div class="orders-panel-title">
                <span class="orders-panel-icon">
                    <i class="iconsax" icon-name="clipboard-text-1"></i>
                </span>
                <div>
                    <h3>Y lệnh</h3>
                    <p>Các y lệnh và kế hoạch điều trị mới nhất trong danh sách phụ trách của bạn</p>
                </div>
            </div>
            <span class="orders-panel-badge">
                <?= count($dashboardActivities); ?> y lệnh mới
            </span>
        </div>

        <div class="dashboard-notice-card orders-notice-card">
            <?php if (!empty($dashboardActivities)): ?>
                <div class="dashboard-notice-list">
                    <?php foreach ($dashboardActivities as $activity): ?>
                        <?php $activityAt = !empty($activity['activity_at']) ? strtotime($activity['activity_at']) : false; ?>
                        <a class="dashboard-notice-item is-<?= htmlspecialchars($activity['tone']); ?>"
                            href="<?= htmlspecialchars($activity['href']); ?>">
                            <span class="dashboard-notice-icon">
                                <i class="iconsax" icon-name="<?= htmlspecialchars($activity['icon']); ?>"></i>
                            </span>
                            <span class="dashboard-notice-content">
                                <strong><?= htmlspecialchars($activity['title']); ?></strong>
                                <span>
                                    <?= htmlspecialchars($activity['patient_name']); ?>
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
                                <span><?= htmlspecialchars($activity['status_label']); ?></span>
                                <time><?= $activityAt ? date('H:i d/m', $activityAt) : '---'; ?></time>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="iconsax empty-state-icon" icon-name="tick-circle"></i>
                    <p>Không có y lệnh mới.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
