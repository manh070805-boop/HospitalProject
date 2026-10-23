<?php

require_once __DIR__ . '/../../core/PortalAccess.php';
require_once __DIR__ . '/../../core/AuditLogger.php';
require_once __DIR__ . '/../../core/Internal/VitalRiskScorer.php';
require_once __DIR__ . '/../../models/Doctor/DoctorAdmissionModel.php';
require_once __DIR__ . '/../../models/Doctor/DoctorTreatmentPlanModel.php';

class DoctorTreatmentPlanController
{
    private $admissionModel;
    private $treatmentPlanModel;

    private const WRITABLE_ADMISSION_STATUSES = ['active', 'stable'];

    public function __construct()
    {
        PortalAccess::requireAuth(['doctor']);
        $this->admissionModel = new DoctorAdmissionModel();
        $this->treatmentPlanModel = new DoctorTreatmentPlanModel();
    }

    public function index()
    {
        $user = PortalAccess::user();
        $doctorId = (int) $user['user_id'];
        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $allowedStatuses = ['', 'active', 'missing'];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $plans = $this->treatmentPlanModel->getAllForDoctor($doctorId, $keyword, $status);

        require_once __DIR__ . '/../../views/Doctor/treatment-plans/index.php';
    }

    public function showCreateForm()
    {
        $admissionId = (int) ($_GET['admission_id'] ?? 0);
        $user = PortalAccess::user();
        $doctorId = (int) $user['user_id'];

        if (!$admissionId) {
            header("Location: index.php?url=doctor-portal/patients&error=" . urlencode("Thiếu ID ca nội trú"));
            exit;
        }

        $admission = $this->admissionModel->getAdmissionById($admissionId, $doctorId);
        if (!$admission || !in_array($admission['status'], self::WRITABLE_ADMISSION_STATUSES, true)) {
            header("Location: index.php?url=doctor-portal/patients&error=" . urlencode("Ca nội trú không hợp lệ hoặc bạn không có quyền tạo y lệnh"));
            exit;
        }

        $latestVital = $this->admissionModel->getLatestVital($admissionId);
        $latestVitalRisk = $latestVital ? VitalRiskScorer::assess($latestVital) : null;
        $currentTreatmentPlans = $this->treatmentPlanModel->getAllByAdmissionForDoctor($admissionId, $doctorId);
        $currentTreatmentPlan = $currentTreatmentPlans[0] ?? null;

        require_once __DIR__ . '/../../views/Doctor/treatment-plans/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=doctor-portal/patients");
            exit;
        }

        $user = PortalAccess::user();
        $doctorId = (int) $user['user_id'];
        $admissionId = (int) ($_POST['admission_id'] ?? 0);
        $errors = [];

        if (!$admissionId) {
            $errors[] = "Thiếu ID ca nội trú";
        }

        $admission = $admissionId ? $this->admissionModel->getAdmissionById($admissionId, $doctorId) : null;
        if (!$admission || !in_array($admission['status'], self::WRITABLE_ADMISSION_STATUSES, true)) {
            $errors[] = "Ca nội trú không hợp lệ hoặc bạn không có quyền tạo y lệnh";
        }

        $latestVital = $admission ? $this->admissionModel->getLatestVital($admissionId) : null;
        if (!$latestVital) {
            $errors[] = "Cần có dữ liệu sinh tồn trước khi lập kế hoạch điều trị";
        }

        $diagnosis = trim($_POST['diagnosis'] ?? '');
        $treatmentGoal = trim($_POST['treatment_goal'] ?? '');
        $medicalOrder = trim($_POST['medical_order'] ?? '');
        $treatmentNote = trim($_POST['treatment_note'] ?? '');

        if ($diagnosis === '') {
            $errors[] = "Chẩn đoán không được để trống";
        }
        if ($medicalOrder === '') {
            $errors[] = "Y lệnh không được để trống";
        }

        if (!empty($errors)) {
            $url = "index.php?url=doctor-portal/treatment-plans/create&admission_id={$admissionId}";
            header("Location: " . $this->appendFlash($url, 'error', implode(' | ', $errors)));
            exit;
        }

        $existingPlans = $this->treatmentPlanModel->getAllByAdmissionForDoctor($admissionId, $doctorId);
        $existingPlan = $existingPlans[0] ?? null;

        $planId = $this->treatmentPlanModel->createActivePlan([
            'admission_id' => $admissionId,
            'diagnosis' => $diagnosis,
            'treatment_goal' => $treatmentGoal,
            'medical_order' => $medicalOrder,
            'treatment_note' => $treatmentNote,
            'created_by' => $doctorId,
        ]);

        if (!$planId) {
            header("Location: index.php?url=doctor-portal/treatment-plans/create&admission_id={$admissionId}&error=" . urlencode("Không thể lưu kế hoạch điều trị"));
            exit;
        }

