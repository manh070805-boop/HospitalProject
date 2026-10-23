<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="theme-patients">

    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Chi tiết bệnh nhân</h2>
            <p>Hồ sơ &amp; lịch sử nhập viện của <strong><?= htmlspecialchars($patient['full_name']) ?></strong></p>
        </div>
        <div style="display:flex;gap:.6rem;align-items:center;">
            <a href="index.php?url=patients/edit&id=<?= (int) $patient['patient_id'] ?>"
                class="btn btn-patient reveal-item delay-1">
                <i class="iconsax" icon-name="edit-2"></i>
                Chỉnh sửa
            </a>
            <a href="index.php?url=patients" class="btn btn-secondary reveal-item delay-1">
                <i class="iconsax" icon-name="arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    <!-- ── Trạng thái nhập viện hiện tại ── -->
    <div class="stats-grid stats-grid-compact reveal-item delay-1" style="margin-bottom:1.5rem;">
        <div class="stat-card <?= $activeAdmission ? 'metric-card-border-blue' : 'metric-card-border-purple' ?>">
            <div class="metric-row">
                <div>
                    <h3>Trạng thái</h3>
                    <?php if ($activeAdmission): ?>
                        <p class="metric-value-blue" style="font-size:1.05rem;font-weight:700;">
                            <i class="iconsax" icon-name="hospital" style="vertical-align:middle;margin-right:4px;"></i>
                            Đang nằm viện
                        </p>
                    <?php else: ?>
                        <p class="metric-value-purple" style="font-size:1.05rem;font-weight:700;">
                            <i class="iconsax" icon-name="logout-1" style="vertical-align:middle;margin-right:4px;"></i>
                            Đã xuất viện / Chưa nhập viện
                        </p>
                    <?php endif; ?>
                </div>
                <div class="metric-icon-box <?= $activeAdmission ? 'is-blue' : 'is-purple' ?>">
                    <i class="iconsax" icon-name="<?= $activeAdmission ? 'hospital' : 'logout-1' ?>"></i>
                </div>
            </div>
        </div>

        <?php if ($activeAdmission): ?>
            <div class="stat-card metric-card-border-blue">
                <div class="metric-row">
                    <div>
                        <h3>Khoa điều trị</h3>
                        <p class="metric-value-blue" style="font-size:.95rem;font-weight:600;">
                            <?= htmlspecialchars($activeAdmission['department_name'] ?? '---') ?>
                        </p>
                    </div>
                    <div class="metric-icon-box is-blue">
                        <i class="iconsax" icon-name="buildings-2"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card metric-card-border-pink">
                <div class="metric-row">
                    <div>
                        <h3>Phòng / Giường</h3>
                        <p class="metric-value-pink" style="font-size:.95rem;font-weight:600;">
                            <?= htmlspecialchars(($activeAdmission['room_number'] ?? '?') . ' / Giường ' . ($activeAdmission['bed_number'] ?? '?')) ?>
                        </p>
                    </div>
                    <div class="metric-icon-box is-pink">
                        <i class="iconsax" icon-name="hospital"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card metric-card-border-green">
                <div class="metric-row">
                    <div>
                        <h3>Bác sĩ phụ trách</h3>
                        <p class="metric-value-green" style="font-size:.95rem;font-weight:600;">
                            <?= htmlspecialchars($activeAdmission['doctor_name'] ?? 'Chưa chỉ định') ?>
                        </p>
                    </div>
                    <div class="metric-icon-box is-green">
                        <i class="iconsax" icon-name="user-2"></i>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="stat-card metric-card-border-purple">
                <div class="metric-row">
                    <div>
                        <h3>Tổng lượt nhập viện</h3>
                        <p class="metric-value-purple"><?= count($admissions) ?></p>
                    </div>
                    <div class="metric-icon-box is-purple">
                        <i class="iconsax" icon-name="document-text-1"></i>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="card card-elevated reveal-item delay-2" style="margin-bottom:1.5rem;">
        <div class="card-header card-header-clean" style="padding:1rem 1.4rem .5rem;">
            <div style="display:flex;align-items:center;gap:.6rem;">
                <div class="metric-icon-box is-purple" style="width:32px;height:32px;font-size:.85rem;">
                    <i class="iconsax" icon-name="user-2"></i>
                </div>
                <strong style="font-size:.95rem;">Thông tin hành chính</strong>
            </div>
        </div>
        <div style="padding:1rem 1.4rem 1.4rem;">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem;">
                <div>
                    <div class="patient-form-label" style="color:var(--text-muted);font-size:.78rem;margin-bottom:2px;">
                        Mã bệnh nhân</div>
                    <span class="patient-code-badge"><?= htmlspecialchars($patient['patient_code']) ?></span>
                </div>
                <div>
                    <div class="patient-form-label" style="color:var(--text-muted);font-size:.78rem;margin-bottom:2px;">
                        Họ và tên</div>
                    <div style="font-weight:600;"><?= htmlspecialchars($patient['full_name']) ?></div>
                </div>
                <div>
                    <div class="patient-form-label" style="color:var(--text-muted);font-size:.78rem;margin-bottom:2px;">
                        Ngày sinh</div>
                    <div>
                        <?= !empty($patient['date_of_birth']) ? date('d/m/Y', strtotime($patient['date_of_birth'])) : '---' ?>
                    </div>
                </div>
                <div>
                    <div class="patient-form-label" style="color:var(--text-muted);font-size:.78rem;margin-bottom:2px;">
                        Giới tính</div>
                    <?php $g = $patient['gender'] ?? ''; ?>
                    <span class="badge patient-gender-badge <?= $g === 'Nam' ? 'is-male' : 'is-female' ?>">
                        <i class="iconsax" icon-name="user-2"></i> <?= htmlspecialchars($g ?: '---') ?>
                    </span>
                </div>
                <div>
                    <div class="patient-form-label" style="color:var(--text-muted);font-size:.78rem;margin-bottom:2px;">
                        CCCD</div>
                    <div><?= !empty($patient['citizen_id']) ? htmlspecialchars($patient['citizen_id']) : '---' ?></div>
                </div>
                <div>
                    <div class="patient-form-label" style="color:var(--text-muted);font-size:.78rem;margin-bottom:2px;">
                        Số điện thoại</div>
                    <div><?= !empty($patient['phone']) ? htmlspecialchars($patient['phone']) : '---' ?></div>
                </div>
                <div style="grid-column:1/-1;">
                    <div class="patient-form-label" style="color:var(--text-muted);font-size:.78rem;margin-bottom:2px;">
                        Địa chỉ</div>
                    <div><?= !empty($patient['address']) ? htmlspecialchars($patient['address']) : '---' ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Lịch sử nhập viện ── -->
    <div class="card card-elevated reveal-item delay-3">
        <div class="card-header card-header-clean" style="padding:1rem 1.4rem .5rem;">
            <div style="display:flex;align-items:center;gap:.6rem;">
                <div class="metric-icon-box is-blue" style="width:32px;height:32px;font-size:.85rem;">
                    <i class="iconsax" icon-name="document-text-1"></i>
                </div>
                <strong style="font-size:.95rem;">Lịch sử nhập viện</strong>
                <span class="badge"
                    style="background:var(--accent-blue-subtle,#e8f4fd);color:var(--accent-blue,#2563eb);font-size:.75rem;padding:2px 8px;border-radius:99px;margin-left:4px;">
                    <?= count($admissions) ?> lượt
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table premium-table">
                <thead>
                    <tr>
                        <th class="table-th-id">#</th>
                        <th>Ngày nhập</th>
                        <th>Khoa</th>
                        <th>Phòng / Giường</th>
                        <th>Bác sĩ phụ trách</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($admissions)): ?>
                        <?php foreach ($admissions as $adm): ?>
                            <?php
                            $st = strtolower(trim($adm['status'] ?? ''));
                            if ($st === 'active' || $st === 'stable') {
                                $stLabel = 'Đang nằm viện';
                                $stClass = 'badge-status-active';
                            } elseif ($st === 'discharged') {
                                $stLabel = 'Đã xuất viện';
                                $stClass = 'badge-status-discharged';
                            } else {
                                $stLabel = ucfirst($st ?: '---');
                                $stClass = '';
                            }
                            ?>
                            <tr>
                                <td class="col-id"><?= (int) $adm['admission_id'] ?></td>
                                <td class="table-text-sm-muted">
                                    <?= !empty($adm['admission_date']) ? date('d/m/Y', strtotime($adm['admission_date'])) : '---' ?>
                                </td>
                                <td><?= htmlspecialchars($adm['department_name'] ?? '---') ?></td>
                                <td class="table-text-sm-muted">
                                    <?php
                                    $room = $adm['room_number'] ?? null;
                                    $bed = $adm['bed_number'] ?? null;
                                    echo ($room && $bed) ? htmlspecialchars("$room / $bed") : '---';
                                    ?>
                                </td>
                                <td class="table-text-sm-muted">
                                    <?= htmlspecialchars($adm['doctor_name'] ?? '---') ?>
                                </td>
                                <td>
                                    <span class="badge admission-status-badge <?= $stClass ?>">
                                        <?= $stLabel ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <div class="patient-empty-block">
                                    <div class="patient-empty-figure">
                                        <i class="iconsax" icon-name="document-text-1"></i>
                                    </div>
                                    <p class="patient-empty-title">Chưa có lịch sử nhập viện</p>
                                    <p class="patient-empty-text">Bệnh nhân này chưa có lần nhập viện nào được ghi nhận.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .admission-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 99px;
        font-size: .75rem;
        font-weight: 600;
        letter-spacing: .02em;
    }

    .badge-status-active {
        background: rgba(37, 99, 235, .12);
        color: #2563eb;
    }

    .badge-status-discharged {
        background: rgba(100, 116, 139, .12);
        color: #475569;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>