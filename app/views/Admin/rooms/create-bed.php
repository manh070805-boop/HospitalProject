<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php
$selectedStatus = $_POST['status'] ?? 'available';
?>

<div class="theme-rooms rooms-container">


    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2 class="room-form-page-title">Thêm giường bệnh</h2>
            <p>Thêm giường mới cho phòng: <strong
                    class="room-inline-highlight"><?= htmlspecialchars($room['room_code']) ?></strong></p>
        </div>
        <a href="index.php?url=rooms/show&id=<?= $room['room_id'] ?>" class="btn btn-secondary reveal-item delay-1">
            <i class="iconsax" icon-name="arrow-left"></i>
            Quay lại phòng
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error reveal-item">
            <i class="iconsax rooms-alert-icon" icon-name="info-circle"></i>
            <ul class="room-alert-list">
                <?php foreach ($error as $er): ?>
                    <li><?= htmlspecialchars($er) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card reveal-item delay-1 room-info-banner room-form-card-narrow room-info-banner-spaced">
        <div class="room-info-banner-content">
            <div class="room-info-banner-icon">
                <i class="iconsax room-info-banner-icon-symbol" icon-name="building-1"></i>
            </div>
            <div>
                <p class="room-info-banner-eyebrow">Phòng đang thêm giường</p>
                <p class="room-info-banner-title">
                    <?= htmlspecialchars($room['room_code']) ?>
                    <?php if (!empty($room['room_name'])): ?>
                        <span class="room-info-banner-subtitle"> — <?= htmlspecialchars($room['room_name']) ?></span>
                    <?php endif; ?>
                </p>
                <?php if (!empty($room['department_name'])): ?>
                    <p class="room-info-banner-meta">Khoa: <?= htmlspecialchars($room['department_name']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card reveal-item delay-2 room-form-card room-form-card-narrow">

        <form action="index.php?url=rooms/store-bed" method="POST">
            <input type="hidden" name="room_id" value="<?= $room['room_id'] ?>">
            <div class="form-group">
                <label class="room-form-label">Mã
                    giường
                    <span class="room-required-mark">*</span></label>
                <div class="input-with-icon">
                    <i class="iconsax" icon-name="hashtag"></i>
                    <input type="text" name="bed_code" class="form-control" placeholder="Ví dụ: G01, BED-A1..."
                        maxlength="20" value="<?= htmlspecialchars($_POST['bed_code'] ?? '') ?>" required>
                </div>
                <p class="room-form-help">Mã giường phải là duy nhất trong phòng này.</p>
            </div>

            <div class="form-group room-form-group-spaced">
                <label class="room-form-label">Trạng
                    thái giường
                    <span class="room-required-mark">*</span></label>
                <div class="room-status-options">
                    <?php foreach ($statuses as $val => $info): ?>
                        <label
                            class="room-status-option <?= $info['class'] ?> <?= ($selectedStatus === $val) ? 'selected' : '' ?>">
                            <input type="radio" name="status" value="<?= $val ?>"
                                class="room-status-input <?= $info['class'] ?>" <?= ($selectedStatus === $val) ? 'checked' : '' ?>>
                            <i class="iconsax room-status-icon" icon-name="<?= $info['icon'] ?>"></i>
                            <span class="room-status-label"><?= $info['label'] ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-actions room-form-actions">
                <a href="index.php?url=rooms/show&id=<?= $room['room_id'] ?>" class="btn btn-secondary">
                    Hủy bỏ
                </a>
                <button type="submit" class="btn btn-theme room-form-submit room-form-submit-sm">
                    <i class="iconsax" icon-name="add-circle"></i>
                    Thêm giường
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
