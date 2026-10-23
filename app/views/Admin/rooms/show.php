<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php
$roomStatusInfo = $roomStatuses[$room['status'] ?? 'active'] ?? $roomStatuses['inactive'];
$statusLabel = $roomStatusInfo['label'];
$statusClass = $roomStatusInfo['class'];

$capacity = (int) ($roomStats['capacity'] ?? 0);
$occupancy = (int) ($roomStats['occupancy'] ?? 0);
$percent = $capacity > 0 ? round(($occupancy / $capacity) * 100) : 0;
$availableBeds = max(0, $capacity - $occupancy);
$bedCount = count($beds ?? []);
?>
<div class="theme-rooms rooms-page rooms-container">
    <div class="page-header-actions">
        <a href="index.php?url=rooms" class="btn btn-secondary reveal-item delay-1">
            <i class="iconsax" icon-name="arrow-left"></i>
            Quay lại danh sách
        </a>
    </div>

    <div class="page-header rooms-page-header reveal-item">
        <div class="page-header-info">
            <h2><?= htmlspecialchars($room['room_name'] ?: $room['room_code']) ?></h2>
            <p>Theo dõi tình trạng phòng, công suất giường bệnh và thao tác quản trị liên quan.</p>
        </div>

        <div class="rooms-page-actions">
            <a href="index.php?url=rooms/edit&id=<?= $room['room_id'] ?>" class="btn btn-secondary">
                <i class="iconsax" icon-name="edit-2"></i>
                Chỉnh sửa
            </a>
            <button type="button"
                onclick="confirmDeleteRoom('<?= $room['room_id'] ?>', '<?= htmlspecialchars($room['room_code']) ?>')"
                class="btn btn-danger">
                <i class="iconsax" icon-name="trash"></i>
                Xóa phòng
            </button>
        </div>
    </div>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success reveal-item">
            <i class="iconsax rooms-alert-icon" icon-name="tick-circle"></i>
            <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-error reveal-item">
            <i class="iconsax rooms-alert-icon" icon-name="info-circle"></i>
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="rooms-detail-grid">
        <section class="card room-summary-card reveal-item delay-1">
            <div class="room-summary-content">
                <div class="room-summary-code"><?= htmlspecialchars(mb_substr($room['room_code'], 0, 4)) ?></div>
                <div class="room-summary-main">
                    <div class="room-summary-title-row">
                        <h3><?= htmlspecialchars($room['room_name'] ?: $room['room_code']) ?></h3>
                        <span class="room-status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                    </div>
                    <p class="room-summary-meta">
                        Khoa: <strong><?= htmlspecialchars($room['department_name'] ?? 'Chưa có khoa') ?></strong>
                        <?php if (!empty($room['room_type'])): ?>
                            <span class="room-meta-separator">•</span>
                            Loại phòng: <strong><?= htmlspecialchars($room['room_type']) ?></strong>
                        <?php endif; ?>
                        <span class="room-meta-separator">•</span>
                        Mã: <strong><?= htmlspecialchars($room['room_code']) ?></strong>
                    </p>

                    <div class="room-summary-stats">
                        <div class="room-summary-stat">
                            <span class="room-summary-stat-label">Tổng giường</span>
                            <strong class="room-summary-stat-value"><?= $capacity ?></strong>
                        </div>
                        <div class="room-summary-stat">
                            <span class="room-summary-stat-label">Đã sử dụng</span>
                            <strong class="room-summary-stat-value is-primary"><?= $occupancy ?></strong>
                        </div>
                        <div class="room-summary-stat">
                            <span class="room-summary-stat-label">Còn trống</span>
                            <strong class="room-summary-stat-value is-success"><?= $availableBeds ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <aside class="card room-occupancy-card reveal-item delay-2">
            <div class="room-occupancy-card-body">
                <p class="room-occupancy-label">Tỉ lệ lấp đầy</p>
                <?php
                $circumference = 2 * M_PI * 52;
                $dashOffset = $circumference * (1 - ($percent / 100));
                ?>
                <div class="room-occupancy-ring">
                    <svg class="room-occupancy-chart" viewBox="0 0 116 116" aria-hidden="true">
                        <circle class="room-occupancy-chart-track" cx="58" cy="58" r="52"></circle>
                        <circle class="room-occupancy-chart-progress" cx="58" cy="58" r="52"
                            stroke-dasharray="<?= number_format($circumference, 2, '.', '') ?>"
                            stroke-dashoffset="<?= number_format($dashOffset, 2, '.', '') ?>"></circle>
                    </svg>
                    <span><?= $percent ?>%</span>
                </div>
                <p class="room-occupancy-note"><?= $occupancy ?>/<?= $capacity ?> giường đang có bệnh nhân nội trú</p>
            </div>
        </aside>
    </div>

    <section class="card rooms-beds-card reveal-item delay-3">
        <div class="card-header">
            <div>
                <h3>Danh sách giường bệnh</h3>
                <p><?= $bedCount ?> giường đang hoạt động trong phòng hiện tại.</p>
            </div>
            <a href="index.php?url=rooms/create-bed&room_id=<?= $room['room_id'] ?>" class="btn btn-theme">
                Thêm giường
            </a>
        </div>

        <div class="card-body">
            <?php if (!empty($beds)): ?>
                <div class="room-bed-grid">
                    <?php foreach ($beds as $index => $bed): ?>
                        <?php
                        $bedStatusInfo = $bedStatuses[$bed['status'] ?? 'available'] ?? $bedStatuses['maintenance'];
                        $bedStatusText = $bedStatusInfo['label'];
                        $bedDotClass = 'is-' . ($bed['status'] ?? 'available');

                        if (($bed['status'] ?? '') === 'occupied') {
                            $bedCardClass = 'is-occupied';
                            $bedTitle = htmlspecialchars($bed['patient_name'] ?? 'Bệnh nhân');
                            $bedSubtitle = 'Đang tiếp nhận điều trị nội trú';
                        } elseif (($bed['status'] ?? '') === 'maintenance') {
                            $bedCardClass = 'is-maintenance';
                            $bedTitle = 'Bảo trì';
                            $bedSubtitle = 'Tạm thời chưa thể tiếp nhận bệnh nhân';
                        } else {
                            $bedCardClass = '';
                            $bedTitle = 'Trống';
                            $bedSubtitle = 'Sẵn sàng tiếp nhận bệnh nhân';
                        }
                        ?>
                        <article class="room-bed-card <?= $bedCardClass ?> reveal-item">
                            <div class="room-bed-header">
                                <span class="room-bed-number"><?= htmlspecialchars($bed['bed_code']) ?></span>
                                <span class="room-bed-chip"><?= $bedStatusText ?></span>
                            </div>

                            <div class="room-bed-status-row">
                                <span class="room-status-dot <?= $bedDotClass ?>"></span>
                                <span class="room-bed-status-text"><?= $bedStatusText ?></span>
                            </div>

                            <h4 class="room-bed-title"><?= $bedTitle ?></h4>
                            <p class="room-bed-subtitle"><?= $bedSubtitle ?></p>

                            <div class="room-bed-actions">
                                <a href="index.php?url=rooms/edit-bed&id=<?= $bed['bed_id'] ?>"
                                    class="btn-action btn-action-edit" title="Chỉnh sửa giường">
                                    <i class="iconsax" icon-name="edit-2"></i>
                                </a>
                                <button type="button"
                                    onclick="confirmDeleteBed('<?= $bed['bed_id'] ?>', '<?= htmlspecialchars($bed['bed_code']) ?>')"
                                    class="btn-action btn-action-delete" title="Xóa giường">
                                    <i class="iconsax" icon-name="trash"></i>
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>

                    <a href="index.php?url=rooms/create-bed&room_id=<?= $room['room_id'] ?>" class="room-add-bed-card">
                        <div class="room-add-card-plus">
                            <i class="iconsax" icon-name="add"></i>
                        </div>
                        <span class="room-add-card-label">Thêm giường mới</span>
                        <strong>Bổ sung giường bệnh cho phòng hiện tại</strong>
                        <p>Tạo thêm giường mới để tăng khả năng tiếp nhận bệnh nhân khi cần.</p>
                    </a>
                </div>
            <?php else: ?>
                <div class="empty-state room-empty-state">
                    <i class="iconsax room-empty-icon" icon-name="box"></i>
                    <p>Phòng này chưa có giường nào được khai báo trong hệ thống.</p>
                    <a href="index.php?url=rooms/create-bed&room_id=<?= $room['room_id'] ?>"
                        class="btn btn-theme room-empty-action">
                        Thêm giường đầu tiên
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<form id="delete-bed-form" action="index.php?url=rooms/delete-bed" method="POST" style="display:none;">
    <input type="hidden" name="id" id="delete-bed-id">
</form>

<script>
    function confirmDeleteBed(id, code) {
        if (confirm('Bạn có chắc chắn muốn xóa giường ' + code + ' không? Thao tác này không thể hoàn tác.')) {
            document.getElementById('delete-bed-id').value = id;
            document.getElementById('delete-bed-form').submit();
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>