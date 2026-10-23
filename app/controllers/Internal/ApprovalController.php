<?php

require_once __DIR__ . '/../../../app/core/PortalAccess.php';
require_once __DIR__ . '/../../../app/core/AuditLogger.php';
require_once __DIR__ . '/../../../app/core/Internal/TaskGenerator.php';
require_once __DIR__ . '/../../models/Internal/CarePlanModel.php';
require_once __DIR__ . '/../../models/Internal/ShiftTaskModel.php';
require_once __DIR__ . '/../../models/Internal/InternalAdmissionModel.php';

class ApprovalController
{
    private $carePlanModel;
    private $shiftTaskModel;
    private $admissionModel;

    public function __construct()
    {
        PortalAccess::requireAuth(['head_nurse']);
        $this->carePlanModel  = new CarePlanModel();
        $this->shiftTaskModel = new ShiftTaskModel();
        $this->admissionModel = new InternalAdmissionModel();
    }

    public function index()
    {
        $user   = PortalAccess::user();
        $deptId = (int) ($user['department_id'] ?? 0);

        $pendingPlans = $this->carePlanModel->getPendingForDepartment($deptId);

        require_once __DIR__ . '/../../views/Internal/approvals/index.php';
    }

    public function show()
    {
        $id   = (int) ($_GET['id'] ?? 0);
        $user = PortalAccess::user();

        $carePlan = $this->carePlanModel->getById($id);
        if (!$carePlan || $carePlan['status'] !== 'pending') {
            header("Location: " . $this->appendFlash('index.php?url=internal/approvals', 'error', "Không tìm thấy kế hoạch hoặc không ở trạng thái chờ duyệt"));
            exit;
        }
        if (($carePlan['treatment_plan_status'] ?? '') !== 'active') {
            header("Location: " . $this->appendFlash('index.php?url=internal/approvals', 'error', "Kế hoạch điều trị đã hoàn tất hoặc không còn active"));
            exit;
        }

        require_once __DIR__ . '/../../views/Internal/approvals/show.php';
    }

    public function approve()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=internal/approvals");
            exit;
        }

        $id   = (int) ($_POST['care_plan_id'] ?? 0);
        $note = trim($_POST['approval_note'] ?? '');
        $user = PortalAccess::user();

        $carePlan = $this->carePlanModel->getById($id);
        if (!$carePlan || $carePlan['status'] !== 'pending') {
            header("Location: " . $this->appendFlash('index.php?url=internal/approvals', 'error', "Không thể duyệt kế hoạch này"));
            exit;
        }
        if (($carePlan['treatment_plan_status'] ?? '') !== 'active') {
            header("Location: " . $this->appendFlash('index.php?url=internal/approvals', 'error', "Kế hoạch điều trị đã hoàn tất, không thể duyệt kế hoạch chăm sóc này"));
            exit;
        }

        $this->carePlanModel->updateStatus($id, 'approved');

        $this->saveApprovalRecord($id, $user['user_id'], 'approved', $note);

        TaskGenerator::generateFromCarePlan($id, $carePlan, $this->shiftTaskModel);

        AuditLogger::log($user['user_id'], 'APPROVE', 'CARE_PLAN', "Duyệt kế hoạch chăm sóc ID: {$id}");

        header("Location: " . $this->appendFlash('index.php?url=internal/approvals', 'success', "Đã duyệt kế hoạch và sinh task theo ca tự động!"));
        exit;
    }

    public function reject()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=internal/approvals");
            exit;
        }

        $id   = (int) ($_POST['care_plan_id'] ?? 0);
        $note = trim($_POST['approval_note'] ?? '');
        $user = PortalAccess::user();

        $carePlan = $this->carePlanModel->getById($id);
        if (!$carePlan || $carePlan['status'] !== 'pending') {
            header("Location: " . $this->appendFlash('index.php?url=internal/approvals', 'error', "Không thể từ chối kế hoạch này"));
            exit;
        }

        $this->carePlanModel->updateStatus($id, 'rejected');
        $this->saveApprovalRecord($id, $user['user_id'], 'rejected', $note);

        AuditLogger::log($user['user_id'], 'REJECT', 'CARE_PLAN', "Từ chối kế hoạch chăm sóc ID: {$id}. Ghi chú: {$note}");

        header("Location: " . $this->appendFlash('index.php?url=internal/approvals', 'success', "Đã từ chối kế hoạch. Điều dưỡng sẽ được thông báo để chỉnh sửa."));
        exit;
    }

    private function saveApprovalRecord(int $carePlanId, int $headNurseId, string $decision, string $note): void
    {
        $conn = require __DIR__ . '/../../../config/database.php';
        $sql  = "insert into care_plan_approvals
                     (care_plan_id, approved_by, approval_status, approval_note, approved_at)
                 values
                     (:care_plan_id, :approved_by, :approval_status, :approval_note, GETDATE())";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':care_plan_id',    $carePlanId,  PDO::PARAM_INT);
        $stmt->bindValue(':approved_by',     $headNurseId, PDO::PARAM_INT);
        $stmt->bindValue(':approval_status', $decision);
        $stmt->bindValue(':approval_note',   $note ?: null);
        $stmt->execute();
    }


    private function appendFlash(string $url, string $key, string $message): string
    {
        $sep = str_contains($url, '?') ? '&' : '?';
        return $url . $sep . $key . '=' . urlencode($message);
    }
}
