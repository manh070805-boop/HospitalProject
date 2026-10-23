<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Kế hoạch điều trị</h2>
            <p>Các ca đang có kế hoạch active hoặc chưa có kế hoạch điều trị active</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success reveal-item"><i class="iconsax"
                icon-name="tick-circle"></i><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item"><i class="iconsax"
                icon-name="info-circle"></i><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="card dashboard-table-card reveal-item">
        <div class="card-header">
            <div>
                <h3><i class="iconsax" icon-name="clipboard-text-1"></i> Kế hoạch điều trị</h3>
                <p><?= count($plans) ?> ca phù hợp</p>
            </div>
        </div>

        <div class="card-body">
            <form method="GET" action="index.php" class="search-form search-form-wide mb-3">
                <input type="hidden" name="url" value="doctor-portal/treatment-plans">
                <div class="search-row">
                    <div class="input-with-icon search-field">
                        <i class="iconsax" icon-name="search-normal-1"></i>
                        <input type="text" name="keyword" class="form-control"
                            value="<?= htmlspecialchars($keyword ?? '') ?>"
                            placeholder="Tìm theo mã BN, tên bệnh nhân, chẩn đoán hoặc y lệnh">
                    </div>

                    <select name="status" class="form-control search-select search-select-min-180">
                        <option value="" <?= ($status ?? '') === '' ? 'selected' : '' ?>>Tất cả</option>
                        <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Đang active</option>
                        <option value="missing" <?= ($status ?? '') === 'missing' ? 'selected' : '' ?>>Chưa có active</option>
                    </select>

                    <button type="submit" class="btn btn-primary">
                        <i class="iconsax" icon-name="search-normal-1"></i> Lọc
                    </button>
                </div>
            </form>

            <?php if (empty($plans)): ?>
                <div class="empty-state">
                    <i class="iconsax empty-state-icon" icon-name="clipboard-text-1"></i>
                    <p>Chưa có kế hoạch điều trị phù hợp.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table premium-table">
                        <thead>
                            <tr>
                                <th>Bệnh nhân</th>
                                <th>Chẩn đoán</th>
                                <th>Y lệnh</th>
                                <th>Trạng thái</th>
                                <th>Cập nhật</th>
                                <th class="plan-actions-cell">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($plans as $plan): ?>
                                <?php
                                $hasPlan = !empty($plan['treatment_plan_id']);
                                $statusLabel = $hasPlan ? 'Active' : 'Chưa có';
                                $statusClass = $hasPlan ? 'badge-active' : 'badge-inactive';
                                $returnUrl = 'index.php?url=doctor-portal/treatment-plans';
                                $openAlertCount = (int) ($plan['open_alert_count'] ?? 0);
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($plan['patient_name'] ?? '---') ?></strong><br>
                                        <span class="table-text-muted">
                                            <?= htmlspecialchars($plan['patient_code'] ?? '---') ?>
                                            · <?= htmlspecialchars($plan['room_name'] ?? '---') ?>
                                            / <?= htmlspecialchars($plan['bed_code'] ?? '---') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($hasPlan): ?>
                                            <strong><?= htmlspecialchars(mb_substr($plan['diagnosis'] ?? '---', 0, 70)) ?></strong>
                                        <?php else: ?>
                                            <span class="table-text-muted">Chưa có kế hoạch điều trị active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="table-text-muted">
                                        <?= $hasPlan ? htmlspecialchars(mb_substr($plan['medical_order'] ?? '---', 0, 90)) : 'Cần lập y lệnh mới' ?>
                                    </td>
                                    <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                                    <td>
                                        <?php $changedAt = $plan['updated_at'] ?? $plan['created_at'] ?? null; ?>
                                        <?= !empty($changedAt) ? date('H:i d/m/Y', strtotime($changedAt)) : '---' ?>
                                    </td>
                                    <td class="plan-actions-cell">
                                        <div class="actions-row plan-actions-row">
                                            <?php if ($hasPlan): ?>
                                                <a href="index.php?url=doctor-portal/treatment-plans/show&id=<?= (int) $plan['treatment_plan_id'] ?>"
                                                    class="btn-action btn-action-view" title="Xem kế hoạch điều trị">
                                                    <i class="iconsax" icon-name="eye"></i>
                                                </a>

                                                <a href="index.php?url=doctor-portal/treatment-plans/history&admission_id=<?= (int) $plan['admission_id'] ?>"
                                                    class="btn-action btn-action-info" title="Lịch sử kế hoạch điều trị">
                                                    <i class="iconsax" icon-name="history"></i>
                                                </a>

                                                <?php if ($openAlertCount > 0): ?>
                                                    <button type="button" class="btn-action btn-action-edit" disabled
                                                        title="Còn <?= $openAlertCount ?> cảnh báo chưa xử lý">
                                                        <i class="iconsax" icon-name="tick-circle"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <form method="POST" action="index.php?url=doctor-portal/treatment-plans/complete">
                                                        <input type="hidden" name="treatment_plan_id"
                                                            value="<?= (int) $plan['treatment_plan_id'] ?>">
                                                        <input type="hidden" name="return_url"
                                                            value="<?= htmlspecialchars($returnUrl) ?>">
                                                        <button type="submit" class="btn-action btn-action-edit"
                                                            title="Hoàn tất kế hoạch điều trị">
                                                            <i class="iconsax" icon-name="tick-circle"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="index.php?url=doctor-portal/treatment-plans/create&admission_id=<?= (int) $plan['admission_id'] ?>"
                                                    class="btn-action btn-action-edit" title="Lập kế hoạch điều trị">
                                                    <i class="iconsax" icon-name="add"></i>
                                                </a>

                                                <a href="index.php?url=doctor-portal/treatment-plans/history&admission_id=<?= (int) $plan['admission_id'] ?>"
                                                    class="btn-action btn-action-info" title="Lịch sử kế hoạch điều trị">
                                                    <i class="iconsax" icon-name="history"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
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
