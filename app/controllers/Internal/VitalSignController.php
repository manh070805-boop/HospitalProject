<?php

require_once __DIR__ . '/../../../app/core/PortalAccess.php';
require_once __DIR__ . '/../../../app/core/AuditLogger.php';
require_once __DIR__ . '/../../../app/core/Internal/AlertRuleEngine.php';
require_once __DIR__ . '/../../../app/core/Internal/VitalRiskScorer.php';
require_once __DIR__ . '/../../models/Internal/VitalSignModel.php';
require_once __DIR__ . '/../../models/Internal/AlertModel.php';
require_once __DIR__ . '/../../models/Internal/InternalAdmissionModel.php';

class VitalSignController
{
    private $vitalModel;
    private $alertModel;
    private $admissionModel;
    private const DISCHARGED_STATUS = 'discharged';

    public function __construct()
    {
        PortalAccess::requireAuth(['nurse', 'head_nurse']);
        $this->vitalModel = new VitalSignModel();
        $this->alertModel = new AlertModel();
        $this->admissionModel = new InternalAdmissionModel();
    }

    public function index()
    {
        $admissionId = (int) ($_GET['admission_id'] ?? 0);
        if ($admissionId) {
            header("Location: index.php?url=internal/vitals/history&admission_id=" . $admissionId);
            exit;
        }

        $user = PortalAccess::user();
        $keyword = trim($_GET['keyword'] ?? '');
        $filter = trim($_GET['filter'] ?? 'due');
        $allowedFilters = ['due', 'risk', 'all'];
        if (!in_array($filter, $allowedFilters, true)) {
            $filter = 'due';
        }

        $admissions = $this->vitalModel->getActiveAdmissionsForVitals(
            (int) $user['user_id'],
            $user['role_key'],
            isset($user['department_id']) ? (int) $user['department_id'] : null,
            $keyword
        );

        $now = time();
        foreach ($admissions as &$item) {
            $latestRecord = !empty($item['latest_record_id']) ? $item : null;
            $risk = $latestRecord ? VitalRiskScorer::assess($latestRecord) : [
                'score' => 0,
                'level' => 'none',
                'label' => 'Chưa ghi',
                'class' => 'risk-watch',
                'signals' => [],
                'recommendation' => 'Cần ghi sinh tồn lần đầu cho ca nội trú này.',
                'next_interval_minutes' => 0,
                'next_label' => 'ngay',
            ];

            $nextDueAt = null;
            $isDue = true;
            if (!empty($item['recorded_at'])) {
                $recordedAt = strtotime($item['recorded_at']);
                $nextDueAt = $recordedAt ? $recordedAt + ((int) $risk['next_interval_minutes'] * 60) : null;
                $isDue = $nextDueAt ? $nextDueAt <= $now : true;
            }

            $item['risk'] = $risk;
            $item['is_due'] = $isDue;
            $item['next_due_at'] = $nextDueAt;
        }
        unset($item);

        $totalAdmissions = count($admissions);
        $dueCount = count(array_filter($admissions, static fn($item) => !empty($item['is_due'])));
        $riskCount = count(array_filter($admissions, static fn($item) => in_array($item['risk']['level'] ?? '', ['critical', 'high'], true)));

        if ($filter === 'due') {
            $admissions = array_values(array_filter($admissions, static fn($item) => !empty($item['is_due'])));
        } elseif ($filter === 'risk') {
            $admissions = array_values(array_filter($admissions, static fn($item) => in_array($item['risk']['level'] ?? '', ['critical', 'high'], true)));
        }

        require_once __DIR__ . '/../../views/Internal/vitals/index.php';
    }

    public function showCreateForm()
    {
        $admissionId = (int) ($_GET['admission_id'] ?? 0);
        if (!$admissionId) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Thiếu ID ca nội trú"));
            exit;
        }
        $user = PortalAccess::user();
        $admission = $this->admissionModel->getAdmissionById($admissionId, $user['user_id'], $user['role_key']);

