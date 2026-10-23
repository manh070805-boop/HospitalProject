<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="theme-departments">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Chỉnh sửa khoa / phòng</h2>
            <p>Cập nhật thông tin chi tiết cho đơn vị: <strong
                    class="page-header-highlight"><?= htmlspecialchars($department['department_name']) ?></strong>
            </p>
        </div>
        <a href="index.php?url=departments" class="btn btn-secondary reveal-item delay-1">
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
        <form action="index.php?url=departments/update" method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($department['department_id']) ?>">

            <div class="form-group">
                <label class="admin-form-label">Tên khoa
                    / phòng</label>
                <div class="input-with-icon">
                    <i class="iconsax" icon-name="hospital"></i>
                    <input type="text" name="department_name" class="form-control"
                        placeholder="Ví dụ: Khoa Ngoại, Phòng Tài chính..."
                        value="<?= htmlspecialchars($department['department_name'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group admin-form-group-spaced">
                <label class="admin-form-label">Mô tả /
                    Chức năng</label>
                <div class="input-with-icon input-with-icon-textarea">
                    <i class="iconsax" icon-name="document-text-1"></i>
                    <textarea name="description" class="form-control" rows="4"
                        placeholder="Mô tả chức năng nhiệm vụ của đơn vị..."><?= htmlspecialchars($department['description'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-actions admin-form-actions">
                <a href="index.php?url=departments" class="btn btn-secondary">
                    Hủy bỏ
                </a>
                <button type="submit" class="btn btn-theme admin-form-submit-md">
                    <i class="iconsax" icon-name="refresh"></i>
                    Cập nhật thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
