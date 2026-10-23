<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php
$chartRows = array_reverse($vitals ?? []);
$vitalChartData = array_map(static function ($row) {
    return [
        'time' => !empty($row['recorded_at']) ? date('H:i d/m', strtotime($row['recorded_at'])) : '',
        'temperature' => isset($row['temperature']) ? (float) $row['temperature'] : null,
        'pulse' => isset($row['pulse']) ? (int) $row['pulse'] : null,
        'bp_sys' => isset($row['blood_pressure_systolic']) ? (int) $row['blood_pressure_systolic'] : null,
        'bp_dia' => isset($row['blood_pressure_diastolic']) ? (int) $row['blood_pressure_diastolic'] : null,
        'respiratory_rate' => isset($row['respiratory_rate']) ? (int) $row['respiratory_rate'] : null,
        'spo2' => isset($row['spo2']) ? (float) $row['spo2'] : null,
    ];
}, $chartRows);
?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Lịch sử sinh tồn</h2>
            <p><?= htmlspecialchars($admission['patient_name']) ?> &mdash; <?= htmlspecialchars($admission['bed_code'] ?? '') ?></p>
        </div>
        <div style="display:flex; gap:8px;">
            <?php if (in_array($admission['status'], ['active', 'stable'])): ?>
                <a href="index.php?url=internal/vitals/create&admission_id=<?= (int) $admission['admission_id'] ?>" class="btn btn-primary">
                    <i class="iconsax" icon-name="add"></i> Ghi sinh tồn mới
                </a>
            <?php endif; ?>
            <a href="index.php?url=internal/admissions/show&id=<?= (int) $admission['admission_id'] ?>" class="btn btn-secondary">
                <i class="iconsax" icon-name="arrow-left"></i> Trở về
            </a>
        </div>
    </div>

    <?php if (empty($vitals)): ?>
        <div class="card reveal-item">
            <div class="card-body">
                <div class="empty-state">
                    <i class="iconsax empty-state-icon" icon-name="heart"></i>
                    <p>Chưa có dữ liệu sinh tồn nào.</p>
                    <?php if (in_array($admission['status'], ['active', 'stable'])): ?>
                        <a href="index.php?url=internal/vitals/create&admission_id=<?= (int) $admission['admission_id'] ?>" class="btn btn-primary">
                            Ghi sinh tồn ngay
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="vital-risk-grid reveal-item">
            <div class="vital-risk-card <?= htmlspecialchars($latestRisk['class'] ?? 'risk-stable') ?>">
                <div class="vital-risk-label">Risk Score hiện tại</div>
                <div class="vital-risk-score"><?= (int) ($latestRisk['score'] ?? 0) ?></div>
                <div class="vital-risk-title"><?= htmlspecialchars($latestRisk['label'] ?? 'Ổn định') ?></div>
                <p><?= htmlspecialchars($latestRisk['recommendation'] ?? 'Tiếp tục theo dõi định kỳ theo ca.') ?></p>
            </div>

            <div class="vital-risk-card <?= htmlspecialchars($trend['class'] ?? 'risk-stable') ?>">
                <div class="vital-risk-label">Xu hướng gần nhất</div>
                <div class="vital-risk-title"><?= htmlspecialchars($trend['label'] ?? 'Chưa đủ dữ liệu xu hướng') ?></div>
                <p><?= htmlspecialchars($trend['detail'] ?? '') ?></p>
                <div class="vital-next-check">
                    <i class="iconsax" icon-name="clock"></i>
                    Đề xuất đo lại: <?= htmlspecialchars($latestRisk['next_label'] ?? '4 giờ') ?>
                </div>
            </div>
        </div>

        <div class="card vital-chart-card reveal-item delay-1">
            <div class="card-header card-header-clean">
                <h4><i class="iconsax" icon-name="chart-square" style="font-size:16px;"></i> Xu hướng sinh tồn</h4>
                <div class="vital-chart-tabs" role="group" aria-label="Chọn chỉ số biểu đồ">
                    <button type="button" class="vital-chart-tab is-active" data-metric="spo2">SpO2</button>
                    <button type="button" class="vital-chart-tab" data-metric="pulse">Mạch</button>
                    <button type="button" class="vital-chart-tab" data-metric="temperature">Nhiệt độ</button>
                    <button type="button" class="vital-chart-tab" data-metric="bp_sys">HA tâm thu</button>
                    <button type="button" class="vital-chart-tab" data-metric="respiratory_rate">Nhịp thở</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="vitalTrendCanvas" class="vital-trend-canvas" height="240"></canvas>
            </div>
        </div>

        <div class="card dashboard-table-card reveal-item">
            <table class="table premium-table">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Risk</th>
                            <th>Nhiệt độ</th>
                            <th>Mạch</th>
                            <th>Huyết áp</th>
                            <th>Nhịp thở</th>
                            <th>SpO2</th>
                            <th>Người ghi</th>
                            <th>Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vitals as $v): ?>
                            <?php
                                $risk = $v['risk'] ?? ['score' => 0, 'label' => 'Ổn định', 'class' => 'risk-stable', 'signals' => []];
                                $isCritical = in_array($risk['level'] ?? '', ['critical', 'high'], true);
                            ?>
                            <tr style="<?= $isCritical ? 'background: rgba(239,68,68,0.06);' : '' ?>">
                                <td>
                                    <?php if (!empty($v['recorded_at'])): ?>
                                        <strong><?= date('H:i', strtotime($v['recorded_at'])) ?></strong>
                                        <br><span class="table-text-muted" style="font-size:13px;"><?= date('d/m/Y', strtotime($v['recorded_at'])) ?></span>
                                    <?php else: ?>
                                        ---
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="vital-risk-pill <?= htmlspecialchars($risk['class']) ?>">
                                        <?= (int) $risk['score'] ?> · <?= htmlspecialchars($risk['label']) ?>
                                    </span>
                                    <?php if (!empty($risk['signals'])): ?>
                                        <br><span class="table-text-muted" style="font-size:12px;"><?= htmlspecialchars(implode(', ', array_slice($risk['signals'], 0, 2))) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($v['temperature'] ?? '---') ?>°C</td>
                                <td><?= htmlspecialchars($v['pulse'] ?? '---') ?></td>
                                <td><?= htmlspecialchars($v['blood_pressure_systolic'] ?? '---') ?>/<?= htmlspecialchars($v['blood_pressure_diastolic'] ?? '---') ?></td>
                                <td><?= htmlspecialchars($v['respiratory_rate'] ?? '---') ?></td>
                                <td>
                                    <?php $spo2 = (int) ($v['spo2'] ?? 100); ?>
                                    <?php if ($spo2 < 90): ?>
                                        <span class="vital-risk-pill risk-critical"><?= $spo2 ?>%</span>
                                    <?php elseif ($spo2 < 92): ?>
                                        <span class="vital-risk-pill risk-high"><?= $spo2 ?>%</span>
                                    <?php else: ?>
                                        <?= $spo2 ?>%
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($v['recorded_by_name'] ?? '---') ?></td>
                                <td class="table-text-muted"><?= htmlspecialchars(mb_substr($v['symptoms_note'] ?? '', 0, 40)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
        </div>
    <?php endif; ?>
</div>

<script>
(() => {
    const rows = <?= json_encode($vitalChartData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const canvas = document.getElementById('vitalTrendCanvas');
    if (!canvas || rows.length === 0) return;

    const metrics = {
        spo2: { label: 'SpO2 (%)', color: '#2563eb' },
        pulse: { label: 'Mạch (lần/phút)', color: '#dc2626' },
        temperature: { label: 'Nhiệt độ (°C)', color: '#d97706' },
        bp_sys: { label: 'Huyết áp tâm thu (mmHg)', color: '#7c3aed' },
        respiratory_rate: { label: 'Nhịp thở (lần/phút)', color: '#0f766e' }
    };

    function draw(metric) {
        const ctx = canvas.getContext('2d');
        const ratio = window.devicePixelRatio || 1;
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width * ratio;
        canvas.height = 240 * ratio;
        ctx.scale(ratio, ratio);
        ctx.clearRect(0, 0, rect.width, 240);

        const values = rows.map(row => row[metric]).filter(value => value !== null);
        if (values.length === 0) return;

        const padding = { left: 44, right: 18, top: 26, bottom: 38 };
        const width = rect.width - padding.left - padding.right;
        const height = 240 - padding.top - padding.bottom;
        const min = Math.min(...values);
        const max = Math.max(...values);
        const range = Math.max(1, max - min);
        const yMin = min - range * 0.18;
        const yMax = max + range * 0.18;

        ctx.strokeStyle = '#e5e7eb';
        ctx.lineWidth = 1;
        ctx.fillStyle = '#64748b';
        ctx.font = '12px system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';

        for (let i = 0; i < 4; i++) {
            const y = padding.top + (height / 3) * i;
            ctx.beginPath();
            ctx.moveTo(padding.left, y);
            ctx.lineTo(padding.left + width, y);
            ctx.stroke();
            const labelValue = yMax - ((yMax - yMin) / 3) * i;
            ctx.fillText(labelValue.toFixed(metric === 'temperature' ? 1 : 0), 8, y + 4);
        }

        const points = rows.map((row, index) => {
            const value = row[metric];
            const x = padding.left + (rows.length === 1 ? width / 2 : (width / (rows.length - 1)) * index);
            const y = value === null ? null : padding.top + (yMax - value) / (yMax - yMin) * height;
            return { x, y, value, time: row.time };
        }).filter(point => point.y !== null);

        ctx.strokeStyle = metrics[metric].color;
        ctx.lineWidth = 3;
        ctx.beginPath();
        points.forEach((point, index) => {
            if (index === 0) ctx.moveTo(point.x, point.y);
            else ctx.lineTo(point.x, point.y);
        });
        ctx.stroke();

        points.forEach(point => {
            ctx.fillStyle = '#ffffff';
            ctx.strokeStyle = metrics[metric].color;
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.arc(point.x, point.y, 4, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
        });

        ctx.fillStyle = '#0f172a';
        ctx.font = '600 13px system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
        ctx.fillText(metrics[metric].label, padding.left, 16);

        ctx.fillStyle = '#64748b';
        ctx.font = '11px system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
        const first = rows[0]?.time || '';
        const last = rows[rows.length - 1]?.time || '';
        ctx.fillText(first, padding.left, 228);
        const lastWidth = ctx.measureText(last).width;
        ctx.fillText(last, padding.left + width - lastWidth, 228);
    }

    document.querySelectorAll('.vital-chart-tab').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.vital-chart-tab').forEach(item => item.classList.remove('is-active'));
            button.classList.add('is-active');
            draw(button.dataset.metric);
        });
    });

    draw('spo2');
    window.addEventListener('resize', () => {
        const active = document.querySelector('.vital-chart-tab.is-active')?.dataset.metric || 'spo2';
        draw(active);
    });
})();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
