<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php
$plannedTaskLabels = [
    'vital' => 'Ghi sinh tồn và đánh giá dấu hiệu bất thường',
    'spo2' => 'Theo dõi SpO2, nhịp thở và mức độ khó thở',
    'wound' => 'Chăm sóc vết thương',
    'blood_sugar' => 'Kiểm tra đường huyết',
];

$selectedPlannedTasks = [];
if (!empty($carePlan['planned_tasks'])) {
    $decoded = json_decode($carePlan['planned_tasks'], true);
    if (is_array($decoded)) {
        foreach ($decoded as $key => $val) {
            if (isset($val['times']) && is_array($val['times'])) {
                $selectedPlannedTasks[] = [
                    'label' => !empty($val['label']) ? $val['label'] : ($plannedTaskLabels[$key] ?? $key),
                    'times' => $val['times'],
                    'note'  => $val['note'] ?? ''
                ];
            } else {
                $selectedPlannedTasks[] = [
                    'label' => $plannedTaskLabels[$key] ?? $key,
                    'times' => is_array($val) ? $val : [$val],
                    'note'  => ''
                ];
            }
        }
    }
}
$linkedTreatmentStatus = strtolower(trim((string) ($carePlan['treatment_plan_status'] ?? '')));
$linkedTreatmentActive = $linkedTreatmentStatus === 'active';
?>

<div class="admin-content">
<div class="page-header reveal-item">
    <div class="page-header-info">
        <h2><?= htmlspecialchars($carePlan['plan_title'] ?? 'Kế hoạch chăm sóc') ?></h2>
        <p><?= htmlspecialchars($carePlan['patient_name'] ?? '') ?> &mdash; <?= htmlspecialchars($carePlan['patient_code'] ?? '') ?></p>
    </div>
    <div class="page-header-actions">
        <a href="index.php?url=doctor-portal/admissions/show&id=<?= (int) $carePlan['admission_id'] ?>" class="btn btn-secondary">
            <i class="iconsax" icon-name="hospital"></i> Xem chi tiết ca nội trú
        </a>
        <a href="index.php?url=doctor-portal/care-plans" class="btn btn-light">
            <i class="iconsax" icon-name="arrow-left"></i> Trở về danh sách
        </a>
    </div>
</div>

<?php if (!empty($carePlan['treatment_plan_id']) && !$linkedTreatmentActive): ?>
    <div class="alert alert-warning reveal-item">
        <i class="iconsax" icon-name="info-circle"></i>
        Kế hoạch điều trị liên kết đã kết thúc. Kế hoạch chăm sóc này chỉ còn là bản tham chiếu lịch sử.
    </div>
<?php endif; ?>

