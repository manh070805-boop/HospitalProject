<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Danh sách kế hoạch chăm sóc</h2>
            <p><?= htmlspecialchars($admission['patient_name']) ?></p>
        </div>
        <div style="display:flex; gap:8px;">
            <?php if (in_array($admission['status'], ['active', 'stable'])): ?>
                <a href="index.php?url=internal/care-plans/create&admission_id=<?= (int) $admission['admission_id'] ?>"
                    class="btn btn-primary">
                    <i class="iconsax" icon-name="add"></i> Lập kế hoạch mới
                </a>
            <?php endif; ?>
            <a href="index.php?url=internal/admissions/show&id=<?= (int) $admission['admission_id'] ?>"
                class="btn btn-secondary">
                <i class="iconsax" icon-name="arrow-left"></i> Trở về
            </a>
        </div>
    </div>

    <?php if (empty($carePlans)): ?>
        <div class="card reveal-item">
            <div class="card-body">
                <div class="empty-state">
                    <i class="iconsax empty-state-icon" icon-name="heart"></i>
                    <p>Chưa có kế hoạch chăm sóc nào.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card dashboard-table-card reveal-item">
            <table class="table premium-table">
                <thead>
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Người tạo</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th class="text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($carePlans as $cp):
                        $cs = $cp['status'] ?? 'draft'; ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($cp['plan_title'] ?? '---') ?></strong></td>
                            <td><?= htmlspecialchars($cp['created_by_name'] ?? '---') ?></td>
                            <td>
                                <?php if (!empty($cp['created_at'])): ?>
                                    <strong><?= date('H:i', strtotime($cp['created_at'])) ?></strong>
                                    <br><span class="table-text-muted"
                                        style="font-size:13px;"><?= date('d/m/Y', strtotime($cp['created_at'])) ?></span>
                                <?php else: ?>
                                    ---
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($cs === 'approved'): ?>
                                    <span class="badge badge-active">Đã duyệt</span>
                                <?php elseif ($cs === 'pending'): ?>
                                    <span class="badge badge-info">Chờ duyệt</span>
                                <?php elseif ($cs === 'rejected'): ?>
                                    <span class="badge badge-discharged">Bị từ chối</span>
                                <?php else: ?>
                                    <span class="badge badge-inactive">Nháp</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <a href="index.php?url=internal/care-plans/show&id=<?= (int) $cp['care_plan_id'] ?>"
                                    class="btn-action btn-action-view" title="Xem">
                                    <i class="iconsax" icon-name="eye"></i>
                                </a>
                                <?php if ($cs === 'draft'): ?>
                                    <form action="index.php?url=internal/care-plans/delete" method="POST" style="display:inline;"
                                        onsubmit="return confirm('Xóa nháp kế hoạch chăm sóc này? Thao tác này không thể hoàn tác.');">
                                        <input type="hidden" name="care_plan_id" value="<?= (int) $cp['care_plan_id'] ?>">
                                        <input type="hidden" name="return_url"
                                            value="index.php?url=internal/care-plans&admission_id=<?= (int) $admission['admission_id'] ?>">
                                        <button type="submit" class="btn-action btn-action-delete" title="Xóa nháp">
                                            <i class="iconsax" icon-name="trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>