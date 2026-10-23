<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="theme-users">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Quản lý người dùng</h2>
            <p>Danh sách các tài khoản truy cập hệ thống</p>
        </div>
        <a href="index.php?url=users/create" class="btn btn-theme reveal-item delay-1">
            <i class="iconsax" icon-name="user-2-add"></i>
            Thêm người dùng mới
        </a>
    </div>

    <?php
    $params = [];
    if (!empty($keyword))
        $params['keyword'] = $keyword;
    if (!empty($status))
        $params['status'] = $status;
    if (!empty($department_id))
        $params['department_id'] = $department_id;
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


    <div class="card card-elevated reveal-item delay-2">
        <div class="card-header card-header-clean">
            <form action="index.php" method="GET" class="search-form search-form-wide">
                <input type="hidden" name="url" value="users">

                <div class="search-row">
                    <div class="input-with-icon search-field search-field-min">
                        <i class="iconsax" icon-name="search-normal-1"></i>
                        <input type="text" name="keyword" class="form-control"
                            placeholder="Tìm theo họ tên, username..." value="<?= htmlspecialchars($keyword ?? '') ?>">
                    </div>

                    <select name="status" class="form-control search-select search-select-min-160">
                        <option value="">Trạng thái</option>
                        <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="locked" <?= ($status ?? '') === 'locked' ? 'selected' : '' ?>>Bị khóa</option>
                    </select>

                    <select name="department_id" class="form-control search-select search-select-min-180">
                        <option value="">Tất cả khoa phòng</option>
                        <?php if (!empty($departments)): ?>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['department_id'] ?>" <?= (isset($department_id) && $department_id == $dept['department_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['department_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>

                    <button type="submit" class="btn btn-theme">
                        <i class="iconsax" icon-name="filter-search"></i>
                        Lọc
                    </button>

                    <?php if (!empty($keyword) || !empty($status) || !empty($department_id)): ?>
                        <a href="index.php?url=users" class="btn btn-secondary" title="Xóa bộ lọc">
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
                        <th>Họ và tên</th>
                        <th>Tài khoản</th>
                        <th>Vai trò</th>
                        <th>Khoa</th>
                        <th>Trạng thái</th>
                        <th class="text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $row): ?>
                            <tr>
                                <td class="col-id">#<?= $row['user_id'] ?></td>

                                <td class="cell-strong"><?= htmlspecialchars($row['full_name']) ?></td>
                                <td>
                                    <div class="entity-cell">
                                        <div class="entity-icon entity-icon-sm">
                                            <i class="iconsax" icon-name="user-2-circle"></i>
                                        </div>
                                        <div class="entity-title-muted">
                                            <?= htmlspecialchars($row['username']) ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-badge">
                                        <?= htmlspecialchars($row['role_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="entity-inline-meta">
                                        <i class="iconsax" icon-name="hospital"></i>
                                        <?= !empty($row['department_name']) ? htmlspecialchars($row['department_name']) : '---' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $userStatus = strtolower($row['status'] ?? '');
                                    if ($userStatus == 'active'): ?>
                                        <span class="badge badge-active"><span class="status-dot dot-active"></span>Hoạt động</span>
                                    <?php elseif ($userStatus == 'locked'): ?>
                                        <span class="badge badge-locked"><span class="status-dot dot-locked"></span>Bị khóa</span>
                                    <?php else: ?>
                                        <span class="badge badge-neutral"><span class="status-dot dot-neutral"></span><?= htmlspecialchars($row['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <div class="actions-row">
                                        <a class="btn-action btn-action-edit"
                                            href="index.php?url=users/edit&id=<?= $row['user_id'] ?>" title="Chỉnh sửa">
                                            <i class="iconsax" icon-name="edit-2"></i>
                                        </a>
                                        <?php if (strtolower($row['status']) !== 'locked'): ?>
                                        <a class="btn-action btn-action-lock"
                                            href="index.php?url=users/lock&id=<?= $row['user_id'] ?>"
                                            onclick="return confirm('Bạn có chắc muốn khóa tài khoản &quot;<?= htmlspecialchars($row['username']) ?>&quot; không?')"
                                            title="Khóa tài khoản">
                                            <i class="iconsax" icon-name="lock-1"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="iconsax empty-state-icon" icon-name="user-2-x"></i>
                                <p>Không tìm thấy người dùng nào phù hợp.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <div class="pagination pagination-card">
                <?php if ($page > 1): ?>
                    <a href="index.php?url=users<?= !empty($queryString) ? '&' . $queryString : '' ?>&page=<?= $page - 1 ?>"
                        class="pagination-link pagination-link-nav pagination-link-wide pagination-link-icon">
                        <i class="iconsax" icon-name="arrow-left"></i>
                        <span>Trang trước</span>
                    </a>
                <?php endif; ?>

                <div class="pagination-pages">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="index.php?url=users<?= !empty($queryString) ? '&' . $queryString : '' ?>&page=<?= $i ?>"
                            class="pagination-link <?= ($i == $page) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>

                <?php if ($page < $totalPages): ?>
                    <a href="index.php?url=users<?= !empty($queryString) ? '&' . $queryString : '' ?>&page=<?= $page + 1 ?>"
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
