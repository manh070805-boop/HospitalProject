<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Theo dõi bệnh nhân nội trú</h2>
            <p>Danh sách các ca đang điều trị hiện tại.</p>
        </div>
        <div class="page-header-actions">
            <a href="index.php?url=internal/patients/search" class="btn btn-primary">
                <i class="iconsax" icon-name="user-2-add"></i> Tiếp nhận mới
            </a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success reveal-item">
            <i class="iconsax" icon-name="tick-circle"></i> <?= htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item">
            <i class="iconsax" icon-name="info-circle"></i> <?= htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <div class="card dashboard-table-card reveal-item">
        <table class="table premium-table">
            <thead>
                <tr>
                    <th class="table-th-id">Phòng/Giường</th>
                    <th>Bệnh nhân</th>
                    <th>Bác sĩ phụ trách</th>
                    <th>Y tá phụ trách</th>
                    <th>Thời gian nhập</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($admissions)): ?>
                    <?php foreach ($admissions as $a): ?>
                        <tr>
                            <td>
                                <div class="cell-strong"><?= htmlspecialchars($a['room_name']); ?></div>
                                <div class="table-text-muted">Giường: <?= htmlspecialchars($a['bed_code']); ?></div>
                            </td>
                            <td>
                                <a class="cell-strong table-link"
                                    href="index.php?url=internal/patients/show&id=<?= $a['patient_id']; ?>">
                                    <?= htmlspecialchars($a['patient_name']); ?>
                                </a>
                                <div class="table-text-muted">
                                    Mã: <?= htmlspecialchars($a['patient_code']); ?> · <?= htmlspecialchars($a['gender']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    <i class="iconsax" icon-name="user-2-tag" style="font-size: 10px;"></i>
                                    <?= $a['doctor_name'] ? htmlspecialchars($a['doctor_name']) : 'Chưa phân công'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    <i class="iconsax" icon-name="user-2" style="font-size: 10px;"></i>
                                    <?= $a['nurse_name'] ? htmlspecialchars($a['nurse_name']) : 'Chưa phân công'; ?>
                                </span>
                            </td>
                            <td><?= date('H:i d/m/Y', strtotime($a['admission_date'])); ?></td>
                            <td>
                                <?php $currentStatus = strtolower(trim($a['status'] ?? 'active')); ?>
                                <?php if ($currentStatus === 'discharged'): ?>
                                    <span class="badge badge-discharged"><span class="status-dot dot-discharged"></span>Đã xuất viện</span>
                                <?php else: ?>
                                    <span class="badge badge-active"><span class="status-dot dot-active"></span>Đang nhập viện</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <div class="inpatient-actions">
                                    <a href="index.php?url=internal/admissions/show&id=<?= $a['admission_id']; ?>"
                                        class="btn-action btn-action-edit" title="Quản lý ca nội trú">
                                        <i class="iconsax" icon-name="clipboard-text-1"></i>
                                    </a>
                                    <?php if (($role ?? '') === 'head_nurse' && strtolower(trim($a['status'] ?? '')) === 'active'): ?>
                                        <a href="index.php?url=internal/admissions/transfer&id=<?= $a['admission_id']; ?>"
                                            class="btn-action btn-action-edit" title="Chuyển khoa/giường">
                                            <i class="iconsax" icon-name="refresh"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="index.php?url=internal/patients/show&id=<?= $a['patient_id']; ?>"
                                        class="btn-action btn-action-view" title="Xem chi tiết bệnh nhân">
                                        <i class="iconsax" icon-name="eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="iconsax empty-state-icon" icon-name="user-2-x"></i>
                            <p>Không có bệnh nhân nội trú nào trong danh sách theo dõi của bạn.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
