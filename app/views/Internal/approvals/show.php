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
?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Xem xét kế hoạch chăm sóc</h2>
            <p><?= htmlspecialchars($carePlan['patient_name'] ?? '') ?> &mdash; <?= htmlspecialchars($carePlan['patient_code'] ?? '') ?></p>
        </div>
        <a href="index.php?url=internal/approvals" class="btn btn-secondary">
            <i class="iconsax" icon-name="arrow-left"></i> Hàng đợi duyệt
        </a>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px;">
        <div>
            <div class="card reveal-item" style="margin-bottom:20px;">
                <div class="card-header"><h4><?= htmlspecialchars($carePlan['plan_title'] ?? '') ?></h4></div>
                <div class="card-body" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <span class="table-text-muted">Nhận định bệnh nhân:</span>
                        <p style="margin:4px 0 0; white-space:pre-line;"><?= htmlspecialchars($carePlan['assessment_summary'] ?? '---') ?></p>
                    </div>
                    <div>
                        <span class="table-text-muted">Chẩn đoán điều dưỡng:</span>
                        <p style="margin:4px 0 0; white-space:pre-line;"><?= htmlspecialchars($carePlan['nursing_diagnosis'] ?? '---') ?></p>
                    </div>
                    <div>
                        <span class="table-text-muted">Mục tiêu chăm sóc:</span>
                        <p style="margin:4px 0 0; white-space:pre-line;"><?= htmlspecialchars($carePlan['care_goal'] ?? '---') ?></p>
                    </div>
                    <div>
                        <span class="table-text-muted">Kế hoạch can thiệp:</span>
                        <p style="margin:4px 0 0; white-space:pre-line;"><?= htmlspecialchars($carePlan['intervention_plan'] ?? '---') ?></p>
                    </div>
                    <?php if (!empty($carePlan['monitoring_frequency'])): ?>
                        <div style="grid-column:span 2;">
                            <span class="table-text-muted">Tần suất theo dõi:</span>
                            <p style="margin:4px 0 0;"><?= htmlspecialchars($carePlan['monitoring_frequency']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card reveal-item delay-1" style="margin-bottom:20px;">
                <div class="card-header">
                    <h4>Đầu việc thực hiện dự kiến</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($selectedPlannedTasks)): ?>
                        <div style="display:flex; flex-direction:column; gap:12px;">
                            <?php foreach ($selectedPlannedTasks as $task): ?>
                                <div style="display:flex; flex-direction:column; gap:6px; border-bottom: 1px solid #f3f4f6; padding-bottom: 10px;">
                                    <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start;">
                                        <div style="display:flex; flex-direction:column; gap:2px;">
                                            <strong style="color: #111827;"><?= htmlspecialchars($task['label']) ?></strong>
                                            <?php if (!empty($task['note'])): ?>
                                                <small style="color: #6b7280; font-style: italic;">Ghi chú: <?= htmlspecialchars($task['note']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div style="display:flex; flex-wrap:wrap; gap:4px; justify-content:flex-end;">
                                            <?php foreach ($task['times'] as $time): ?>
                                                <span class="badge badge-info" style="font-size: 11px; padding: 2px 8px;"><?= htmlspecialchars($time) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="margin:0; color:#6b7280;">Chưa có đầu việc nào được chọn.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($carePlan['diagnosis'])): ?>
                <div class="card reveal-item delay-1">
                    <div class="card-header"><h4>Kế hoạch điều trị liên kết (Bác sĩ)</h4></div>
                    <div class="card-body" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div><span class="table-text-muted">Chẩn đoán:</span> <strong><?= htmlspecialchars($carePlan['diagnosis']) ?></strong></div>
                        <div><span class="table-text-muted">Mục tiêu điều trị:</span> <?= htmlspecialchars($carePlan['treatment_goal'] ?? '---') ?></div>
                        <?php if (!empty($carePlan['medical_order'])): ?>
                            <div style="grid-column:span 2;"><span class="table-text-muted">Y lệnh:</span>
                                <pre style="white-space:pre-wrap; font-family:inherit; margin:4px 0 0;"><?= htmlspecialchars($carePlan['medical_order']) ?></pre>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div>
            <div class="card reveal-item">
                <div class="card-header"><h4>Quyết định</h4></div>
                <div class="card-body" style="display:flex; flex-direction:column; gap:14px;">
                    <div><span class="table-text-muted">Điều dưỡng:</span> <strong><?= htmlspecialchars($carePlan['created_by_name'] ?? '---') ?></strong></div>
                    <div><span class="table-text-muted">Ngày gửi:</span> <?= !empty($carePlan['created_at']) ? date('H:i d/m/Y', strtotime($carePlan['created_at'])) : '---' ?></div>

                    <hr style="border:none; border-top:1px solid var(--border-subtle);">

                    <form action="index.php?url=internal/approvals/approve" method="POST"
                          onsubmit="return confirm('Xác nhận duyệt kế hoạch này? Task sẽ được tự động tạo.');">
                        <input type="hidden" name="care_plan_id" value="<?= (int) $carePlan['care_plan_id'] ?>">
                        <div class="form-group">
                            <label for="approve_note">Ghi chú (tùy chọn)</label>
                            <textarea name="approval_note" id="approve_note" class="form-control" rows="2"
                                      placeholder="Kế hoạch phù hợp, đồng ý..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%;">
                            <i class="iconsax" icon-name="tick-circle"></i> Duyệt kế hoạch
                        </button>
                    </form>

                    <form action="index.php?url=internal/approvals/reject" method="POST"
                          onsubmit="return confirm('Xác nhận từ chối kế hoạch này?');">
                        <input type="hidden" name="care_plan_id" value="<?= (int) $carePlan['care_plan_id'] ?>">
                        <div class="form-group">
                            <label for="reject_note">Lý do từ chối <span class="required-star">*</span></label>
                            <textarea name="approval_note" id="reject_note" class="form-control" rows="2"
                                      placeholder="Cần bổ sung thêm..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger" style="width:100%;">
                            <i class="iconsax" icon-name="x-circle"></i> Từ chối
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
