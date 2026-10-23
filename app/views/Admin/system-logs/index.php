<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="theme-logs">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Nhật ký hệ thống</h2>
            <p>Theo dõi tất cả hoạt động trong hệ thống bệnh viện</p>
        </div>
        <a href="index.php?url=system-logs/stats" class="btn btn-theme reveal-item delay-1">
            <i class="iconsax" icon-name="bar-graph-1"></i>
            Thống kê
        </a>
    </div>

    <?php
    $params = [];
    if (!empty($keyword)) $params['keyword'] = $keyword;
    if (!empty($actionFilter)) $params['action'] = $actionFilter;
    $queryString = http_build_query($params);
    ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success reveal-item">
            <i class="iconsax" icon-name="tick-circle"></i>
            <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item">
            <i class="iconsax" icon-name="info-circle"></i>
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <?php
    $totalAll = array_sum($actionCounts);
    $countCreate = $actionCounts['CREATE'] ?? 0;
    $countUpdate = $actionCounts['UPDATE'] ?? 0;
    $countDelete = $actionCounts['DELETE'] ?? 0;
    $countLogin = ($actionCounts['LOGIN'] ?? 0) + ($actionCounts['LOGOUT'] ?? 0);
    ?>
    <div class="stats-grid stats-grid-compact reveal-item delay-1">
        <div class="stat-card metric-card-border-purple">
            <div class="metric-row">
                <div>
                    <h3>Tổng bản ghi</h3>
                    <p class="metric-value-purple"><?= $totalAll ?></p>
                </div>
                <div class="metric-icon-box is-purple">
                    <i class="iconsax" icon-name="note-text"></i>
                </div>
            </div>
        </div>
        <div class="stat-card stat-success metric-card-border-green">
            <div class="metric-row">
                <div>
                    <h3>Tạo mới</h3>
                    <p class="metric-value-green"><?= $countCreate ?></p>
                </div>
                <div class="metric-icon-box is-green">
                    <i class="iconsax" icon-name="add-circle"></i>
                </div>
            </div>
        </div>
        <div class="stat-card stat-warning metric-card-border-amber">
            <div class="metric-row">
                <div>
                    <h3>Cập nhật</h3>
                    <p class="metric-value-amber"><?= $countUpdate ?></p>
                </div>
                <div class="metric-icon-box is-amber">
                    <i class="iconsax" icon-name="edit-2"></i>
                </div>
            </div>
        </div>
        <div class="stat-card stat-danger metric-card-border-red">
            <div class="metric-row">
                <div>
                    <h3>Xóa</h3>
                    <p class="metric-value-red"><?= $countDelete ?></p>
                </div>
                <div class="metric-icon-box is-red">
                    <i class="iconsax" icon-name="trash"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-elevated reveal-item delay-2">
        <div class="card-header card-header-clean">
            <form action="index.php" method="GET" class="search-form search-form-wide">
                <input type="hidden" name="url" value="system-logs">

                <div class="search-row">
                    <div class="input-with-icon search-field search-field-min">
                        <i class="iconsax" icon-name="search-normal-1"></i>
                        <input type="text" name="keyword" class="form-control" placeholder="Tìm theo hành động, mô tả, người dùng..." value="<?= htmlspecialchars($keyword ?? '') ?>">
                    </div>

                    <select name="action" class="form-control search-select search-select-min-160">
                        <option value="">Tất cả hành động</option>
                        <option value="CREATE" <?= ($actionFilter ?? '') === 'CREATE' ? 'selected' : '' ?>>Tạo mới</option>
                        <option value="UPDATE" <?= ($actionFilter ?? '') === 'UPDATE' ? 'selected' : '' ?>>Cập nhật</option>
                        <option value="DELETE" <?= ($actionFilter ?? '') === 'DELETE' ? 'selected' : '' ?>>Xóa</option>
                        <option value="LOGIN" <?= ($actionFilter ?? '') === 'LOGIN' ? 'selected' : '' ?>>Đăng nhập</option>
                        <option value="LOGOUT" <?= ($actionFilter ?? '') === 'LOGOUT' ? 'selected' : '' ?>>Đăng xuất</option>
                    </select>

                    <button type="submit" class="btn btn-theme">
                        <i class="iconsax" icon-name="filter-search"></i>
                        Lọc
                    </button>

                    <?php if (!empty($keyword) || !empty($actionFilter)): ?>
                        <a href="index.php?url=system-logs" class="btn btn-secondary" title="Xóa bộ lọc">
                            <i class="iconsax" icon-name="refresh"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table premium-table">
                <thead>
                    <tr>
                        <th class="table-th-id">ID</th>
                        <th>Thời gian</th>
                        <th>Người thực hiện</th>
                        <th>Hành động</th>
                        <th>Mô tả</th>
                        <th class="text-right">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $row): ?>
                            <tr>
                                <td class="col-id">#<?= $row['log_id'] ?></td>

                                <td>
                                    <div class="logs-time-cell">
                                        <span class="logs-time-date">
                                            <?php
                                            if (!empty($row['created_at'])) {
                                                $dt = new DateTime($row['created_at']);
                                                echo $dt->format('d/m/Y');
                                            } else { echo '---'; }
                                            ?>
                                        </span>
                                        <span class="logs-time-hour">
                                            <?php
                                            if (!empty($row['created_at'])) {
                                                echo $dt->format('H:i:s');
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="logs-user">
                                        <div class="logs-user-avatar">
                                            <i class="iconsax" icon-name="user-2"></i>
                                        </div>
                                        <div>
                                            <span class="logs-user-name">
                                                <?= !empty($row['full_name']) ? htmlspecialchars($row['full_name']) : 'Hệ thống' ?>
                                            </span>
                                            <?php if (!empty($row['username'])): ?>
                                                <span class="logs-user-handle">@<?= htmlspecialchars($row['username']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $action = strtoupper($row['action'] ?? '');
                                    $actionConfig = [
                                        'CREATE' => ['class' => 'is-create', 'icon' => 'add-circle', 'label' => 'Tạo mới'],
                                        'UPDATE' => ['class' => 'is-update', 'icon' => 'edit-2', 'label' => 'Cập nhật'],
                                        'DELETE' => ['class' => 'is-delete', 'icon' => 'trash', 'label' => 'Xóa'],
                                        'LOGIN'  => ['class' => 'is-login', 'icon' => 'login', 'label' => 'Đăng nhập'],
                                        'LOGOUT' => ['class' => 'is-logout', 'icon' => 'logout-1', 'label' => 'Đăng xuất'],
                                    ];
                                    $cfg = $actionConfig[$action] ?? ['class' => 'is-default', 'icon' => 'document-text-1', 'label' => $action];
                                    ?>
                                    <span class="logs-action-badge <?= $cfg['class'] ?>">
                                        <i class="iconsax" icon-name="<?= $cfg['icon'] ?>"></i>
                                        <?= $cfg['label'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="logs-description">
                                        <?= htmlspecialchars($row['description'] ?? '---') ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a class="btn-action btn-action-view"
                                       href="index.php?url=system-logs/show&id=<?= $row['log_id'] ?>" title="Xem chi tiết">
                                        <i class="iconsax" icon-name="eye"></i>
                                    </a>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <div class="logs-empty-block">
                                    <div class="logs-empty-figure">
                                        <i class="iconsax" icon-name="document-text-1"></i>
                                    </div>
                                    <p class="logs-empty-title">Chưa có nhật ký nào</p>
                                    <p class="logs-empty-text">Các hoạt động trong hệ thống sẽ được ghi lại tự động tại đây.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <div class="pagination pagination-card">
                <?php if ($page > 1): ?>
                    <a href="index.php?url=system-logs<?= !empty($queryString) ? '&' . $queryString : '' ?>&page=<?= $page - 1 ?>" class="pagination-link pagination-link-nav pagination-link-wide pagination-link-icon">
                        <i class="iconsax" icon-name="arrow-left"></i>
                        <span>Trang trước</span>
                    </a>
                <?php endif; ?>

                <div class="pagination-pages">
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    ?>
                    <?php if ($startPage > 1): ?>
                        <a href="index.php?url=system-logs<?= !empty($queryString) ? '&' . $queryString : '' ?>&page=1" class="pagination-link">1</a>
                        <?php if ($startPage > 2): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="index.php?url=system-logs<?= !empty($queryString) ? '&' . $queryString : '' ?>&page=<?= $i ?>"
                            class="pagination-link <?= ($i == $page) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php endif; ?>
                        <a href="index.php?url=system-logs<?= !empty($queryString) ? '&' . $queryString : '' ?>&page=<?= $totalPages ?>" class="pagination-link"><?= $totalPages ?></a>
                    <?php endif; ?>
                </div>

                <?php if ($page < $totalPages): ?>
                    <a href="index.php?url=system-logs<?= !empty($queryString) ? '&' . $queryString : '' ?>&page=<?= $page + 1 ?>" class="pagination-link pagination-link-nav pagination-link-wide pagination-link-icon">
                        <span>Trang sau</span>
                        <i class="iconsax" icon-name="arrow-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

