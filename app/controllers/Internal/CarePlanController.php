<?php

require_once __DIR__ . '/../../../app/core/PortalAccess.php';
require_once __DIR__ . '/../../../app/core/AuditLogger.php';
require_once __DIR__ . '/../../../app/core/Internal/VitalRiskScorer.php';
require_once __DIR__ . '/../../../app/core/Internal/CarePlanWorkflowService.php';
require_once __DIR__ . '/../../models/Internal/CarePlanModel.php';
require_once __DIR__ . '/../../models/Internal/TreatmentPlanModel.php';
require_once __DIR__ . '/../../models/Internal/InternalAdmissionModel.php';
require_once __DIR__ . '/../../models/Internal/VitalSignModel.php';
require_once __DIR__ . '/../../models/Internal/CarePlanApprovalModel.php';

class CarePlanController
{
    private $carePlanModel;
    private $treatmentPlanModel;
    private $admissionModel;
    private $vitalModel;

    private const WRITABLE_STATUSES = ['active', 'stable', 'critical'];

    public function __construct()
    {
        PortalAccess::requireAuth(['nurse', 'head_nurse']);
        $this->carePlanModel = new CarePlanModel();
        $this->treatmentPlanModel = new TreatmentPlanModel();
        $this->admissionModel = new InternalAdmissionModel();
        $this->vitalModel = new VitalSignModel();
    }

    public function index()
    {
        $user = PortalAccess::user();
        $keyword = trim($_GET['keyword'] ?? '');
        $filter = trim($_GET['filter'] ?? 'attention');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $allowedFilters = ['attention', 'pending', 'approved', 'all'];
        if (!in_array($filter, $allowedFilters, true)) {
            $filter = 'attention';
        }

        $worklist = $this->carePlanModel->getCarePlanWorklist(
            (int) $user['user_id'],
            $user['role_key'],
            isset($user['department_id']) ? (int) $user['department_id'] : null,
            $keyword
        );

        $processed = CarePlanWorkflowService::processWorklist($worklist, $filter);
        $worklist = $processed['worklist'];
        extract($processed['stats']);

        $totalRows = count($worklist);
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;
        $worklist = array_slice($worklist, $offset, $perPage);
        $displayStart = $totalRows > 0 ? $offset + 1 : 0;
        $displayEnd = min($offset + $perPage, $totalRows);

        require_once __DIR__ . '/../../views/Internal/care-plans/worklist.php';
    }

    public function history()
    {
        $admissionId = (int) ($_GET['admission_id'] ?? 0);
        if (!$admissionId) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Thiếu ID ca nhập viện"));
            exit;
        }

        $user = PortalAccess::user();
        $admission = $this->admissionModel->getAdmissionById($admissionId, $user['user_id'], $user['role_key']);

