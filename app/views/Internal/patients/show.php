<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="theme-patients">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Hồ sơ bệnh nhân</h2>
            <p>Thông tin tiếp nhận, tình trạng nội trú và lịch sử chăm sóc.</p>
        </div>
        <div class="patient-view-actions reveal-item delay-1">
            <?php if (empty($activeAdmission)): ?>
                <a href="index.php?url=internal/admissions/create&patient_id=<?= (int) $patient['patient_id']; ?>"
                    class="btn btn-patient">
                    <i class="iconsax" icon-name="hospital"></i>
                    Tiếp nhận nội trú
                </a>
            <?php endif; ?>

            <a href="index.php?url=internal/patients/search" class="btn btn-secondary">
                <i class="iconsax" icon-name="arrow-left"></i>
                Quay lại
            </a>
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

    <div class="card patient-profile-card reveal-item delay-1">
        <div class="patient-panel-bar"></div>
        <div class="patient-profile-body">
            <div class="patient-profile-top">
                <div class="patient-avatar">
                    <span>
                        <?= mb_substr($patient['full_name'] ?? 'B', 0, 1) ?>
                    </span>
                </div>

                <div class="patient-profile-main">
                    <div class="patient-profile-title-row">
                        <h2 class="patient-profile-title">
                            <?= htmlspecialchars($patient['full_name']) ?>
                        </h2>
                        <span class="patient-code-badge">
                            <?= htmlspecialchars($patient['patient_code']) ?>
                        </span>
                    </div>
                    <p class="patient-profile-subtitle">
                        <?= !empty($activeAdmission) ? 'Đang điều trị nội trú' : 'Chưa có ca nội trú đang mở' ?>
                    </p>
                </div>
            </div>

            <div class="patient-info-grid">
                <div class="patient-info-item">
                    <div class="patient-info-icon is-purple">
                        <i class="iconsax" icon-name="calendar-2"></i>
                    </div>
                    <div>
                        <small class="patient-info-label">Ngày sinh</small>
                        <p class="patient-info-value">
                            <?= !empty($patient['date_of_birth']) ? date('d/m/Y', strtotime($patient['date_of_birth'])) : '---' ?>
                        </p>
                    </div>
                </div>

                <div class="patient-info-item">
                    <div class="patient-info-icon is-blue">
                        <i class="iconsax" icon-name="person-card"></i>
                    </div>
                    <div>
                        <small class="patient-info-label">CCCD</small>
                        <p class="patient-info-value">
                            <?= !empty($patient['citizen_id']) ? htmlspecialchars($patient['citizen_id']) : '---' ?>
                        </p>
                    </div>
                </div>

                <div class="patient-info-item">
                    <div class="patient-info-icon is-pink">
                        <i class="iconsax" icon-name="phone"></i>
                    </div>
                    <div>
                        <small class="patient-info-label">Số điện thoại</small>
                        <p class="patient-info-value">
                            <?= !empty($patient['phone']) ? htmlspecialchars($patient['phone']) : '---' ?>
                        </p>
                    </div>
                </div>

                <div class="patient-info-item">
                    <div class="patient-info-icon is-amber">
                        <i class="iconsax" icon-name="user-2"></i>
                    </div>
                    <div>
                        <small class="patient-info-label">Giới tính</small>
                        <p class="patient-info-value">
                            <?= htmlspecialchars($patient['gender'] ?? '---') ?>
                        </p>
                    </div>
                </div>

                <div class="patient-info-item">
                    <div class="patient-info-icon is-amber">
                        <i class="iconsax" icon-name="location"></i>
                    </div>
                    <div>
                        <small class="patient-info-label">Địa chỉ</small>
                        <p class="patient-info-value">
                            <?= !empty($patient['address']) ? htmlspecialchars($patient['address']) : '---' ?>
                        </p>
                    </div>
                </div>
            </div>

            <?php if (!empty($activeAdmission)): ?>
                <div class="patient-active-admission">
                    <div class="patient-history-info">
                        <div class="patient-history-icon">
                            <i class="iconsax" icon-name="hospital"></i>
                        </div>
                        <div>
                            <h3 class="patient-history-title">Ca nội trú hiện tại</h3>
                            <p class="patient-history-meta">
                                <?= htmlspecialchars($activeAdmission['room_name'] ?? 'Chưa rõ phòng') ?>
                                <span class="patient-admission-separator">—</span>
                                Giường <?= htmlspecialchars($activeAdmission['bed_code'] ?? '---') ?>
                            </p>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap: 12px; margin-top: 16px;">
                        <span class="badge badge-active">
                            <span class="status-dot dot-active"></span>
                            Đang điều trị
                        </span>
                        <a href="index.php?url=internal/admissions/show&id=<?= (int) $activeAdmission['admission_id']; ?>" class="btn-patient">
                            <i class="iconsax" icon-name="clipboard-text-1"></i> Quản lý ca nội trú
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card patient-history-card reveal-item delay-2">
        <div class="card-header card-header-clean">
            <div class="patient-history-header">
                <div class="patient-history-info">
                    <div class="patient-history-icon">
                        <i class="iconsax" icon-name="clipboard-text-1"></i>
                    </div>
                    <div>
                        <h3 class="patient-history-title">Lịch sử nhập viện</h3>
                        <p class="patient-history-meta">
                            Tổng: <?= count($admissions ?? []) ?> lượt
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table premium-table">
                <thead>
                    <tr>
                        <th class="table-th-id">ID</th>
                        <th>Phòng / Giường</th>
                        <th>Điều dưỡng phụ trách</th>
                        <th>Ngày nhập viện</th>
                        <th>Trạng thái</th>
                        <th class="text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($admissions)): ?>
                        <?php foreach ($admissions as $adm): ?>
                            <tr>
                                <td class="col-id">#<?= (int) $adm['admission_id'] ?></td>
                                <td>
                                    <div class="patient-admission-room">
                                        <i class="iconsax" icon-name="house-2"></i>
                                        <strong><?= htmlspecialchars($adm['room_name'] ?? '---') ?></strong>
                                        <span class="patient-admission-separator">—</span>
                                        <span class="patient-admission-bed">
                                            Giường <?= htmlspecialchars($adm['bed_code'] ?? '---') ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="patient-nurse-meta">
                                        <i class="iconsax" icon-name="user-2"></i>
                                        <?= htmlspecialchars($adm['nurse_name'] ?? '---') ?>
                                    </span>
                                </td>
                                <td class="table-text-sm-muted">
                                    <?= !empty($adm['admission_date']) ? date('d/m/Y H:i', strtotime($adm['admission_date'])) : '---' ?>
                                </td>
                                <td>
                                    <?php
                                    $st = strtolower($adm['status'] ?? '');
                                    if ($st === 'active'): ?>
                                        <span class="badge badge-active">
                                            <span class="status-dot dot-active"></span>
                                            Đang điều trị
                                        </span>
                                    <?php elseif ($st === 'discharged'): ?>
                                        <span class="badge badge-discharged">
                                            <span class="status-dot dot-discharged"></span>
                                            Đã xuất viện
                                        </span>
                                    <?php elseif ($st === 'transferred'): ?>
                                        <span class="badge badge-transferred">
                                            <span class="status-dot dot-transferred"></span>
                                            Chuyển viện
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-inactive">
                                            <span class="status-dot dot-inactive"></span>
                                            <?= htmlspecialchars($adm['status'] ?? '---') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <a href="index.php?url=internal/admissions/show&id=<?= (int) $adm['admission_id'] ?>" class="btn-action btn-action-view" title="Xem chi tiết ca nội trú">
                                        <i class="iconsax" icon-name="eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <div class="patient-empty-block">
                                    <div class="patient-empty-figure-sm">
                                        <i class="iconsax" icon-name="clipboard-text-1"></i>
                                    </div>
                                    <p class="patient-empty-title patient-empty-title-sm">Chưa có lượt nhập viện nào</p>
                                    <p class="patient-empty-text">Bệnh nhân chưa được tiếp nhận vào nội trú.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
