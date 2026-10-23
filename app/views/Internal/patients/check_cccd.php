<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Kiểm tra CCCD bệnh nhân</h2>
            <p>Vui lòng nhập CCCD để kiểm tra thông tin bệnh nhân trước khi tiếp nhận.</p>
        </div>
        <div class="page-header-actions">
            <a href="index.php?url=internal/patients/search" class="btn btn-secondary">
                <i class="iconsax" icon-name="arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="card reveal-item">
        <div class="card-header">
            <h4>Kiểm tra thông tin</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error reveal-item">
                    <ul class="mb-0">
                        <li><i class="iconsax" icon-name="info-circle" style="font-size: 14px;"></i> <?= htmlspecialchars($error); ?></li>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="index.php?url=internal/patients/check-cccd" method="POST" class="premium-form">
                <div class="form-grid">
                    <div class="form-group mb-3">
                        <label class="form-label">CCCD <span class="text-danger">*</span></label>
                        <input type="text" name="citizen_id" value="" class="form-control" placeholder="Nhập 12 số CCCD" maxlength="12" pattern="[0-9]{12}" required autofocus>
                        <small class="text-muted mt-1 d-block">Hệ thống sẽ tự động kiểm tra xem bệnh nhân đã từng khám tại bệnh viện chưa.</small>
                    </div>
                </div>

                <div class="form-actions mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="iconsax" icon-name="search-normal-1"></i> Kiểm tra CCCD
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
