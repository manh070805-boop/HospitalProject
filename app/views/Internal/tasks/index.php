<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php
$query = [];
if (!empty($admissionId))
    $query['admission_id'] = $admissionId;
if ($shiftFilter !== 'all')
    $query['shift'] = $shiftFilter;
if ($statusFilter !== 'open')
    $query['status'] = $statusFilter;
if ($keyword !== '')
    $query['keyword'] = $keyword;
$currentTaskUrl = 'index.php?url=internal/tasks' . (!empty($query) ? '&' . http_build_query($query) : '');

$shiftLabels = [
    'morning' => 'Ca sáng',
    'afternoon' => 'Ca chiều',
    'night' => 'Ca đêm',
];

$statusLabels = [
    'pending' => ['label' => 'Chờ làm', 'class' => 'task-status-pending'],
    'in_progress' => ['label' => 'Đang làm', 'class' => 'task-status-progress'],
    'completed' => ['label' => 'Hoàn thành', 'class' => 'task-status-completed'],
    'skipped' => ['label' => 'Bỏ qua', 'class' => 'task-status-skipped'],
];
?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Shift Worklist</h2>
            <p><?= $role === 'head_nurse' ? 'Điều phối task chăm sóc trong khoa theo ca' : 'Task chăm sóc được giao cho bạn hôm nay' ?>
            </p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success reveal-item"><i class="iconsax" icon-name="tick-circle"></i>
            <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item"><i class="iconsax" icon-name="info-circle"></i>
            <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="task-command-grid reveal-item">
        <div class="task-command-card is-open">
            <span>Đang mở</span>
            <strong><?= (int) $openCount ?></strong>
            <small>Tổng việc chờ & đang làm</small>
        </div>
        <div class="task-command-card is-progress">
            <span>Đang làm</span>
            <strong><?= (int) $inProgressCount ?></strong>
            <small>Task đã bắt đầu nhưng chưa hoàn tất</small>
        </div>
        <div class="task-command-card is-overdue">
            <span>Quá hạn</span>
            <strong><?= (int) $overdueCount ?></strong>
            <small>Task chưa hoàn thành sau giờ dự kiến</small>
        </div>
        <div class="task-command-card is-done">
            <span>Hoàn thành</span>
            <strong><?= (int) $completedCount ?></strong>
            <small>Tổng task đã hoàn tất hôm nay / <?= (int) $totalTasks ?></small>
        </div>
    </div>

    <div class="card card-elevated reveal-item">
        <div class="card-header card-header-clean task-toolbar">
            <form action="index.php" method="GET" class="search-form search-form-wide">
                <input type="hidden" name="url" value="internal/tasks">
                <?php if (!empty($admissionId)): ?>
                    <input type="hidden" name="admission_id" value="<?= (int) $admissionId ?>">
                <?php endif; ?>
                <input type="hidden" name="shift" value="<?= htmlspecialchars($shiftFilter) ?>">
                <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
                <div class="search-row search-row-nowrap">
                    <div class="input-with-icon search-field search-field-min">
                        <i class="iconsax" icon-name="search-normal-1"></i>
                        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>"
                            placeholder="Bệnh nhân / Mã BN / Phòng / Giường / Nội dung task..." class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="iconsax" icon-name="search-normal-1"></i> Tìm kiếm
                    </button>
                    <?php if ($keyword !== ''): ?>
                        <a href="index.php?url=internal/tasks<?= !empty($admissionId) ? '&admission_id=' . (int) $admissionId : '' ?>&shift=<?= htmlspecialchars($shiftFilter) ?>&status=<?= htmlspecialchars($statusFilter) ?>"
                            class="btn btn-light">
                            <i class="iconsax" icon-name="refresh"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="task-filter-group">
                <div class="task-filter-tabs">
                    <?php foreach (['all' => 'Tất cả', 'morning' => 'Sáng', 'afternoon' => 'Chiều', 'night' => 'Đêm'] as $key => $label): ?>
                        <a class="<?= $shiftFilter === $key ? 'is-active' : '' ?>"
                            href="index.php?url=internal/tasks<?= !empty($admissionId) ? '&admission_id=' . (int) $admissionId : '' ?>&shift=<?= $key ?>&status=<?= urlencode($statusFilter) ?><?= $keyword !== '' ? '&keyword=' . urlencode($keyword) : '' ?>">
                            <?= htmlspecialchars($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="task-filter-tabs">
                    <?php
                    $statusCounts = [
                        'open' => $openCount,
                        'overdue' => $overdueCount,
                        'pending' => $pendingCount,
                        'in_progress' => $inProgressCount,
                        'completed' => $completedCount,
                        'all' => $totalTasks
                    ];
                    foreach (['open' => 'Đang mở', 'overdue' => 'Quá hạn', 'pending' => 'Chờ', 'in_progress' => 'Đang làm', 'completed' => 'Xong', 'all' => 'Tất cả'] as $key => $label):
                        ?>
                        <a class="<?= $statusFilter === $key ? 'is-active' : '' ?>"
                            href="index.php?url=internal/tasks<?= !empty($admissionId) ? '&admission_id=' . (int) $admissionId : '' ?>&shift=<?= urlencode($shiftFilter) ?>&status=<?= $key ?><?= $keyword !== '' ? '&keyword=' . urlencode($keyword) : '' ?>">
                            <?= htmlspecialchars($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card dashboard-table-card reveal-item">
        <div class="table-responsive">
            <table class="table premium-table task-worklist-table">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Bệnh nhân</th>
                        <th>Task</th>
                        <th>Điều dưỡng</th>
                        <th>Trạng thái</th>
                        <th class="task-action-header">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tasks)): ?>
                        <?php foreach ($tasks as $task): ?>
                            <?php
                            $status = $task['status'] ?? 'pending';
                            $statusMeta = $statusLabels[$status] ?? ['label' => $status, 'class' => 'task-status-pending'];
                            $scheduledTime = !empty($task['scheduled_time']) ? strtotime($task['scheduled_time']) : null;
                            $type = $task['task_type'] ?? ['label' => 'Chăm sóc', 'icon' => 'clipboard-text-1'];
                            $priority = $task['priority'] ?? ['label' => 'Bình thường', 'class' => 'risk-stable'];
                            ?>
                            <tr class="<?= !empty($task['is_overdue']) ? 'task-row-overdue' : '' ?>">
                                <td>
                                    <div class="task-time-cell">
                                        <strong><?= $scheduledTime ? date('H:i', $scheduledTime) : '---' ?></strong>
                                        <span><?= htmlspecialchars($shiftLabels[$task['shift_type'] ?? ''] ?? 'Khác') ?></span>
                                        <?php if (!empty($task['is_overdue'])): ?>
                                            <small>Quá hạn</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="task-patient-cell">
                                        <div class="task-patient-avatar">
                                            <?= htmlspecialchars(mb_substr($task['patient_name'] ?? 'B', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($task['patient_name'] ?? '---') ?></strong>
                                            <span><?= htmlspecialchars($task['patient_code'] ?? '') ?> ·
                                                <?= htmlspecialchars($task['room_name'] ?? '---') ?> · Giường
                                                <?= htmlspecialchars($task['bed_code'] ?? '---') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="task-main-cell">
                                        <div class="task-type-row">
                                            <span class="task-type-pill">
                                                <i class="iconsax" icon-name="<?= htmlspecialchars($type['icon']) ?>"></i>
                                                <?= htmlspecialchars($type['label']) ?>
                                            </span>
                                            <span class="task-priority-pill <?= htmlspecialchars($priority['class']) ?>">
                                                <?= htmlspecialchars($priority['label']) ?>
                                            </span>
                                        </div>
                                        <strong><?= htmlspecialchars($task['task_name'] ?? '---') ?></strong>
                                        <?php if (!empty($task['task_description'])): ?>
                                            <p><?= htmlspecialchars(mb_substr($task['task_description'], 0, 140)) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($role === 'head_nurse'): ?>
                                        <form action="index.php?url=internal/tasks/assign" method="POST" class="task-assign-form">
                                            <input type="hidden" name="task_id" value="<?= (int) $task['task_id'] ?>">
                                            <input type="hidden" name="return_url" value="<?= htmlspecialchars($currentTaskUrl) ?>">
                                            <select name="nurse_id" class="form-control">
                                                <?php foreach ($nurses as $nurse): ?>
                                                    <option value="<?= (int) $nurse['user_id'] ?>" <?= (int) ($task['assigned_to'] ?? 0) === (int) $nurse['user_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($nurse['full_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-light">
                                                <i class="iconsax" icon-name="user-2-tick"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span
                                            class="table-text-muted"><?= htmlspecialchars($task['assigned_nurse_name'] ?? '---') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="task-status-pill <?= htmlspecialchars($statusMeta['class']) ?>">
                                        <?= htmlspecialchars($statusMeta['label']) ?>
                                    </span>
                                </td>
                                <td class="task-action-cell">
                                    <?php if ($role !== 'head_nurse'): ?>
                                        <div class="task-action-stack">
                                            <?php if ($status === 'pending'): ?>
                                                <form action="index.php?url=internal/tasks/start" method="POST">
                                                    <input type="hidden" name="task_id" value="<?= (int) $task['task_id'] ?>">
                                                    <input type="hidden" name="return_url"
                                                        value="<?= htmlspecialchars($currentTaskUrl) ?>">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="iconsax" icon-name="play"></i> Bắt đầu
                                                    </button>
                                                </form>
                                            <?php elseif ($status === 'in_progress'): ?>
                                                <form action="index.php?url=internal/tasks/complete" method="POST"
                                                    class="task-note-form">
                                                    <input type="hidden" name="task_id" value="<?= (int) $task['task_id'] ?>">
                                                    <input type="hidden" name="return_url"
                                                        value="<?= htmlspecialchars($currentTaskUrl) ?>">
                                                    <input type="text" name="note" class="form-control" placeholder="Kết quả thực hiện">
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="iconsax" icon-name="tick-circle"></i> Hoàn thành
                                                    </button>
                                                </form>
                                                <form action="index.php?url=internal/tasks/skip" method="POST" class="task-note-form"
                                                    onsubmit="return confirm('Xác nhận bỏ qua task này?');">
                                                    <input type="hidden" name="task_id" value="<?= (int) $task['task_id'] ?>">
                                                    <input type="hidden" name="return_url"
                                                        value="<?= htmlspecialchars($currentTaskUrl) ?>">
                                                    <input type="text" name="note" class="form-control" placeholder="Lý do bỏ qua">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="iconsax" icon-name="x-circle"></i> Bỏ qua
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="table-text-muted">Không còn thao tác</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <a href="index.php?url=internal/admissions/show&id=<?= (int) $task['admission_id'] ?>"
                                            class="btn btn-sm btn-light">
                                            <i class="iconsax" icon-name="hospital"></i> Hồ sơ ca
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="iconsax empty-state-icon" icon-name="task-list-square"></i>
                                <p>Không có task phù hợp với bộ lọc hiện tại.</p>
                                <a href="index.php?url=internal/tasks&shift=all&status=all"
                                    class="btn btn-sm btn-primary">Xem tất cả task hôm nay</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>