<div class="content-grid">
    <div class="content-main">
        <div class="card reveal-item">
            <div class="card-header">
                <h3><i class="iconsax" icon-name="note-text"></i> Nội dung chăm sóc</h3>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-field">
                        <span class="detail-label">Nhận định bệnh nhân:</span>
                        <p class="detail-text">
                            <?= htmlspecialchars($carePlan['assessment_summary'] ?? 'Chưa có') ?>
                        </p>
                    </div>
                    <div class="detail-field">
                        <span class="detail-label">Chẩn đoán điều dưỡng:</span>
                        <p class="detail-text">
                            <?= htmlspecialchars($carePlan['nursing_diagnosis'] ?? 'Chưa có') ?>
                        </p>
                    </div>
                    <div class="detail-field">
                        <span class="detail-label">Mục tiêu chăm sóc:</span>
                        <p class="detail-text">
                            <?= htmlspecialchars($carePlan['care_goal'] ?? 'Chưa có') ?>
                        </p>
                    </div>
                    <div class="detail-field">
                        <span class="detail-label">Kế hoạch can thiệp:</span>
                        <p class="detail-text">
                            <?= htmlspecialchars($carePlan['intervention_plan'] ?? 'Chưa có') ?>
                        </p>
                    </div>
                    <?php if (!empty($carePlan['monitoring_frequency'])): ?>
                        <div class="detail-field detail-divider">
                            <span class="detail-label">Tần suất theo dõi:</span>
                            <p class="detail-text detail-text-strong"><?= htmlspecialchars($carePlan['monitoring_frequency']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card reveal-item delay-1">
            <div class="card-header">
                <h3><i class="iconsax" icon-name="task-list-square"></i> Các đầu việc điều dưỡng thực hiện</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($selectedPlannedTasks)): ?>
                    <div class="task-stack">
                        <?php foreach ($selectedPlannedTasks as $task): ?>
                            <div class="task-card">
                                <div class="task-card-row">
                                    <div class="task-card-main">
                                        <strong class="task-card-title"><?= htmlspecialchars($task['label']) ?></strong>
                                        <?php if (!empty($task['note'])): ?>
                                            <p class="table-text-muted task-card-note">
                                                <i class="iconsax" icon-name="note"></i>
                                                <?= htmlspecialchars($task['note']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="task-time-list">
                                        <?php foreach ($task['times'] as $time): ?>
                                            <span class="badge badge-info badge-strong"><?= htmlspecialchars($time) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>Chưa có đầu việc cụ thể được lập.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="content-sidebar">
        <div class="card reveal-item">
            <div class="card-header">
                <h3><i class="iconsax" icon-name="info-circle"></i> Trạng thái & Phê duyệt</h3>
            </div>
            <div class="card-body">
                <div class="meta-stack">
                <div class="meta-block">
                    <span class="detail-label">Trạng thái hiện tại:</span>
                    <?php 
                    $cs = $carePlan['status'] ?? 'draft'; 
                    $badgeClass = $cs === 'approved' ? 'badge-active' : ($cs === 'pending' ? 'badge-info' : ($cs === 'rejected' ? 'badge-danger' : 'badge-inactive'));
                    $statusText = $cs === 'approved' ? 'Đã duyệt' : ($cs === 'pending' ? 'Chờ duyệt' : ($cs === 'rejected' ? 'Bị từ chối' : 'Nháp'));
                    ?>
                    <span class="badge <?= $badgeClass ?> status-badge-lg">
                        <?= htmlspecialchars($statusText) ?>
                    </span>
                </div>

                <div class="meta-block">
                    <span class="detail-label">Người lập kế hoạch:</span>
                    <strong class="detail-value"><?= htmlspecialchars($carePlan['created_by_name'] ?? '---') ?></strong>
                    <span class="meta-small">
                        Lúc <?= !empty($carePlan['created_at']) ? date('H:i d/m/Y', strtotime($carePlan['created_at'])) : '---' ?>
                    </span>
                </div>

                <?php if (!empty($latestApproval)): ?>
                    <?php $approvalTone = $latestApproval['approval_status'] === 'rejected' ? 'is-rejected' : 'is-approved'; ?>
                    <div class="approval-note-card <?= $approvalTone ?>">
                        <h4 class="approval-note-title">
                            <i class="iconsax" icon-name="message-dots-favorite"></i> Phản hồi của ĐD Trưởng
                        </h4>
                        <p class="approval-note-text">
                            "<?= htmlspecialchars($latestApproval['approval_note'] ?: 'Không có ghi chú thêm.') ?>"
                        </p>
                        <div class="approval-note-meta">
                            <span><?= htmlspecialchars($latestApproval['approved_by_name']) ?></span>
                            <span><?= date('H:i d/m/Y', strtotime($latestApproval['approved_at'])) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($carePlan['diagnosis'])): ?>
            <div class="card reveal-item delay-1">
                <div class="card-header">
                    <h3><i class="iconsax" icon-name="clipboard-text-1"></i> Dựa trên kế hoạch điều trị</h3>
                </div>
                <div class="card-body">
                    <div class="detail-block detail-block-spaced">
                        <span class="detail-label">Chẩn đoán của bác sĩ:</span>
                        <p class="detail-text detail-text-strong"><?= htmlspecialchars($carePlan['diagnosis']) ?></p>
                    </div>
                    <div class="detail-block detail-block-spaced">
                        <span class="detail-label">Mục tiêu điều trị:</span>
                        <p class="detail-text"><?= htmlspecialchars($carePlan['treatment_goal'] ?? '---') ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
