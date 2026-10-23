<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php
$currentStatus = $status ?? '';
$params = ['url' => 'rooms'];
if (!empty($keyword)) {
    $params['keyword'] = $keyword;
}
if (!empty($status)) {
    $params['status'] = $status;
}
$queryString = http_build_query($params);
?>
<div class="theme-rooms rooms-page rooms-container">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Phòng &amp; Giường bệnh</h2>
            <p>Theo dõi công suất phòng, tình trạng hoạt động và khả năng tiếp nhận bệnh nhân.</p>
        </div>
        <a href="index.php?url=rooms/create" class="btn btn-theme reveal-item delay-1">
            <i class="iconsax" icon-name="hospital"></i>
            Thêm phòng mới
        </a>
    </div>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success reveal-item">
            <i class="iconsax rooms-alert-icon" icon-name="tick-circle"></i>
            <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item">
            <i class="iconsax rooms-alert-icon" icon-name="info-circle"></i>
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="card rooms-filter-card reveal-item delay-1">
        <div class="card-header">
            <div>
                <h3>Tìm kiếm và lọc phòng bệnh</h3>
                <p>Tìm theo mã phòng, tên phòng, khoa hoặc lọc theo trạng thái hoạt động.</p>
            </div>
        </div>
        <div class="card-body">
            <form action="index.php" method="GET" class="rooms-filter-form">
                <input type="hidden" name="url" value="rooms">
                <div class="rooms-filter-row">
                    <div class="input-with-icon rooms-filter-input">
                        <i class="iconsax" icon-name="search-normal-1"></i>
                        <input type="text" name="keyword" class="form-control"
                            placeholder="Tìm theo mã phòng, tên phòng hoặc khoa..."
                            value="<?= htmlspecialchars($keyword ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-patient">
                        <i class="iconsax" icon-name="filter-search"></i>
                        Tìm kiếm
                    </button>
                    <?php if (!empty($keyword) || !empty($status)): ?>
                        <a href="index.php?url=rooms" class="btn btn-secondary" title="Xóa bộ lọc">
                            <i class="iconsax" icon-name="refresh"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="rooms-filter-chips">
                    <button type="submit" name="status" value=""
                        class="rooms-filter-chip <?= ($currentStatus === '') ? 'active' : '' ?>">
                        Tất cả
                    </button>
                    <?php foreach ($statuses as $val => $info): ?>
                        <button type="submit" name="status" value="<?= $val ?>"
                            class="rooms-filter-chip <?= ($currentStatus === $val) ? 'active' : '' ?>">
                            <?= $info['label'] ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($rooms)): ?>
        <div class="rooms-grid">
            <?php foreach ($rooms as $index => $room): ?>
                <?php
                $roomStatus = $statuses[$room['status'] ?? 'inactive'] ?? $statuses['inactive'];
                $statusText = $roomStatus['label'];
                $statusClass = $roomStatus['class'];
                $barClass = $roomStatus['class'];

                $capacity = (int) ($room['capacity'] ?? 0);
                $occupancy = (int) ($room['occupancy'] ?? 0);
                $percent = $capacity > 0 ? round(($occupancy / $capacity) * 100) : 0;
                ?>
                <article class="room-card reveal-item">
                    <div class="room-card-content">
                        <div class="room-card-header">
                            <div class="room-code-badge"><?= htmlspecialchars($room['room_code']) ?></div>
                            <span class="room-status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                        </div>

                        <div class="room-card-body">
                            <h3 class="room-title"><?= htmlspecialchars($room['room_name'] ?: $room['room_code']) ?></h3>
                            <p class="room-meta">
                                <?= htmlspecialchars($room['department_name'] ?? 'Chưa có khoa') ?>
                                <?php if (!empty($room['room_type'])): ?>
                                    <span class="room-meta-separator">•</span>
                                    <?= htmlspecialchars($room['room_type']) ?>
                                <?php endif; ?>
                            </p>

                            <?php if (($room['status'] ?? '') === 'maintenance'): ?>
                                <div class="room-maintenance-note">
                                    <strong>Phòng đang bảo trì.</strong>
                                    <span>Tạm thời không tiếp nhận bệnh nhân mới trong giai đoạn này.</span>
                                </div>
                            <?php else: ?>
                                <div class="room-progress-block">
                                    <div class="room-progress-labels">
                                        <div>
                                            <span class="room-progress-label">Tỉ lệ lấp đầy</span>
                                            <strong class="room-progress-value"><?= $occupancy ?>/<?= $capacity ?></strong>
                                        </div>
                                        <span class="room-progress-percent"><?= $percent ?>%</span>
                                    </div>
                                    <svg class="room-progress-track" viewBox="0 0 100 10" preserveAspectRatio="none"
                                        aria-hidden="true">
                                        <rect class="room-progress-track-base" x="0" y="0" width="100" height="10" rx="5" ry="5">
                                        </rect>
                                        <rect class="room-progress-bar <?= $barClass ?>" x="0" y="0" width="<?= $percent ?>"
                                            height="10" rx="5" ry="5"></rect>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="room-card-actions">
                        <a href="index.php?url=rooms/show&id=<?= $room['room_id'] ?>" class="room-card-link">
                            Chi tiết
                        </a>
                        <a class="btn-action btn-action-edit" href="index.php?url=rooms/edit&id=<?= $room['room_id'] ?>"
                            title="Chỉnh sửa">
                            <i class="iconsax" icon-name="edit-2"></i>
                        </a>
                        <button type="button" class="btn-action btn-action-delete"
                            onclick="confirmDelete('<?= $room['room_id'] ?>', '<?= htmlspecialchars($room['room_code']) ?>')"
                            title="Xóa">
                            <i class="iconsax" icon-name="trash"></i>
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>

            <a href="index.php?url=rooms/create" class="room-add-card reveal-item delay-5">
                <div class="room-add-card-plus">
                    <i class="iconsax" icon-name="add"></i>
                </div>
                <span class="room-add-card-label">Tạo phòng mới</span>
                <strong>Thêm một phòng điều trị mới cho bệnh viện</strong>
                <p>Bổ sung không gian điều trị, cấp cứu hoặc khu cách ly bằng biểu mẫu quản lý hiện tại.</p>
            </a>
        </div>
    <?php else: ?>
        <div class="card reveal-item">
            <div class="empty-state room-empty-state">
                <i class="iconsax room-empty-icon" icon-name="box"></i>
                <p>Không tìm thấy phòng nào phù hợp với bộ lọc hiện tại.</p>
                <a href="index.php?url=rooms/create" class="btn btn-theme room-empty-action">Thêm phòng mới</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($totalPages) && $totalPages > 1): ?>
        <div class="pagination pagination-card">
            <?php if ($page > 1): ?>
                <a href="index.php?<?= $queryString ?>&page=<?= $page - 1 ?>"
                    class="pagination-link pagination-link-nav pagination-link-wide pagination-link-icon">
                    <i class="iconsax" icon-name="arrow-left"></i>
                    <span>Trang trước</span>
                </a>
            <?php endif; ?>

            <div class="rooms-pagination-pages">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="index.php?<?= $queryString ?>&page=<?= $i ?>"
                        class="pagination-link <?= ($i == $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>

            <?php if ($page < $totalPages): ?>
                <a href="index.php?<?= $queryString ?>&page=<?= $page + 1 ?>"
                    class="pagination-link pagination-link-nav pagination-link-wide pagination-link-icon">
                    <span>Trang sau</span>
                    <i class="iconsax" icon-name="arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<form id="delete-room-form" action="index.php?url=rooms/destroy" method="POST" style="display:none;">
    <input type="hidden" name="id" id="delete-room-id">
</form>

<script>
    function confirmDelete(id, code) {
        if (confirm('Bạn có chắc chắn muốn xóa phòng ' + code + ' không? Thao tác này không thể hoàn tác.')) {
            document.getElementById('delete-room-id').value = id;
            document.getElementById('delete-room-form').submit();
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>