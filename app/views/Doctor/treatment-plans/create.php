<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
<div class="page-header reveal-item">
    <div class="page-header-info">
        <h2><?= !empty($currentTreatmentPlan) ? 'Cập nhật kế hoạch điều trị' : 'Tạo kế hoạch điều trị' ?></h2>
        <p>
            <?= htmlspecialchars($admission['patient_name'] ?? '---') ?>
            · <?= htmlspecialchars($admission['patient_code'] ?? '---') ?>
            · <?= htmlspecialchars($admission['room_name'] ?? '---') ?>/<?= htmlspecialchars($admission['bed_code'] ?? '---') ?>
        </p>
    </div>

    <div class="page-header-actions">
        <a href="index.php?url=doctor-portal/admissions/show&id=<?= (int) $admission['admission_id'] ?>" class="btn btn-secondary">
            <i class="iconsax" icon-name="arrow-left"></i> Quay lại ca nội trú
        </a>
    </div>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error reveal-item">
        <i class="iconsax" icon-name="info-circle"></i>
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<?php if (empty($latestVital)): ?>
    <div class="alert alert-error reveal-item">
        <i class="iconsax" icon-name="heart"></i>
        Bệnh nhân chưa có dữ liệu sinh tồn. Cần điều dưỡng ghi sinh tồn trước khi bác sĩ lập kế hoạch điều trị.
    </div>
<?php endif; ?>

<div class="content-grid">
    <div class="card reveal-item content-main">
        <div class="card-header">
            <h3><i class="iconsax" icon-name="clipboard-text-1"></i> Nội dung kế hoạch điều trị</h3>
        </div>

        <div class="card-body">
            <form method="POST" action="index.php?url=doctor-portal/treatment-plans/store">
                <input type="hidden" name="admission_id" value="<?= (int) $admission['admission_id'] ?>">

                <div class="form-group">
                    <label for="diagnosis">Chẩn đoán</label>
                    <textarea id="diagnosis" name="diagnosis" class="form-control" rows="4" required
                        placeholder="VD: Tăng huyết áp độ II, theo dõi biến chứng tim mạch..."><?= htmlspecialchars($_POST['diagnosis'] ?? $currentTreatmentPlan['diagnosis'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="treatment_goal">Mục tiêu điều trị</label>
                    <textarea id="treatment_goal" name="treatment_goal" class="form-control" rows="3"
                        placeholder="Mục tiêu lâm sàng cần đạt, chỉ số cần theo dõi..."><?= htmlspecialchars($_POST['treatment_goal'] ?? $currentTreatmentPlan['treatment_goal'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="medical_order">Y lệnh</label>
                    <textarea id="medical_order" name="medical_order" class="form-control" rows="7" required
                        placeholder="Thuốc, theo dõi, xét nghiệm/cận lâm sàng, ngưỡng báo bác sĩ..."><?= htmlspecialchars($_POST['medical_order'] ?? $currentTreatmentPlan['medical_order'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="treatment_note">Ghi chú điều trị</label>
                    <textarea id="treatment_note" name="treatment_note" class="form-control" rows="4"
                        placeholder="Chống chỉ định, điểm cần lưu ý khi điều dưỡng thực hiện..."><?= htmlspecialchars($_POST['treatment_note'] ?? $currentTreatmentPlan['treatment_note'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" <?= empty($latestVital) ? 'disabled' : '' ?>>
                        <i class="iconsax" icon-name="tick-circle"></i>
                        <?= !empty($currentTreatmentPlan) ? 'Cập nhật kế hoạch điều trị' : 'Lưu kế hoạch điều trị active' ?>
                    </button>
                    <a href="index.php?url=doctor-portal/admissions/show&id=<?= (int) $admission['admission_id'] ?>" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>

    <div class="content-sidebar">
        <div class="card reveal-item delay-1">
            <div class="card-header">
                <h3><i class="iconsax" icon-name="heart"></i> Sinh tồn gần nhất</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($latestVital)): ?>
                    <?php $risk = $latestVitalRisk ?? ['score' => 0, 'label' => 'Ổn định', 'class' => 'risk-stable', 'recommendation' => 'Tiếp tục theo dõi định kỳ.']; ?>
                    <div class="vital-admission-risk <?= htmlspecialchars($risk['class']) ?>">
                        <div>
                            <span>Risk Score</span>
                            <strong><?= (int) $risk['score'] ?> · <?= htmlspecialchars($risk['label']) ?></strong>
                        </div>
                        <small><?= htmlspecialchars($risk['recommendation']) ?></small>
                    </div>
                    <div class="chip-grid">
                        <div class="vital-chip"><span>HA</span><strong><?= htmlspecialchars($latestVital['blood_pressure_systolic'] ?? '---') ?>/<?= htmlspecialchars($latestVital['blood_pressure_diastolic'] ?? '---') ?></strong></div>
                        <div class="vital-chip"><span>SpO2</span><strong><?= htmlspecialchars($latestVital['spo2'] ?? '---') ?>%</strong></div>
                        <div class="vital-chip"><span>Mạch</span><strong><?= htmlspecialchars($latestVital['pulse'] ?? '---') ?></strong></div>
                        <div class="vital-chip"><span>Nhiệt độ</span><strong><?= htmlspecialchars($latestVital['temperature'] ?? '---') ?>°C</strong></div>
                    </div>
                <?php else: ?>
                    <div class="empty-state"><p>Chưa có dữ liệu sinh tồn.</p></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card reveal-item delay-2">
            <div class="card-header">
                <h3>Kế hoạch điều trị hiện hành</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($currentTreatmentPlan)): ?>
                    <strong><?= htmlspecialchars($currentTreatmentPlan['diagnosis'] ?? '---') ?></strong>
                    <div class="mt-2">
                        <span class="badge <?= ($currentTreatmentPlan['status'] ?? 'active') === 'active' ? 'badge-active' : 'badge-inactive' ?>">
                            <?= htmlspecialchars($currentTreatmentPlan['status'] ?? 'active') ?>
                        </span>
                    </div>
                    <p class="detail-text table-text-muted">
                        <?= htmlspecialchars(mb_substr($currentTreatmentPlan['medical_order'] ?? '', 0, 160)) ?>
                    </p>
                <?php else: ?>
                    <div class="empty-state"><p>Chưa có kế hoạch điều trị.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
