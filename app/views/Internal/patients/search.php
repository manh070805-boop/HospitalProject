<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Tìm kiếm bệnh nhân</h2>
            <p>Nhập CCCD, tên, mã bệnh nhân hoặc số điện thoại để kiểm tra hồ sơ.</p>
        </div>
        <div class="page-header-actions">
            <a href="index.php?url=internal/patients/check-cccd" class="btn btn-primary">
                <i class="iconsax" icon-name="user-2-add"></i> Tiếp nhận bệnh nhân mới
            </a>
        </div>
    </div>
    <div class="card card-elevated reveal-item">
        <div class="card-header card-header-clean">
            <form action="index.php" method="GET" class="search-form search-form-wide">
                <input type="hidden" name="url" value="internal/patients/search">
                <div class="search-row search-row-nowrap">
                    <div class="input-with-icon search-field search-field-min">
                        <i class="iconsax" icon-name="search-normal-1"></i>
                        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword); ?>"
                            placeholder="CCCD / Họ tên / Số điện thoại / Mã BN..." class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="iconsax" icon-name="search-normal-1"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success reveal-item">
            <i class="iconsax" icon-name="tick-circle"></i> <?= htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger reveal-item">
            <i class="iconsax" icon-name="info-circle"></i> <?= htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-section reveal-item">
        <h3 class="dashboard-section-title"><?= htmlspecialchars($resultTitle); ?></h3>
    </div>

    <div class="card dashboard-table-card reveal-item">
        <table class="table premium-table">
            <thead>
                <tr>
                    <th class="table-th-id">Mã BN</th>
                    <th>Họ và tên</th>
                    <th>CCCD</th>
                    <th>Ngày sinh</th>
                    <th>Giới tính</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ</th>
                    <th class="intake-action-header">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($patients)): ?>
                    <?php foreach ($patients as $p): ?>
                        <tr>
                            <td class="col-id"><?= htmlspecialchars($p['patient_code']); ?></td>
                            <td>
                                <div class="cell-strong"><?= htmlspecialchars($p['full_name']); ?></div>
                            </td>
                            <td class="table-text-muted"><?= !empty($p['citizen_id']) ? htmlspecialchars($p['citizen_id']) : '---'; ?></td>
                            <td><?= !empty($p['date_of_birth']) ? date('d/m/Y', strtotime($p['date_of_birth'])) : ''; ?></td>
                            <td><?= htmlspecialchars($p['gender']); ?></td>
                            <td><?= htmlspecialchars($p['phone']); ?></td>
                            <td class="table-text-muted"><?= htmlspecialchars($p['address']); ?></td>
                            <td class="intake-action-cell">
                                <div class="intake-action-stack">
                                    <?php if (!empty($p['active_admission_id'])): ?>
                                        <span class="badge badge-info intake-status-badge">
                                            <i class="iconsax" icon-name="tick-circle"></i>
                                            Đã tiếp nhận
                                        </span>
                                        <a href="index.php?url=internal/patients/show&id=<?= $p['patient_id']; ?>"
                                            class="btn btn-sm btn-light intake-action-button">
                                            <i class="iconsax" icon-name="document-text-1"></i> Xem chi tiết
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?url=internal/admissions/create&patient_id=<?= $p['patient_id']; ?>"
                                            class="btn btn-sm btn-success intake-action-button">
                                            <i class="iconsax" icon-name="hospital"></i> Tiếp nhận
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="iconsax empty-state-icon" icon-name="box-search"></i>
                            <p><?= $keyword !== '' ? 'Không tìm thấy bệnh nhân nào khớp với từ khóa.' : 'Chưa có bệnh nhân nào trong phạm vi hiển thị.'; ?>
                            </p>
                            <a href="index.php?url=internal/patients/check-cccd" class="btn btn-sm btn-primary">Tiếp nhận bệnh nhân
                                mới</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
