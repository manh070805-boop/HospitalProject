<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Lịch sử kế hoạch chăm sóc</h2>
            <p>Bệnh nhân: <?= htmlspecialchars($admission['patient_name']) ?> (Mã: <?= htmlspecialchars($admission['patient_code']) ?>)</p>
        </div>
        <div class="page-header-actions">
            <a href="index.php?url=internal/admissions/show&id=<?= (int)$admission['admission_id'] ?>" class="btn btn-secondary">
                <i class="iconsax" icon-name="arrow-left"></i> Trở về
            </a>
            <?php if (in_array($admission['status'], ['active', 'stable'])): ?>
                <a href="index.php?url=internal/care-plans/create&admission_id=<?= (int)$admission['admission_id'] ?>" class="btn btn-primary">
                    <i class="iconsax" icon-name="add"></i> Lập kế hoạch mới
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($carePlans)): ?>
        <div class="card reveal-item">
            <div class="card-body">
                <div class="empty-state">
                    <i class="iconsax empty-state-icon" icon-name="clipboard-text-1"></i>
                    <p>Chưa có kế hoạch chăm sóc nào cho đợt điều trị này.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card dashboard-table-card reveal-item">
            <table class="table premium-table" style="table-layout: fixed; width: 100%;">
                <colgroup>
                    <col style="width: 110px;">  <!-- Ngày lập -->
                    <col style="width: 28%;">    <!-- Chẩn đoán điều dưỡng -->
                    <col style="width: auto;">   <!-- Mục tiêu - chiếm phần còn lại -->
                    <col style="width: 130px;">  <!-- Điều dưỡng -->
                    <col style="width: 110px;">  <!-- Trạng thái -->
                    <col style="width: 70px;">   <!-- Thao tác -->
                </colgroup>
                <thead>
                    <tr>
                        <th>Ngày lập</th>
                        <th>Chẩn đoán điều dưỡng</th>
                        <th>Mục tiêu</th>
                        <th>Điều dưỡng</th>
                        <th>Trạng thái</th>
                        <th class="text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($carePlans as $cp): ?>
                        <tr>
                            <td style="white-space: nowrap;">
                                <strong><?= date('H:i', strtotime($cp['created_at'])) ?></strong>
                                <br><span class="table-text-muted" style="font-size:12px;"><?= date('d/m/Y', strtotime($cp['created_at'])) ?></span>
                            </td>
                            <td style="word-break: break-word;">
                                <strong class="text-primary"><?= htmlspecialchars($cp['nursing_diagnosis']) ?></strong>
                            </td>
                            <td class="table-text-muted" style="word-break: break-word; font-size: 13px;">
                                <?= htmlspecialchars(mb_substr($cp['care_goal'] ?? '', 0, 80)) ?><?= mb_strlen($cp['care_goal'] ?? '') > 80 ? '...' : '' ?>
                            </td>
                            <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= htmlspecialchars($cp['created_by_name'] ?? '---') ?>
                            </td>
                            <td style="white-space: nowrap;">
                                <?php 
                                    $statusClass = 'badge-pending';
                                    $statusText = 'Chờ duyệt';
                                    switch($cp['status']) {
                                        case 'approved': $statusClass = 'badge-active'; $statusText = 'Đã duyệt'; break;
                                        case 'rejected': $statusClass = 'badge-inactive'; $statusText = 'Từ chối'; break;
                                        case 'draft': $statusClass = 'badge-light'; $statusText = 'Nháp'; break;
                                    }
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td class="text-right" style="white-space: nowrap;">
                                <a href="index.php?url=internal/care-plans/show&id=<?= (int)$cp['care_plan_id'] ?>" class="btn-action btn-action-view" title="Xem chi tiết">
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