        if ($existingPlan) {
            AuditLogger::log($doctorId, 'UPDATE', 'TREATMENT_PLAN', "Cập nhật nội dung kế hoạch điều trị ID: {$planId} cho admission ID: {$admissionId}");
            $message = "Đã cập nhật kế hoạch điều trị";
        } else {
            AuditLogger::log($doctorId, 'CREATE', 'TREATMENT_PLAN', "Tạo kế hoạch điều trị ID: {$planId} cho admission ID: {$admissionId}");
            $message = "Đã tạo kế hoạch điều trị active";
        }

        header("Location: index.php?url=doctor-portal/admissions/show&id={$admissionId}&success=" . urlencode($message));
        exit;
    }

    public function show()
    {
        $planId = (int) ($_GET['id'] ?? 0);
        $user = PortalAccess::user();
        $doctorId = (int) $user['user_id'];

        if (!$planId) {
            header("Location: index.php?url=doctor-portal/treatment-plans&error=" . urlencode("Thiếu ID kế hoạch điều trị"));
            exit;
        }

        $plan = $this->treatmentPlanModel->getByIdForDoctor($planId, $doctorId);
        if (!$plan) {
            header("Location: index.php?url=doctor-portal/treatment-plans&error=" . urlencode("Không tìm thấy kế hoạch điều trị hoặc bạn không có quyền xem"));
            exit;
        }

        require_once __DIR__ . '/../../views/Doctor/treatment-plans/show.php';
    }

    public function history()
    {
        $admissionId = (int) ($_GET['admission_id'] ?? 0);
        $user = PortalAccess::user();
        $doctorId = (int) $user['user_id'];

        if (!$admissionId) {
            header("Location: index.php?url=doctor-portal/treatment-plans");
            exit;
        }

        $admission = $this->admissionModel->getAdmissionById($admissionId, $doctorId);
        if (!$admission) {
            header("Location: index.php?url=doctor-portal/treatment-plans&error=" . urlencode("Không có quyền xem lịch sử kế hoạch điều trị"));
            exit;
        }

        $plans = $this->treatmentPlanModel->getAllByAdmissionForDoctor($admissionId, $doctorId);
        $plan = $plans[0] ?? null;
        $changeLogs = $this->treatmentPlanModel->getChangeLogsByAdmissionForDoctor($admissionId, $doctorId);

        require_once __DIR__ . '/../../views/Doctor/treatment-plans/history.php';
    }

    public function stop()
    {
        $this->updateStatus('stopped');
    }

    public function complete()
    {
        $this->updateStatus('completed');
    }

    private function updateStatus(string $status)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=doctor-portal/treatment-plans");
            exit;
        }

        $user = PortalAccess::user();
        $doctorId = (int) $user['user_id'];
        $planId = (int) ($_POST['treatment_plan_id'] ?? 0);
        $returnUrl = $this->resolveDoctorReturnUrl($_POST['return_url'] ?? null, 'index.php?url=doctor-portal/treatment-plans');

        if (!$planId) {
            header("Location: " . $this->appendFlash($returnUrl, 'error', "Thiếu ID phác đồ"));
            exit;
        }

        $plan = $this->treatmentPlanModel->getByIdForDoctor($planId, $doctorId);
        if (!$plan) {
            header("Location: " . $this->appendFlash($returnUrl, 'error', "Không tìm thấy phác đồ hoặc bạn không có quyền cập nhật"));
            exit;
        }

        $updated = $this->treatmentPlanModel->updateStatusForDoctor($planId, $doctorId, $status);
        if ($updated) {
            AuditLogger::log($doctorId, 'UPDATE', 'TREATMENT_PLAN', "Cập nhật kế hoạch điều trị ID: {$planId} sang {$status}");
            $message = $status === 'completed'
                ? "Đã hoàn tất kế hoạch điều trị và lưu vào lịch sử thay đổi"
                : "Đã cập nhật trạng thái kế hoạch điều trị";
            header("Location: " . $this->appendFlash($returnUrl, 'success', $message));
            exit;
        }

        $errorMessage = $this->treatmentPlanModel->getLastError() ?: "Chỉ có thể cập nhật kế hoạch điều trị đang active";
        header("Location: " . $this->appendFlash($returnUrl, 'error', $errorMessage));
        exit;
    }

    private function resolveDoctorReturnUrl(?string $url, string $default): string
    {
        $url = trim((string) $url);
        if ($url !== '' && str_starts_with($url, 'index.php?url=doctor-portal')) {
            return $url;
        }

        return $default;
    }

    private function appendFlash(string $url, string $key, string $message): string
    {
        $sep = str_contains($url, '?') ? '&' : '?';
        return $url . $sep . $key . '=' . urlencode($message);
    }
}
