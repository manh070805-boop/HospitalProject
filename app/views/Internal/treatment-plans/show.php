<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Kế hoạch điều trị (Bác sĩ)</h2>
            <p><?= htmlspecialchars($treatmentPlan['patient_name'] ?? '') ?> &mdash; BS. <?= htmlspecialchars($treatmentPlan['doctor_name'] ?? '') ?></p>
        </div>
        <a href="index.php?url=internal/admissions/show&id=<?= (int) $treatmentPlan['admission_id'] ?>" class="btn btn-secondary">
            <i class="iconsax" icon-name="arrow-left"></i> Trở về
        </a>
    </div>

    <div class="card reveal-item">
        <div class="card-header"><h4>Chi tiết kế hoạch điều trị</h4></div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:18px;">
                <div>
                    <span class="table-text-muted">Chẩn đoán:</span>
                    <p style="font-weight:600; margin:4px 0 0;"><?= htmlspecialchars($treatmentPlan['diagnosis'] ?? '---') ?></p>
                </div>
                <div>
                    <span class="table-text-muted">Mục tiêu điều trị:</span>
                    <p style="margin:4px 0 0;"><?= htmlspecialchars($treatmentPlan['treatment_goal'] ?? '---') ?></p>
                </div>
                <?php if (!empty($treatmentPlan['medical_order'])): ?>
                    <div style="grid-column: span 2;">
                        <span class="table-text-muted">Y lệnh:</span>
                        <pre style="white-space:pre-wrap; font-family:inherit; margin:4px 0 0;"><?= htmlspecialchars($treatmentPlan['medical_order']) ?></pre>
                    </div>
                <?php endif; ?>
                <?php if (!empty($treatmentPlan['treatment_note'])): ?>
                    <div style="grid-column: span 2;">
                        <span class="table-text-muted">Ghi chú bác sĩ:</span>
                        <p style="margin:4px 0 0;"><?= htmlspecialchars($treatmentPlan['treatment_note']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
