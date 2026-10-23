<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
<div class="page-header reveal-item">
    <div class="page-header-info">
        <h2>Kế hoạch điều trị #<?= (int) $plan['treatment_plan_id'] ?></h2>
        <p>
            <?= htmlspecialchars($plan['patient_name'] ?? '---') ?>
            · <?= htmlspecialchars($plan['patient_code'] ?? '---') ?>
            · <?= htmlspecialchars($plan['room_name'] ?? '---') ?>/<?= htmlspecialchars($plan['bed_code'] ?? '---') ?>
        </p>
    </div>

    <div class="page-header-actions">
        <a href="index.php?url=doctor-portal/admissions/show&id=<?= (int) $plan['admission_id'] ?>" class="btn btn-secondary">
            <i class="iconsax" icon-name="arrow-left"></i> Ca nội trú
        </a>
        <a href="index.php?url=doctor-portal/treatment-plans/history&admission_id=<?= (int) $plan['admission_id'] ?>" class="btn btn-light">
            <i class="iconsax" icon-name="history"></i> Lịch sử
        </a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success reveal-item"><i class="iconsax" icon-name="tick-circle"></i><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error reveal-item"><i class="iconsax" icon-name="info-circle"></i><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="card reveal-item">
    <div class="card-header">
        <h3><i class="iconsax" icon-name="clipboard-text-1"></i> Nội dung kế hoạch điều trị</h3>
        <?php
        $planStatus = $plan['status'] ?? 'active';
        $statusClass = $planStatus === 'active' ? 'badge-active' : ($planStatus === 'updated' ? 'badge-info' : 'badge-inactive');
        $openAlertCount = (int) ($plan['open_alert_count'] ?? 0);
        ?>
        <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($planStatus) ?></span>
    </div>

    <div class="card-body">
        <div class="detail-grid mb-3">
            <div class="detail-field">
                <span class="detail-label">Ngày tạo</span>
                <p class="detail-text detail-text-strong">
                    <?= !empty($plan['created_at']) ? date('H:i d/m/Y', strtotime($plan['created_at'])) : '---' ?>
                </p>
            </div>
            <div class="detail-field">
                <span class="detail-label">Cập nhật cuối</span>
                <p class="detail-text detail-text-strong">
                    <?= !empty($plan['updated_at']) ? date('H:i d/m/Y', strtotime($plan['updated_at'])) : '---' ?>
                </p>
            </div>
        </div>

        <div class="detail-block mb-3">
            <span class="detail-label">Chẩn đoán</span>
            <p class="detail-text detail-text-strong">
                <?= htmlspecialchars($plan['diagnosis'] ?? '---') ?>
            </p>
        </div>

        <div class="detail-block mb-3">
            <span class="detail-label">Mục tiêu điều trị</span>
            <p class="detail-text">
                <?= htmlspecialchars($plan['treatment_goal'] ?? '---') ?>
            </p>
        </div>

        <div class="detail-block mb-3">
            <span class="detail-label">Y lệnh</span>
            <p class="detail-text">
                <?= htmlspecialchars($plan['medical_order'] ?? '---') ?>
            </p>
        </div>

        <div class="detail-block">
            <span class="detail-label">Ghi chú điều trị</span>
            <p class="detail-text">
                <?= htmlspecialchars($plan['treatment_note'] ?? '---') ?>
            </p>
        </div>

        <?php if ($planStatus === 'active'): ?>
            <?php if ($openAlertCount > 0): ?>
                <div class="alert alert-warning" style="margin-top:16px;">
                    <i class="iconsax" icon-name="info-circle"></i>
                    Còn <?= $openAlertCount ?> cảnh báo chưa xử lý. Bác sĩ cần xử lý hết cảnh báo trước khi hoàn tất kế hoạch điều trị.
                </div>
            <?php endif; ?>
            <div class="form-actions">
                <a href="index.php?url=doctor-portal/treatment-plans/create&admission_id=<?= (int) $plan['admission_id'] ?>" class="btn btn-primary">
                    <i class="iconsax" icon-name="refresh"></i> Cập nhật kế hoạch điều trị
                </a>

                <?php if ($openAlertCount > 0): ?>
                    <button type="button" class="btn btn-success" disabled title="Cần xử lý hết cảnh báo trước khi hoàn tất">
                        <i class="iconsax" icon-name="tick-circle"></i> Hoàn tất
                    </button>
                <?php else: ?>
                    <form method="POST" action="index.php?url=doctor-portal/treatment-plans/complete">
                        <input type="hidden" name="treatment_plan_id" value="<?= (int) $plan['treatment_plan_id'] ?>">
                        <input type="hidden" name="return_url" value="index.php?url=doctor-portal/treatment-plans/history&admission_id=<?= (int) $plan['admission_id'] ?>">
                        <button type="submit" class="btn btn-success">
                            <i class="iconsax" icon-name="tick-circle"></i> Hoàn tất
                        </button>
                    </form>
                <?php endif; ?>

                <form method="POST" action="index.php?url=doctor-portal/treatment-plans/stop"
                    onsubmit="return confirm('Xác nhận dừng kế hoạch điều trị active này?');">
                    <input type="hidden" name="treatment_plan_id" value="<?= (int) $plan['treatment_plan_id'] ?>">
                    <input type="hidden" name="return_url" value="index.php?url=doctor-portal/treatment-plans/history&admission_id=<?= (int) $plan['admission_id'] ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="iconsax" icon-name="x-circle"></i> Dừng kế hoạch điều trị
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
