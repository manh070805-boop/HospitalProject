<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="theme-patients">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Chỉnh sửa bệnh nhân</h2>
            <p>Cập nhật hồ sơ bệnh nhân #<?= htmlspecialchars($patient['patient_code'] ?? '') ?></p>
        </div>
        <a href="index.php?url=patients" class="btn btn-secondary reveal-item delay-1">
            <i class="iconsax" icon-name="arrow-left"></i>
            Quay lại danh sách
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error reveal-item">
            <i class="iconsax" icon-name="info-circle"></i>
            <ul>
                <?php foreach ($error as $er): ?>
                    <li><?php echo htmlspecialchars($er); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card patient-panel reveal-item delay-2">

        <div class="patient-panel-bar"></div>

        <div class="patient-panel-body">

            <div class="patient-panel-header">
                <div class="patient-panel-header-icon">
                    <i class="iconsax" icon-name="edit-2"></i>
                </div>
                <div>
                    <h3>Chỉnh sửa thông
                        tin</h3>
                    <p>Cập nhật thông tin hành chính
                        của bệnh nhân</p>
                </div>
            </div>

            <form action="index.php?url=patients/update" method="POST">
                <input type="hidden" name="id" value="<?= $patient['patient_id'] ?>">

                <div class="patient-code-banner">
                    <div class="patient-code-banner-row">
                        <i class="iconsax" icon-name="hashtag"></i>
                        <div>
                            <small class="patient-code-label">Mã bệnh nhân (Không thể thay
                                đổi)</small>
                            <p class="patient-code-value">
                                <?= htmlspecialchars($patient['patient_code']) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="patient-form-grid">
                    <div class="form-group patient-form-group-full">
                        <label class="patient-form-label patient-form-label-inline">
                            Họ và tên
                            <span class="form-required">*</span>
                        </label>
                        <div class="input-with-icon">
                            <i class="iconsax" icon-name="user-2"></i>
                            <input type="text" name="full_name" class="form-control" placeholder="VD: Nguyễn Văn A"
                                value="<?= htmlspecialchars($full_name ?? $patient['full_name'] ?? '') ?>" required
                                maxlength="255">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="patient-form-label patient-form-label-inline">
                            Ngày sinh
                            <span class="form-required">*</span>
                        </label>
                        <div class="input-with-icon">
                            <i class="iconsax" icon-name="calendar-2"></i>
                            <input type="date" name="date_of_birth" class="form-control"
                                value="<?= htmlspecialchars($date_of_birth ?? (isset($patient['date_of_birth']) ? date('Y-m-d', strtotime($patient['date_of_birth'])) : '')) ?>"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="patient-form-label patient-form-label-inline">
                            Giới tính
                            <span class="form-required">*</span>
                        </label>
                        <div class="input-with-icon">
                            <i class="iconsax" icon-name="users"></i>
                            <?php $currentGender = $gender ?? $patient['gender'] ?? ''; ?>
                            <select name="gender" class="form-control" required>
                                <option value="">-- Chọn giới tính --</option>
                                <option value="Nam" <?= $currentGender === 'Nam' ? 'selected' : '' ?>>Nam</option>
                                <option value="Nữ" <?= $currentGender === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="patient-form-label">CCCD</label>
                        <div class="input-with-icon">
                            <i class="iconsax" icon-name="person-card"></i>
                            <input type="text" name="citizen_id" class="form-control" placeholder="VD: 012345678901"
                                value="<?= htmlspecialchars($citizen_id ?? $patient['citizen_id'] ?? '') ?>" maxlength="12"
                                pattern="[0-9]{12}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="patient-form-label">Số
                            điện thoại</label>
                        <div class="input-with-icon">
                            <i class="iconsax" icon-name="phone"></i>
                            <input type="text" name="phone" class="form-control" placeholder="VD: 0912345678"
                                value="<?= htmlspecialchars($phone ?? $patient['phone'] ?? '') ?>" maxlength="20">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="patient-form-label">Địa
                            chỉ</label>
                        <div class="input-with-icon">
                            <i class="iconsax" icon-name="location"></i>
                            <input type="text" name="address" class="form-control"
                                placeholder="VD: 123 Nguyễn Huệ, Q.1, TP.HCM"
                                value="<?= htmlspecialchars($address ?? $patient['address'] ?? '') ?>" maxlength="255">
                        </div>
                    </div>
                </div>

                <div class="form-actions admin-form-actions">
                    <a href="index.php?url=patients" class="btn btn-secondary">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="btn btn-patient patient-submit">
                        <i class="iconsax" icon-name="tick-circle"></i>
                        Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
