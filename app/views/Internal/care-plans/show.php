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
                    'note' => $val['note'] ?? ''
                ];
            } else {
                $selectedPlannedTasks[] = [
                    'label' => $plannedTaskLabels[$key] ?? $key,
                    'times' => is_array($val) ? $val : [$val],
                    'note' => ''
                ];
            }
        }
    }
}
$linkedTreatmentStatus = strtolower(trim((string) ($carePlan['treatment_plan_status'] ?? '')));
$linkedTreatmentActive = $linkedTreatmentStatus === 'active';
$isDraft = ($carePlan['status'] ?? '') === 'draft';
$canEditDraft = $isDraft && $linkedTreatmentActive;
?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2><?= htmlspecialchars($carePlan['plan_title'] ?? 'Kế hoạch chăm sóc') ?></h2>
            <p><?= htmlspecialchars($carePlan['patient_name'] ?? '') ?> &mdash;
                <?= htmlspecialchars($carePlan['patient_code'] ?? '') ?>
            </p>
        </div>
        <div style="display:flex; gap:8px;">
            <?php if ($isDraft): ?>
                <?php if ($canEditDraft): ?>
                    <a href="index.php?url=internal/care-plans/edit&id=<?= (int) $carePlan['care_plan_id'] ?>"
                        class="btn btn-secondary">
                        <i class="iconsax" icon-name="edit-2"></i> Chỉnh sửa
                    </a>
                <?php endif; ?>
                <form action="index.php?url=internal/care-plans/delete" method="POST"
                    onsubmit="return confirm('Xóa nháp kế hoạch chăm sóc này? Thao tác này không thể hoàn tác.');">
                    <input type="hidden" name="care_plan_id" value="<?= (int) $carePlan['care_plan_id'] ?>">
                    <input type="hidden" name="return_url"
                        value="index.php?url=internal/care-plans&admission_id=<?= (int) $carePlan['admission_id'] ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="iconsax" icon-name="trash"></i> Xóa nháp
                    </button>
                </form>
            <?php endif; ?>
            <a href="index.php?url=internal/admissions/show&id=<?= (int) $carePlan['admission_id'] ?>"
                class="btn btn-light">
                <i class="iconsax" icon-name="arrow-left"></i> Trở về
            </a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success reveal-item"><i class="iconsax" icon-name="tick-circle"></i>
            <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item"><i class="iconsax" icon-name="info-circle"></i>
            <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    <?php if (!empty($carePlan['treatment_plan_id']) && !$linkedTreatmentActive): ?>
        <div class="alert alert-warning reveal-item">
            <i class="iconsax" icon-name="info-circle"></i>
            Kế hoạch điều trị liên kết đã kết thúc. Kế hoạch chăm sóc này chỉ còn giá trị lịch sử và không dùng để sinh công
            việc chăm sóc mới.
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px;">
        <div>
            <div class="card reveal-item" style="margin-bottom:20px;">
                <div class="card-header">
                    <h4>Nội dung chăm sóc</h4>
                </div>
                <div class="card-body" style="display:grid; grid-template-columns: 1fr 1fr; gap:18px;">
                    <div>
                        <span class="table-text-muted">Nhận định bệnh nhân:</span>
                        <p style="margin:4px 0 0; white-space:pre-line;">
                            <?= htmlspecialchars($carePlan['assessment_summary'] ?? 'Chưa có') ?>
                        </p>
                    </div>
                    <div>
                        <span class="table-text-muted">Chẩn đoán điều dưỡng:</span>
                        <p style="margin:4px 0 0; white-space:pre-line;">
                            <?= htmlspecialchars($carePlan['nursing_diagnosis'] ?? 'Chưa có') ?>
                        </p>
                    </div>
                    <div>
                        <span class="table-text-muted">Mục tiêu chăm sóc:</span>
                        <p style="margin:4px 0 0; white-space:pre-line;">
                            <?= htmlspecialchars($carePlan['care_goal'] ?? 'Chưa có') ?>
                        </p>
                    </div>
                    <div>
                        <span class="table-text-muted">Kế hoạch can thiệp:</span>
                        <p style="margin:4px 0 0; white-space:pre-line;">
                            <?= htmlspecialchars($carePlan['intervention_plan'] ?? 'Chưa có') ?>
                        </p>
                    </div>
                    <?php if (!empty($carePlan['monitoring_frequency'])): ?>
                        <div style="grid-column: span 2;">
                            <span class="table-text-muted">Tần suất theo dõi:</span>
                            <p style="margin:4px 0 0;"><?= htmlspecialchars($carePlan['monitoring_frequency']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card reveal-item delay-1" style="margin-bottom:20px;">
                <div class="card-header">
                    <h4>Đầu việc thực hiện</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($selectedPlannedTasks)): ?>
                        <div style="display:flex; flex-direction:column; gap:16px;">
                            <?php foreach ($selectedPlannedTasks as $task): ?>
                                <div
                                    style="display:flex; flex-direction:column; gap:8px; border-bottom: 1px solid #f3f4f6; padding-bottom: 12px;">
                                    <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start;">
                                        <div style="display:flex; flex-direction:column; gap:4px;">
                                            <strong style="color: #111827;"><?= htmlspecialchars($task['label']) ?></strong>
                                            <?php if (!empty($task['note'])): ?>
                                                <div
                                                    style="display:flex; align-items:center; gap:6px; color: #6b7280; font-size: 13px;">
                                                    <i class="iconsax" icon-name="note" style="font-size:14px;"></i>
                                                    <span><?= htmlspecialchars($task['note']) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div style="display:flex; flex-wrap:wrap; gap:6px; justify-content:flex-end;">
                                            <?php foreach ($task['times'] as $time): ?>
                                                <span class="badge badge-info"
                                                    style="font-weight: 500;"><?= htmlspecialchars($time) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="margin:0;">Chưa chọn đầu việc thực hiện cụ thể.</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($carePlan['diagnosis'])): ?>
                <div class="card reveal-item delay-1" style="margin-bottom:20px;">
                    <div class="card-header">
                        <h4><i class="iconsax" icon-name="clipboard-text-1" style="font-size:14px;"></i> Kế hoạch điều trị
                            liên kết</h4>
                    </div>
                    <div class="card-body">
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                            <div><span class="table-text-muted">Chẩn đoán bác sĩ:</span>
                                <strong><?= htmlspecialchars($carePlan['diagnosis']) ?></strong>
                            </div>
                            <div><span class="table-text-muted">Mục tiêu điều trị:</span>
                                <?= htmlspecialchars($carePlan['treatment_goal'] ?? '---') ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Cột phải: meta + actions -->
        <div>
            <div class="card reveal-item" style="margin-bottom:20px;">
                <div class="card-header">
                    <h4>Thông tin</h4>
                </div>
                <div class="card-body" style="display:flex; flex-direction:column; gap:12px;">
                    <div>
                        <span class="table-text-muted">Trạng thái:</span><br>
                        <?php $cs = $carePlan['status'] ?? 'draft'; ?>
                        <?php if ($cs === 'approved'): ?>
                            <span class="badge badge-active">Đã duyệt</span>
                        <?php elseif ($cs === 'pending'): ?>
                            <span class="badge badge-info">Chờ duyệt</span>
                        <?php elseif ($cs === 'rejected'): ?>
                            <span class="badge badge-discharged">Bị từ chối</span>
                        <?php else: ?>
                            <span class="badge badge-inactive">Nháp</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="table-text-muted">Người tạo:</span>
                        <?= htmlspecialchars($carePlan['created_by_name'] ?? '---') ?>
                    </div>
                    <div>
                        <span class="table-text-muted">Ngày tạo:</span>
                        <?= !empty($carePlan['created_at']) ? date('H:i d/m/Y', strtotime($carePlan['created_at'])) : '---' ?>
                    </div>
                    <div style="margin-top: 10px;">
                        <button type="button" class="btn btn-secondary btn-sm"
                            onclick="var p=document.getElementById('approvalHistoryPanel'); p.style.display = p.style.display==='none'?'block':'none';"
                            style="width: 100%;">
                            <i class="iconsax" icon-name="history"></i> Lịch sử phê duyệt
                        </button>
                    </div>
                </div>
            </div>

            <?php if (!empty($latestApproval)): ?>
                <div class="card reveal-item delay-1 <?= $latestApproval['approval_status'] === 'rejected' ? 'border-danger' : 'border-success' ?>"
                    style="margin-bottom:20px; background: <?= $latestApproval['approval_status'] === 'rejected' ? '#fff5f5' : '#f0fff4' ?>;">
                    <div class="card-header">
                        <h4
                            style="color: <?= $latestApproval['approval_status'] === 'rejected' ? '#c53030' : '#2f855a' ?>;">
                            <i class="iconsax" icon-name="message-text"></i> Phản hồi của ĐDT
                        </h4>
                    </div>
                    <div class="card-body">
                        <p style="margin:0 0 8px; color: #2d3748; font-style: italic; font-size: 14px;">
                            "<?= htmlspecialchars($latestApproval['approval_note'] ?: 'Không có ghi chú thêm.') ?>"
                        </p>
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; font-size: 12px; color: #718096; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 8px;">
                            <span><?= htmlspecialchars($latestApproval['approved_by_name']) ?></span>
                            <span><?= date('H:i d/m/Y', strtotime($latestApproval['approved_at'])) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <?php if ($isDraft): ?>
                <div class="card reveal-item delay-1">
                    <div class="card-header">
                        <h4>Hành động</h4>
                    </div>
                    <div class="card-body" style="display:flex; flex-direction:column; gap:10px;">
                        <?php if ($canEditDraft): ?>
                            <a href="index.php?url=internal/care-plans/edit&id=<?= (int) $carePlan['care_plan_id'] ?>"
                                class="btn btn-secondary">
                                <i class="iconsax" icon-name="edit-2"></i> Chỉnh sửa nháp
                            </a>
                            <form action="index.php?url=internal/care-plans/submit" method="POST"
                                onsubmit="return confirm('Gửi kế hoạch này cho điều dưỡng trưởng duyệt?');">
                                <input type="hidden" name="care_plan_id" value="<?= (int) $carePlan['care_plan_id'] ?>">
                                <button type="submit" class="btn btn-primary" style="width:100%;">
                                    <i class="iconsax" icon-name="send-2"></i> Gửi chờ duyệt
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning" style="margin:0;">
                                Không thể chỉnh sửa hoặc gửi duyệt vì kế hoạch điều trị liên kết không còn active.
                            </div>
                        <?php endif; ?>
                        <form action="index.php?url=internal/care-plans/delete" method="POST"
                            onsubmit="return confirm('Xóa nháp kế hoạch chăm sóc này? Thao tác này không thể hoàn tác.');">
                            <input type="hidden" name="care_plan_id" value="<?= (int) $carePlan['care_plan_id'] ?>">
                            <input type="hidden" name="return_url"
                                value="index.php?url=internal/care-plans&admission_id=<?= (int) $carePlan['admission_id'] ?>">
                            <button type="submit" class="btn btn-danger" style="width:100%;">
                                <i class="iconsax" icon-name="trash"></i> Xóa nháp
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Approval History Panel (PHP-rendered, no AJAX) -->
<div id="approvalHistoryPanel" style="display:none; margin-top:16px;">
    <div class="card">
        <div class="card-header">
            <h4><i class="iconsax" icon-name="history" style="font-size:14px;"></i> Lịch sử phê duyệt</h4>
        </div>
        <div class="card-body">
            <?php if (empty($approvalHistory)): ?>
                <p style="text-align:center; color:#6b7280; margin:0; font-size:13px;">Chưa có lịch sử phê duyệt nào.</p>
            <?php else: ?>
                <div style="position:relative; padding-left:20px; border-left:2px solid #e5e7eb;">
                    <?php foreach ($approvalHistory as $ah): ?>
                        <?php
                            $ahStatus = $ah['approval_status'] ?? '';
                            $dotColor  = $ahStatus === 'approved' ? '#10b981' : ($ahStatus === 'rejected' ? '#ef4444' : '#3b82f6');
                            $badgeCls  = $ahStatus === 'approved' ? 'badge-active' : ($ahStatus === 'rejected' ? 'badge-discharged' : 'badge-info');
                            $badgeLbl  = $ahStatus === 'approved' ? 'Đã duyệt' : ($ahStatus === 'rejected' ? 'Từ chối' : htmlspecialchars($ahStatus));
                        ?>
                        <div style="margin-bottom:16px; position:relative;">
                            <div style="position:absolute; left:-27px; top:3px; width:10px; height:10px; border-radius:50%; background:<?= $dotColor ?>; border:2px solid #fff;"></div>
                            <div style="font-weight:500; color:#111827; font-size:14px;">
                                <?= htmlspecialchars($ah['approved_by_name'] ?? '---') ?>
                                <span style="font-weight:normal; color:#6b7280; font-size:12px; margin-left:8px;">
                                    <?= !empty($ah['approved_at']) ? date('H:i d/m/Y', strtotime($ah['approved_at'])) : '' ?>
                                </span>
                            </div>
                            <div style="margin:4px 0;"><span class="badge <?= $badgeCls ?>"><?= $badgeLbl ?></span></div>
                            <?php if (!empty($ah['approval_note'])): ?>
                                <div style="background:#f9fafb; padding:8px 12px; border-radius:4px; font-size:13px; color:#374151; font-style:italic; border-left:3px solid #d1d5db;">
                                    &ldquo;<?= htmlspecialchars($ah['approval_note']) ?>&rdquo;
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>