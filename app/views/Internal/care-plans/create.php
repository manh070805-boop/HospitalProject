<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Lập kế hoạch chăm sóc</h2>
            <p><?= htmlspecialchars($admission['patient_name']) ?></p>
        </div>
        <a href="index.php?url=internal/admissions/show&id=<?= (int) $admission['admission_id'] ?>"
            class="btn btn-secondary">
            <i class="iconsax" icon-name="arrow-left"></i> Trở về
        </a>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item"><i class="iconsax" icon-name="info-circle"></i>
            <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <?php if (!empty($treatmentPlan)): ?>
        <div class="alert alert-info reveal-item" style="display:flex; gap:10px; align-items:center;">
            <i class="iconsax" icon-name="clipboard-text-1"></i>
            <div>
                <strong>Kế hoạch điều trị hiện hành:</strong> <?= htmlspecialchars($treatmentPlan['diagnosis'] ?? '') ?>
                &mdash; <a
                    href="index.php?url=internal/treatment-plans/show&id=<?= (int) $treatmentPlan['treatment_plan_id'] ?>">Xem
                    chi tiết</a>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger reveal-item">
            <i class="iconsax" icon-name="warning-triangle"></i>
            <strong>Cảnh báo:</strong> Bác sĩ chưa lập kế hoạch điều trị active. Cần có y lệnh trước khi điều dưỡng lập kế
            hoạch chăm sóc.
        </div>
    <?php endif; ?>

    <div class="care-context-grid reveal-item">
        <div class="care-context-card">
            <span>Ca nội trú</span>
            <strong><?= htmlspecialchars($admission['patient_name']) ?></strong>
            <p><?= htmlspecialchars($admission['department_name'] ?? '---') ?></p>
        </div>
        <div
            class="care-context-card <?= !empty($latestVitalRisk) ? htmlspecialchars($latestVitalRisk['class']) : 'risk-watch' ?>">
            <span>Risk Score sinh tồn</span>
            <?php if (!empty($latestVitalRisk)): ?>
                <strong><?= (int) $latestVitalRisk['score'] ?> · <?= htmlspecialchars($latestVitalRisk['label']) ?></strong>
                <p><?= htmlspecialchars($latestVitalRisk['recommendation']) ?></p>
            <?php else: ?>
                <strong>Chưa có dữ liệu</strong>
                <p>Cần ghi sinh tồn trước khi lập kế hoạch chăm sóc.</p>
            <?php endif; ?>
        </div>
        <div class="care-context-card <?= !empty($treatmentPlan) ? 'risk-stable' : 'risk-high' ?>">
            <span>Kế hoạch điều trị</span>
            <strong><?= !empty($treatmentPlan) ? 'Đã liên kết' : 'Thiếu y lệnh' ?></strong>
            <p><?= !empty($treatmentPlan) ? htmlspecialchars(mb_substr($treatmentPlan['diagnosis'] ?? 'Chưa ghi chẩn đoán', 0, 90)) : 'Cần bác sĩ lập kế hoạch điều trị trước.' ?>
            </p>
        </div>
    </div>

    <div class="care-template-panel reveal-item">
        <div class="care-template-header">
            <div>
                <h4>Mẫu kế hoạch chăm sóc</h4>
                <p>Chọn mẫu phù hợp để điền nhanh nội dung, sau đó điều chỉnh theo tình trạng thực tế.</p>
            </div>
        </div>
        <div class="care-template-grid">
            <button type="button" class="care-template-card" data-care-template="hypertension">
                <strong>Tăng huyết áp</strong>
                <span>Theo dõi HA, đau đầu, tuân thủ thuốc</span>
            </button>
            <button type="button" class="care-template-card" data-care-template="low_spo2">
                <strong>SpO2 thấp / Hô hấp</strong>
                <span>Theo dõi SpO2, khó thở, tư thế thở</span>
            </button>
            <button type="button" class="care-template-card" data-care-template="post_op">
                <strong>Hậu phẫu</strong>
                <span>Đau, vết mổ, chảy máu, vận động sớm</span>
            </button>
            <button type="button" class="care-template-card" data-care-template="diabetes">
                <strong>Đái tháo đường</strong>
                <span>Đường huyết, ăn uống, dấu hiệu hạ đường</span>
            </button>
            <button type="button" class="care-template-card" data-care-template="fall_risk">
                <strong>Nguy cơ té ngã</strong>
                <span>An toàn, hỗ trợ di chuyển, phòng ngừa</span>
            </button>
            <button type="button" class="care-template-card" data-care-template="wound_care">
                <strong>Chăm sóc vết thương</strong>
                <span>Thay băng, nhiễm trùng, dịch tiết</span>
            </button>
        </div>
    </div>

    <div class="card reveal-item">
        <div class="card-header">
            <h4>Thông tin kế hoạch chăm sóc</h4>
        </div>
        <div class="card-body">
            <form action="index.php?url=internal/care-plans/store" method="POST" class="premium-form">
                <input type="hidden" name="admission_id" value="<?= (int) $admission['admission_id'] ?>">
                <?php if (!empty($treatmentPlan)): ?>
                    <input type="hidden" name="treatment_plan_id" value="<?= (int) $treatmentPlan['treatment_plan_id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="plan_title">Tiêu đề kế hoạch <span class="required-star">*</span></label>
                    <input type="text" id="plan_title" name="plan_title" class="form-control"
                        placeholder="VD: Kế hoạch chăm sóc sau phẫu thuật ngày 1" required>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:18px;">
                    <div class="form-group">
                        <label for="assessment_summary">Nhận định bệnh nhân</label>
                        <textarea id="assessment_summary" name="assessment_summary" class="form-control" rows="4"
                            placeholder="Bệnh nhân tỉnh táo, sinh tồn ổn định..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="nursing_diagnosis">Chẩn đoán điều dưỡng</label>
                        <textarea id="nursing_diagnosis" name="nursing_diagnosis" class="form-control" rows="4"
                            placeholder="Nguy cơ nhiễm trùng vết thương do..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="care_goal">Mục tiêu chăm sóc</label>
                        <textarea id="care_goal" name="care_goal" class="form-control" rows="4"
                            placeholder="Vết thương lành, không nhiễm trùng trong 7 ngày..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="intervention_plan">Kế hoạch can thiệp</label>
                        <textarea id="intervention_plan" name="intervention_plan" class="form-control" rows="4"
                            placeholder="Thay băng 2 lần/ngày, theo dõi dấu hiệu nhiễm trùng..." required></textarea>
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="monitoring_frequency">Tần suất theo dõi sinh tồn</label>
                        <input type="text" id="monitoring_frequency" name="monitoring_frequency" class="form-control"
                            placeholder="VD: 4 giờ/lần trong 24h đầu, sau đó 8 giờ/lần" required>
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
                            #tasks_grid {
                                grid-template-columns: 1fr !important;
                            }

                            .btn-add-task {
                                grid-column: span 1;
                            }
                        }
                    </style>

                    <div class="form-group" style="grid-column: span 2;">
                        <label>Chọn đầu việc thực hiện</label>
                        <div id="tasks_grid"
                            style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:12px;">

                            <div class="task-card">
                                <div class="task-header">
                                    <strong>Ghi sinh tồn và đánh giá dấu hiệu bất thường</strong>
                                </div>
                                <input type="hidden" name="planned_tasks[vital][label]"
                                    value="Ghi sinh tồn và đánh giá dấu hiệu bất thường">
                                <div class="task-times">
                                    <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox"
                                            name="planned_tasks[vital][times][]" value="07:00"
                                            onchange="updateTaskState(this)"> 07:00</label>
                                    <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox"
                                            name="planned_tasks[vital][times][]" value="13:00"
                                            onchange="updateTaskState(this)"> 13:00</label>
                                    <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox"
                                            name="planned_tasks[vital][times][]" value="19:00"
                                            onchange="updateTaskState(this)"> 19:00</label>
                                    <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox"
                                            name="planned_tasks[vital][times][]" value="21:00"
                                            onchange="updateTaskState(this)"> 21:00</label>
                                </div>
                                <div style="display:flex; gap:6px; margin-bottom:10px;">
                                    <input type="time" class="form-control"
                                        style="width:auto; padding:2px 6px; height:28px; font-size:13px;">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        style="padding:2px 8px; font-size:12px; border:1px solid #d1d5db; border-radius:4px; background:#f3f4f6; cursor:pointer;"
                                        onclick="addTimeToTask(this, 'vital')">+ Thêm giờ</button>
                                </div>
                                <input type="text" name="planned_tasks[vital][note]" class="task-note-input"
                                    placeholder="Ghi chú chi tiết (nếu có)...">
                            </div>

                            <div class="task-card">
                                <div class="task-header">
                                    <strong>Theo dõi SpO2, nhịp thở và mức độ khó thở</strong>
                                </div>
                                <input type="hidden" name="planned_tasks[spo2][label]"
                                    value="Theo dõi SpO2, nhịp thở và mức độ khó thở">
                                <div class="task-times">
                                    <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox"
                                            name="planned_tasks[spo2][times][]" value="08:00"
                                            onchange="updateTaskState(this)"> 08:00</label>
                                    <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox"
                                            name="planned_tasks[spo2][times][]" value="14:00"
                                            onchange="updateTaskState(this)"> 14:00</label>
                                    <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox"
                                            name="planned_tasks[spo2][times][]" value="20:00"
                                            onchange="updateTaskState(this)"> 20:00</label>
                                    <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox"
                                            name="planned_tasks[spo2][times][]" value="22:00"
                                            onchange="updateTaskState(this)"> 22:00</label>
                                </div>
                                <div style="display:flex; gap:6px; margin-bottom:10px;">
                                    <input type="time" class="form-control"
                                        style="width:auto; padding:2px 6px; height:28px; font-size:13px;">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        style="padding:2px 8px; font-size:12px; border:1px solid #d1d5db; border-radius:4px; background:#f3f4f6; cursor:pointer;"
                                        onclick="addTimeToTask(this, 'spo2')">+ Thêm giờ</button>
                                </div>
                                <input type="text" name="planned_tasks[spo2][note]" class="task-note-input"
                                    placeholder="Ghi chú chi tiết (nếu có)...">
                            </div>

                            <div class="task-card">
                                <div class="task-header">
                                    <strong>Chăm sóc vết thương</strong>
                                </div>
                                <input type="hidden" name="planned_tasks[wound][label]" value="Chăm sóc vết thương">
                                <div class="task-times">
                                    <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox"
                                            name="planned_tasks[wound][times][]" value="08:00"
                                            onchange="updateTaskState(this)"> 08:00</label>
                                    <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox"
                                            name="planned_tasks[wound][times][]" value="20:00"
                                            onchange="updateTaskState(this)"> 20:00</label>
                                </div>
                                <div style="display:flex; gap:6px; margin-bottom:10px;">
                                    <input type="time" class="form-control"
                                        style="width:auto; padding:2px 6px; height:28px; font-size:13px;">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        style="padding:2px 8px; font-size:12px; border:1px solid #d1d5db; border-radius:4px; background:#f3f4f6; cursor:pointer;"
                                        onclick="addTimeToTask(this, 'wound')">+ Thêm giờ</button>
                                </div>
                                <input type="text" name="planned_tasks[wound][note]" class="task-note-input"
                                    placeholder="Ghi chú chi tiết (nếu có)...">
                            </div>

                            <div class="task-card">
                                <div class="task-header">
                                    <strong>Kiểm tra đường huyết</strong>
                                </div>
                                <input type="hidden" name="planned_tasks[blood_sugar][label]"
                                    value="Kiểm tra đường huyết">
                                <div class="task-times">
                                    <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox"
                                            name="planned_tasks[blood_sugar][times][]" value="06:30"
                                            onchange="updateTaskState(this)"> 06:30</label>
                                    <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox"
                                            name="planned_tasks[blood_sugar][times][]" value="11:00"
                                            onchange="updateTaskState(this)"> 11:00</label>
                                    <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox"
                                            name="planned_tasks[blood_sugar][times][]" value="16:30"
                                            onchange="updateTaskState(this)"> 16:30</label>
                                    <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox"
                                            name="planned_tasks[blood_sugar][times][]" value="21:00"
                                            onchange="updateTaskState(this)"> 21:00</label>
                                </div>
                                <div style="display:flex; gap:6px; margin-bottom:10px;">
                                    <input type="time" class="form-control"
                                        style="width:auto; padding:2px 6px; height:28px; font-size:13px;">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        style="padding:2px 8px; font-size:12px; border:1px solid #d1d5db; border-radius:4px; background:#f3f4f6; cursor:pointer;"
                                        onclick="addTimeToTask(this, 'blood_sugar')">+ Thêm giờ</button>
                                </div>
                                <input type="text" name="planned_tasks[blood_sugar][note]" class="task-note-input"
                                    placeholder="Ghi chú chi tiết (nếu có)...">
                            </div>

                            <button type="button" class="btn-add-task" onclick="addCustomTask()">
                                + Thêm đầu việc tùy chỉnh
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="iconsax" icon-name="tick-circle"></i> Lưu nháp
                    </button>
                    <a href="index.php?url=internal/admissions/show&id=<?= (int) $admission['admission_id'] ?>"
                        class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (() => {
        const templates = {
            hypertension: {
                title: 'Kế hoạch chăm sóc bệnh nhân tăng huyết áp',
                assessment: 'Bệnh nhân cần theo dõi huyết áp sát, đánh giá đau đầu, chóng mặt, đau ngực, khó thở và mức độ tuân thủ dùng thuốc.',
                diagnosis: 'Nguy cơ biến chứng tim mạch/liên quan đến huyết áp tăng hoặc dao động huyết áp.',
                goal: 'Huyết áp được kiểm soát theo mục tiêu điều trị, bệnh nhân không xuất hiện dấu hiệu cảnh báo và hiểu các điểm cần báo nhân viên y tế.',
                intervention: 'Đo huyết áp đúng kỹ thuật theo y lệnh; theo dõi đau đầu, chóng mặt, đau ngực, khó thở; nhắc bệnh nhân dùng thuốc đúng giờ; hướng dẫn nghỉ ngơi, hạn chế vận động gắng sức; báo bác sĩ khi HA vượt ngưỡng cảnh báo hoặc có triệu chứng bất thường.',
                frequency: 'Đo sinh tồn 4 giờ/lần; đo lại sau 30 phút nếu HA tăng cao hoặc có triệu chứng.'
            },
            low_spo2: {
                title: 'Kế hoạch chăm sóc bệnh nhân SpO2 thấp',
                assessment: 'Bệnh nhân cần theo dõi SpO2, nhịp thở, mức độ khó thở, tím tái, ho đờm và đáp ứng với tư thế/oxy nếu có y lệnh.',
                diagnosis: 'Trao đổi khí không hiệu quả hoặc nguy cơ giảm oxy máu liên quan đến tình trạng hô hấp.',
                goal: 'SpO2 duy trì trong ngưỡng mục tiêu, bệnh nhân giảm khó thở và không xuất hiện dấu hiệu suy hô hấp.',
                intervention: 'Theo dõi SpO2 và nhịp thở; đặt tư thế Fowler nếu phù hợp; hướng dẫn thở chậm sâu; thực hiện oxy theo y lệnh; theo dõi dấu hiệu tím tái, co kéo cơ hô hấp; báo bác sĩ khi SpO2 giảm dưới ngưỡng cảnh báo.',
                frequency: 'Theo dõi SpO2 1 giờ/lần khi bất thường; 4 giờ/lần khi ổn định.'
            },
            post_op: {
                title: 'Kế hoạch chăm sóc hậu phẫu',
                assessment: 'Bệnh nhân sau phẫu thuật cần theo dõi đau, vết mổ, chảy máu, dẫn lưu, sinh tồn và khả năng vận động.',
                diagnosis: 'Đau cấp và nguy cơ nhiễm trùng vết mổ liên quan đến can thiệp phẫu thuật.',
                goal: 'Đau được kiểm soát, vết mổ khô sạch, không có dấu hiệu nhiễm trùng hoặc chảy máu bất thường.',
                intervention: 'Đánh giá đau theo thang điểm; theo dõi vết mổ, băng, dịch dẫn lưu; thay băng theo y lệnh; hỗ trợ vận động sớm an toàn; theo dõi sốt, mạch nhanh, tụt huyết áp; báo bác sĩ khi đau tăng, chảy máu hoặc dịch bất thường.',
                frequency: 'Đánh giá đau mỗi ca và khi bệnh nhân than đau; kiểm tra vết mổ ít nhất 2 lần/ngày.'
            },
            diabetes: {
                title: 'Kế hoạch chăm sóc bệnh nhân đái tháo đường',
                assessment: 'Bệnh nhân cần theo dõi đường huyết, chế độ ăn, dấu hiệu hạ/tăng đường huyết và tuân thủ điều trị.',
                diagnosis: 'Nguy cơ đường huyết không ổn định liên quan đến bệnh nền, chế độ ăn hoặc điều trị.',
                goal: 'Đường huyết được theo dõi và duy trì trong ngưỡng mục tiêu, bệnh nhân nhận biết dấu hiệu bất thường.',
                intervention: 'Theo dõi đường huyết theo y lệnh; quan sát run tay, vã mồ hôi, lơ mơ, khát nhiều; phối hợp bữa ăn và thuốc/insulin; hướng dẫn bệnh nhân báo ngay khi có dấu hiệu hạ đường huyết; ghi nhận lượng ăn và triệu chứng.',
                frequency: 'Kiểm tra đường huyết trước ăn và trước ngủ theo y lệnh; sinh tồn mỗi ca.'
            },
            fall_risk: {
                title: 'Kế hoạch chăm sóc phòng ngừa té ngã',
                assessment: 'Bệnh nhân có nguy cơ té ngã cần đánh giá ý thức, sức cơ, chóng mặt, thuốc đang dùng và khả năng tự di chuyển.',
                diagnosis: 'Nguy cơ té ngã liên quan đến yếu cơ, chóng mặt, thay đổi ý thức hoặc môi trường chưa an toàn.',
                goal: 'Bệnh nhân không xảy ra té ngã trong thời gian nằm viện và biết gọi hỗ trợ khi cần di chuyển.',
                intervention: 'Đặt chuông gọi trong tầm với; nâng thành giường khi cần; hướng dẫn không tự ý rời giường; hỗ trợ đi vệ sinh/di chuyển; giữ sàn khô thoáng; đánh dấu nguy cơ té ngã và bàn giao trong ca.',
                frequency: 'Đánh giá nguy cơ té ngã mỗi ca; kiểm tra an toàn buồng bệnh đầu mỗi ca.'
            },
            wound_care: {
                title: 'Kế hoạch chăm sóc vết thương',
                assessment: 'Bệnh nhân có vết thương cần theo dõi vị trí, kích thước, dịch tiết, mùi, đau, đỏ nóng sưng và tình trạng băng.',
                diagnosis: 'Nguy cơ nhiễm trùng liên quan đến tổn thương da/mô và can thiệp chăm sóc vết thương.',
                goal: 'Vết thương tiến triển tốt, không có dấu hiệu nhiễm trùng, dịch tiết giảm và bệnh nhân được kiểm soát đau.',
                intervention: 'Thay băng đúng quy trình vô khuẩn; đánh giá dịch tiết, mùi, màu sắc, đau; giữ vùng tổn thương sạch khô; hướng dẫn bệnh nhân không tự tháo băng; báo bác sĩ khi có sốt, mủ, mùi hôi, đỏ lan hoặc đau tăng.',
                frequency: 'Thay băng theo y lệnh; kiểm tra băng và đau mỗi ca.'
            }
        };

        function setValue(id, value) {
            const element = document.getElementById(id);
            if (element) element.value = value;
        }

        window.updateTaskState = function (checkbox) {
            const card = checkbox.closest('.task-card');
            if (!card) return;
            const anyChecked = Array.from(card.querySelectorAll('input[type="checkbox"]')).some(cb => cb.checked);
            if (anyChecked) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
        };

        window.addTimeToTask = function (btn, taskKey) {
            const container = btn.closest('.task-card').querySelector('.task-times');
            const input = btn.previousElementSibling;
            const timeVal = input.value;
            if (!timeVal) return;

            const exists = Array.from(container.querySelectorAll('input[type="checkbox"]')).some(cb => cb.value === timeVal);
            if (!exists) {
                const labelHtml = `<label style="display:flex; align-items:center; gap:6px;"><input type="checkbox" name="planned_tasks[${taskKey}][times][]" value="${timeVal}" checked onchange="updateTaskState(this)"> ${timeVal}</label>`;

                container.insertAdjacentHTML('beforeend', labelHtml);
                const labels = Array.from(container.querySelectorAll('label'));
                labels.sort((a, b) => {
                    const timeA = a.querySelector('input').value;
                    const timeB = b.querySelector('input').value;
                    return timeA.localeCompare(timeB);
                });
                container.innerHTML = '';
                labels.forEach(l => container.appendChild(l));

                updateTaskState(container.querySelector('input[value="' + timeVal + '"]'));
            } else {
                const cb = Array.from(container.querySelectorAll('input[type="checkbox"]')).find(cb => cb.value === timeVal);
                if (cb && !cb.checked) {
                    cb.checked = true;
                    updateTaskState(cb);
                }
            }
            input.value = '';
        };

        window.presetTimes = function (btn, timesToSelect) {
            const card = btn.closest('.task-card');
            if (!card) return;

            card.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);

            card.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                if (timesToSelect.includes(cb.value)) {
                    cb.checked = true;
                }
            });
            updateTaskState(card.querySelector('input[type="checkbox"]'));
        };

        window.addCustomTask = function () {
            const grid = document.getElementById('tasks_grid');
            const btn = grid.querySelector('.btn-add-task');
            const uniqueId = 'custom_' + Date.now();

            const hours = ['06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00'];
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
                setValue('plan_title', template.title);
                setValue('assessment_summary', template.assessment);
                setValue('nursing_diagnosis', template.diagnosis);
                setValue('care_goal', template.goal);
                setValue('intervention_plan', template.intervention);
                setValue('monitoring_frequency', template.frequency);
            });
        });

    })();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>