        if (!$admission) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Không có quyền xem lịch sử này"));
            exit;
        }

        $carePlans = $this->carePlanModel->getByAdmission($admissionId);

        require_once __DIR__ . '/../../views/Internal/care-plans/history.php';
    }



    public function showCreateForm()
    {
        $admissionId = (int) ($_GET['admission_id'] ?? 0);
        if (!$admissionId) {
            header("Location: index.php?url=internal/monitoring");
            exit;
        }

        $user = PortalAccess::user();
        $admission = $this->admissionModel->getAdmissionById($admissionId, $user['user_id'], $user['role_key']);

        if (!$admission || !in_array($admission['status'], self::WRITABLE_STATUSES, true)) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Ca nội trú không hợp lệ hoặc đã xuất viện"));
            exit;
        }

        $treatmentPlan = $this->treatmentPlanModel->getCurrentByAdmission($admissionId);
        $latestVital = $this->vitalModel->getLatestByAdmission($admissionId);
        $latestVitalRisk = $latestVital ? VitalRiskScorer::assess($latestVital) : null;

        if (!$latestVital) {
            header("Location: index.php?url=internal/vitals/create&admission_id={$admissionId}&error=" . urlencode("Cần ghi sinh tồn ban đầu trước khi lập kế hoạch chăm sóc"));
            exit;
        }

        if (!$treatmentPlan) {
            header("Location: index.php?url=internal/admissions/show&id={$admissionId}&error=" . urlencode("Cần bác sĩ lập kế hoạch điều trị active sau khi xem sinh tồn trước khi điều dưỡng lập kế hoạch chăm sóc"));
            exit;
        }

        require_once __DIR__ . '/../../views/Internal/care-plans/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=internal/monitoring");
            exit;
        }

        $admissionId = (int) ($_POST['admission_id'] ?? 0);
        $treatmentPlanId = ($_POST['treatment_plan_id'] ?? '') !== '' ? (int) $_POST['treatment_plan_id'] : null;
        $user = PortalAccess::user();
        $errors = [];

        if (!$admissionId)
            $errors[] = "Thiếu thông tin ca nội trú";
        if (empty(trim($_POST['plan_title'] ?? '')))
            $errors[] = "Tiêu đề kế hoạch không được để trống";
        if (empty(trim($_POST['assessment_summary'] ?? '')))
            $errors[] = "Nhận định bệnh nhân không được để trống";
        if (empty(trim($_POST['nursing_diagnosis'] ?? '')))
            $errors[] = "Chẩn đoán điều dưỡng không được để trống";
        if (empty(trim($_POST['care_goal'] ?? '')))
            $errors[] = "Mục tiêu chăm sóc không được để trống";
        if (empty(trim($_POST['intervention_plan'] ?? '')))
            $errors[] = "Kế hoạch can thiệp không được để trống";
        if (empty(trim($_POST['monitoring_frequency'] ?? '')))
            $errors[] = "Tần suất theo dõi không được để trống";

        $admission = null;
        $latestVital = null;
        $currentTreatmentPlan = null;
        if ($admissionId) {
            $admission = $this->admissionModel->getAdmissionById($admissionId, (int) $user['user_id'], $user['role_key']);
            if (!$admission || !in_array($admission['status'], self::WRITABLE_STATUSES, true)) {
                $errors[] = "Ca nội trú không hợp lệ hoặc bạn không có quyền lập kế hoạch";
            } else {
                $latestVital = $this->vitalModel->getLatestByAdmission($admissionId);
                $currentTreatmentPlan = $this->treatmentPlanModel->getCurrentByAdmission($admissionId);
            }
        }

        if ($admissionId && !$latestVital) {
            $errors[] = "Cần ghi sinh tồn ban đầu trước khi lập kế hoạch chăm sóc";
        }

        if ($admissionId && !$currentTreatmentPlan) {
            $errors[] = "Cần bác sĩ lập kế hoạch điều trị active trước khi điều dưỡng lập kế hoạch chăm sóc";
        }

        if ($currentTreatmentPlan && $treatmentPlanId !== (int) $currentTreatmentPlan['treatment_plan_id']) {
            $errors[] = "Kế hoạch điều trị đã thay đổi, vui lòng mở lại form lập kế hoạch";
        }

        if (!empty($errors)) {
            header("Location: index.php?url=internal/care-plans/create&admission_id={$admissionId}&error=" . urlencode(implode(' | ', $errors)));
            exit;
        }

        $data = [
            'admission_id' => $admissionId,
            'treatment_plan_id' => $treatmentPlanId,
            'created_by' => $user['user_id'],
            'plan_title' => trim($_POST['plan_title'] ?? ''),
            'assessment_summary' => trim($_POST['assessment_summary'] ?? ''),
            'nursing_diagnosis' => trim($_POST['nursing_diagnosis'] ?? ''),
            'care_goal' => trim($_POST['care_goal'] ?? ''),
            'intervention_plan' => trim($_POST['intervention_plan'] ?? ''),
            'monitoring_frequency' => trim($_POST['monitoring_frequency'] ?? ''),
            'planned_tasks' => $this->plannedTasksJsonFromPost(),
        ];

        $carePlanId = $this->carePlanModel->create($data);

        if (!$carePlanId) {
            header("Location: index.php?url=internal/care-plans/create&admission_id={$admissionId}&error=" . urlencode("Lưu thất bại, vui lòng thử lại"));
            exit;
        }

        AuditLogger::log($user['user_id'], 'CREATE', 'CARE_PLAN', "Tạo kế hoạch chăm sóc ID: {$carePlanId} cho admission ID: {$admissionId}");

        header("Location: index.php?url=internal/care-plans/show&id={$carePlanId}&success=" . urlencode("Đã lưu kế hoạch chăm sóc dạng nháp!"));
        exit;
    }

    public function show()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $user = PortalAccess::user();

        $carePlan = $this->carePlanModel->getById($id);
        if (!$carePlan) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Không tìm thấy kế hoạch chăm sóc"));
            exit;
        }

        $admission = $this->admissionModel->getAdmissionById((int) $carePlan['admission_id'], $user['user_id'], $user['role_key']);
        if (!$admission) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Không có quyền xem"));
            exit;
        }

        $latestApproval = $this->carePlanModel->getLatestApproval($id);

        $approvalModel = new CarePlanApprovalModel();
        $approvalHistory = $approvalModel->getHistoryByCarePlan($id);

        require_once __DIR__ . '/../../views/Internal/care-plans/show.php';
    }

    public function showEditForm()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $user = PortalAccess::user();

        $carePlan = $this->carePlanModel->getById($id);
        if (!$carePlan || $carePlan['status'] !== 'draft') {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Chỉ có thể sửa kế hoạch đang ở trạng thái nháp"));
            exit;
        }

        $admission = $this->admissionModel->getAdmissionById((int) $carePlan['admission_id'], $user['user_id'], $user['role_key']);
        if (!$admission) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Không có quyền sửa"));
            exit;
        }

        $treatmentPlan = $this->treatmentPlanModel->getCurrentByAdmission((int) $carePlan['admission_id']);
        $latestVital = $this->vitalModel->getLatestByAdmission((int) $carePlan['admission_id']);
        $latestVitalRisk = $latestVital ? VitalRiskScorer::assess($latestVital) : null;

        if (!$latestVital) {
            header("Location: index.php?url=internal/vitals/create&admission_id=" . (int) $carePlan['admission_id'] . "&error=" . urlencode("Cần ghi sinh tồn trước khi chỉnh kế hoạch chăm sóc"));
            exit;
        }

        if (!$treatmentPlan) {
            header("Location: index.php?url=internal/care-plans/show&id={$id}&error=" . urlencode("Cần bác sĩ có kế hoạch điều trị active trước khi chỉnh kế hoạch chăm sóc"));
            exit;
        }

        require_once __DIR__ . '/../../views/Internal/care-plans/edit.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=internal/monitoring");
            exit;
        }

        $id = (int) ($_POST['care_plan_id'] ?? 0);
        $user = PortalAccess::user();

        $carePlan = $this->carePlanModel->getById($id);
        if (!$carePlan || $carePlan['status'] !== 'draft') {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Không thể sửa kế hoạch này"));
            exit;
        }

        $admissionId = (int) $carePlan['admission_id'];
        $admission = $this->admissionModel->getAdmissionById($admissionId, (int) $user['user_id'], $user['role_key']);
        $latestVital = $admission ? $this->vitalModel->getLatestByAdmission($admissionId) : null;
        $currentTreatmentPlan = $admission ? $this->treatmentPlanModel->getCurrentByAdmission($admissionId) : null;
        $errors = [];

        if (!$admission || !in_array($admission['status'], self::WRITABLE_STATUSES, true)) {
            $errors[] = "Ca nội trú không hợp lệ hoặc bạn không có quyền sửa kế hoạch";
        }
        if (!$latestVital) {
            $errors[] = "Cần ghi sinh tồn trước khi cập nhật kế hoạch chăm sóc";
        }
        if (!$currentTreatmentPlan) {
            $errors[] = "Cần bác sĩ có kế hoạch điều trị active trước khi cập nhật kế hoạch chăm sóc";
        }
        if (empty(trim($_POST['plan_title'] ?? '')))
            $errors[] = "Tiêu đề kế hoạch không được để trống";
        if (empty(trim($_POST['assessment_summary'] ?? '')))
            $errors[] = "Nhận định bệnh nhân không được để trống";
        if (empty(trim($_POST['nursing_diagnosis'] ?? '')))
            $errors[] = "Chẩn đoán điều dưỡng không được để trống";
        if (empty(trim($_POST['care_goal'] ?? '')))
            $errors[] = "Mục tiêu chăm sóc không được để trống";
        if (empty(trim($_POST['intervention_plan'] ?? '')))
            $errors[] = "Kế hoạch can thiệp không được để trống";
        if (empty(trim($_POST['monitoring_frequency'] ?? '')))
            $errors[] = "Tần suất theo dõi không được để trống";

        if (!empty($errors)) {
            header("Location: index.php?url=internal/care-plans/edit&id={$id}&error=" . urlencode(implode(' | ', $errors)));
            exit;
        }

        $data = [
            'treatment_plan_id' => (int) $currentTreatmentPlan['treatment_plan_id'],
            'plan_title' => trim($_POST['plan_title'] ?? ''),
            'assessment_summary' => trim($_POST['assessment_summary'] ?? ''),
            'nursing_diagnosis' => trim($_POST['nursing_diagnosis'] ?? ''),
            'care_goal' => trim($_POST['care_goal'] ?? ''),
            'intervention_plan' => trim($_POST['intervention_plan'] ?? ''),
            'monitoring_frequency' => trim($_POST['monitoring_frequency'] ?? ''),
            'planned_tasks' => $this->plannedTasksJsonFromPost(),
        ];

        $this->carePlanModel->update($id, $data);
        AuditLogger::log($user['user_id'], 'UPDATE', 'CARE_PLAN', "Cập nhật kế hoạch chăm sóc ID: {$id}");

        header("Location: index.php?url=internal/care-plans/show&id={$id}&success=" . urlencode("Đã cập nhật kế hoạch!"));
        exit;
    }

    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=internal/monitoring");
            exit;
        }

        $id = (int) ($_POST['care_plan_id'] ?? 0);
        $user = PortalAccess::user();

        $carePlan = $this->carePlanModel->getById($id);
        if (!$carePlan || $carePlan['status'] !== 'draft') {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Chỉ gửi duyệt được kế hoạch đang là nháp"));
            exit;
        }

        if (empty($carePlan['treatment_plan_id'])) {
            header("Location: index.php?url=internal/care-plans/show&id={$id}&error=" . urlencode("Bắt buộc phải gắn kế hoạch điều trị của bác sĩ trước khi gửi duyệt"));
            exit;
        }

        if (!$this->treatmentPlanModel->validatePlanForAdmission((int) $carePlan['treatment_plan_id'], (int) $carePlan['admission_id'])) {
            header("Location: index.php?url=internal/care-plans/show&id={$id}&error=" . urlencode("Kế hoạch điều trị đã được cập nhật, vui lòng chọn lại bản hiện hành"));
            exit;
        }

        $latestVital = $this->vitalModel->getLatestByAdmission((int) $carePlan['admission_id']);
        if (!$latestVital) {
            header("Location: index.php?url=internal/vitals/create&admission_id=" . (int) $carePlan['admission_id'] . "&error=" . urlencode("Cần ghi sinh tồn trước khi gửi duyệt kế hoạch chăm sóc"));
            exit;
        }

        if (
            !empty($carePlan['created_at'])
            && !empty($latestVital['recorded_at'])
            && strtotime($latestVital['recorded_at']) > strtotime($carePlan['created_at'])
            && in_array(VitalRiskScorer::assess($latestVital)['level'] ?? '', ['critical', 'high'], true)
        ) {
            header("Location: index.php?url=internal/care-plans/edit&id={$id}&error=" . urlencode("Có sinh tồn nguy cơ cao mới hơn bản nháp, vui lòng cập nhật kế hoạch trước khi gửi duyệt"));
            exit;
        }

        if ($this->carePlanModel->hasPending((int) $carePlan['admission_id'])) {
            header("Location: index.php?url=internal/care-plans/show&id={$id}&error=" . urlencode("Bệnh nhân này đã có kế hoạch chăm sóc đang chờ duyệt. Vui lòng đợi điều dưỡng trưởng xử lý bản đó trước."));
            exit;
        }

        $this->carePlanModel->updateStatus($id, 'pending');
        AuditLogger::log($user['user_id'], 'UPDATE', 'CARE_PLAN', "Gửi duyệt kế hoạch chăm sóc ID: {$id}");

        header("Location: index.php?url=internal/care-plans/show&id={$id}&success=" . urlencode("Đã gửi kế hoạch chờ điều dưỡng trưởng duyệt!"));
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=internal/care-plans");
            exit;
        }

        $id = (int) ($_POST['care_plan_id'] ?? 0);
        $user = PortalAccess::user();
        $carePlan = $this->carePlanModel->getById($id);
        if (!$carePlan) {
            header("Location: index.php?url=internal/care-plans&error=" . urlencode("Không tìm thấy kế hoạch chăm sóc"));
            exit;
        }
        $admissionId = (int) $carePlan['admission_id'];
        $returnUrl = $this->resolveReturnUrl($_POST['return_url'] ?? null, "index.php?url=internal/care-plans&admission_id={$admissionId}");

        $admission = $this->admissionModel->getAdmissionById($admissionId, (int) $user['user_id'], $user['role_key']);
        if (!$admission) {
            header("Location: index.php?url=internal/care-plans&error=" . urlencode("Không có quyền xóa kế hoạch này"));
            exit;
        }

        if (($carePlan['status'] ?? '') !== 'draft') {
            header("Location: index.php?url=internal/care-plans/show&id={$id}&error=" . urlencode("Chỉ có thể xóa kế hoạch đang ở trạng thái nháp"));
            exit;
        }

        if (!$this->carePlanModel->deleteDraft($id)) {
            header("Location: index.php?url=internal/care-plans/show&id={$id}&error=" . urlencode("Không thể xóa nháp, vui lòng thử lại"));
            exit;
        }

        AuditLogger::log($user['user_id'], 'DELETE', 'CARE_PLAN', "Xóa nháp kế hoạch chăm sóc ID: {$id} cho admission ID: {$admissionId}");

        header("Location: " . $this->appendFlash($returnUrl, 'success', "Đã xóa nháp kế hoạch chăm sóc"));
        exit;
    }

    public function approvalHistory()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $user = PortalAccess::user();

        $carePlan = $this->carePlanModel->getById($id);
        if (!$carePlan) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy kế hoạch chăm sóc']);
            exit;
        }

        $admission = $this->admissionModel->getAdmissionById((int) $carePlan['admission_id'], $user['user_id'], $user['role_key']);
        if (!$admission) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không có quyền xem']);
            exit;
        }

        $approvalModel = new CarePlanApprovalModel();
        $history = $approvalModel->getHistoryByCarePlan($id);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $history]);
        exit;
    }

    private function resolveReturnUrl(?string $url, string $default = 'index.php?url=internal/care-plans'): string
    {
        $url = trim((string) $url);
        if ($url !== '' && str_starts_with($url, 'index.php?url=internal/')) {
            return $url;
        }
        return $default;
    }

    private function plannedTasksJsonFromPost(): ?string
    {
        $postedTasks = $_POST['planned_tasks'] ?? [];
        if (!is_array($postedTasks)) {
            return null;
        }

        $plannedTasks = [];
        foreach ($postedTasks as $taskKey => $taskData) {
            if (!is_array($taskData)) {
                continue;
            }

            $times = $taskData['times'] ?? [];
            if (!is_array($times)) {
                $times = [$times];
            }

            $validTimes = [];
            foreach ($times as $t) {
                $t = trim((string) $t);
                if (preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $t)) {
                    $validTimes[] = $t;
                }
            }

            if (empty($validTimes)) {
                continue;
            }

            $plannedTasks[$taskKey] = [
                'label' => trim((string) ($taskData['label'] ?? '')),
                'times' => array_values(array_unique($validTimes)),
                'note' => trim((string) ($taskData['note'] ?? ''))
            ];
        }

        if (empty($plannedTasks)) {
            return null;
        }

        $encoded = json_encode($plannedTasks, JSON_UNESCAPED_UNICODE);
        return $encoded !== false ? $encoded : null;
    }

    private function appendFlash(string $url, string $key, string $message): string
    {
        $sep = str_contains($url, '?') ? '&' : '?';
        return $url . $sep . $key . '=' . urlencode($message);
    }
}
