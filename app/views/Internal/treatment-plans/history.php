<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Lịch sử kế hoạch điều trị</h2>
            <p><?= htmlspecialchars($admission['patient_name']) ?></p>
        </div>
        <a href="index.php?url=internal/admissions/show&id=<?= (int) $admission['admission_id'] ?>" class="btn btn-secondary">
            <i class="iconsax" icon-name="arrow-left"></i> Trở về
        </a>
    </div>

    <?php if (empty($plans)): ?>
        <div class="card reveal-item">
            <div class="card-body">
                <div class="empty-state">
                    <i class="iconsax empty-state-icon" icon-name="clipboard-text-1"></i>
                    <p>Chưa có kế hoạch điều trị nào.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card dashboard-table-card reveal-item">
            <table class="table premium-table">
                    <thead>
                        <tr>
                            <th>Ngày lập</th>
                            <th>Chẩn đoán</th>
                            <th>Mục tiêu</th>
                            <th>Bác sĩ</th>
                            <th>Trạng thái</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plans as $tp): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($tp['created_at'])): ?>
                                        <strong><?= date('H:i', strtotime($tp['created_at'])) ?></strong>
                                        <br><span class="table-text-muted" style="font-size:13px;"><?= date('d/m/Y', strtotime($tp['created_at'])) ?></span>
                                    <?php else: ?>
                                        ---
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($tp['diagnosis'] ?? '---') ?></strong></td>
                                <td class="table-text-muted"><?= htmlspecialchars(mb_substr($tp['treatment_goal'] ?? '', 0, 50)) ?></td>
                                <td><?= htmlspecialchars($tp['doctor_name'] ?? '---') ?></td>
                                <td><span class="badge badge-active"><?= htmlspecialchars($tp['status'] ?? 'active') ?></span></td>
                                <td class="text-right">
                                    <a href="index.php?url=internal/treatment-plans/show&id=<?= (int) $tp['treatment_plan_id'] ?>" class="btn-action btn-action-view" title="Xem">
                                        <i class="iconsax" icon-name="eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
