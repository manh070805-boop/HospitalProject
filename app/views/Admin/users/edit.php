<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="theme-users">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Cập nhật người dùng</h2>
            <p>Chỉnh sửa thông tin tài khoản: <strong
                    class="page-header-highlight"><?= htmlspecialchars($user['full_name']) ?></strong></p>
        </div>
        <a href="index.php?url=users" class="btn btn-secondary reveal-item delay-1">
            <i class="iconsax" icon-name="arrow-left"></i>
            Quay lại danh sách
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error reveal-item">
            <i class="iconsax" icon-name="info-circle"></i>
            <ul>
                <?php foreach ($errors as $er): ?>
                    <li><?php echo htmlspecialchars($er); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card admin-form-card admin-form-card-lg reveal-item delay-2">
        <form action="index.php?url=users/update" method="POST">
            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">

            <div class="admin-form-grid-2">
                <div class="form-group">
                    <label class="admin-form-label">Họ và
                        tên</label>
                    <div class="input-with-icon">
                        <i class="iconsax" icon-name="user-2"></i>
                        <input type="text" name="full_name" class="form-control" placeholder="Nhập họ tên đầy đủ"
                            value="<?= htmlspecialchars($user['full_name']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="admin-form-label">Tên
                        đăng nhập (Username)</label>
                    <div class="input-with-icon">
                        <i class="iconsax" icon-name="person-card"></i>
                        <input type="text" name="username" class="form-control" placeholder="Mã nhân viên"
                            value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="admin-form-label">Mật
                        khẩu mới</label>
                    <div class="input-with-icon">
                        <i class="iconsax" icon-name="lock-1"></i>
                        <input type="password" name="password" class="form-control"
                            placeholder="Để trống nếu không đổi">
                    </div>
                    <small class="admin-form-help">Chỉ nhập nếu bạn muốn thay
                        đổi mật khẩu hiện tại.</small>
                </div>

                <div class="form-group">
                    <label class="admin-form-label">Vai
                        trò</label>
                    <div class="input-with-icon">
                        <i class="iconsax" icon-name="briefcase"></i>
                        <select name="role_id" id="role_id" class="form-control" required>
                            <option value="">-- Chọn vai trò --</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['role_id'] ?>" <?= ($user['role_id'] == $role['role_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role['role_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="admin-form-label">Khoa/Phòng
                        ban</label>
                    <div class="input-with-icon">
                        <i class="iconsax" icon-name="house-2"></i>
                        <select name="department_id" id="department_id" class="form-control">
                            <option value="">-- Chọn khoa / phòng ban --</option>
                            <?php foreach ($departments as $dp): ?>
                                <option value="<?= $dp['department_id'] ?>"
                                    <?= ($user['department_id'] == $dp['department_id']) ? 'selected' : '' ?>>
                                    <?php echo htmlspecialchars($dp['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="admin-form-label">Trạng
                        thái tài khoản</label>
                    <div class="input-with-icon">
                        <i class="iconsax" icon-name="status"></i>
                        <select name="status" id="status" class="form-control">
                            <option value="active" <?= $user['status'] == 'active' ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="locked" <?= $user['status'] == 'locked' ? 'selected' : '' ?>>Bị khóa</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions admin-form-actions">
                <a href="index.php?url=users" class="btn btn-secondary">
                    Hủy bỏ
                </a>
                <button type="submit" class="btn btn-theme admin-form-submit-sm">
                    <i class="iconsax" icon-name="edit-2"></i>
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
