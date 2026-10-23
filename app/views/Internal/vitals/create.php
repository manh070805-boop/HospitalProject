<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content">
    <div class="page-header reveal-item">
        <div class="page-header-info">
            <h2>Ghi sinh tồn</h2>
            <p><?= htmlspecialchars($admission['patient_name']) ?> &mdash; Phòng
                <?= htmlspecialchars($admission['room_name'] ?? '---') ?>, Giường
                <?= htmlspecialchars($admission['bed_code'] ?? '---') ?></p>
        </div>
        <a href="index.php?url=internal/admissions/show&id=<?= (int) $admission['admission_id'] ?>"
            class="btn btn-secondary">
            <i class="iconsax" icon-name="arrow-left"></i> Trở về
        </a>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error reveal-item">
            <i class="iconsax" icon-name="info-circle"></i>
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="vital-live-panel risk-stable reveal-item">
        <div>
            <span class="vital-live-label">Đánh giá tức thì</span>
            <strong id="vitalLiveTitle">Risk Score 0 · Ổn định</strong>
            <p id="vitalLiveRecommendation">Nhập đủ thông số để hệ thống đánh giá nguy cơ sinh tồn.</p>
        </div>
        <div class="vital-live-next">
            <span>Đo lại</span>
            <strong id="vitalLiveNext">4 giờ</strong>
        </div>
    </div>

    <div class="card reveal-item" style="max-width: 720px;">
        <div class="card-header">
            <h4>Thông số sinh tồn</h4>
        </div>
        <div class="card-body">
            <form action="index.php?url=internal/vitals/store" method="POST" class="premium-form">
                <input type="hidden" name="admission_id" value="<?= (int) $admission['admission_id'] ?>">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label for="temperature">Nhiệt độ (°C) <span class="required-star">*</span></label>
                        <input type="number" id="temperature" name="temperature" step="0.1" min="33" max="42"
                            placeholder="VD: 37.2" class="form-control" data-vital-field="temperature" required>
                        <small class="vital-field-hint" data-hint-for="temperature"></small>
                    </div>

                    <div class="form-group">
                        <label for="pulse">Mạch (lần/phút) <span class="required-star">*</span></label>
                        <input type="number" id="pulse" name="pulse" min="0" max="250" placeholder="VD: 80"
                            class="form-control" data-vital-field="pulse" required>
                        <small class="vital-field-hint" data-hint-for="pulse"></small>
                    </div>

                    <div class="form-group">
                        <label for="blood_pressure_systolic">Huyết áp tâm thu (mmHg) <span
                                class="required-star">*</span></label>
                        <input type="number" id="blood_pressure_systolic" name="blood_pressure_systolic" min="0"
                            max="300" placeholder="VD: 120" class="form-control"
                            data-vital-field="blood_pressure_systolic" required>
                        <small class="vital-field-hint" data-hint-for="blood_pressure_systolic"></small>
                    </div>

                    <div class="form-group">
                        <label for="blood_pressure_diastolic">Huyết áp tâm trương (mmHg) <span
                                class="required-star">*</span></label>
                        <input type="number" id="blood_pressure_diastolic" name="blood_pressure_diastolic" min="0"
                            max="200" placeholder="VD: 80" class="form-control"
                            data-vital-field="blood_pressure_diastolic" required>
                        <small class="vital-field-hint" data-hint-for="blood_pressure_diastolic"></small>
                    </div>

                    <div class="form-group">
                        <label for="respiratory_rate">Nhịp thở (lần/phút) <span class="required-star">*</span></label>
                        <input type="number" id="respiratory_rate" name="respiratory_rate" min="0" max="60"
                            placeholder="VD: 18" class="form-control" data-vital-field="respiratory_rate" required>
                        <small class="vital-field-hint" data-hint-for="respiratory_rate"></small>
                    </div>

                    <div class="form-group">
                        <label for="spo2">SpO2 (%) <span class="required-star">*</span></label>
                        <input type="number" id="spo2" name="spo2" min="0" max="100" placeholder="VD: 98"
                            class="form-control" data-vital-field="spo2" required>
                        <small class="vital-field-hint" data-hint-for="spo2"></small>
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="symptoms_note">Ghi chú triệu chứng</label>
                        <textarea id="symptoms_note" name="symptoms_note" class="form-control" rows="3"
                            placeholder="Bệnh nhân than đau đầu, buồn nôn..."></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="iconsax" icon-name="tick-circle"></i> Lưu sinh tồn
                    </button>
                    <a href="index.php?url=internal/admissions/show&id=<?= (int) $admission['admission_id'] ?>"
                        class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card dashboard-table-card reveal-item delay-1" style="max-width: 720px; margin-top: 20px;">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 15px 20px;">
            <h4><i class="iconsax" icon-name="info-circle" style="font-size:14px;"></i> Ngưỡng cảnh báo</h4>
        </div>
        <table class="table premium-table">
            <thead>
                <tr>
                    <th>Chỉ số</th>
                    <th>Cảnh báo cao</th>
                    <th>Nguy hiểm</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>SpO2</td>
                    <td>
                        < 92%</td>
                    <td>
                        < 90%</td>
                </tr>
                <tr>
                    <td>HA tâm thu</td>
                    <td>≥ 160 mmHg</td>
                    <td>≥ 180 mmHg</td>
                </tr>
                <tr>
                    <td>HA tâm trương</td>
                    <td>≥ 100 mmHg</td>
                    <td>≥ 110 mmHg</td>
                </tr>
                <tr>
                    <td>Mạch nhanh</td>
                    <td>≥ 120 lần/phút</td>
                    <td>≥ 130 lần/phút</td>
                </tr>
                <tr>
                    <td>Mạch chậm</td>
                    <td>≤ 50 lần/phút</td>
                    <td>≤ 40 lần/phút</td>
                </tr>
                <tr>
                    <td>Nhịp thở nhanh</td>
                    <td>≥ 24 lần/phút</td>
                    <td>≥ 30 lần/phút</td>
                </tr>
                <tr>
                    <td>Nhịp thở chậm</td>
                    <td>≤ 10 lần/phút</td>
                    <td>≤ 8 lần/phút</td>
                </tr>
                <tr>
                    <td>Sốt</td>
                    <td>≥ 38.5°C</td>
                    <td>≥ 39.5°C</td>
                </tr>
                <tr>
                    <td>Hạ thân nhiệt</td>
                    <td>≤ 35.5°C</td>
                    <td>≤ 35.0°C</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    (() => {
        const inputs = Array.from(document.querySelectorAll('[data-vital-field]'));
        const panel = document.querySelector('.vital-live-panel');
        const title = document.getElementById('vitalLiveTitle');
        const recommendation = document.getElementById('vitalLiveRecommendation');
        const next = document.getElementById('vitalLiveNext');

        function valueOf(field) {
            const input = document.querySelector(`[data-vital-field="${field}"]`);
            if (!input || input.value === '') return null;
            const value = Number(input.value);
            return Number.isFinite(value) ? value : null;
        }

        function setHint(field, state, text) {
            const hint = document.querySelector(`[data-hint-for="${field}"]`);
            const input = document.querySelector(`[data-vital-field="${field}"]`);
            if (!hint || !input) return;
            hint.textContent = text || '';
            hint.className = `vital-field-hint ${state ? `is-${state}` : ''}`;
            input.classList.remove('is-watch', 'is-high', 'is-critical');
            if (state) input.classList.add(`is-${state}`);
        }

        function assess() {
            let score = 0;
            const signals = [];
            const fields = ['temperature', 'pulse', 'blood_pressure_systolic', 'blood_pressure_diastolic', 'respiratory_rate', 'spo2'];
            fields.forEach(field => setHint(field, '', ''));

            const spo2 = valueOf('spo2');
            if (spo2 !== null) {
                if (spo2 < 90) { score += 3; signals.push('SpO2 nguy hiểm'); setHint('spo2', 'critical', 'Nguy hiểm'); }
                else if (spo2 < 92) { score += 2; signals.push('SpO2 thấp'); setHint('spo2', 'high', 'Cần theo dõi sát'); }
                else if (spo2 < 95) { score += 1; signals.push('SpO2 cần theo dõi'); setHint('spo2', 'watch', 'Cần chú ý'); }
            }

            const sbp = valueOf('blood_pressure_systolic');
            const dbp = valueOf('blood_pressure_diastolic');
            if (sbp !== null || dbp !== null) {
                if ((sbp !== null && sbp >= 180) || (dbp !== null && dbp >= 110)) {
                    score += 3; signals.push('Huyết áp nguy hiểm');
                    if (sbp !== null && sbp >= 180) setHint('blood_pressure_systolic', 'critical', 'Nguy hiểm');
                    if (dbp !== null && dbp >= 110) setHint('blood_pressure_diastolic', 'critical', 'Nguy hiểm');
                } else if ((sbp !== null && sbp >= 160) || (dbp !== null && dbp >= 100)) {
                    score += 2; signals.push('Huyết áp cao');
                    if (sbp !== null && sbp >= 160) setHint('blood_pressure_systolic', 'high', 'Cao');
                    if (dbp !== null && dbp >= 100) setHint('blood_pressure_diastolic', 'high', 'Cao');
                } else if ((sbp !== null && sbp < 90) || (dbp !== null && dbp < 60)) {
                    score += 2; signals.push('Huyết áp thấp');
                    if (sbp !== null && sbp < 90) setHint('blood_pressure_systolic', 'high', 'Thấp');
                    if (dbp !== null && dbp < 60) setHint('blood_pressure_diastolic', 'high', 'Thấp');
                }
            }

            const pulse = valueOf('pulse');
            if (pulse !== null) {
                if (pulse >= 130 || pulse <= 40) { score += 3; signals.push('Mạch nguy hiểm'); setHint('pulse', 'critical', 'Nguy hiểm'); }
                else if (pulse >= 120 || pulse <= 50) { score += 2; signals.push('Mạch bất thường'); setHint('pulse', 'high', 'Bất thường'); }
                else if (pulse >= 100 || pulse <= 60) { score += 1; signals.push('Mạch cần theo dõi'); setHint('pulse', 'watch', 'Cần chú ý'); }
            }

            const rr = valueOf('respiratory_rate');
            if (rr !== null) {
                if (rr >= 30 || rr <= 8) { score += 3; signals.push('Nhịp thở nguy hiểm'); setHint('respiratory_rate', 'critical', 'Nguy hiểm'); }
                else if (rr >= 24 || rr <= 10) { score += 2; signals.push('Nhịp thở bất thường'); setHint('respiratory_rate', 'high', 'Bất thường'); }
            }

            const temperature = valueOf('temperature');
            if (temperature !== null) {
                if (temperature >= 39.5 || temperature <= 35) { score += 3; signals.push('Thân nhiệt nguy hiểm'); setHint('temperature', 'critical', 'Nguy hiểm'); }
                else if (temperature >= 38.5 || temperature <= 35.5) { score += 2; signals.push('Thân nhiệt bất thường'); setHint('temperature', 'high', 'Bất thường'); }
                else if (temperature >= 37.8) { score += 1; signals.push('Sốt nhẹ'); setHint('temperature', 'watch', 'Cần chú ý'); }
            }

            let level = 'stable';
            if (score >= 7) level = 'critical';
            else if (score >= 4) level = 'high';
            else if (score >= 1) level = 'watch';

            const labels = { critical: 'Nguy cơ cao', high: 'Cần theo dõi sát', watch: 'Cần chú ý', stable: 'Ổn định' };
            const nextLabels = { critical: '15 phút', high: '30 phút', watch: '1 giờ', stable: '4 giờ' };
            const recommendations = {
                critical: 'Báo bác sĩ phụ trách và đo lại sinh tồn sau 15 phút.',
                high: 'Theo dõi sát, xử trí theo y lệnh và đo lại sau 30 phút.',
                watch: 'Theo dõi thêm triệu chứng và đo lại sau 1 giờ.',
                stable: 'Tiếp tục theo dõi định kỳ theo ca.'
            };

            panel.className = `vital-live-panel risk-${level} reveal-item`;
            title.textContent = `Risk Score ${score} · ${labels[level]}`;
            recommendation.textContent = signals.length ? `${recommendations[level]} Bất thường: ${signals.join(', ')}.` : recommendations[level];
            next.textContent = nextLabels[level];
        }

        inputs.forEach(input => input.addEventListener('input', assess));
        assess();
    })();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>