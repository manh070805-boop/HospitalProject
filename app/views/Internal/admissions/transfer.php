<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Chuyển khoa/giường nội trú</h2>
            <p><?= htmlspecialchars($admission['patient_name']) ?> &mdash; Ca nội trú #<?= (int) $admission['admission_id'] ?></p>
        </div>
        <div class="page-header-actions">
            <a href="index.php?url=internal/admissions/show&id=<?= (int) $admission['admission_id'] ?>"
                class="btn btn-secondary">
                <i class="iconsax" icon-name="arrow-left"></i> Trở về ca nội trú
            </a>
        </div>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item">
            <i class="iconsax" icon-name="info-circle"></i>
            <?= htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <div class="card reveal-item" style="margin-bottom: 24px;">
        <div class="card-header">
            <h4><i class="iconsax" icon-name="hospital" style="font-size:16px;"></i> Thông tin hiện tại</h4>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px;">
                <div>
                    <span class="table-text-muted">Khoa hiện tại:</span>
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
            </div>
        </div>
    </div>

    <div class="card reveal-item delay-1">
        <div class="card-header">
            <h4>Thông tin chuyển ca</h4>
        </div>
        <div class="card-body">
            <form action="index.php" method="GET" class="premium-form" style="margin-bottom: 18px;">
                <input type="hidden" name="url" value="internal/admissions/transfer">
                <input type="hidden" name="id" value="<?= (int) $admission['admission_id']; ?>">
                <div class="form-group mb-3">
                    <label class="form-label">Khoa điều trị mới <span class="text-danger">*</span></label>
                    <select name="department_id" class="form-control select-premium" onchange="this.form.submit()" required>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?= $department['department_id']; ?>"
                                <?= (int) $department['department_id'] === (int) $selectedDepartmentId ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($department['department_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="table-text-muted d-block mt-1">Giường trống, bác sĩ và điều dưỡng sẽ được lọc theo khoa đã chọn.</small>
                </div>
            </form>

            <form action="index.php?url=internal/admissions/transfer" method="POST" class="premium-form">
                <input type="hidden" name="admission_id" value="<?= (int) $admission['admission_id']; ?>">
                <input type="hidden" name="department_id" value="<?= htmlspecialchars((string) $selectedDepartmentId); ?>">

                <div class="form-grid">
                    <div class="form-group mb-3">
                        <label class="form-label">Giường bệnh <span class="text-danger">*</span></label>
                        <select name="bed_id" class="form-control select-premium" required>
                            <option value="">-- Chọn giường --</option>
                            <?php if (!empty($availableBeds)): ?>
                                <?php foreach ($availableBeds as $bed): ?>
                                    <option value="<?= $bed['bed_id']; ?>"
                                        <?= (int) $bed['bed_id'] === (int) ($admission['bed_id'] ?? 0) ? 'selected' : ''; ?>>
                                        Phòng <?= htmlspecialchars($bed['room_name']); ?> - Giường
                                        <?= htmlspecialchars($bed['bed_code']); ?>
                                        <?= (int) $bed['bed_id'] === (int) ($admission['bed_id'] ?? 0) ? ' (hiện tại)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Chưa có giường trống trong khoa này</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Bác sĩ phụ trách <span class="text-danger">*</span></label>
                        <select name="doctor_id" class="form-control select-premium" required>
                            <option value="">-- Chọn bác sĩ --</option>
                            <?php if (!empty($doctors)): ?>
                                <?php foreach ($doctors as $doc): ?>
                                    <option value="<?= $doc['user_id']; ?>"
                                        <?= (int) $doc['user_id'] === (int) ($admission['doctor_id'] ?? 0) ? 'selected' : ''; ?>>
                                        Bác sĩ <?= htmlspecialchars($doc['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Chưa có bác sĩ active trong khoa này</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Điều dưỡng phụ trách <span class="text-danger">*</span></label>
                        <select name="nurse_id" class="form-control select-premium" required>
                            <option value="">-- Chọn điều dưỡng --</option>
                            <?php if (!empty($nurses)): ?>
                                <?php foreach ($nurses as $nurse): ?>
                                    <option value="<?= $nurse['user_id']; ?>"
                                        <?= (int) $nurse['user_id'] === (int) ($admission['nurse_id'] ?? 0) ? 'selected' : ''; ?>>
                                        Điều dưỡng <?= htmlspecialchars($nurse['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Chưa có điều dưỡng active trong khoa này</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Lý do chuyển <span class="text-danger">*</span></label>
                        <textarea name="transfer_note" class="form-control" rows="4"
                            placeholder="VD: Bệnh nhân cần chuyển khoa theo diễn biến lâm sàng..." required></textarea>
                        <small class="table-text-muted d-block mt-1">Tối đa 255 ký tự, nội dung này sẽ được lưu vào lịch sử giường và audit log.</small>
                    </div>
                </div>

                <div class="form-actions mt-4 text-right">
                    <a href="index.php?url=internal/admissions/show&id=<?= (int) $admission['admission_id'] ?>"
                        class="btn btn-secondary">
                        Hủy
                    </a>
                    <button type="submit" class="btn btn-primary" <?= empty($availableBeds) || empty($doctors) || empty($nurses) ? 'disabled' : ''; ?>
                        onclick="return confirm('Xác nhận chuyển ca nội trú này?');">
                        <i class="iconsax" icon-name="refresh"></i> Xác nhận chuyển ca
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
