<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Bệnh nhân của tôi</h2>
            <p>Danh sách ca nội trú đang được phân công cho bác sĩ</p>
        </div>
    </div>

    <div class="card dashboard-table-card reveal-item">
        <div class="card-header">
            <div>
                <h3><i class="iconsax" icon-name="user-2"></i> Danh sách bệnh nhân</h3>
                <p>
                    Hiển thị <?= (int) $displayStart ?>-<?= (int) $displayEnd ?>
                    trong tổng số <?= (int) $totalRows ?> ca
                </p>
            </div>
        </div>

        <div class="card-body">
            <form method="GET" action="index.php" class="search-form search-form-wide mb-3">
                <input type="hidden" name="url" value="doctor-portal/admissions">

                <div class="search-row">
                    <div class="input-with-icon search-field">
                        <i class="iconsax" icon-name="search-normal-1"></i>
                        <input type="text" name="keyword" class="form-control"
                            value="<?= htmlspecialchars($keyword ?? '') ?>"
                            placeholder="Tìm theo mã BN, họ tên, SĐT, phòng hoặc giường">
                    </div>

                    <select name="filter" class="form-control search-select search-select-min-180">
                        <option value="all" <?= ($filter ?? 'all') === 'all' ? 'selected' : '' ?>>Tất cả</option>
                        <option value="alerts" <?= ($filter ?? '') === 'alerts' ? 'selected' : '' ?>>Có cảnh báo</option>
                        <option value="no-plan" <?= ($filter ?? '') === 'no-plan' ? 'selected' : '' ?>>Chưa có kế hoạch
                            điều trị</option>
                        <option value="with-plan" <?= ($filter ?? '') === 'with-plan' ? 'selected' : '' ?>>Đã có kế hoạch
                            điều trị</option>
                    </select>

                    <button type="submit" class="btn btn-primary">
                        <i class="iconsax" icon-name="search-normal-1"></i> Tìm
                    </button>
                </div>
            </form>

            <?php if (empty($patients)): ?>
                <div class="empty-state">
                    <i class="iconsax empty-state-icon" icon-name="user-2"></i>
                    <p>Không có bệnh nhân phù hợp.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table premium-table">
                        <thead>
                            <tr>
                                <th>Bệnh nhân</th>
                                <th>Vị trí</th>
                                <th>Cảnh báo</th>
                                <th>Kế hoạch điều trị</th>
                                <th class="text-right">Thao tác</th>
                                <th>Sinh tồn mới nhất</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $row): ?>
                                <?php
                                $hasAlert = !empty($row['alert_id']);
                                $hasPlan = !empty($row['treatment_plan_id']);
                                $age = '---';

                                if (!empty($row['date_of_birth'])) {
                                    $birth = new DateTime($row['date_of_birth']);
                                    $age = $birth->diff(new DateTime())->y;
                                }

                                $severity = $row['severity'] ?? '';
                                $alertClass = in_array($severity, ['critical', 'high'], true) ? 'badge-danger' : 'badge-info';
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($row['patient_name'] ?? '---') ?></strong><br>
                                        <span class="table-text-muted">
                                            <?= htmlspecialchars($row['patient_code'] ?? '---') ?>
                                            · <?= htmlspecialchars($row['gender'] ?? '---') ?>
                                            · <?= htmlspecialchars((string) $age) ?> tuổi
                                        </span>
                                    </td>

                                    <td>
                                        <strong><?= htmlspecialchars($row['room_name'] ?? '---') ?></strong><br>
                                        <span class="table-text-muted">
                                            Giường <?= htmlspecialchars($row['bed_code'] ?? '---') ?>
                                            · <?= htmlspecialchars($row['department_name'] ?? '---') ?>
                                        </span>
                                    </td>


                                    <td>
                                        <?php if ($hasAlert): ?>
                                            <span class="badge <?= $alertClass ?>">
                                                <?= htmlspecialchars($severity ?: 'medium') ?>
                                            </span>
                                            <div class="table-text-muted mt-2">
                                                <?= htmlspecialchars(mb_substr($row['alert_message'] ?? $row['alert_type'] ?? '', 0, 55)) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge badge-active">Không có</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($hasPlan): ?>
                                            <span class="badge badge-active">Active</span>
                                            <div class="table-text-muted mt-2">
                                                <?= htmlspecialchars(mb_substr($row['diagnosis'] ?? '', 0, 55)) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge badge-inactive">Chưa có</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-right">
                                        <div class="actions-row">
                                            <a href="index.php?url=doctor-portal/admissions/show&id=<?= (int) $row['admission_id'] ?>"
                                                class="btn-action btn-action-view" title="Xem chi tiết ca nội trú">
                                                <i class="iconsax" icon-name="eye"></i>
                                            </a>

                                            <a href="index.php?url=doctor-portal/treatment-plans/create&admission_id=<?= (int) $row['admission_id'] ?>"
                                                class="btn-action btn-action-edit" title="Tạo y lệnh">
                                                <i class="iconsax" icon-name="add"></i>
                                            </a>
                                        </div>
                                    </td>

                                    <td>
                                        <?php if (!empty($row['latest_record_id'])): ?>
                                            <div class="table-text-muted">
                                                HA
                                                <strong>
                                                    <?= htmlspecialchars($row['blood_pressure_systolic'] ?? '---') ?>/<?= htmlspecialchars($row['blood_pressure_diastolic'] ?? '---') ?>
                                                </strong>
                                                · SpO2 <strong><?= htmlspecialchars($row['spo2'] ?? '---') ?>%</strong>
                                            </div>
                                            <span class="table-text-muted">
                                                Mạch <?= htmlspecialchars($row['pulse'] ?? '---') ?> ·
                                                <?= !empty($row['recorded_at']) ? date('H:i d/m/Y', strtotime($row['recorded_at'])) : '---' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-inactive">Chưa ghi</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php
                            $query = http_build_query([
                                'url' => 'doctor-portal/admissions',
                                'keyword' => $keyword,
                                'filter' => $filter,
                                'page' => $i,
                            ]);
                            ?>
                            <a class="pagination-link <?= $i === $page ? 'active' : '' ?>"
                                href="index.php?<?= htmlspecialchars($query) ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>