<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Theo dõi sinh tồn</h2>
            <p>Chọn bệnh nhân nội trú để ghi sinh tồn, xem lịch sử và theo dõi Risk Score.</p>
        </div>
        <div class="page-header-actions">
            <a href="index.php?url=internal/monitoring" class="btn btn-secondary">
                <i class="iconsax" icon-name="hospital"></i> Danh sách nội trú
            </a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success reveal-item">
            <i class="iconsax" icon-name="tick-circle"></i> <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item">
            <i class="iconsax" icon-name="info-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="vitals-command-grid reveal-item">
        <div class="vitals-command-card">
            <span>Cần ghi sinh tồn</span>
            <strong><?= (int) $dueCount ?></strong>
            <small>Bệnh nhân chưa ghi hoặc đã đến hạn đo lại</small>
        </div>
        <div class="vitals-command-card is-risk">
            <span>Nguy cơ cao</span>
            <strong><?= (int) $riskCount ?></strong>
            <small>Risk Score mức theo dõi sát hoặc nguy cơ cao</small>
        </div>
        <div class="vitals-command-card">
            <span>Đang nội trú</span>
            <strong><?= (int) $totalAdmissions ?></strong>
            <small>Ca active/stable trong phạm vi phụ trách</small>
        </div>
    </div>

    <div class="card card-elevated reveal-item">
        <div class="card-header card-header-clean vitals-toolbar">
            <form action="index.php" method="GET" class="search-form search-form-wide">
                <input type="hidden" name="url" value="internal/vitals">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <div class="search-row search-row-nowrap">
                    <div class="input-with-icon search-field search-field-min">
                        <i class="iconsax" icon-name="search-normal-1"></i>
                        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>"
                            placeholder="Tên / Mã BN / CCCD / Phòng / Giường..." class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="iconsax" icon-name="search-normal-1"></i> Tìm kiếm
                    </button>
                    <?php if ($keyword !== ''): ?>
                        <a href="index.php?url=internal/vitals&filter=<?= htmlspecialchars($filter) ?>"
                            class="btn btn-light">
                            <i class="iconsax" icon-name="refresh"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="vitals-filter-tabs">
                <a class="<?= $filter === 'due' ? 'is-active' : '' ?>"
                    href="index.php?url=internal/vitals&filter=due<?= $keyword !== '' ? '&keyword=' . urlencode($keyword) : '' ?>">
                    Cần ghi
                </a>
                <a class="<?= $filter === 'risk' ? 'is-active' : '' ?>"
                    href="index.php?url=internal/vitals&filter=risk<?= $keyword !== '' ? '&keyword=' . urlencode($keyword) : '' ?>">
                    Cảnh báo
                </a>
                <a class="<?= $filter === 'all' ? 'is-active' : '' ?>"
                    href="index.php?url=internal/vitals&filter=all<?= $keyword !== '' ? '&keyword=' . urlencode($keyword) : '' ?>">
                    Tất cả
                </a>
            </div>
        </div>
    </div>

    <div class="card dashboard-table-card reveal-item">
        <div class="table-responsive">
            <table class="table premium-table vitals-worklist-table">
                <thead>
                    <tr>
                        <th>Bệnh nhân</th>
                        <th>Phòng / Giường</th>
                        <th>Lần ghi gần nhất</th>
                        <th>Risk</th>
                        <th>Đến hạn</th>
                        <th class="vitals-action-header">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($admissions)): ?>
                        <?php foreach ($admissions as $row): ?>
                            <?php
                            $risk = $row['risk'];
                            $nextDueText = !empty($row['next_due_at'])
                                ? date('H:i d/m', (int) $row['next_due_at'])
                                : 'Cần ghi ngay';
                            ?>
                            <tr>
                                <td>
                                    <div class="vitals-patient-cell">
                                        <div class="vitals-patient-avatar">
                                            <?= htmlspecialchars(mb_substr($row['patient_name'] ?? 'B', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($row['patient_name'] ?? '---') ?></strong>
                                            <span><?= htmlspecialchars($row['patient_code'] ?? '') ?> · CCCD
                                                <?= htmlspecialchars($row['citizen_id'] ?? '---') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['room_name'] ?? '---') ?></strong>
                                    <br><span class="table-text-muted">Giường
                                        <?= htmlspecialchars($row['bed_code'] ?? '---') ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($row['latest_record_id'])): ?>
                                        <strong><?= date('H:i', strtotime($row['recorded_at'])) ?></strong>
                                        <br><span class="table-text-muted"><?= date('d/m/Y', strtotime($row['recorded_at'])) ?> ·
                                            <?= htmlspecialchars($row['recorded_by_name'] ?? '---') ?></span>
                                        <br><span class="table-text-muted">
                                            SpO2 <?= htmlspecialchars($row['spo2'] ?? '---') ?>% ·
                                            Mạch <?= htmlspecialchars($row['pulse'] ?? '---') ?> ·
                                            HA
                                            <?= htmlspecialchars($row['blood_pressure_systolic'] ?? '---') ?>/<?= htmlspecialchars($row['blood_pressure_diastolic'] ?? '---') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="vital-risk-pill risk-watch">Chưa ghi</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="vital-risk-pill <?= htmlspecialchars($risk['class']) ?>">
                                        <?= (int) $risk['score'] ?> · <?= htmlspecialchars($risk['label']) ?>
                                    </span>
                                    <?php if (!empty($risk['signals'])): ?>
                                        <br><span class="table-text-muted"
                                            style="font-size:12px;"><?= htmlspecialchars(implode(', ', array_slice($risk['signals'], 0, 2))) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['is_due'])): ?>
                                        <span class="vitals-due-pill is-due">Cần ghi</span>
                                    <?php else: ?>
                                        <span class="vitals-due-pill">Chưa đến hạn</span>
                                    <?php endif; ?>
                                    <br><span class="table-text-muted"><?= htmlspecialchars($nextDueText) ?></span>
                                </td>
                                <td class="vitals-action-cell">
                                    <div class="vitals-action-stack">
                                        <a href="index.php?url=internal/vitals/create&admission_id=<?= (int) $row['admission_id'] ?>"
                                            class="btn btn-sm btn-primary">
                                            <i class="iconsax" icon-name="heart"></i> Ghi sinh tồn
                                        </a>
                                        <a href="index.php?url=internal/vitals/history&admission_id=<?= (int) $row['admission_id'] ?>"
                                            class="btn btn-sm btn-light">
                                            <i class="iconsax" icon-name="clock"></i> Lịch sử
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="iconsax empty-state-icon" icon-name="heart"></i>
                                <p>Không có bệnh nhân phù hợp với bộ lọc hiện tại.</p>
                                <a href="index.php?url=internal/vitals&filter=all" class="btn btn-sm btn-primary">Xem tất cả
                                    nội trú</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>