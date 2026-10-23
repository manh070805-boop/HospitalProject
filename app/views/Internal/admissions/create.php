<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Lập hồ sơ nhập viện</h2>
            <p>Gán giường bệnh và bác sĩ phụ trách cho bệnh nhân.</p>
        </div>
        <div class="page-header-actions">
            <a href="index.php?url=internal/patients/search" class="btn btn-secondary">
                <i class="iconsax" icon-name="arrow-left"></i> Quay lại tìm kiếm
            </a>
        </div>
    </div>

    <div class="card reveal-item" style="margin-bottom: 24px;">
        <div class="card-header">
            <h4>Thông tin Bệnh nhân</h4>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div>
                    <span class="table-text-muted">Mã BN:</span>
                    <strong><?= $patient['patient_code']; ?></strong>
                </div>
                <div>
                    <span class="table-text-muted">Họ tên:</span>
                    <strong><?= htmlspecialchars($patient['full_name']); ?></strong>
                </div>
                <div>
                    <span class="table-text-muted">CCCD:</span>
                    <?= !empty($patient['citizen_id']) ? htmlspecialchars($patient['citizen_id']) : '---'; ?>
                </div>
                <div>
                    <span class="table-text-muted">Giới tính:</span>
                    <?= $patient['gender']; ?>
                </div>
                <div>
                    <span class="table-text-muted">Ngày sinh:</span>
                    <?= date('d/m/Y', strtotime($patient['date_of_birth'])); ?>
                </div>
                <div>
                    <span class="table-text-muted">Điện thoại:</span>
                    <?= htmlspecialchars($patient['phone']); ?>
                </div>
                <div>
                    <span class="table-text-muted">Địa chỉ:</span>
                    <?= htmlspecialchars($patient['address']); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card reveal-item delay-1">
        <div class="card-header">
            <h4>Phân công Nội trú</h4>
        </div>
        <div class="card-body">
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <i class="iconsax" icon-name="info-circle" style="font-size: 14px;"></i>
                    <?= htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="index.php" method="GET" class="premium-form" style="margin-bottom: 18px;">
                <input type="hidden" name="url" value="internal/admissions/create">
                <input type="hidden" name="patient_id" value="<?= $patient['patient_id']; ?>">
                <div class="form-group mb-3">
                    <label class="form-label">Khoa điều trị <span class="text-danger">*</span></label>
                    <select name="department_id" class="form-control select-premium" onchange="this.form.submit()" required>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?= $department['department_id']; ?>"
                                <?= (int) $department['department_id'] === (int) $selectedDepartmentId ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($department['department_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="table-text-muted d-block mt-1">Giường trống và bác sĩ phụ trách sẽ được lọc theo khoa đã chọn.</small>
                </div>
            </form>

            <form action="index.php?url=internal/admissions/store" method="POST" class="premium-form">
                <input type="hidden" name="patient_id" value="<?= $patient['patient_id']; ?>">
                <input type="hidden" name="department_id" value="<?= htmlspecialchars((string) $selectedDepartmentId); ?>">

                <div class="form-grid">
                    <div class="form-group mb-3">
                        <label class="form-label">Phân giường bệnh <span class="text-danger">*</span></label>
                        <select name="bed_id" class="form-control select-premium" required>
                            <?php if (!empty($availableBeds)): ?>
                                <option value="">-- Chọn giường trống --</option>
                                <?php foreach ($availableBeds as $bed): ?>
                                    <option value="<?= $bed['bed_id']; ?>">
                                        Phòng <?= htmlspecialchars($bed['room_name']); ?> - Giường
                                        <?= htmlspecialchars($bed['bed_code']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="__temporary__" selected>Giường tạm - chờ xếp giường chính thức</option>
                            <?php endif; ?>
                        </select>
                        <?php if (!empty($availableBeds)): ?>
                            <small class="table-text-muted d-block mt-1">Chỉ hiển thị các giường hiện đang trống trong
                                khoa đã chọn.</small>
                        <?php else: ?>
                            <small class="table-text-muted d-block mt-1">Khoa hiện hết giường trống. Bệnh nhân sẽ được đưa vào giường tạm và cần chuyển sang giường chính thức sau.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Chọn Bác sĩ phụ trách <span class="text-danger">*</span></label>
                        <select name="doctor_id" class="form-control select-premium" required>
                            <option value="">-- Chọn bác sĩ trong khoa --</option>
                            <?php if (!empty($doctors)): ?>
                                <?php foreach ($doctors as $doc): ?>
                                    <option value="<?= $doc['user_id']; ?>">
                                        Bác sĩ <?= htmlspecialchars($doc['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Chưa có bác sĩ active trong khoa này</option>
                            <?php endif; ?>
                        </select>
                        <small class="table-text-muted d-block mt-1">Bác sĩ thuộc khoa đã chọn sẽ nhận thông tin điều trị.</small>
                    </div>
                </div>

                <div class="form-actions mt-4 text-right">
                    <button type="submit" class="btn btn-primary" <?= empty($doctors) ? 'disabled' : ''; ?>>
                        <i class="iconsax" icon-name="hospital"></i> Xác nhận Nhập viện
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
