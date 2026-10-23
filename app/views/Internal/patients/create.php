<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Tiếp nhận bệnh nhân mới</h2>
            <p>Kiểm tra CCCD trước khi tạo hồ sơ để tránh trùng bệnh nhân.</p>
        </div>
        <div class="page-header-actions">
            <a href="index.php?url=internal/patients/search" class="btn btn-secondary">
                <i class="iconsax" icon-name="arrow-left"></i> Quay lại
            </a>
        </div>
    </div>


    <div class="card reveal-item">
        <div class="card-header">
            <h4>Thông tin cơ bản</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error reveal-item">
                    <ul class="mb-0">
                        <?php foreach ($error as $err): ?>
                            <li><i class="iconsax" icon-name="info-circle" style="font-size: 14px;"></i> <?= $err; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="index.php?url=internal/patients/store" method="POST" class="premium-form">
                <div class="form-grid">
                    <div class="form-group mb-3">
                        <label class="form-label">Mã bệnh nhân (Tự động)</label>
                        <input type="text" value="<?= $patientCode; ?>" class="form-control" disabled>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($full_name ?? ''); ?>"
                            class="form-control" placeholder="Nguyễn Văn A" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">CCCD <span class="text-danger">*</span></label>
                        <input type="text" name="citizen_id" value="<?= htmlspecialchars($citizen_id ?? ''); ?>"
                            class="form-control" placeholder="012345678901" maxlength="12" pattern="[0-9]{12}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Ngày sinh <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" value="<?= htmlspecialchars($date_of_birth ?? ''); ?>"
                            class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Giới tính <span class="text-danger">*</span></label>
                        <select name="gender" class="form-control select-premium" required>
                            <option value="">-- Chọn giới tính --</option>
                            <option value="Nam" <?= ($gender ?? '') === 'Nam' ? 'selected' : ''; ?>>Nam</option>
                            <option value="Nữ" <?= ($gender ?? '') === 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($phone ?? ''); ?>"
                            class="form-control" placeholder="0901234567" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($address ?? ''); ?>"
                            class="form-control" placeholder="Số 123, Đường ABC, Quận XYZ...">
                    </div>
                </div>

                <div class="form-actions mt-4 text-right">
                    <button type="reset" class="btn btn-secondary">Nhập lại</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="iconsax" icon-name="tick-circle"></i> Xác nhận tiếp nhận
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
