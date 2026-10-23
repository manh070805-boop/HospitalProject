<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
<div class="page-header reveal-item">
    <div class="page-header-info">
        <h2>Kế hoạch chăm sóc</h2>
        <p>Theo dõi kế hoạch chăm sóc điều dưỡng lập dựa trên kế hoạch điều trị của bác sĩ</p>
    </div>
</div>

<div class="card dashboard-table-card reveal-item">
    <div class="card-header">
        <div>
            <h3><i class="iconsax" icon-name="note-text"></i> Kế hoạch chăm sóc mới nhất</h3>
            <p><?= count($carePlans) ?> bệnh nhân (Hiển thị bản ghi mới nhất cho mỗi ca nội trú)</p>
        </div>
    </div>

    <div class="card-body">
        <form method="GET" action="index.php" class="search-form search-form-wide mb-3">
            <input type="hidden" name="url" value="doctor-portal/care-plans">
            <div class="search-row">
                <div class="input-with-icon search-field">
                    <i class="iconsax" icon-name="search-normal-1"></i>
                    <input type="text" name="keyword" class="form-control"
                        value="<?= htmlspecialchars($keyword ?? '') ?>"
                        placeholder="Tìm theo bệnh nhân, tiêu đề kế hoạch chăm sóc hoặc chẩn đoán">
                </div>

                <select name="status" class="form-control search-select search-select-min-180">
                    <option value="" <?= ($status ?? '') === '' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                    <option value="draft" <?= ($status ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= ($status ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= ($status ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>

                <button type="submit" class="btn btn-primary">
                    <i class="iconsax" icon-name="search-normal-1"></i> Lọc
                </button>
            </div>
        </form>

        <?php if (empty($carePlans)): ?>
            <div class="empty-state">
                <i class="iconsax empty-state-icon" icon-name="note-text"></i>
                <p>Chưa có kế hoạch chăm sóc phù hợp.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table premium-table">
                    <thead>
                        <tr>
                            <th>Bệnh nhân</th>
                            <th>Kế hoạch chăm sóc</th>
                            <th>Kế hoạch điều trị liên kết</th>
                            <th>Trạng thái</th>
                            <th>Người tạo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($carePlans as $plan): ?>
                            <?php
                            $careStatus = $plan['status'] ?? 'draft';
                            $statusClass = $careStatus === 'approved' ? 'badge-active' : ($careStatus === 'pending' ? 'badge-info' : 'badge-inactive');
                            $needsReview = !empty($plan['treatment_plan_id'])
                                && !empty($plan['active_treatment_plan_id'])
                                && (int) $plan['treatment_plan_id'] !== (int) $plan['active_treatment_plan_id'];
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($plan['patient_name'] ?? '---') ?></strong><br>
                                    <span class="table-text-muted">
                                        <?= htmlspecialchars($plan['patient_code'] ?? '---') ?>
                                        · <?= htmlspecialchars($plan['room_name'] ?? '---') ?>/<?= htmlspecialchars($plan['bed_code'] ?? '---') ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($plan['plan_title'] ?? 'Không có tiêu đề') ?></strong>
                                    <p class="table-text-muted alert-item-text">
                                        <?= htmlspecialchars(mb_substr($plan['assessment_summary'] ?? '', 0, 90)) ?>
                                    </p>
                                </td>
                                <td>
                                    <?php if (!empty($plan['treatment_plan_id'])): ?>
                                        <span class="badge <?= $needsReview ? 'badge-danger' : 'badge-active' ?>">
                                            TP #<?= (int) $plan['treatment_plan_id'] ?>
                                        </span>
                                        <?php if ($needsReview): ?>
                                            <div class="table-text-muted mt-2 text-danger">
                                                Cần rà soát theo kế hoạch điều trị active mới
                                            </div>
                                        <?php else: ?>
                                            <div class="table-text-muted mt-2">
                                                <?= htmlspecialchars(mb_substr($plan['diagnosis'] ?? '', 0, 60)) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-inactive">Chưa gắn</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($careStatus) ?></span></td>
                                <td>
                                    <?= htmlspecialchars($plan['created_by_name'] ?? '---') ?><br>
                                    <span class="table-text-muted"><?= !empty($plan['created_at']) ? date('H:i d/m/Y', strtotime($plan['created_at'])) : '---' ?></span>
                                </td>
                                <td class="text-right">
                                    <a href="index.php?url=doctor-portal/care-plans/show&id=<?= (int) $plan['care_plan_id'] ?>" class="btn-action btn-action-view" title="Xem chi tiết kế hoạch">
                                        <i class="iconsax" icon-name="eye"></i>
                                    </a>
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
