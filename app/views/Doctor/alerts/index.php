<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php
$isPatientScoped = !empty($isPatientScoped);
$displayRows = $isPatientScoped ? ($alerts ?? []) : ($alertGroups ?? []);

$formatSeverityLabel = static function (?string $severity): string {
    return match ($severity) {
        'critical' => 'Critical',
        'high' => 'High',
        'medium' => 'Medium',
        'low' => 'Low',
        default => 'Theo dõi',
    };
};

$formatSeverityClass = static function (?string $severity): string {
    return in_array($severity, ['critical', 'high'], true) ? 'badge-danger' : 'badge-info';
};
?>

<div class="admin-content">
<div class="page-header reveal-item">
    <div class="page-header-info">
        <h2>Cảnh báo sinh tồn</h2>
        <p>
            <?= $isPatientScoped
                ? 'Cảnh báo riêng của bệnh nhân được chọn'
                : 'Hàng đợi đã gộp theo bệnh nhân để giảm quá tải thông tin' ?>
        </p>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success reveal-item"><i class="iconsax" icon-name="tick-circle"></i><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error reveal-item"><i class="iconsax" icon-name="info-circle"></i><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="card dashboard-table-card reveal-item">
    <div class="card-header">
        <div>
            <h3><i class="iconsax" icon-name="warning-triangle"></i> <?= $isPatientScoped ? 'Cảnh báo của bệnh nhân' : 'Bệnh nhân cần xử lý' ?></h3>
            <p><?= count($displayRows) ?> <?= $isPatientScoped ? 'cảnh báo phù hợp' : 'bệnh nhân có cảnh báo phù hợp' ?></p>
        </div>

        <?php if ($isPatientScoped): ?>
            <a href="index.php?url=doctor-portal/alerts" class="btn btn-light">
                <i class="iconsax" icon-name="arrow-left"></i> Tổng hợp cảnh báo
            </a>
        <?php endif; ?>
    </div>

    <div class="card-body">
        <form method="GET" action="index.php" class="search-form search-form-wide mb-3">
            <input type="hidden" name="url" value="doctor-portal/alerts">
            <?php if (!empty($patientId)): ?>
                <input type="hidden" name="patient_id" value="<?= (int) $patientId ?>">
            <?php endif; ?>
            <div class="search-row">
                <div class="input-with-icon search-field">
                    <i class="iconsax" icon-name="search-normal-1"></i>
                    <input type="text" name="keyword" class="form-control"
                        value="<?= htmlspecialchars($keyword ?? '') ?>"
                        placeholder="Tìm theo bệnh nhân hoặc nội dung cảnh báo">
                </div>

                <select name="severity" class="form-control search-select search-select-min-180">
                    <option value="" <?= ($severity ?? '') === '' ? 'selected' : '' ?>>Tất cả mức độ</option>
                    <option value="critical" <?= ($severity ?? '') === 'critical' ? 'selected' : '' ?>>Critical</option>
                    <option value="high" <?= ($severity ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                    <option value="medium" <?= ($severity ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="low" <?= ($severity ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                </select>

                <button type="submit" class="btn btn-primary">
                    <i class="iconsax" icon-name="search-normal-1"></i> Lọc
                </button>
            </div>
        </form>

        <?php if (empty($displayRows)): ?>
            <div class="empty-state">
                <i class="iconsax empty-state-icon" icon-name="warning-triangle"></i>
                <p>Không có cảnh báo sinh tồn đang mở.</p>
            </div>
        <?php elseif (!$isPatientScoped): ?>
            <div class="table-responsive">
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
                            $sev = $group['severity'] ?? 'medium';
                            $newCount = (int) ($group['new_count'] ?? 0);
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($group['patient_name'] ?? '---') ?></strong><br>
                                    <span class="table-text-muted">
                                        <?= htmlspecialchars($group['patient_code'] ?? '---') ?>
                                        · <?= htmlspecialchars($group['room_name'] ?? '---') ?>
                                        / <?= htmlspecialchars($group['bed_code'] ?? '---') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $formatSeverityClass($sev) ?>"><?= htmlspecialchars($formatSeverityLabel($sev)) ?></span>
                                    <div class="meta-small"><?= (int) ($group['alert_count'] ?? 0) ?> cảnh báo đang mở</div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($group['alert_type'] ?? 'Cảnh báo') ?></strong>
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
                                        <span class="badge badge-danger"><?= $newCount ?> mới</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Đã xác nhận</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($group['latest_alert_at']) ? date('H:i d/m/Y', strtotime($group['latest_alert_at'])) : '---' ?></td>
                                <td class="text-right">
                                    <div class="actions-row">
                                        <a href="index.php?url=doctor-portal/alerts&patient_id=<?= (int) $group['patient_id'] ?>"
                                            class="btn-action btn-action-view" title="Xem chi tiết các cảnh báo">
                                            <i class="iconsax" icon-name="eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table premium-table">
                    <thead>
                        <tr>
                            <th>Bệnh nhân</th>
                            <th>Cảnh báo</th>
                            <th>Sinh tồn liên quan</th>
                            <th>Mức độ</th>
                            <th>Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alerts as $alert): ?>
                            <?php
                            $sev = $alert['severity'] ?? 'medium';
                            $sevClass = $formatSeverityClass($sev);
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($alert['patient_name'] ?? '---') ?></strong><br>
                                    <span class="table-text-muted">
                                        <?= htmlspecialchars($alert['patient_code'] ?? '---') ?>
                                        · <?= htmlspecialchars($alert['room_name'] ?? '---') ?>
                                        / <?= htmlspecialchars($alert['bed_code'] ?? '---') ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($alert['alert_type'] ?? 'Cảnh báo') ?></strong>
                                    <p class="table-text-muted alert-item-text">
                                        <?= htmlspecialchars($alert['alert_message'] ?? '') ?>
                                    </p>
                                </td>
                                <td>
                                    <div class="badge-list">
                                        <span class="badge badge-inactive">HA <?= htmlspecialchars($alert['blood_pressure_systolic'] ?? '---') ?>/<?= htmlspecialchars($alert['blood_pressure_diastolic'] ?? '---') ?></span>
                                        <span class="badge badge-inactive">SpO2 <?= htmlspecialchars($alert['spo2'] ?? '---') ?>%</span>
                                        <span class="badge badge-inactive">Mạch <?= htmlspecialchars($alert['pulse'] ?? '---') ?></span>
                                        <span class="badge badge-inactive">Nhịp thở <?= htmlspecialchars($alert['respiratory_rate'] ?? '---') ?></span>
                                    </div>
                                </td>
                                <td><span class="badge <?= $sevClass ?>"><?= htmlspecialchars($formatSeverityLabel($sev)) ?></span></td>
                                <td><?= !empty($alert['created_at']) ? date('H:i d/m/Y', strtotime($alert['created_at'])) : '---' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
