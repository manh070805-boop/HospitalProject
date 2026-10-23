<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php
$selectedPlannedTasks = [];
if (!empty($carePlan['planned_tasks'])) {
    $decodedPlannedTasks = json_decode($carePlan['planned_tasks'], true);
    if (is_array($decodedPlannedTasks)) {
        foreach ($decodedPlannedTasks as $key => $val) {
            if (isset($val['times'])) {
                $selectedPlannedTasks[$key] = $val;
            } else {
                $selectedPlannedTasks[$key] = [
                    'label' => '',
                    'times' => is_array($val) ? $val : [$val],
                    'note' => ''
                ];
            }
        }
    }
}

$isPlannedTaskChecked = static function (string $taskKey, string $time) use ($selectedPlannedTasks): string {
    $taskData = $selectedPlannedTasks[$taskKey] ?? null;
    if (!$taskData) return '';
    return is_array($taskData['times']) && in_array($time, $taskData['times'], true) ? 'checked' : '';
};

$getTaskNote = static function (string $taskKey) use ($selectedPlannedTasks): string {
    return htmlspecialchars($selectedPlannedTasks[$taskKey]['note'] ?? '');
};

$getTaskLabel = static function (string $taskKey) use ($selectedPlannedTasks): string {
    return htmlspecialchars($selectedPlannedTasks[$taskKey]['label'] ?? '');
};

$isTaskActive = static function (string $taskKey) use ($selectedPlannedTasks): string {
    $taskData = $selectedPlannedTasks[$taskKey] ?? null;
    return $taskData && !empty($taskData['times']) ? 'active' : '';
};

$standardKeys = ['vital', 'spo2', 'wound', 'blood_sugar'];
$customTasks = array_filter($selectedPlannedTasks, fn($k) => !in_array($k, $standardKeys, true), ARRAY_FILTER_USE_KEY);

