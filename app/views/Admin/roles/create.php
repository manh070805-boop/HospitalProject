<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="theme-roles">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Thêm chức vụ mới</h2>
            <p>Tạo thông tin chức vụ và phân quyền trong hệ thống</p>
        </div>
        <a href="index.php?url=roles" class="btn btn-secondary reveal-item delay-1">
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

    <div class="card admin-form-card admin-form-card-sm reveal-item delay-2">
        <form action="index.php?url=roles/store" method="POST">
            <div class="form-group">
                <label class="admin-form-label">Tên chức
                    vụ</label>
                <div class="input-with-icon">
                    <i class="iconsax" icon-name="shield-tick"></i>
                    <input type="text" name="role_name" class="form-control"
                        placeholder="Ví dụ: Quản trị viên, Nhân viên..."
                        value="<?= htmlspecialchars($_POST['role_name'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group admin-form-group-spaced">
                <label class="admin-form-label">Mô tả
                    chức năng</label>
                <div class="input-with-icon input-with-icon-textarea">
                    <i class="iconsax" icon-name="document-text-1"></i>
                    <textarea name="description" class="form-control" rows="4"
                        placeholder="Mô tả các quyền hạn của chức vụ này..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-actions admin-form-actions">
                <a href="index.php?url=roles" class="btn btn-secondary">
                    Hủy bỏ
                </a>
                <button type="submit" class="btn btn-theme admin-form-submit-sm">
                    <i class="iconsax" icon-name="tick-circle"></i>
                    Lưu chức vụ
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
