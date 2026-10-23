<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php
$isGroupedView = !empty($isGroupedView);
$displayRows = $isGroupedView ? ($alertGroups ?? []) : ($alerts ?? []);

$formatSeverityLabel = static function (?string $severity): string {
    return match ($severity) {
        'critical' => 'NGUY HIỂM',
        'high' => 'CAO',
        'medium' => 'THEO DÕI',
        'low' => 'THẤP',
        default => 'THEO DÕI',
    };
};

$formatSeverityClass = static function (?string $severity): string {
    return $severity === 'critical' ? 'badge-discharged' : (in_array($severity, ['high', 'medium'], true) ? 'badge-info' : 'badge-inactive');
};
?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Cảnh báo sinh tồn</h2>
            <p>
                <?= $admission
                    ? htmlspecialchars($admission['patient_name']) . ' — ' . htmlspecialchars($admission['room_name'] ?? '')
                    : 'Hàng đợi đã gộp theo bệnh nhân để giảm quá tải cảnh báo' ?>
            </p>
        </div>
        <?php if ($admission): ?>
            <div class="page-header-actions">
                <a href="index.php?url=internal/alerts" class="btn btn-light">
                    <i class="iconsax" icon-name="arrow-left"></i> Tổng hợp cảnh báo
                </a>
                <a href="index.php?url=internal/admissions/show&id=<?= (int) $admission['admission_id'] ?>" class="btn btn-secondary">
                    <i class="iconsax" icon-name="hospital"></i> Ca nội trú
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success reveal-item"><i class="iconsax" icon-name="tick-circle"></i> <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item"><i class="iconsax" icon-name="info-circle"></i> <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <?php if (empty($displayRows)): ?>
        <div class="card reveal-item">
            <div class="card-body">
                <div class="empty-state">
                    <i class="iconsax empty-state-icon" icon-name="tick-circle"></i>
                    <p>Không có cảnh báo nào đang mở.</p>
                </div>
            </div>
        </div>
    <?php elseif ($isGroupedView): ?>
        <div class="card dashboard-table-card reveal-item">
            <div class="card-header">
                <div>
                    <h3><i class="iconsax" icon-name="warning-triangle"></i> Bệnh nhân cần xử lý</h3>
                    <p><?= count($alertGroups ?? []) ?> bệnh nhân có cảnh báo đang mở</p>
                </div>
            </div>

            <table class="table premium-table">
                <thead>
                    <tr>
                        <th>Bệnh nhân</th>
                        <th>Ưu tiên</th>
                        <th>Cảnh báo đại diện</th>
                        <th>Sinh tồn liên quan</th>
                        <th>Trạng thái</th>
                        <th>Cập nhật</th>
                        <th class="text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alertGroups as $group): ?>
                        <?php
                        $sev = $group['severity'] ?? 'high';
                        $newCount = (int) ($group['new_count'] ?? 0);
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($group['patient_name'] ?? '') ?></strong>
                                <br>
                                <span class="table-text-muted" style="font-size:13px;">
                                    <?= htmlspecialchars($group['patient_code'] ?? '') ?>
                                    · <?= htmlspecialchars($group['room_name'] ?? '---') ?>
                                    / <?= htmlspecialchars($group['bed_code'] ?? '---') ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $formatSeverityClass($sev) ?>">
                                    <?= htmlspecialchars($formatSeverityLabel($sev)) ?>
                                </span>
                                <div class="meta-small"><?= (int) ($group['alert_count'] ?? 0) ?> cảnh báo đang mở</div>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($group['alert_type'] ?? '') ?></strong>
                                <p class="table-text-muted alert-item-text">
                                    <?= htmlspecialchars($group['alert_message'] ?? '') ?>
                                </p>
                            </td>
                            <td>
                                <div class="badge-list">
                                    <span class="badge badge-inactive">HA <?= htmlspecialchars($group['blood_pressure_systolic'] ?? '---') ?>/<?= htmlspecialchars($group['blood_pressure_diastolic'] ?? '---') ?></span>
                                    <span class="badge badge-inactive">SpO2 <?= htmlspecialchars($group['spo2'] ?? '---') ?>%</span>
                                    <span class="badge badge-inactive">Mạch <?= htmlspecialchars($group['pulse'] ?? '---') ?></span>
                                    <span class="badge badge-inactive">Nhịp thở <?= htmlspecialchars($group['respiratory_rate'] ?? '---') ?></span>
                                </div>
                            </td>
                            <td>
                                <?php if ($newCount > 0): ?>
                                    <span class="badge badge-discharged"><?= $newCount ?> mới</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Đã xác nhận</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($group['latest_alert_at'])): ?>
                                    <strong><?= date('H:i', strtotime($group['latest_alert_at'])) ?></strong>
                                    <br><span class="table-text-muted" style="font-size:13px;"><?= date('d/m/Y', strtotime($group['latest_alert_at'])) ?></span>
                                <?php else: ?>
                                    ---
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <a href="index.php?url=internal/alerts&admission_id=<?= (int) ($group['admission_id'] ?? 0) ?>"
                                    class="btn-action btn-action-view" title="Xem cảnh báo của bệnh nhân">
                                    <i class="iconsax" icon-name="eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="card dashboard-table-card reveal-item">
            <table class="table premium-table">
                    <thead>
                        <tr>
                            <th>Mức độ</th>
                            <th>Loại cảnh báo</th>
                            <th>Nội dung</th>
                            <?php if (!$admission): ?><th>Bệnh nhân</th><?php endif; ?>
                            <th>Trạng thái</th>
                            <th>Thời gian</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alerts as $al): ?>
                            <?php $sev = $al['severity'] ?? 'high'; ?>
                            <tr>
                                <td>
                                    <span class="badge <?= $formatSeverityClass($sev) ?>">
                                        <?= htmlspecialchars($formatSeverityLabel($sev)) ?>
                                    </span>
                                </td>
                                <td><strong><?= htmlspecialchars($al['alert_type'] ?? '') ?></strong></td>
                                <td class="table-text-muted"><?= htmlspecialchars($al['alert_message'] ?? '') ?></td>
                                <?php if (!$admission): ?>
                                    <td>
                                        <strong><?= htmlspecialchars($al['patient_name'] ?? '') ?></strong>
                                        <br><span class="table-text-muted" style="font-size:13px;"><?= htmlspecialchars($al['patient_code'] ?? '') ?></span>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <?php $st = $al['status'] ?? 'new'; ?>
                                    <?php if ($st === 'new'): ?>
                                        <span class="badge badge-discharged">Mới</span>
                                    <?php elseif ($st === 'acknowledged'): ?>
                                        <span class="badge badge-info">Đã xác nhận</span>
                                    <?php else: ?>
                                        <span class="badge badge-active">Đã xử lý</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($al['created_at'])): ?>
                                        <strong><?= date('H:i', strtotime($al['created_at'])) ?></strong>
                                        <br><span class="table-text-muted" style="font-size:13px;"><?= date('d/m/Y', strtotime($al['created_at'])) ?></span>
                                    <?php else: ?>
                                        ---
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <?php
                                        $returnUrl = $admission
                                            ? 'index.php?url=internal/alerts&admission_id=' . (int) $admission['admission_id']
                                            : 'index.php?url=internal/alerts';
                                    ?>
                                    <?php if ($al['status'] === 'new'): ?>
                                        <form action="index.php?url=internal/alerts/acknowledge" method="POST" style="display:inline;">
                                            <input type="hidden" name="alert_id" value="<?= (int) $al['alert_id'] ?>">
                                            <input type="hidden" name="return_url" value="<?= htmlspecialchars($returnUrl) ?>">
                                            <button type="submit" class="btn-action btn-action-view" title="Xác nhận"><i class="iconsax" icon-name="tick-circle"></i></button>
                                        </form>
                                    <?php elseif ($al['status'] === 'acknowledged'): ?>
                                        <form action="index.php?url=internal/alerts/resolve" method="POST" style="display:inline;">
                                            <input type="hidden" name="alert_id" value="<?= (int) $al['alert_id'] ?>">
                                            <input type="hidden" name="return_url" value="<?= htmlspecialchars($returnUrl) ?>">
                                            <button type="submit" class="btn-action btn-action-edit" title="Xử lý xong"><i class="iconsax" icon-name="verify"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="table-text-muted">Đã xong</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
