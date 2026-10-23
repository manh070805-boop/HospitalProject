<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php
$selectedStatus = $room['status'] ?? 'active';
?>

<div class="theme-rooms rooms-container">

    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2 class="room-form-page-title">Chỉnh sửa phòng</h2>
            <p>Cập nhật thông tin cho phòng: <strong
                    class="room-inline-highlight"><?= htmlspecialchars($room['room_code']) ?></strong></p>
        </div>
        <a href="index.php?url=rooms" class="btn btn-secondary reveal-item delay-1">
            <i class="iconsax" icon-name="arrow-left"></i>
            Quay lại danh sách
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

    <div class="card reveal-item delay-2 room-form-card room-form-card-wide">

        <form action="index.php?url=rooms/update" method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($room['room_id']) ?>">

            <div class="form-group">
                <label class="room-form-label">Khoa
                    <span class="room-required-mark">*</span></label>
                <div class="input-with-icon">
                    <i class="iconsax" icon-name="hospital"></i>
                    <select name="department_id" class="form-control" required>
                        <option value="">-- Chọn khoa --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['department_id'] ?>"
                                <?= ($room['department_id'] == $dept['department_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['department_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group room-form-group-spaced">
                <label class="room-form-label">Mã phòng
                    <span class="room-required-mark">*</span></label>
                <div class="input-with-icon">
                    <i class="iconsax" icon-name="hashtag"></i>
                    <input type="text" name="room_code" class="form-control" placeholder="Ví dụ: P101, ICU-01..."
                        maxlength="20" value="<?= htmlspecialchars($room['room_code'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group room-form-group-spaced">
                <label class="room-form-label">Tên
                    phòng</label>
                <div class="input-with-icon">
                    <i class="iconsax" icon-name="building-1"></i>
                    <input type="text" name="room_name" class="form-control"
                        placeholder="Ví dụ: Phòng Hồi sức cấp cứu..." maxlength="100"
                        value="<?= htmlspecialchars($room['room_name'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group room-form-group-spaced">
                <label class="room-form-label">Loại phòng</label>
                <div class="input-with-icon">
                    <i class="iconsax" icon-name="grid-apps"></i>
                    <select name="room_type" class="form-control">
                        <option value="">-- Chọn loại phòng --</option>
                        <?php
                        $roomTypes = ['Phòng thường', 'Phòng VIP', 'ICU', 'Phòng cách ly', 'Phòng phẫu thuật', 'Phòng cấp cứu'];
                        foreach ($roomTypes as $rt): ?>
                            <option value="<?= $rt ?>" <?= (($room['room_type'] ?? '') === $rt) ? 'selected' : '' ?>>
                                <?= $rt ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group room-form-group-spaced">
                <label class="room-form-label">Trạng thái
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
                <a href="index.php?url=rooms" class="btn btn-secondary">
                    Hủy bỏ
                </a>
                <button type="submit" class="btn btn-theme room-form-submit room-form-submit-md">
                    <i class="iconsax" icon-name="refresh"></i>
                    Cập nhật thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
