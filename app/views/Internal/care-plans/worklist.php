<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php
$buildCarePlanUrl = static function (array $overrides = []) use ($filter, $keyword) {
    $params = array_merge([
        'url' => 'internal/care-plans',
        'filter' => $filter,
        'keyword' => $keyword,
    ], $overrides);

    foreach ($params as $key => $value) {
        if ($value === '' || $value === null) {
            unset($params[$key]);
        }
    }

    return 'index.php?' . http_build_query($params);
};
?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Điều phối kế hoạch chăm sóc</h2>
            <p>Theo dõi ca nội trú cần lập kế hoạch, chờ duyệt và kế hoạch đã được phê duyệt.</p>
        </div>
        <div class="page-header-actions">
            <a href="index.php?url=internal/monitoring" class="btn btn-secondary">
                <i class="iconsax" icon-name="hospital"></i> Bệnh nhân nội trú
            </a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success reveal-item">
            <i class="iconsax" icon-name="tick-circle"></i> <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item">
            <i class="iconsax" icon-name="info-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="care-command-grid care-command-grid-five reveal-item">
        <div class="care-command-card is-attention">
            <span>Cần xử lý</span>
            <strong><?= (int) $attentionCount ?></strong>
            <small>Thiếu sinh tồn, thiếu y lệnh, thiếu kế hoạch, y lệnh mới hoặc cần đánh giá lại</small>
        </div>
        <div class="care-command-card is-overdue">
            <span>Quá hạn</span>
            <strong><?= (int) $overdueCount ?></strong>
            <small>Ca đã vượt SLA lập, gửi duyệt hoặc chỉnh sửa kế hoạch</small>
        </div>
        <div class="care-command-card">
            <span>Chờ duyệt</span>
            <strong><?= (int) $pendingCount ?></strong>
            <small>Kế hoạch đã gửi điều dưỡng trưởng</small>
        </div>
        <div class="care-command-card is-approved">
            <span>Đã duyệt</span>
            <strong><?= (int) $approvedCount ?></strong>
            <small>Kế hoạch sẵn sàng triển khai task chăm sóc</small>
        </div>
        <div class="care-command-card">
            <span>Tổng ca nội trú</span>
            <strong><?= (int) $totalAdmissions ?></strong>
            <small>Ca active/stable trong phạm vi phụ trách</small>
        </div>
    </div>

    <div class="card card-elevated reveal-item">
        <div class="card-header card-header-clean care-toolbar">
            <form action="index.php" method="GET" class="search-form search-form-wide">
                <input type="hidden" name="url" value="internal/care-plans">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <div class="search-row search-row-nowrap">
                    <div class="input-with-icon search-field search-field-min">
                        <i class="iconsax" icon-name="search-normal-1"></i>
                        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>"
                            placeholder="Tên / Mã BN / Chẩn đoán / Kế hoạch..." class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="iconsax" icon-name="search-normal-1"></i> Tìm kiếm
                    </button>
                    <?php if ($keyword !== ''): ?>
                        <a href="<?= htmlspecialchars($buildCarePlanUrl(['keyword' => '', 'page' => null])) ?>"
                            class="btn btn-light">
                            <i class="iconsax" icon-name="refresh"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="care-filter-tabs">
                <a class="<?= $filter === 'attention' ? 'is-active' : '' ?>"
                    href="<?= htmlspecialchars($buildCarePlanUrl(['filter' => 'attention', 'page' => null])) ?>">
                    Cần xử lý
                </a>
                <a class="<?= $filter === 'pending' ? 'is-active' : '' ?>"
                    href="<?= htmlspecialchars($buildCarePlanUrl(['filter' => 'pending', 'page' => null])) ?>">
                    Chờ duyệt
                </a>
                <a class="<?= $filter === 'approved' ? 'is-active' : '' ?>"
                    href="<?= htmlspecialchars($buildCarePlanUrl(['filter' => 'approved', 'page' => null])) ?>">
                    Đã duyệt
                </a>
                <a class="<?= $filter === 'all' ? 'is-active' : '' ?>"
                    href="<?= htmlspecialchars($buildCarePlanUrl(['filter' => 'all', 'page' => null])) ?>">
                    Tất cả
                </a>
            </div>
        </div>
    </div>

    <!-- Room strip removed per user request -->

    <div class="card dashboard-table-card reveal-item">
        <div class="worklist-result-bar">
            <span>
                Đang xem <?= (int) $displayStart ?>-<?= (int) $displayEnd ?> / <?= (int) $totalRows ?> ca
            </span>
            <strong>Sắp xếp theo mức ưu tiên xử lý</strong>
        </div>
        <div class="table-responsive">
            <table class="table premium-table care-worklist-table">
                <thead>
                    <tr>
                        <th>Bệnh nhân</th>
                        <th>Kế hoạch chăm sóc</th>
                        <th>Y lệnh / Chẩn đoán</th>
                        <th>Sinh tồn</th>
                        <th class="care-action-header">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($worklist)): ?>
                        <?php foreach ($worklist as $row): ?>
                            <?php
                            $state = $row['plan_state'];
                            $risk = $row['vital_risk'];
                            $sla = $row['sla'];
                            $showSlaBadge = in_array($sla['level'] ?? '', ['overdue', 'due_soon'], true);
                            ?>
                            <tr>
                                <td>
                                    <div class="care-patient-cell">
                                        <div class="care-patient-avatar">
                                            <?= htmlspecialchars(mb_substr($row['patient_name'] ?? 'B', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($row['patient_name'] ?? '---') ?></strong>
                                            <span>
                                                <?= htmlspecialchars($row['patient_code'] ?? '') ?>
                                                <?php if (!empty($row['admission_date'])): ?>
                                                    · Nhập viện: <?= date('d/m/Y', strtotime($row['admission_date'])) ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($showSlaBadge): ?>
                                        <span class="care-sla-pill <?= htmlspecialchars($sla['class']) ?>">
                                            <?= htmlspecialchars($sla['label']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="care-state-pill <?= htmlspecialchars($state['class']) ?>">
                                            <?= htmlspecialchars($state['label']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <div class="care-plan-title">
                                        <?php if (($state['key'] ?? '') === 'outdated'): ?>
                                            <?= htmlspecialchars($row['plan_title'] ?? 'Kế hoạch cũ') ?> · cần lập lại theo y lệnh
                                            mới
                                        <?php elseif (($state['key'] ?? '') === 'vital_changed'): ?>
                                            <?= htmlspecialchars($row['plan_title'] ?? 'Kế hoạch hiện tại') ?> · sinh tồn mới cần
                                            bác sĩ đánh giá lại
                                        <?php elseif (($state['key'] ?? '') === 'no_treatment'): ?>
                                            Chờ bác sĩ lập y lệnh điều trị
                                        <?php else: ?>
                                            <?= !empty($row['plan_title']) ? htmlspecialchars($row['plan_title']) : 'Chưa có kế hoạch chăm sóc' ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($row['care_plan_created_at'])): ?>
                                        <span class="table-text-muted">
                                            <?= date('H:i d/m/Y', strtotime($row['care_plan_created_at'])) ?>
                                            · <?= htmlspecialchars($row['care_plan_created_by_name'] ?? '---') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['treatment_plan_id'])): ?>
                                        <strong><?= htmlspecialchars(mb_substr($row['diagnosis'] ?? 'Chưa ghi chẩn đoán', 0, 70)) ?></strong>
                                        <br><span
                                            class="table-text-muted"><?= htmlspecialchars(mb_substr($row['treatment_goal'] ?? '---', 0, 80)) ?></span>
                                    <?php else: ?>
                                        <span class="care-state-pill risk-high">Thiếu y lệnh</span>
                                        <br><span class="table-text-muted">Cần bác sĩ lập kế hoạch điều trị trước khi gửi
                                            duyệt.</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="vital-risk-pill <?= htmlspecialchars($risk['class']) ?>">
                                        <?= (int) $risk['score'] ?> · <?= htmlspecialchars($risk['label']) ?>
                                    </span>
                                    <?php if (!empty($row['latest_vital_record_id'])): ?>
                                        <br><span class="table-text-muted">
                                            <?= date('H:i d/m', strtotime($row['vital_recorded_at'])) ?>
                                            · SpO2 <?= htmlspecialchars($row['spo2'] ?? '---') ?>%
                                            · HA
                                            <?= htmlspecialchars($row['blood_pressure_systolic'] ?? '---') ?>/<?= htmlspecialchars($row['blood_pressure_diastolic'] ?? '---') ?>
                                        </span>
                                    <?php else: ?>
                                        <br><span class="table-text-muted">Chưa có sinh tồn gần nhất.</span>
                                    <?php endif; ?>
                                </td>
                                <td class="care-action-cell" style="width: 120px; text-align: right;">
                                    <div class="inpatient-actions">
                                        <a href="index.php?url=internal/admissions/show&id=<?= (int) $row['admission_id'] ?>"
                                            class="btn-action btn-action-view" title="Hồ sơ ca">
                                            <i class="iconsax" icon-name="hospital"></i>
                                        </a>

                                        <?php if (!empty($row['care_plan_id'])): ?>
                                            <a href="index.php?url=internal/care-plans/show&id=<?= (int) $row['care_plan_id'] ?>"
                                                class="btn-action btn-action-edit" title="Xem kế hoạch">
                                                <i class="iconsax" icon-name="note-text"></i>
                                            </a>
                                        <?php elseif (!empty($row['treatment_plan_id'])): ?>
                                            <a href="index.php?url=internal/care-plans/create&admission_id=<?= (int) $row['admission_id'] ?>"
                                                class="btn-action btn-action-edit" title="Lập kế hoạch">
                                                <i class="iconsax" icon-name="add"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-state">
                                <i class="iconsax empty-state-icon" icon-name="clipboard-text-1"></i>
                                <p>Không có ca nội trú phù hợp với bộ lọc hiện tại.</p>
                                <a href="index.php?url=internal/care-plans&filter=all" class="btn btn-sm btn-primary">Xem
                                    tất cả</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination pagination-card">
                <?php if ($page > 1): ?>
                    <a href="<?= htmlspecialchars($buildCarePlanUrl(['page' => $page - 1])) ?>"
                        class="pagination-link pagination-link-nav pagination-link-wide pagination-link-icon">
                        <i class="iconsax" icon-name="arrow-left"></i>
                        <span>Trang trước</span>
                    </a>
                <?php endif; ?>

                <div class="pagination-pages">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="<?= htmlspecialchars($buildCarePlanUrl(['page' => $i])) ?>"
                            class="pagination-link <?= $i === $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>

                <?php if ($page < $totalPages): ?>
                    <a href="<?= htmlspecialchars($buildCarePlanUrl(['page' => $page + 1])) ?>"
                        class="pagination-link pagination-link-nav pagination-link-wide pagination-link-icon">
                        <span>Trang sau</span>
                        <i class="iconsax" icon-name="arrow-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
