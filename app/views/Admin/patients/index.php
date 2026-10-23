<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="theme-patients">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Quản lý bệnh nhân</h2>
            <p>Danh sách hồ sơ bệnh nhân trong hệ thống</p>
        </div>
    </div>

    <?php
    $params = [];
    if (!empty($keyword))
        $params['keyword'] = $keyword;
    if (!empty($gender))
        $params['gender'] = $gender;
    $queryString = http_build_query($params);
    ?>

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

    <div class="stats-grid stats-grid-compact reveal-item delay-1">
        <div class="stat-card metric-card-border-purple">
            <div class="metric-row">
                <div>
                    <h3>Tổng bệnh nhân</h3>
                    <p class="metric-value-lg metric-value-purple"><?= $stats['total'] ?? 0 ?></p>
                </div>
                <div class="metric-icon-box is-purple">
                    <i class="iconsax" icon-name="users"></i>
                </div>
            </div>
        </div>
        <div class="stat-card metric-card-border-blue">
            <div class="metric-row">
                <div>
                    <h3>Nam</h3>
                    <p class="metric-value-blue"><?= $stats['Nam'] ?? 0 ?></p>
                </div>
                <div class="metric-icon-box is-blue">
                    <i class="iconsax" icon-name="user-2"></i>
                </div>
            </div>
        </div>
        <div class="stat-card metric-card-border-pink">
            <div class="metric-row">
                <div>
                    <h3>Nữ</h3>
                    <p class="metric-value-pink"><?= $stats['Nữ'] ?? 0 ?></p>
                </div>
                <div class="metric-icon-box is-pink">
                    <i class="iconsax" icon-name="user-2"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-elevated reveal-item delay-2">
        <div class="card-header card-header-clean">
            <form action="index.php" method="GET" class="search-form search-form-wide">
                <input type="hidden" name="url" value="patients">
                <div class="search-row">
                    <div class="input-with-icon search-field search-field-min">
                        <i class="iconsax" icon-name="search-normal-1"></i>
                        <input type="text" name="keyword" class="form-control"
                            placeholder="Tìm theo CCCD, mã BN, họ tên, SĐT..."
                            value="<?= htmlspecialchars($keyword ?? '') ?>">
                    </div>

                    <div class="input-with-icon search-select-min-160">
                        <i class="iconsax" icon-name="filter"></i>
                        <select name="gender" class="form-control">
                            <option value="">Tất cả giới tính</option>
                            <option value="Nam" <?= ($gender ?? '') === 'Nam' ? 'selected' : '' ?>>Nam</option>
                            <option value="Nữ" <?= ($gender ?? '') === 'Nữ' ? 'selected' : '' ?>>Nữ</option>

                        </select>
                    </div>

                    <button type="submit" class="btn btn-patient">
                        <i class="iconsax" icon-name="filter-search"></i>
                        Tìm kiếm
                    </button>

                    <?php if (!empty($keyword) || !empty($gender)): ?>
                        <a href="index.php?url=patients" class="btn btn-secondary" title="Xóa bộ lọc">
                            <i class="iconsax" icon-name="refresh"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table premium-table">
                <thead>
                    <tr>
                        <th class="table-th-id">ID</th>
                        <th>Mã BN</th>
                        <th>Họ tên</th>
                        <th>CCCD</th>
                        <th>Ngày sinh</th>
                        <th>Giới tính</th>
                        <th>Số điện thoại</th>
                        <th class="table-th-actions">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($patients)): ?>
                        <?php foreach ($patients as $row): ?>
                            <tr>
                                <td class="col-id">#<?= $row['patient_id'] ?></td>
                                <td>
                                    <span class="patient-code-badge">
                                        <?= htmlspecialchars($row['patient_code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="patient-row-person">
                                        <div class="patient-row-avatar">
                                            <i class="iconsax" icon-name="user-2"></i>
                                        </div>
                                        <span class="patient-row-name"><?= htmlspecialchars($row['full_name']) ?></span>
                                    </div>
                                </td>

                                <td class="table-text-sm-muted">
                                    <?= !empty($row['citizen_id']) ? htmlspecialchars($row['citizen_id']) : '---' ?>
                                </td>

                                <td class="table-text-sm-muted">
                                    <?= !empty($row['date_of_birth']) ? date('d/m/Y', strtotime($row['date_of_birth'])) : '---' ?>
                                </td>
                                <td>
                                    <?php
                                    $g = $row['gender'] ?? '';
                                    if ($g === 'Nam'): ?>
                                        <span class="badge patient-gender-badge is-male">
                                            <i class="iconsax" icon-name="user-2"></i> Nam
                                        </span>
                                    <?php else: ?>
                                        <span class="badge patient-gender-badge is-female">
                                            <i class="iconsax" icon-name="user-2"></i> Nữ
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="table-text-sm-muted">
                                    <?= !empty($row['phone']) ? htmlspecialchars($row['phone']) : '---' ?>
                                </td>
                                <td class="text-right">
                                    <div class="actions-row">
                                        <a class="btn-action btn-action-view"
                                            href="index.php?url=patients/show&id=<?= $row['patient_id'] ?>" title="Xem chi tiết">
                                            <i class="iconsax" icon-name="eye"></i>
                                        </a>
                                        <a class="btn-action btn-action-edit"
                                            href="index.php?url=patients/edit&id=<?= $row['patient_id'] ?>" title="Chỉnh sửa">
                                            <i class="iconsax" icon-name="edit-2"></i>
                                        </a>
                                        <a class="btn-action btn-action-delete"
                                            href="index.php?url=patients/delete&id=<?= $row['patient_id'] ?>"
                                            onclick="return confirm('Bạn có chắc muốn xóa bệnh nhân này không?')" title="Xóa">
                                            <i class="iconsax" icon-name="trash"></i>
                                        </a>
                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-state">
                                <div class="patient-empty-block">
                                    <div class="patient-empty-figure">
                                        <i class="iconsax" icon-name="users"></i>
                                    </div>
                                    <p class="patient-empty-title">Không tìm thấy
                                        bệnh nhân nào</p>
                                    <p class="patient-empty-text">Hãy thử tìm kiếm với từ khóa khác
                                        hoặc thêm bệnh nhân mới.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <div class="pagination pagination-card">
                <?php if ($page > 1): ?>
                    <a href="index.php?url=patients<?= !empty($queryString) ? '&' . $queryString : '' ?>&page=<?= $page - 1 ?>"
                        class="pagination-link pagination-link-nav pagination-link-wide pagination-link-icon">
                        <i class="iconsax" icon-name="arrow-left"></i>
                        <span>Trang trước</span>
                    </a>
                <?php endif; ?>

                <div class="pagination-pages">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="index.php?url=patients<?= !empty($queryString) ? '&' . $queryString : '' ?>&page=<?= $i ?>"
                            class="pagination-link <?= ($i == $page) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>

                <?php if ($page < $totalPages): ?>
                    <a href="index.php?url=patients<?= !empty($queryString) ? '&' . $queryString : '' ?>&page=<?= $page + 1 ?>"
                        class="pagination-link pagination-link-nav pagination-link-wide pagination-link-icon">
                        <span>Trang sau</span>
                        <i class="iconsax" icon-name="arrow-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>



<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
