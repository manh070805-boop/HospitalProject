<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Chi tiết ca nội trú #<?= (int) $admission['admission_id'] ?></h2>
            <p>
                <?= htmlspecialchars($admission['patient_name'] ?? '---') ?>
                · <?= htmlspecialchars($admission['department_name'] ?? 'Chưa rõ khoa') ?>
            </p>
        </div>

        <div class="page-header-actions">
            <a href="index.php?url=doctor-portal/admissions" class="btn btn-secondary">
                <i class="iconsax" icon-name="arrow-left"></i> Danh sách bệnh nhân
            </a>

            <?php if (in_array($admission['status'], ['active', 'stable'], true)): ?>
                <a href="index.php?url=doctor-portal/treatment-plans/create&admission_id=<?= (int) $admission['admission_id'] ?>"
                    class="btn btn-primary">
                    <i class="iconsax" icon-name="add"></i> Tạo y lệnh
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success reveal-item">
            <i class="iconsax" icon-name="tick-circle"></i>
            <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item">
            <i class="iconsax" icon-name="info-circle"></i>
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="content-grid">
        <div class="content-main">
            <div class="card reveal-item">
                <div class="card-header">
                    <h3><i class="iconsax" icon-name="user-2"></i> Thông tin bệnh nhân</h3>
                </div>

                <div class="card-body">
                    <div class="detail-grid detail-grid-tight">
                        <div class="detail-field">
                            <span class="detail-label">Mã BN:</span>
                            <strong
                                class="detail-value"><?= htmlspecialchars($admission['patient_code'] ?? '---') ?></strong>
                        </div>

                        <div class="detail-field">
                            <span class="detail-label">Họ tên:</span>
                            <strong
                                class="detail-value"><?= htmlspecialchars($admission['patient_name'] ?? '---') ?></strong>
                        </div>

                        <div class="detail-field">
                            <span class="detail-label">Ngày sinh:</span>
                            <?= !empty($admission['date_of_birth']) ? date('d/m/Y', strtotime($admission['date_of_birth'])) : '---' ?>
                        </div>

                        <div class="detail-field">
                            <span class="detail-label">Giới tính:</span>
                            <?= htmlspecialchars($admission['gender'] ?? '---') ?>
                        </div>

                        <div class="detail-field">
                            <span class="detail-label">Điện thoại:</span>
                            <?= htmlspecialchars($admission['phone'] ?? '---') ?>
                        </div>

                        <div class="detail-field">
                            <span class="detail-label">Địa chỉ:</span>
                            <?= htmlspecialchars($admission['address'] ?? '---') ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card reveal-item delay-1">
                <div class="card-header">
                    <h3><i class="iconsax" icon-name="hospital"></i> Thông tin nội trú</h3>
                </div>

                <div class="card-body">
                    <div class="detail-grid detail-grid-tight">
                        <div class="detail-field">
                            <span class="detail-label">Khoa:</span>
                            <strong
                                class="detail-value"><?= htmlspecialchars($admission['department_name'] ?? '---') ?></strong>
                        </div>

                        <div class="detail-field">
                            <span class="detail-label">Phòng / Giường:</span>
                            <strong
                                class="detail-value"><?= htmlspecialchars($admission['room_name'] ?? '---') ?></strong>
                            · Giường <?= htmlspecialchars($admission['bed_code'] ?? '---') ?>
                        </div>

                        <div class="detail-field">
                            <span class="detail-label">Bác sĩ phụ trách:</span>
                            <strong
                                class="detail-value"><?= htmlspecialchars($admission['doctor_name'] ?? '---') ?></strong>
                        </div>

                        <div class="detail-field">
                            <span class="detail-label">Điều dưỡng phụ trách:</span>
                            <strong
                                class="detail-value"><?= htmlspecialchars($admission['nurse_name'] ?? '---') ?></strong>
                        </div>

                        <div class="detail-field">
                            <span class="detail-label">Ngày nhập viện:</span>
                            <?= !empty($admission['admission_date']) ? date('H:i d/m/Y', strtotime($admission['admission_date'])) : '---' ?>
                        </div>

                        <div class="detail-field">
                            <span class="detail-label">Trạng thái:</span>
                            <span
                                class="badge badge-active"><?= htmlspecialchars($admission['status'] ?? 'active') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card reveal-item delay-2">
                <div class="card-header">
                    <h3><i class="iconsax" icon-name="clipboard-text-1"></i> Kế hoạch điều trị</h3>
                    <a href="index.php?url=doctor-portal/treatment-plans/history&admission_id=<?= (int) $admission['admission_id'] ?>"
                        class="btn btn-sm btn-light">Lịch sử</a>
                </div>

                <div class="card-body">
                    <?php if (!empty($treatmentPlan)): ?>
                        <div class="detail-grid detail-grid-tight">
                            <div class="detail-field">
                                <span class="detail-label">Chẩn đoán:</span>
                                <p class="detail-text detail-text-strong">
                                    <?= htmlspecialchars($treatmentPlan['diagnosis'] ?? '---') ?>
                                </p>
                            </div>

                            <div class="detail-field">
                                <span class="detail-label">Mục tiêu điều trị:</span>
                                <p class="detail-text">
                                    <?= htmlspecialchars($treatmentPlan['treatment_goal'] ?? '---') ?>
                                </p>
                            </div>
                        </div>

                        <div class="detail-block detail-block-spaced">
                            <span class="detail-label">Y lệnh:</span>
                            <p class="detail-text">
                                <?= htmlspecialchars($treatmentPlan['medical_order'] ?? '---') ?>
                            </p>
                        </div>

                        <?php if (!empty($treatmentPlan['treatment_note'])): ?>
                            <div class="detail-block detail-block-spaced">
                                <span class="detail-label">Ghi chú:</span>
                                <p class="detail-text">
                                    <?= htmlspecialchars($treatmentPlan['treatment_note']) ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <div class="section-actions section-actions-spaced">
                            <a href="index.php?url=doctor-portal/treatment-plans/show&id=<?= (int) $treatmentPlan['treatment_plan_id'] ?>"
                                class="btn btn-sm btn-light">
                                <i class="iconsax" icon-name="eye"></i> Xem đầy đủ
                            </a>

                            <a href="index.php?url=doctor-portal/treatment-plans/create&admission_id=<?= (int) $admission['admission_id'] ?>"
                                class="btn btn-sm btn-primary">
                                <i class="iconsax" icon-name="refresh"></i> Cập nhật kế hoạch điều trị
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="iconsax empty-state-icon" icon-name="clipboard-text-1"></i>
                            <p>Bệnh nhân chưa có kế hoạch điều trị active.</p>
                            <a href="index.php?url=doctor-portal/treatment-plans/create&admission_id=<?= (int) $admission['admission_id'] ?>"
                                class="btn btn-primary mt-3">
                                <i class="iconsax" icon-name="add"></i> Tạo kế hoạch điều trị đầu tiên
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="content-sidebar">
            <div class="card reveal-item">
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
                            <div class="vital-chip">
                                <span class="table-text-muted">Nhiệt độ</span>
                                <strong><?= htmlspecialchars($latestVital['temperature'] ?? '---') ?>°C</strong>
                            </div>

                            <div class="vital-chip">
                                <span class="table-text-muted">Mạch</span>
                                <strong><?= htmlspecialchars($latestVital['pulse'] ?? '---') ?> lần/phút</strong>
                            </div>

                            <div class="vital-chip">
                                <span class="table-text-muted">Huyết áp</span>
                                <strong>
                                    <?= htmlspecialchars($latestVital['blood_pressure_systolic'] ?? '---') ?>/<?= htmlspecialchars($latestVital['blood_pressure_diastolic'] ?? '---') ?>
                                </strong>
                            </div>

                            <div class="vital-chip">
                                <span class="table-text-muted">SpO2</span>
                                <strong><?= htmlspecialchars($latestVital['spo2'] ?? '---') ?>%</strong>
                            </div>

                            <div class="vital-chip">
                                <span class="table-text-muted">Nhịp thở</span>
                                <strong><?= htmlspecialchars($latestVital['respiratory_rate'] ?? '---') ?> lần/phút</strong>
                            </div>
                        </div>

                        <p class="meta-small">
                            Ghi lúc:
                            <?= !empty($latestVital['recorded_at']) ? date('H:i d/m/Y', strtotime($latestVital['recorded_at'])) : '---' ?>
                            · <?= htmlspecialchars($latestVital['recorded_by_name'] ?? '---') ?>
                        </p>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Chưa có dữ liệu sinh tồn.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card reveal-item delay-1">
                <div class="card-header">
                    <div>
                        <h3><i class="iconsax" icon-name="warning-triangle"></i> Cảnh báo</h3>
                        <?php if (!empty($openAlerts)): ?>
                            <p><?= count($openAlerts) ?> cảnh báo đang mở</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body">
                    <?php if (empty($openAlerts)): ?>
                        <div class="empty-state">
                            <p>Không có cảnh báo đang mở.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach (array_slice($openAlerts, 0, 2) as $alert): ?>
                            <?php $sev = $alert['severity'] ?? 'medium'; ?>
                            <div class="alert-item">
                                <span
                                    class="badge <?= in_array($sev, ['critical', 'high'], true) ? 'badge-danger' : 'badge-info' ?>">
                                    <?= htmlspecialchars($sev) ?>
                                </span>

                                <strong class="alert-item-title">
                                    <?= htmlspecialchars($alert['alert_type'] ?? 'Cảnh báo') ?>
                                </strong>

                                <p class="table-text-muted alert-item-text">
                                    <?= htmlspecialchars($alert['alert_message'] ?? '') ?>
                                </p>

                            </div>
                        <?php endforeach; ?>

                        <div class="mt-3">
                            <a href="index.php?url=doctor-portal/alerts&patient_id=<?= (int) ($admission['patient_id'] ?? 0) ?>"
                                class="btn btn-sm btn-light">
                                <i class="iconsax" icon-name="eye"></i> Xem chi tiết
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card reveal-item delay-2">
                <div class="card-header">
                    <h3><i class="iconsax" icon-name="clipboard-tick"></i> Kế hoạch chăm sóc mới nhất</h3>
                </div>

                <div class="card-body">
                    <?php if (!empty($latestCarePlan)): ?>
                        <strong><?= htmlspecialchars($latestCarePlan['plan_title'] ?? 'Không có tiêu đề') ?></strong>
                        <p class="table-text-muted mb-2">
                            <?= htmlspecialchars(mb_substr($latestCarePlan['assessment_summary'] ?? '', 0, 120)) ?>
                        </p>
                        <span class="badge badge-info">
                            <?= htmlspecialchars($latestCarePlan['status'] ?? 'draft') ?>
                        </span>
                        <p class="meta-small">
                            Tạo bởi <?= htmlspecialchars($latestCarePlan['created_by_name'] ?? '---') ?>
                        </p>
                        <div class="mt-3">
                            <a href="index.php?url=doctor-portal/care-plans/show&id=<?= (int) $latestCarePlan['care_plan_id'] ?>"
                                class="btn btn-sm btn-light">
                                <i class="iconsax" icon-name="eye"></i> Xem chi tiết kế hoạch
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Chưa có kế hoạch chăm sóc.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