$renderTaskTimes = static function(string $taskKey, array $predefinedTimes) use ($selectedPlannedTasks, $isPlannedTaskChecked): string {
    $taskData = $selectedPlannedTasks[$taskKey] ?? [];
    $times = $taskData['times'] ?? [];
    $savedTimes = is_array($times) ? $times : [];
    $allTimes = array_unique(array_merge($predefinedTimes, $savedTimes));
    sort($allTimes);
    $html = '';
    foreach ($allTimes as $h) {
        $checked = $isPlannedTaskChecked($taskKey, $h);
        $html .= '<label style="display:flex; align-items:center; gap:6px;"><input type="checkbox" name="planned_tasks['.htmlspecialchars($taskKey).'][times][]" value="'.htmlspecialchars($h).'" '.$checked.' onchange="updateTaskState(this)"> '.htmlspecialchars($h).'</label>';
    }
    return $html;
};
?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Chỉnh sửa kế hoạch chăm sóc</h2>
            <p><?= htmlspecialchars($carePlan['patient_name'] ?? '') ?></p>
        </div>
        <a href="index.php?url=internal/care-plans/show&id=<?= (int) $carePlan['care_plan_id'] ?>" class="btn btn-secondary">
            <i class="iconsax" icon-name="arrow-left"></i> Trở về
        </a>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item"><i class="iconsax" icon-name="info-circle"></i> <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="care-template-panel reveal-item">
        <div class="care-template-header">
            <div>
                <h4>Mẫu kế hoạch chăm sóc</h4>
                <p>Chọn mẫu phù hợp để cập nhật nhanh nội dung nháp hiện tại.</p>
            </div>
        </div>
        <div class="care-template-grid">
            <button type="button" class="care-template-card" data-care-template="hypertension"><strong>Tăng huyết áp</strong><span>Theo dõi HA, đau đầu, tuân thủ thuốc</span></button>
            <button type="button" class="care-template-card" data-care-template="low_spo2"><strong>SpO2 thấp / Hô hấp</strong><span>Theo dõi SpO2, khó thở, tư thế thở</span></button>
            <button type="button" class="care-template-card" data-care-template="post_op"><strong>Hậu phẫu</strong><span>Đau, vết mổ, chảy máu, vận động sớm</span></button>
            <button type="button" class="care-template-card" data-care-template="diabetes"><strong>Đái tháo đường</strong><span>Đường huyết, ăn uống, dấu hiệu hạ đường</span></button>
            <button type="button" class="care-template-card" data-care-template="fall_risk"><strong>Nguy cơ té ngã</strong><span>An toàn, hỗ trợ di chuyển, phòng ngừa</span></button>
            <button type="button" class="care-template-card" data-care-template="wound_care"><strong>Chăm sóc vết thương</strong><span>Thay băng, nhiễm trùng, dịch tiết</span></button>
        </div>
    </div>

    <div class="card reveal-item">
        <div class="card-header"><h4>Chỉnh sửa kế hoạch</h4></div>
        <div class="card-body">
            <form action="index.php?url=internal/care-plans/update" method="POST" class="premium-form">
                <input type="hidden" name="care_plan_id" value="<?= (int) $carePlan['care_plan_id'] ?>">
                <?php if (!empty($treatmentPlan)): ?>
                    <input type="hidden" name="treatment_plan_id" value="<?= (int) $treatmentPlan['treatment_plan_id'] ?>">
                <?php else: ?>
                    <input type="hidden" name="treatment_plan_id" value="">
                <?php endif; ?>

                <div class="form-group">
                    <label for="plan_title">Tiêu đề kế hoạch <span class="required-star">*</span></label>
                    <input type="text" id="plan_title" name="plan_title" class="form-control"
                           value="<?= htmlspecialchars($carePlan['plan_title'] ?? '') ?>" required>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:18px;">
                    <div class="form-group">
                        <label for="assessment_summary">Nhận định bệnh nhân</label>
                        <textarea id="assessment_summary" name="assessment_summary" class="form-control" rows="4" required><?= htmlspecialchars($carePlan['assessment_summary'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="nursing_diagnosis">Chẩn đoán điều dưỡng</label>
                        <textarea id="nursing_diagnosis" name="nursing_diagnosis" class="form-control" rows="4" required><?= htmlspecialchars($carePlan['nursing_diagnosis'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="care_goal">Mục tiêu chăm sóc</label>
                        <textarea id="care_goal" name="care_goal" class="form-control" rows="4" required><?= htmlspecialchars($carePlan['care_goal'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="intervention_plan">Kế hoạch can thiệp</label>
                        <textarea id="intervention_plan" name="intervention_plan" class="form-control" rows="4" required><?= htmlspecialchars($carePlan['intervention_plan'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="monitoring_frequency">Tần suất theo dõi sinh tồn</label>
                        <input type="text" id="monitoring_frequency" name="monitoring_frequency" class="form-control"
                               value="<?= htmlspecialchars($carePlan['monitoring_frequency'] ?? '') ?>" required>
                    </div>

                    <style>
                    .task-card {
                        border: 1px solid #e5e7eb;
                        border-radius: 8px;
                        padding: 12px;
                        transition: all 0.2s ease;
                        background: #fff;
                        position: relative;
                    }
                    .task-card.active {
                        border-color: #2563eb;
                        background-color: #eff6ff;
                    }
                    .task-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        margin-bottom: 8px;
                    }
                    .task-presets button {
                        background: #f3f4f6;
                        border: 1px solid #d1d5db;
                        border-radius: 4px;
                        padding: 2px 6px;
                        font-size: 12px;
                        cursor: pointer;
                        margin-left: 4px;
                        color: #4b5563;
                    }
                    .task-presets button:hover {
                        background: #e5e7eb;
                    }
                    .task-times {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 10px;
                        margin-bottom: 10px;
                    }
                    .task-note-input {
                        width: 100%;
                        padding: 6px 10px;
                        border: 1px solid #d1d5db;
                        border-radius: 4px;
                        font-size: 13px;
                    }
                    .btn-add-task {
                        grid-column: span 2;
                        background: none;
                        border: 1px dashed #9ca3af;
                        color: #4b5563;
                        padding: 10px;
                        border-radius: 8px;
                        cursor: pointer;
                        font-weight: 500;
                        transition: all 0.2s;
                    }
                    .btn-add-task:hover {
                        background: #f9fafb;
                        border-color: #6b7280;
                        color: #111827;
                    }
                    @media (max-width: 768px) {
                        #tasks_grid { grid-template-columns: 1fr !important; }
                        .btn-add-task { grid-column: span 1; }
                    }
                    </style>

                    <div class="form-group" style="grid-column: span 2;">
                        <label>Chọn đầu việc thực hiện</label>
                        <div id="tasks_grid" style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:12px;">
                            
                            <div class="task-card <?= $isTaskActive('vital') ?>">
                                <div class="task-header">
                                    <strong>Ghi sinh tồn và đánh giá dấu hiệu bất thường</strong>
                                </div>
                                <input type="hidden" name="planned_tasks[vital][label]" value="Ghi sinh tồn và đánh giá dấu hiệu bất thường">
                                <div class="task-times">
                                    <?= $renderTaskTimes('vital', ['07:00', '13:00', '19:00', '21:00']) ?>
                                </div>
                                <div style="display:flex; gap:6px; margin-bottom:10px;">
                                    <input type="time" class="form-control" style="width:auto; padding:2px 6px; height:28px; font-size:13px;">
                                    <button type="button" class="btn btn-secondary btn-sm" style="padding:2px 8px; font-size:12px; border:1px solid #d1d5db; border-radius:4px; background:#f3f4f6; cursor:pointer;" onclick="addTimeToTask(this, 'vital')">+ Thêm giờ</button>
                                </div>
                                <input type="text" name="planned_tasks[vital][note]" class="task-note-input" value="<?= $getTaskNote('vital') ?>" placeholder="Ghi chú chi tiết (nếu có)...">
                            </div>

                            <div class="task-card <?= $isTaskActive('spo2') ?>">
                                <div class="task-header">
                                    <strong>Theo dõi SpO2, nhịp thở và mức độ khó thở</strong>
                                </div>
                                <input type="hidden" name="planned_tasks[spo2][label]" value="Theo dõi SpO2, nhịp thở và mức độ khó thở">
                                <div class="task-times">
                                    <?= $renderTaskTimes('spo2', ['08:00', '14:00', '20:00', '22:00']) ?>
                                </div>
                                <div style="display:flex; gap:6px; margin-bottom:10px;">
                                    <input type="time" class="form-control" style="width:auto; padding:2px 6px; height:28px; font-size:13px;">
                                    <button type="button" class="btn btn-secondary btn-sm" style="padding:2px 8px; font-size:12px; border:1px solid #d1d5db; border-radius:4px; background:#f3f4f6; cursor:pointer;" onclick="addTimeToTask(this, 'spo2')">+ Thêm giờ</button>
                                </div>
                                <input type="text" name="planned_tasks[spo2][note]" class="task-note-input" value="<?= $getTaskNote('spo2') ?>" placeholder="Ghi chú chi tiết (nếu có)...">
                            </div>

                            <div class="task-card <?= $isTaskActive('wound') ?>">
                                <div class="task-header">
                                    <strong>Chăm sóc vết thương</strong>
                                </div>
                                <input type="hidden" name="planned_tasks[wound][label]" value="Chăm sóc vết thương">
                                <div class="task-times">
                                    <?= $renderTaskTimes('wound', ['08:00', '20:00']) ?>
                                </div>
                                <div style="display:flex; gap:6px; margin-bottom:10px;">
                                    <input type="time" class="form-control" style="width:auto; padding:2px 6px; height:28px; font-size:13px;">
                                    <button type="button" class="btn btn-secondary btn-sm" style="padding:2px 8px; font-size:12px; border:1px solid #d1d5db; border-radius:4px; background:#f3f4f6; cursor:pointer;" onclick="addTimeToTask(this, 'wound')">+ Thêm giờ</button>
                                </div>
                                <input type="text" name="planned_tasks[wound][note]" class="task-note-input" value="<?= $getTaskNote('wound') ?>" placeholder="Ghi chú chi tiết (nếu có)...">
                            </div>

                            <div class="task-card <?= $isTaskActive('blood_sugar') ?>">
                                <div class="task-header">
                                    <strong>Kiểm tra đường huyết</strong>
                                </div>
                                <input type="hidden" name="planned_tasks[blood_sugar][label]" value="Kiểm tra đường huyết">
                                <div class="task-times">
                                    <?= $renderTaskTimes('blood_sugar', ['06:30', '11:00', '16:30', '21:00']) ?>
                                </div>
                                <div style="display:flex; gap:6px; margin-bottom:10px;">
                                    <input type="time" class="form-control" style="width:auto; padding:2px 6px; height:28px; font-size:13px;">
                                    <button type="button" class="btn btn-secondary btn-sm" style="padding:2px 8px; font-size:12px; border:1px solid #d1d5db; border-radius:4px; background:#f3f4f6; cursor:pointer;" onclick="addTimeToTask(this, 'blood_sugar')">+ Thêm giờ</button>
                                </div>
                                <input type="text" name="planned_tasks[blood_sugar][note]" class="task-note-input" value="<?= $getTaskNote('blood_sugar') ?>" placeholder="Ghi chú chi tiết (nếu có)...">
                            </div>

                            <?php foreach ($customTasks as $key => $task): ?>
                                <div class="task-card <?= $isTaskActive($key) ?>">
                                    <div class="task-header" style="flex-direction: column; gap: 8px; width: 100%;">
                                        <input type="text" name="planned_tasks[<?= htmlspecialchars($key) ?>][label]" class="form-control" value="<?= htmlspecialchars($task['label'] ?? '') ?>" required style="width: 100%; border:none; border-bottom:1px solid #d1d5db; border-radius:0; padding:4px 0; font-weight:bold; outline:none;" oninput="this.style.borderColor='#2563eb'">
                                    </div>
                                    <div class="task-times">
                                        <?= $renderTaskTimes($key, ['06:00','08:00','10:00','12:00','14:00','16:00','18:00','20:00','22:00']) ?>
                                    </div>
                                    <div style="display:flex; gap:6px; margin-bottom:10px;">
                                        <input type="time" class="form-control" style="width:auto; padding:2px 6px; height:28px; font-size:13px;">
                                        <button type="button" class="btn btn-secondary btn-sm" style="padding:2px 8px; font-size:12px; border:1px solid #d1d5db; border-radius:4px; background:#f3f4f6; cursor:pointer;" onclick="addTimeToTask(this, '<?= htmlspecialchars($key) ?>')">+ Thêm giờ</button>
                                    </div>
                                    <input type="text" name="planned_tasks[<?= htmlspecialchars($key) ?>][note]" class="task-note-input" value="<?= htmlspecialchars($task['note'] ?? '') ?>" placeholder="Ghi chú chi tiết (nếu có)...">
                                </div>
                            <?php endforeach; ?>
                            
                            <button type="button" class="btn-add-task" onclick="addCustomTask()">
                                + Thêm đầu việc tùy chỉnh
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="iconsax" icon-name="tick-circle"></i> Lưu thay đổi
                    </button>
                    <a href="index.php?url=internal/care-plans/show&id=<?= (int) $carePlan['care_plan_id'] ?>" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(() => {
    const templates = {
        hypertension: ['Kế hoạch chăm sóc bệnh nhân tăng huyết áp', 'Bệnh nhân cần theo dõi huyết áp sát, đánh giá đau đầu, chóng mặt, đau ngực, khó thở và mức độ tuân thủ dùng thuốc.', 'Nguy cơ biến chứng tim mạch/liên quan đến huyết áp tăng hoặc dao động huyết áp.', 'Huyết áp được kiểm soát theo mục tiêu điều trị, bệnh nhân không xuất hiện dấu hiệu cảnh báo và hiểu các điểm cần báo nhân viên y tế.', 'Đo huyết áp đúng kỹ thuật theo y lệnh; theo dõi đau đầu, chóng mặt, đau ngực, khó thở; nhắc bệnh nhân dùng thuốc đúng giờ; hướng dẫn nghỉ ngơi, hạn chế vận động gắng sức; báo bác sĩ khi HA vượt ngưỡng cảnh báo hoặc có triệu chứng bất thường.', 'Đo sinh tồn 4 giờ/lần; đo lại sau 30 phút nếu HA tăng cao hoặc có triệu chứng.'],
        low_spo2: ['Kế hoạch chăm sóc bệnh nhân SpO2 thấp', 'Bệnh nhân cần theo dõi SpO2, nhịp thở, mức độ khó thở, tím tái, ho đờm và đáp ứng với tư thế/oxy nếu có y lệnh.', 'Trao đổi khí không hiệu quả hoặc nguy cơ giảm oxy máu liên quan đến tình trạng hô hấp.', 'SpO2 duy trì trong ngưỡng mục tiêu, bệnh nhân giảm khó thở và không xuất hiện dấu hiệu suy hô hấp.', 'Theo dõi SpO2 và nhịp thở; đặt tư thế Fowler nếu phù hợp; hướng dẫn thở chậm sâu; thực hiện oxy theo y lệnh; theo dõi dấu hiệu tím tái, co kéo cơ hô hấp; báo bác sĩ khi SpO2 giảm dưới ngưỡng cảnh báo.', 'Theo dõi SpO2 1 giờ/lần khi bất thường; 4 giờ/lần khi ổn định.'],
        post_op: ['Kế hoạch chăm sóc hậu phẫu', 'Bệnh nhân sau phẫu thuật cần theo dõi đau, vết mổ, chảy máu, dẫn lưu, sinh tồn và khả năng vận động.', 'Đau cấp và nguy cơ nhiễm trùng vết mổ liên quan đến can thiệp phẫu thuật.', 'Đau được kiểm soát, vết mổ khô sạch, không có dấu hiệu nhiễm trùng hoặc chảy máu bất thường.', 'Đánh giá đau theo thang điểm; theo dõi vết mổ, băng, dịch dẫn lưu; thay băng theo y lệnh; hỗ trợ vận động sớm an toàn; theo dõi sốt, mạch nhanh, tụt huyết áp; báo bác sĩ khi đau tăng, chảy máu hoặc dịch bất thường.', 'Đánh giá đau mỗi ca và khi bệnh nhân than đau; kiểm tra vết mổ ít nhất 2 lần/ngày.'],
        diabetes: ['Kế hoạch chăm sóc bệnh nhân đái tháo đường', 'Bệnh nhân cần theo dõi đường huyết, chế độ ăn, dấu hiệu hạ/tăng đường huyết và tuân thủ điều trị.', 'Nguy cơ đường huyết không ổn định liên quan đến bệnh nền, chế độ ăn hoặc điều trị.', 'Đường huyết được theo dõi và duy trì trong ngưỡng mục tiêu, bệnh nhân nhận biết dấu hiệu bất thường.', 'Theo dõi đường huyết theo y lệnh; quan sát run tay, vã mồ hôi, lơ mơ, khát nhiều; phối hợp bữa ăn và thuốc/insulin; hướng dẫn bệnh nhân báo ngay khi có dấu hiệu hạ đường huyết; ghi nhận lượng ăn và triệu chứng.', 'Kiểm tra đường huyết trước ăn và trước ngủ theo y lệnh; sinh tồn mỗi ca.'],
        fall_risk: ['Kế hoạch chăm sóc phòng ngừa té ngã', 'Bệnh nhân có nguy cơ té ngã cần đánh giá ý thức, sức cơ, chóng mặt, thuốc đang dùng và khả năng tự di chuyển.', 'Nguy cơ té ngã liên quan đến yếu cơ, chóng mặt, thay đổi ý thức hoặc môi trường chưa an toàn.', 'Bệnh nhân không xảy ra té ngã trong thời gian nằm viện và biết gọi hỗ trợ khi cần di chuyển.', 'Đặt chuông gọi trong tầm với; nâng thành giường khi cần; hướng dẫn không tự ý rời giường; hỗ trợ đi vệ sinh/di chuyển; giữ sàn khô thoáng; đánh dấu nguy cơ té ngã và bàn giao trong ca.', 'Đánh giá nguy cơ té ngã mỗi ca; kiểm tra an toàn buồng bệnh đầu mỗi ca.'],
        wound_care: ['Kế hoạch chăm sóc vết thương', 'Bệnh nhân có vết thương cần theo dõi vị trí, kích thước, dịch tiết, mùi, đau, đỏ nóng sưng và tình trạng băng.', 'Nguy cơ nhiễm trùng liên quan đến tổn thương da/mô và can thiệp chăm sóc vết thương.', 'Vết thương tiến triển tốt, không có dấu hiệu nhiễm trùng, dịch tiết giảm và bệnh nhân được kiểm soát đau.', 'Thay băng đúng quy trình vô khuẩn; đánh giá dịch tiết, mùi, màu sắc, đau; giữ vùng tổn thương sạch khô; hướng dẫn bệnh nhân không tự tháo băng; báo bác sĩ khi có sốt, mủ, mùi hôi, đỏ lan hoặc đau tăng.', 'Thay băng theo y lệnh; kiểm tra băng và đau mỗi ca.']
    };
    const ids = ['plan_title', 'assessment_summary', 'nursing_diagnosis', 'care_goal', 'intervention_plan', 'monitoring_frequency'];

    window.updateTaskState = function(checkbox) {
        const card = checkbox.closest('.task-card');
        if (!card) return;
        const anyChecked = Array.from(card.querySelectorAll('input[type="checkbox"]')).some(cb => cb.checked);
        if (anyChecked) {
            card.classList.add('active');
        } else {
            card.classList.remove('active');
        }
    };

    window.addTimeToTask = function(btn, taskKey) {
        const container = btn.closest('.task-card').querySelector('.task-times');
        const input = btn.previousElementSibling;
        const timeVal = input.value;
        if (!timeVal) return;
        
        const exists = Array.from(container.querySelectorAll('input[type="checkbox"]')).some(cb => cb.value === timeVal);
        if (!exists) {
            const labelHtml = `<label style="display:flex; align-items:center; gap:6px;"><input type="checkbox" name="planned_tasks[${taskKey}][times][]" value="${timeVal}" checked onchange="updateTaskState(this)"> ${timeVal}</label>`;
            
            // insert to DOM and sort visually
            container.insertAdjacentHTML('beforeend', labelHtml);
            const labels = Array.from(container.querySelectorAll('label'));
            labels.sort((a, b) => {
                const timeA = a.querySelector('input').value;
                const timeB = b.querySelector('input').value;
                return timeA.localeCompare(timeB);
            });
            container.innerHTML = '';
            labels.forEach(l => container.appendChild(l));
            
            updateTaskState(container.querySelector('input[value="'+timeVal+'"]'));
        } else {
            const cb = Array.from(container.querySelectorAll('input[type="checkbox"]')).find(cb => cb.value === timeVal);
            if (cb && !cb.checked) {
                cb.checked = true;
                updateTaskState(cb);
            }
        }
        input.value = '';
    };

    window.addCustomTask = function() {
        const grid = document.getElementById('tasks_grid');
        const btn = grid.querySelector('.btn-add-task');
        const uniqueId = 'custom_' + Date.now();
        
        const hours = ['06:00','08:00','10:00','12:00','14:00','16:00','18:00','20:00','22:00'];
        let checkboxesHtml = '';
        hours.forEach(h => {
            checkboxesHtml += `<label style="display:flex; align-items:center; gap:6px;"><input type="checkbox" name="planned_tasks[${uniqueId}][times][]" value="${h}" onchange="updateTaskState(this)"> ${h}</label>`;
        });

        const cardHtml = `
            <div class="task-card">
                <div class="task-header" style="flex-direction: column; gap: 8px; width: 100%;">
                    <input type="text" name="planned_tasks[${uniqueId}][label]" class="form-control" placeholder="Nhập tên đầu việc tùy chỉnh..." required style="width: 100%; border:none; border-bottom:1px solid #d1d5db; border-radius:0; padding:4px 0; font-weight:bold; outline:none;" oninput="this.style.borderColor='#2563eb'">
                </div>
                <div class="task-times">
                    ${checkboxesHtml}
                </div>
                <div style="display:flex; gap:6px; margin-bottom:10px;">
                    <input type="time" class="form-control" style="width:auto; padding:2px 6px; height:28px; font-size:13px;">
                    <button type="button" class="btn btn-secondary btn-sm" style="padding:2px 8px; font-size:12px; border:1px solid #d1d5db; border-radius:4px; background:#f3f4f6; cursor:pointer;" onclick="addTimeToTask(this, '${uniqueId}')">+ Thêm giờ</button>
                </div>
                <input type="text" name="planned_tasks[${uniqueId}][note]" class="task-note-input" placeholder="Ghi chú chi tiết (nếu có)...">
            </div>
        `;
        
        btn.insertAdjacentHTML('beforebegin', cardHtml);
    };

    document.querySelectorAll('[data-care-template]').forEach(button => {
        button.addEventListener('click', () => {
            const template = templates[button.dataset.careTemplate];
            if (!template) return;
            ids.forEach((id, index) => {
                const element = document.getElementById(id);
                if (element) element.value = template[index];
            });
        });
    });
})();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
