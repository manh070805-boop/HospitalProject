<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Chi tiết ca nội trú #<?= (int) $admission['admission_id'] ?></h2>
            <p><?= htmlspecialchars($admission['patient_name']) ?> &mdash;
                <?= htmlspecialchars($admission['department_name'] ?? 'Chưa rõ khoa') ?>
            </p>
        </div>
        <div class="page-header-actions">
            <a href="index.php?url=internal/monitoring" class="btn btn-secondary">
                <i class="iconsax" icon-name="arrow-left"></i> Danh sách nội trú
            </a>
            <?php if (($role ?? '') === 'head_nurse' && strtolower(trim($admission['status'] ?? '')) === 'active'): ?>
                <a href="index.php?url=internal/admissions/transfer&id=<?= (int) $admission['admission_id'] ?>"
                    class="btn btn-light">
                    <i class="iconsax" icon-name="refresh"></i> Chuyển khoa/giường
                </a>
            <?php endif; ?>
            <?php if (in_array($admission['status'], ['active', 'stable'])): ?>
                <a href="index.php?url=internal/vitals/create&admission_id=<?= (int) $admission['admission_id'] ?>"
                    class="btn btn-primary">
                    <i class="iconsax" icon-name="heart"></i> Ghi sinh tồn
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success reveal-item">
            <i class="iconsax" icon-name="tick-circle"></i>
            <?= htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item">
            <i class="iconsax" icon-name="info-circle"></i>
            <?= htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($openAlerts)): ?>
        <div class="alert alert-danger reveal-item" style="display:flex; align-items:center; gap:10px;">
            <i class="iconsax" icon-name="warning-triangle"></i>
            <strong><?= count($openAlerts) ?> cảnh báo sinh tồn đang mở</strong>
            <a href="index.php?url=internal/alerts&admission_id=<?= (int) $admission['admission_id'] ?>"
                class="btn btn-sm btn-danger" style="margin-left:auto;">Xem & xử lý</a>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        <div>
            <div class="card reveal-item" style="margin-bottom: 20px;">
                <div class="card-header">
                    <h4><i class="iconsax" icon-name="user-2" style="font-size:16px;"></i> Thông tin bệnh nhân</h4>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px;">
                        <div>
                            <span class="table-text-muted">Mã BN:</span>
                            <strong><?= htmlspecialchars($admission['patient_code']) ?></strong>
                        </div>
                        <div>
                            <span class="table-text-muted">Họ tên:</span>
                            <strong><?= htmlspecialchars($admission['patient_name']) ?></strong>
                        </div>
                        <div>
                            <span class="table-text-muted">CCCD:</span>
                            <?= !empty($admission['citizen_id']) ? htmlspecialchars($admission['citizen_id']) : '---' ?>
                        </div>
                        <div>
                            <span class="table-text-muted">Ngày sinh:</span>
                            <?= !empty($admission['date_of_birth']) ? date('d/m/Y', strtotime($admission['date_of_birth'])) : '---' ?>
                        </div>
                        <div>
                            <span class="table-text-muted">Giới tính:</span>
                            <?= htmlspecialchars($admission['gender'] ?? '---') ?>
                        </div>
                        <div>
                            <span class="table-text-muted">Điện thoại:</span>
                            <?= htmlspecialchars($admission['phone'] ?? '---') ?>
                        </div>
                        <div>
                            <span class="table-text-muted">Địa chỉ:</span>
                            <?= htmlspecialchars($admission['address'] ?? '---') ?>
                        </div>
                    </div>
                    <div style="margin-top:10px;">
                        <a href="index.php?url=internal/patients/show&id=<?= (int) $admission['patient_id'] ?>"
                            class="btn btn-sm btn-light">
                            <i class="iconsax" icon-name="eye"></i> Xem hồ sơ bệnh nhân
                        </a>
                    </div>
                </div>
            </div>


            <div class="card reveal-item delay-1" style="margin-bottom: 20px;">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <h4><i class="iconsax" icon-name="hospital" style="font-size:16px;"></i> Thông tin ca nội trú</h4>
                    <a href="index.php?url=internal/admissions/bed-history&admission_id=<?= (int) $admission['admission_id'] ?>"
                        class="btn btn-sm btn-light">
                        <i class="iconsax" icon-name="clock"></i> Lịch sử chuyển giường
                    </a>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px;">
                        <div>
                            <span class="table-text-muted">Khoa:</span>
                            <strong><?= htmlspecialchars($admission['department_name'] ?? '---') ?></strong>
                        </div>
                        <div>
                            <span class="table-text-muted">Phòng / Giường:</span>
                            <strong><?= htmlspecialchars($admission['room_name'] ?? '---') ?></strong> — Giường
                            <?= htmlspecialchars($admission['bed_code'] ?? '---') ?>
                        </div>
                        <div>
                            <span class="table-text-muted">Bác sĩ phụ trách:</span>
                            <strong><?= htmlspecialchars($admission['doctor_name'] ?? 'Chưa phân công') ?></strong>
                        </div>
                        <div>
                            <span class="table-text-muted">Điều dưỡng phụ trách:</span>
                            <strong><?= htmlspecialchars($admission['nurse_name'] ?? 'Chưa phân công') ?></strong>
                        </div>
                        <div>
                            <span class="table-text-muted">Ngày nhập viện:</span>
                            <?= !empty($admission['admission_date']) ? date('H:i d/m/Y', strtotime($admission['admission_date'])) : '---' ?>
                        </div>
                        <div>
                            <span class="table-text-muted">Trạng thái:</span>
                            <?php $st = strtolower($admission['status'] ?? 'active'); ?>
                            <?php if ($st === 'active'): ?>
                                <span class="badge badge-active"><span class="status-dot dot-active"></span>Đang điều
                                    trị</span>
                            <?php elseif ($st === 'discharged'): ?>
                                <span class="badge badge-discharged"><span class="status-dot dot-discharged"></span>Đã xuất
                                    viện</span>
                            <?php else: ?>
                                <span class="badge badge-inactive"><span
                                        class="status-dot dot-inactive"></span><?= htmlspecialchars($admission['status']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($admission['initial_diagnosis'])): ?>
                            <div style="grid-column: span 2;">
                                <span class="table-text-muted">Chẩn đoán ban đầu:</span>
                                <p style="margin:4px 0 0;"><?= htmlspecialchars($admission['initial_diagnosis']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>


            <div class="card reveal-item delay-2" style="margin-bottom: 20px;">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <h4><i class="iconsax" icon-name="clipboard-text-1" style="font-size:16px;"></i> Kế hoạch điều trị
                        (Bác sĩ)</h4>
                    <a href="index.php?url=internal/treatment-plans/history&admission_id=<?= (int) $admission['admission_id'] ?>"
                        class="btn btn-sm btn-light">Lịch sử</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($treatmentPlan)): ?>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:14px;">
                            <div><span class="table-text-muted">Chẩn đoán:</span>
                                <strong><?= htmlspecialchars($treatmentPlan['diagnosis'] ?? '---') ?></strong>
                            </div>
                            <div><span class="table-text-muted">Mục tiêu điều trị:</span>
                                <?= htmlspecialchars($treatmentPlan['treatment_goal'] ?? '---') ?></div>
                        </div>
                        <?php if (!empty($treatmentPlan['medical_order'])): ?>
                            <div style="margin-bottom:8px;">
                                <span class="table-text-muted">Y lệnh:</span>
                                <p style="margin:4px 0 0; white-space:pre-line;">
                                    <?= htmlspecialchars($treatmentPlan['medical_order']) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        <div style="margin-top:12px; display:flex; gap:8px;">
                            <a href="index.php?url=internal/treatment-plans/show&id=<?= (int) $treatmentPlan['treatment_plan_id'] ?>"
                                class="btn btn-sm btn-light">
                                <i class="iconsax" icon-name="eye"></i> Xem đầy đủ
                            </a>
                            <?php if (in_array($admission['status'], ['active', 'stable'])): ?>
                                <a href="index.php?url=internal/care-plans/create&admission_id=<?= (int) $admission['admission_id'] ?>"
                                    class="btn btn-sm btn-primary">
                                    <i class="iconsax" icon-name="clipboard-tick"></i> Lập kế hoạch chăm sóc
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="padding:24px 0;">
                            <i class="iconsax empty-state-icon" icon-name="clipboard-text-1"></i>
                            <p>Bác sĩ chưa lập kế hoạch điều trị cho ca này.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>


            <div class="card reveal-item delay-3" style="margin-bottom: 20px;">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <h4><i class="iconsax" icon-name="heart" style="font-size:16px;"></i> Kế hoạch chăm sóc điều dưỡng
                    </h4>
                    <a href="index.php?url=internal/care-plans/history&admission_id=<?= (int) $admission['admission_id'] ?>"
                        class="btn btn-sm btn-light">Xem tất cả</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($latestCarePlan)): ?>
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
                            <div>
                                <strong><?= htmlspecialchars($latestCarePlan['plan_title'] ?? 'Không có tiêu đề') ?></strong>
                                <p class="table-text-muted" style="margin:4px 0 0;">
                                    <?= htmlspecialchars(mb_substr($latestCarePlan['assessment_summary'] ?? '', 0, 120)) ?>...
                                </p>
                            </div>
                            <?php $cs = $latestCarePlan['status'] ?? 'draft'; ?>
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
                        <div style="margin-top:12px;">
                            <a href="index.php?url=internal/care-plans/show&id=<?= (int) $latestCarePlan['care_plan_id'] ?>"
                                class="btn btn-sm btn-light">
                                <i class="iconsax" icon-name="eye"></i> Xem chi tiết
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="padding:24px 0;">
                            <i class="iconsax empty-state-icon" icon-name="heart"></i>
                            <p>Chưa có kế hoạch chăm sóc nào.</p>
                            <?php if (in_array($admission['status'], ['active', 'stable']) && !empty($treatmentPlan)): ?>
                                <a href="index.php?url=internal/care-plans/create&admission_id=<?= (int) $admission['admission_id'] ?>"
                                    class="btn btn-primary" style="margin-top:10px;">
                                    <i class="iconsax" icon-name="add"></i> Lập kế hoạch ngay
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>


        <div>
            <div class="card reveal-item" style="margin-bottom: 20px;">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <h4><i class="iconsax" icon-name="heart-tick" style="font-size:16px;"></i> Sinh tồn gần nhất</h4>
                    <a href="index.php?url=internal/vitals/history&admission_id=<?= (int) $admission['admission_id'] ?>"
                        class="btn btn-sm btn-light">Lịch sử</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($latestVital)): ?>
                        <?php $risk = $latestVitalRisk ?? ['score' => 0, 'label' => 'Ổn định', 'class' => 'risk-stable', 'recommendation' => 'Tiếp tục theo dõi định kỳ theo ca.', 'next_label' => '4 giờ']; ?>
                        <div class="vital-admission-risk <?= htmlspecialchars($risk['class']) ?>">
                            <div>
                                <span>Risk Score</span>
                                <strong><?= (int) $risk['score'] ?> · <?= htmlspecialchars($risk['label']) ?></strong>
                            </div>
                            <small><?= htmlspecialchars($risk['recommendation']) ?></small>
                        </div>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
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
                                <strong><?= htmlspecialchars($latestVital['blood_pressure_systolic'] ?? '---') ?>/<?= htmlspecialchars($latestVital['blood_pressure_diastolic'] ?? '---') ?>
                                    mmHg</strong>
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
                        <p class="table-text-muted" style="margin-top:10px; font-size:12px;">
                            Ghi lúc:
                            <?= !empty($latestVital['recorded_at']) ? date('H:i d/m/Y', strtotime($latestVital['recorded_at'])) : '---' ?>
                            · Đề xuất đo lại: <?= htmlspecialchars($risk['next_label']) ?>
                        </p>
                    <?php else: ?>
                        <div class="empty-state" style="padding:16px 0;">
                            <p>Chưa có dữ liệu sinh tồn.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>


            <?php if (in_array($admission['status'], ['active', 'stable'])): ?>
                <div class="card reveal-item delay-1" style="margin-bottom: 20px;">
                    <div class="card-header">
                        <h4>Hành động nhanh</h4>
                    </div>
                    <div class="card-body" style="display:flex; flex-direction:column; gap:10px;">
                        <a href="index.php?url=internal/vitals/create&admission_id=<?= (int) $admission['admission_id'] ?>"
                            class="btn btn-primary">
                            <i class="iconsax" icon-name="heart"></i> Ghi sinh tồn
                        </a>
                        <a href="index.php?url=internal/tasks&admission_id=<?= (int) $admission['admission_id'] ?>"
                            class="btn btn-success">
                            <i class="iconsax" icon-name="task-list"></i> Công việc chăm sóc
                        </a>
                        <?php if (($role ?? '') === 'head_nurse' && strtolower(trim($admission['status'] ?? '')) === 'active'): ?>
                            <a href="index.php?url=internal/admissions/transfer&id=<?= (int) $admission['admission_id'] ?>"
                                class="btn btn-light">
                                <i class="iconsax" icon-name="refresh"></i> Chuyển khoa/giường
                            </a>
                        <?php endif; ?>
                        <a href="index.php?url=internal/care-plans/create&admission_id=<?= (int) $admission['admission_id'] ?>"
                            class="btn btn-light">
                            <i class="iconsax" icon-name="clipboard-tick"></i> Lập kế hoạch chăm sóc
                        </a>
                        <hr style="border:none; border-top:1px solid var(--border-subtle); margin:4px 0;">
                        <button type="button" class="btn btn-danger" style="width:100%;"
                            onclick="document.getElementById('dischargeModal').style.display='flex'">
                            <i class="iconsax" icon-name="logout-1"></i> Xuất viện
                        </button>
                    </div>
                <?php endif; ?>


                <div class="card reveal-item delay-2">
                    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <h4><i class="iconsax" icon-name="warning-triangle" style="font-size:16px;"></i> Cảnh báo
                            (<?= count($openAlerts) ?>)</h4>
                        <a href="index.php?url=internal/alerts&admission_id=<?= (int) $admission['admission_id'] ?>"
                            class="btn btn-sm btn-light">Lịch sử</a>
                    </div>
                    <div class="card-body" style="display:flex; flex-direction:column; gap:8px;">
                        <?php if (empty($openAlerts)): ?>
                            <div class="empty-state" style="padding:10px 0;">
                                <p style="font-size:13px;">Không có cảnh báo chưa xử lý.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($openAlerts as $al): ?>
                                <div
                                    style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--border-subtle);">
                                    <div style="flex:1;">
                                        <?php $sev = $al['severity'] ?? 'high'; ?>
                                        <span class="badge <?= $sev === 'critical' ? 'badge-discharged' : 'badge-info' ?>"
                                            style="font-size:10px; padding:2px 6px;">
                                            <?= strtoupper($sev) ?>
                                        </span>
                                        <div style="margin-top:4px; font-size:13px; font-weight:500;">
                                            <?= htmlspecialchars($al['alert_type'] ?? '') ?>
                                        </div>
                                        <div class="table-text-muted" style="font-size:12px;">
                                            <?= htmlspecialchars($al['alert_message'] ?? '') ?>
                                        </div>
                                    </div>
                                    <div style="display:flex; gap:4px;">
                                        <?php
                                        $returnUrl = "index.php?url=internal/admissions/show&id=" . (int) $admission['admission_id'];
                                        $st = $al['status'] ?? 'new';
                                        ?>
                                        <?php if ($st === 'new'): ?>
                                            <form action="index.php?url=internal/alerts/acknowledge" method="POST">
                                                <input type="hidden" name="alert_id" value="<?= (int) $al['alert_id'] ?>">
                                                <input type="hidden" name="return_url" value="<?= htmlspecialchars($returnUrl) ?>">
                                                <button type="submit" class="btn-action btn-action-view" title="Xác nhận"><i
                                                        class="iconsax" icon-name="tick-circle"></i></button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="index.php?url=internal/alerts/resolve" method="POST">
                                            <input type="hidden" name="alert_id" value="<?= (int) $al['alert_id'] ?>">
                                            <input type="hidden" name="return_url" value="<?= htmlspecialchars($returnUrl) ?>">
                                            <button type="submit" class="btn-action btn-action-edit" title="Xử lý xong"><i
                                                    class="iconsax" icon-name="verify"></i></button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div style="margin-top:8px;">
                                <a href="index.php?url=internal/alerts&admission_id=<?= (int) $admission['admission_id'] ?>"
                                    class="btn btn-sm btn-danger" style="width:100%;">
                                    Quản lý tất cả
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div id="bedHistoryPanel" style="display:none; border-top:1px solid #e5e7eb; padding:14px 16px;">
                    <?php if (empty($bedHistory)): ?>
                        <p style="margin:0; color:#6b7280; font-size:13px; text-align:center;">Chưa có lịch sử chuyển
                            giường.</p>
                    <?php else: ?>
                        <ul
                            style="list-style:none; padding:0; margin:0; position:relative; padding-left:20px; border-left:2px solid #e5e7eb;">
                            <?php foreach ($bedHistory as $bh): ?>
                                <li style="position:relative; margin-bottom:14px; padding-left:10px;">
                                    <span
                                        style="position:absolute; left:-21px; top:4px; width:10px; height:10px; border-radius:50%; background:#3b82f6; border:2px solid #fff;"></span>
                                    <div style="font-size:12px; color:#6b7280;">
                                        <?= !empty($bh['changed_at']) ? date('H:i d/m/Y', strtotime($bh['changed_at'])) : '---' ?>
                                    </div>
                                    <div style="font-weight:500; font-size:14px; margin-top:3px;">
                                        <?= htmlspecialchars($bh['new_department_name'] ?? 'Không rõ') ?>
                                        &mdash; Phòng <?= htmlspecialchars($bh['new_room_name'] ?? '---') ?>
                                        &mdash; Giường <?= htmlspecialchars($bh['new_bed_code'] ?? '---') ?>
                                    </div>
                                    <?php if (!empty($bh['reason'])): ?>
                                        <div style="font-size:12px; color:#4b5563; margin-top:2px;">
                                            Lý do: <?= htmlspecialchars($bh['reason']) ?>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


    <?php if (in_array($admission['status'], ['active', 'stable'])): ?>
        <div id="dischargeModal" class="modal-overlay"
            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
            <div class="modal-content"
                style="background:#fff; border-radius:8px; width:450px; max-width:90%; box-shadow:0 4px 12px rgba(0,0,0,0.15); overflow:hidden;">
                <div class="modal-header"
                    style="padding:16px 20px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                    <h3 style="margin:0; font-size:18px; color:#111827;">Xác nhận xuất viện</h3>
                    <button type="button" onclick="document.getElementById('dischargeModal').style.display='none'"
                        style="background:none; border:none; cursor:pointer; color:#6b7280;">
                        <i class="iconsax" icon-name="x" style="font-size:20px;"></i>
                    </button>
                </div>
                <form action="index.php?url=internal/admissions/status" method="POST" id="dischargeForm">
                    <div class="modal-body" style="padding:20px;">
                        <p style="margin-top:0; margin-bottom:16px; color:#4b5563; font-size:14px;">Bạn có chắc chắn muốn
                            xuất viện cho bệnh nhân này?</p>
                        <input type="hidden" name="admission_id" value="<?= (int) $admission['admission_id'] ?>">
                        <input type="hidden" name="status" value="discharged">
                        <input type="hidden" name="return_url"
                            value="index.php?url=internal/admissions/show&id=<?= (int) $admission['admission_id'] ?>">

                        <div
                            style="margin-bottom:16px; padding:12px; background:#fef2f2; border:1px solid #fecaca; border-radius:6px;">
                            <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer; margin-bottom:0;">
                                <input type="checkbox" name="force_discharge" id="force_discharge" value="1"
                                    style="margin-top:3px;">
                                <div>
                                    <strong style="color:#b91c1c; font-size:14px; display:block;">Xuất viện khẩn cấp /
                                        Chuyển viện</strong>
                                    <span style="color:#991b1b; font-size:12px; display:block; margin-top:2px;">Bỏ qua tất
                                        cả cảnh báo, công việc và kế hoạch chăm sóc chưa hoàn thành.</span>
                                </div>
                            </label>
                        </div>

                        <div id="dischargeReasonGroup" style="display:none;">
                            <label for="discharge_reason"
                                style="display:block; margin-bottom:6px; font-weight:500; font-size:14px;">Lý do xuất viện
                                khẩn cấp <span style="color:red;">*</span></label>
                            <textarea name="discharge_reason" id="discharge_reason" rows="3"
                                style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;"
                                placeholder="Nhập lý do chuyển viện hoặc xuất viện khẩn cấp..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer"
                        style="padding:16px 20px; border-top:1px solid #e5e7eb; background:#f9fafb; display:flex; justify-content:flex-end; gap:10px;">
                        <button type="button" class="btn btn-light"
                            onclick="document.getElementById('dischargeModal').style.display='none'">Hủy</button>
                        <button type="submit" class="btn btn-danger"><i class="iconsax" icon-name="logout-1"></i> Xuất
                            viện</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.getElementById('force_discharge').addEventListener('change', function () {
                var reasonGroup = document.getElementById('dischargeReasonGroup');
                var reasonInput = document.getElementById('discharge_reason');
                if (this.checked) {
                    reasonGroup.style.display = 'block';
                    reasonInput.required = true;
                } else {
                    reasonGroup.style.display = 'none';
                    reasonInput.required = false;
                    reasonInput.value = '';
                }
            });
        </script>
    <?php endif; ?>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>