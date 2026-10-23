<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<div class="theme-roles">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Quản lý chức vụ</h2>
            <p>Phân quyền và quản lý vai trò trong hệ thống</p>
        </div>
        <a href="index.php?url=roles/create" class="btn btn-theme reveal-item delay-1">
            <i class="iconsax" icon-name="shield-tick"></i>
            Thêm chức vụ mới
        </a>
    </div>

    <?php
    $params = [];
    if (!empty($keyword))
        $params['keyword'] = $keyword;
    $queryString = http_build_query($params);
    ?>

    <?php if (!empty($_GET['success'])): ?>
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

    <div class="card card-elevated reveal-item delay-2">
        <div class="card-header card-header-clean">
            <form action="index.php" method="GET" class="search-form search-form-wide">
                <input type="hidden" name="url" value="roles">

                <div class="search-row search-row-nowrap">
                    <div class="input-with-icon search-field">
                        <i class="iconsax" icon-name="search-normal-1"></i>
                        <input type="text" name="keyword" class="form-control"
                            placeholder="Tìm theo tên chức vụ, mô tả..."
                            value="<?= htmlspecialchars($keyword ?? '') ?>">
                    </div>

                    <button type="submit" class="btn btn-theme">
                        <i class="iconsax" icon-name="filter-search"></i>
                        Tìm kiếm
                    </button>

                    <?php if (!empty($keyword)): ?>
                        <a href="index.php?url=roles" class="btn btn-secondary" title="Xóa bộ lọc">
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
                        <th class="table-th-name-md">Tên Chức Vụ</th>
                        <th>Mô Tả Chức Năng</th>
                        <th class="table-th-actions">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($roles)): ?>
                        <?php foreach ($roles as $rl): ?>
                            <tr>
                                <td class="col-id">#<?= htmlspecialchars($rl['role_id']) ?></td>

                                <td>
                                    <div class="entity-cell">
                                        <div class="entity-icon">
                                            <i class="iconsax" icon-name="shield"></i>
                                        </div>
                                        <span class="entity-title"><?= htmlspecialchars($rl['role_name']) ?></span>
                                    </div>
                                </td>
                                <td class="table-text-muted"><?= htmlspecialchars($rl['description']) ?></td>
                                <td class="text-right">
                                    <div class="actions-row">
                                        <a class="btn-action btn-action-edit"
                                            href="index.php?url=roles/edit&id=<?= $rl['role_id'] ?>" title="Chỉnh sửa">
                                            <i class="iconsax" icon-name="edit-2"></i>
                                        </a>
                                        <a class="btn-action btn-action-delete"
                                            href="index.php?url=roles/delete&id=<?= $rl['role_id'] ?>"
                                            onclick="return confirm('Bạn có chắc muốn xóa chức vụ này không?')" title="Xóa">
                                            <i class="iconsax" icon-name="trash"></i>
                                        </a>
                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i class="iconsax empty-state-icon" icon-name="trash"></i>
                                <p>Không tìm thấy chức vụ nào phù hợp.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <div class="pagination pagination-card">
                <?php if ($page > 1): ?>
                    <a href="index.php?url=roles<?= !empty($queryString) ? '&' . $queryString : '' ?>&page=<?= $page - 1 ?>"
                        class="pagination-link pagination-link-nav pagination-link-wide pagination-link-icon">
                        <i class="iconsax" icon-name="arrow-left"></i>
                        <span>Trang trước</span>
                    </a>
                <?php endif; ?>

                <div class="pagination-pages">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="index.php?url=roles<?= !empty($queryString) ? '&' . $queryString : '' ?>&page=<?= $i ?>"
                            class="pagination-link <?= ($i == $page) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>

                <?php if ($page < $totalPages): ?>
                    <a href="index.php?url=roles<?= !empty($queryString) ? '&' . $queryString : '' ?>&page=<?= $page + 1 ?>"
                        class="pagination-link pagination-link-nav pagination-link-wide pagination-link-icon">
                        <span>Trang sau</span>
                        <i class="iconsax" icon-name="arrow-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>