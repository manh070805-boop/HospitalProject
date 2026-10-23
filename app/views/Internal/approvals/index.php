<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Hàng đợi phê duyệt</h2>
            <p>Kế hoạch chăm sóc đang chờ điều dưỡng trưởng duyệt</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success reveal-item"><i class="iconsax" icon-name="tick-circle"></i> <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item"><i class="iconsax" icon-name="info-circle"></i> <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <?php if (empty($pendingPlans)): ?>
        <div class="card reveal-item">
            <div class="card-body">
                <div class="empty-state">
                    <i class="iconsax empty-state-icon" icon-name="tick-circle"></i>
                    <p>Không có kế hoạch nào chờ duyệt.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card dashboard-table-card reveal-item">
            <table class="table premium-table">
                    <thead>
                        <tr>
                            <th>Bệnh nhân</th>
                            <th>Kế hoạch</th>
                            <th>Chẩn đoán bác sĩ</th>
                            <th>Điều dưỡng</th>
                            <th>Ngày gửi</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingPlans as $cp): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($cp['patient_name'] ?? '---') ?></strong>
                                    <br><span class="table-text-muted" style="font-size:13px;"><?= htmlspecialchars($cp['patient_code'] ?? '') ?></span>
                                </td>
                                <td><?= htmlspecialchars($cp['plan_title'] ?? '---') ?></td>
                                <td class="table-text-muted"><?= htmlspecialchars(mb_substr($cp['diagnosis'] ?? 'Chưa gắn', 0, 40)) ?></td>
                                <td><?= htmlspecialchars($cp['created_by_name'] ?? '---') ?></td>
                                <td>
                                    <?php if (!empty($cp['created_at'])): ?>
                                        <strong><?= date('H:i', strtotime($cp['created_at'])) ?></strong>
                                        <br><span class="table-text-muted" style="font-size:13px;"><?= date('d/m/Y', strtotime($cp['created_at'])) ?></span>
                                    <?php else: ?>
                                        ---
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <a href="index.php?url=internal/approvals/show&id=<?= (int) $cp['care_plan_id'] ?>" class="btn-theme">
                                        <i class="iconsax" icon-name="eye"></i> Xem & Duyệt
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
