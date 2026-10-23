<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Lịch sử chuyển giường</h2>
            <p><?= htmlspecialchars($admission['patient_name']) ?> &mdash;
                <?= htmlspecialchars($admission['patient_code']) ?></p>
        </div>
        <a href="index.php?url=internal/admissions/show&id=<?= (int) $admission['admission_id'] ?>"
            class="btn btn-secondary">
            <i class="iconsax" icon-name="arrow-left"></i> Trở về
        </a>
    </div>

    <div class="card reveal-item" style="margin-bottom:20px;">
        <div class="card-body" style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">
            <div>
                <span class="table-text-muted">Khoa:</span><br>
                <strong><?= htmlspecialchars($admission['department_name'] ?? '---') ?></strong>
            </div>
            <div>
                <span class="table-text-muted">Phòng hiện tại:</span><br>
                <strong><?= htmlspecialchars($admission['room_name'] ?? '---') ?></strong>
            </div>
            <div>
                <span class="table-text-muted">Giường hiện tại:</span><br>
                <strong><?= htmlspecialchars($admission['bed_code'] ?? '---') ?></strong>
            </div>
            <div>
                <span class="table-text-muted">Ngày nhập viện:</span><br>
                <strong><?= !empty($admission['admission_date']) ? date('H:i d/m/Y', strtotime($admission['admission_date'])) : '---' ?></strong>
            </div>
            <div>
                <span class="table-text-muted">Trạng thái:</span><br>
                <?php
                $stMap = ['active' => ['Đang nằm viện', 'badge-active'], 'discharged' => ['Đã xuất viện', 'badge-discharged'], 'stable' => ['Ổn định', 'badge-info']];
                [$stLabel, $stCls] = $stMap[$admission['status'] ?? ''] ?? [htmlspecialchars($admission['status'] ?? '---'), 'badge-info'];
                ?>
                <span class="badge <?= $stCls ?>"><?= $stLabel ?></span>
            </div>
            <div>
                <span class="table-text-muted">Tổng số lần chuyển:</span><br>
                <strong><?= count($bedHistory) ?> bản ghi</strong>
            </div>
        </div>
    </div>

    <?php if (empty($bedHistory)): ?>
        <div class="card reveal-item">
            <div class="card-body">
                <div class="empty-state">
                    <i class="iconsax empty-state-icon" icon-name="hospital"></i>
                    <p>Chưa có lịch sử chuyển giường nào.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card dashboard-table-card reveal-item">
            <table class="table premium-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Thời gian bắt đầu</th>
                        <th>Thời gian kết thúc</th>
                        <th>Khoa</th>
                        <th>Phòng</th>
                        <th>Giường</th>
                        <th>Ghi chú / Lý do</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bedHistory as $i => $bh): ?>
                        <tr>
                            <td class="table-text-muted"><?= count($bedHistory) - $i ?></td>
                            <td>
                                <?php if (!empty($bh['changed_at'])): ?>
                                    <strong><?= date('H:i', strtotime($bh['changed_at'])) ?></strong><br>
                                    <span class="table-text-muted"
                                        style="font-size:13px;"><?= date('d/m/Y', strtotime($bh['changed_at'])) ?></span>
                                <?php else: ?>
                                    <span class="table-text-muted">---</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($bh['end_time'])): ?>
                                    <strong><?= date('H:i', strtotime($bh['end_time'])) ?></strong><br>
                                    <span class="table-text-muted"
                                        style="font-size:13px;"><?= date('d/m/Y', strtotime($bh['end_time'])) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-active" style="font-size:11px;">Hiện tại</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($bh['new_department_name'] ?? '---') ?></strong></td>
                            <td><?= htmlspecialchars($bh['new_room_name'] ?? '---') ?></td>
                            <td>
                                <span
                                    style="font-family:monospace; background:#f3f4f6; padding:2px 8px; border-radius:4px; font-size:13px;">
                                    <?= htmlspecialchars($bh['new_bed_code'] ?? '---') ?>
                                </span>
                            </td>
                            <td class="table-text-muted">
                                <?= !empty($bh['reason']) ? htmlspecialchars($bh['reason']) : '<em>Không có ghi chú</em>' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>