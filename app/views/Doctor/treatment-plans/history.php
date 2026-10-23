<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
<div class="page-header reveal-item">
    <div class="page-header-info">
        <h2>Lịch sử kế hoạch điều trị</h2>
        <p>
            <?= htmlspecialchars($admission['patient_name'] ?? '---') ?>
            · <?= htmlspecialchars($admission['patient_code'] ?? '---') ?>
        </p>
    </div>

    <div class="page-header-actions">
        <a href="index.php?url=doctor-portal/admissions/show&id=<?= (int) $admission['admission_id'] ?>" class="btn btn-secondary">
            <i class="iconsax" icon-name="arrow-left"></i> Ca nội trú
        </a>
    </div>
</div>

<div class="card dashboard-table-card reveal-item">
    <div class="card-header">
        <h3><i class="iconsax" icon-name="clipboard-text-1"></i> Kế hoạch đang active</h3>
    </div>

    <div class="card-body">
        <?php if (empty($plan)): ?>
            <div class="empty-state"><p>Không có kế hoạch điều trị đang active.</p></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table premium-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Chẩn đoán</th>
                            <th>Y lệnh</th>
                            <th>Trạng thái</th>
                            <th>Cập nhật</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $planStatus = $plan['status'] ?? 'active';
                        $statusClass = $planStatus === 'active' ? 'badge-active' : ($planStatus === 'updated' ? 'badge-info' : 'badge-inactive');
                        $changedAt = $plan['updated_at'] ?? $plan['created_at'] ?? null;
                        ?>
                        <tr>
                            <td>#<?= (int) $plan['treatment_plan_id'] ?></td>
                            <td><strong><?= htmlspecialchars(mb_substr($plan['diagnosis'] ?? '---', 0, 80)) ?></strong></td>
                            <td class="table-text-muted"><?= htmlspecialchars(mb_substr($plan['medical_order'] ?? '---', 0, 100)) ?></td>
                            <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($planStatus) ?></span></td>
                            <td><?= !empty($changedAt) ? date('H:i d/m/Y', strtotime($changedAt)) : '---' ?></td>
                            <td class="text-right">
                                <a href="index.php?url=doctor-portal/treatment-plans/show&id=<?= (int) $plan['treatment_plan_id'] ?>" class="btn-action btn-action-view" title="Xem kế hoạch điều trị">
                                    <i class="iconsax" icon-name="eye"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="dashboard-section reveal-item">
    <h3 class="dashboard-section-title">
        <i class="iconsax" icon-name="history"></i>
        Lịch sử thay đổi
    </h3>
    <p class="dashboard-section-text">Các bản được lưu lại khi cập nhật, dừng hoặc hoàn tất kế hoạch điều trị</p>
</div>

<div class="card dashboard-table-card reveal-item">
    <?php if (empty($changeLogs ?? [])): ?>
        <div class="empty-state"><p>Chưa có bản ghi thay đổi.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table premium-table">
                <thead>
                    <tr>
                        <th>Thời điểm lưu</th>
                        <th>Chẩn đoán</th>
                        <th>Y lệnh</th>
                        <th>Trạng thái</th>
                        <th>Người cập nhật</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($changeLogs as $log): ?>
                        <?php
                        $changedAt = !empty($log['changed_at']) ? strtotime($log['changed_at']) : false;
                        $changeLabel = match ($log['change_type'] ?? '') {
                            'completed' => 'Hoàn thành mục tiêu',
                            'stopped' => 'Dừng kế hoạch',
                            'status_update' => 'Đổi trạng thái',
                            'legacy_plan' => 'Bản cũ',
                            default => 'Cập nhật nội dung',
                        };
                        ?>
                        <tr>
                            <td>
                                <strong><?= $changedAt ? date('H:i', $changedAt) : '---' ?></strong>
                                <br><span class="meta-small"><?= $changedAt ? date('d/m/Y', $changedAt) : '---' ?></span>
                                <br><span class="badge badge-info"><?= htmlspecialchars($changeLabel) ?></span>
                            </td>
                            <td><strong><?= htmlspecialchars(mb_substr($log['diagnosis'] ?? '---', 0, 90)) ?></strong></td>
                            <td class="table-text-muted"><?= htmlspecialchars(mb_substr($log['medical_order'] ?? '---', 0, 140)) ?></td>
                            <td><span class="badge badge-inactive"><?= htmlspecialchars($log['status'] ?? '---') ?></span></td>
                            <td>
                                <?= htmlspecialchars($log['full_name'] ?? $log['username'] ?? '---') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