        if (!$admission || ($admission['status'] ?? '') === self::DISCHARGED_STATUS) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Ca nội trú không hợp lệ hoặc đã kết thúc"));
            exit;
        }

        require_once __DIR__ . '/../../views/Internal/vitals/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=internal/monitoring");
            exit;
        }

        $admissionId = (int) ($_POST['admission_id'] ?? 0);
        $user = PortalAccess::user();
        $errors = [];

        if (!$admissionId) {
            $errors[] = "Thiếu thông tin ca nội trú";
        }
        if ($admissionId) {
            $admission = $this->admissionModel->getAdmissionById($admissionId, $user['user_id'], $user['role_key']);
            if (!$admission || ($admission['status'] ?? '') === self::DISCHARGED_STATUS) {
                header("Location: index.php?url=internal/monitoring&error=" . urlencode("Ca nội trú không hợp lệ hoặc bạn không có quyền ghi sinh tồn"));
                exit;
            }
        }

        $temperature = $_POST['temperature'] ?? '';
        $pulse = $_POST['pulse'] ?? '';
        $bpSys = $_POST['blood_pressure_systolic'] ?? '';
        $bpDia = $_POST['blood_pressure_diastolic'] ?? '';
        $rr = $_POST['respiratory_rate'] ?? '';
        $spo2 = $_POST['spo2'] ?? '';
        $symptoms = trim($_POST['symptoms_note'] ?? '');

        if ($temperature === '' || !is_numeric($temperature))
            $errors[] = "Nhiệt độ không hợp lệ";
        if ($pulse === '' || !is_numeric($pulse))
            $errors[] = "Mạch không hợp lệ";
        if ($bpSys === '' || !is_numeric($bpSys))
            $errors[] = "Huyết áp tâm thu không hợp lệ";
        if ($bpDia === '' || !is_numeric($bpDia))
            $errors[] = "Huyết áp tâm trương không hợp lệ";
        if ($rr === '' || !is_numeric($rr))
            $errors[] = "Nhịp thở không hợp lệ";
        if ($spo2 === '' || !is_numeric($spo2))
            $errors[] = "SpO2 không hợp lệ";

        if (!empty($errors)) {
            header("Location: index.php?url=internal/vitals/create&admission_id={$admissionId}&error=" . urlencode(implode(' | ', $errors)));
            exit;
        }

        $record = [
            'admission_id' => $admissionId,
            'temperature' => (float) $temperature,
            'pulse' => (int) $pulse,
            'blood_pressure_systolic' => (int) $bpSys,
            'blood_pressure_diastolic' => (int) $bpDia,
            'respiratory_rate' => (int) $rr,
            'spo2' => (int) $spo2,
            'symptoms_note' => $symptoms,
            'recorded_by' => $user['user_id'],
        ];

        $recordId = $this->vitalModel->createRecord($record);

        $record['record_id'] = $recordId;
        $triggeredRules = AlertRuleEngine::evaluateRecord($record);
        AlertRuleEngine::createAlertsForRecord($admissionId, $recordId, $triggeredRules, $this->alertModel);
        $risk = VitalRiskScorer::assess($record);

        AuditLogger::log($user['user_id'], 'CREATE', 'VITAL_SIGN', "Ghi sinh tồn (ID: {$recordId}) cho admission ID: {$admissionId}");

        $successMessage = "Đã ghi sinh tồn thành công! Risk Score: {$risk['score']} - {$risk['label']}. {$risk['recommendation']}";
        header("Location: index.php?url=internal/admissions/show&id={$admissionId}&success=" . urlencode($successMessage));
        exit;
    }

    public function history()
    {
        $admissionId = (int) ($_GET['admission_id'] ?? 0);
        if (!$admissionId) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Vui lòng chọn một ca nội trú từ danh sách để xem Lịch sử sinh tồn."));
            exit;
        }

        $user = PortalAccess::user();
        $admission = $this->admissionModel->getAdmissionById($admissionId, $user['user_id'], $user['role_key']);

        if (!$admission) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Không có quyền xem"));
            exit;
        }

        $vitals = $this->vitalModel->getByAdmission($admissionId);
        $latestRisk = !empty($vitals) ? VitalRiskScorer::assess($vitals[0]) : null;
        $trend = !empty($vitals) ? VitalRiskScorer::trend($vitals[0], $vitals[1] ?? null) : null;

        foreach ($vitals as &$vital) {
            $vital['risk'] = VitalRiskScorer::assess($vital);
        }
        unset($vital);

        require_once __DIR__ . '/../../views/Internal/vitals/history.php';
    }
}
