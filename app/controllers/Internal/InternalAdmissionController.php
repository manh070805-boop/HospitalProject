<?php

require_once __DIR__ . '/../../../app/core/PortalAccess.php';
require_once __DIR__ . '/../../../app/core/AuditLogger.php';
require_once __DIR__ . '/../../models/Internal/InternalAdmissionModel.php';
require_once __DIR__ . '/../../models/Internal/PatientIntakeModel.php';
require_once __DIR__ . '/../../models/Internal/VitalSignModel.php';
require_once __DIR__ . '/../../models/Internal/AlertModel.php';
require_once __DIR__ . '/../../models/Internal/TreatmentPlanModel.php';
require_once __DIR__ . '/../../models/Internal/CarePlanModel.php';
require_once __DIR__ . '/../../models/Internal/ShiftTaskModel.php';
require_once __DIR__ . '/../../../app/core/Internal/VitalRiskScorer.php';

class InternalAdmissionController
{
    private $admissionModel;
    private $patientModel;
    private $vitalModel;
    private $alertModel;
    private $treatmentPlanModel;
    private $carePlanModel;
    private $shiftTaskModel;

    public function __construct()
    {
        PortalAccess::requireAuth(['nurse', 'head_nurse']);
        $this->admissionModel = new InternalAdmissionModel();
        $this->patientModel = new PatientIntakeModel();
        $this->vitalModel = new VitalSignModel();
        $this->alertModel = new AlertModel();
        $this->treatmentPlanModel = new TreatmentPlanModel();
        $this->carePlanModel = new CarePlanModel();
        $this->shiftTaskModel = new ShiftTaskModel();
    }

    public function showDetail()
    {
        $admissionId = (int) ($_GET['id'] ?? 0);
        if (!$admissionId) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Thiếu ID ca nội trú"));
            exit;
        }

        $user = PortalAccess::user();
        $admission = $this->admissionModel->getAdmissionById($admissionId, $user['user_id'], $user['role_key']);

        if (!$admission) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Không tìm thấy ca nội trú hoặc bạn không có quyền xem"));
            exit;
        }

        $role = $user['role_key'];
        $latestVital = $this->vitalModel->getLatestByAdmission($admissionId);
        $latestVitalRisk = $latestVital ? VitalRiskScorer::assess($latestVital) : null;
        $openAlerts = $this->alertModel->getOpenByAdmission($admissionId);
        $treatmentPlan = $this->treatmentPlanModel->getCurrentByAdmission($admissionId);
        $latestCarePlan = $this->carePlanModel->getLatestByAdmission($admissionId);
        $bedHistory     = $this->admissionModel->getBedHistory($admissionId);

        require_once __DIR__ . '/../../views/Internal/admissions/show.php';
    }

    public function showCreateForm()
    {
        $patientId = $_GET['patient_id'] ?? null;
        if (!$patientId) {
            header("Location: index.php?url=internal/patients/search&error=Thiếu thông tin bệnh nhân");
            exit;
        }

        $patient = $this->patientModel->getPatientById($patientId);
        if (!$patient) {
            header("Location: index.php?url=internal/patients/search&error=Bệnh nhân không tồn tại");
            exit;
        }

        if ($this->admissionModel->isPatientAlreadyAdmitted($patientId)) {
            header("Location: index.php?url=internal/patients/search&error=Bệnh nhân này đang có ca nhập viện chưa xuất viện");
            exit;
        }

        $user = PortalAccess::user();
        $role = $user['role_key'];

        $departments = $this->admissionModel->getDepartments();
        $selectedDepartmentId = $this->resolveSelectedDepartmentId($departments, $_GET['department_id'] ?? null, $user['department_id'] ?? null);
        $availableBeds = $selectedDepartmentId ? $this->admissionModel->getAvailableBeds($selectedDepartmentId) : [];
        $doctors = $selectedDepartmentId ? $this->admissionModel->getDoctors($selectedDepartmentId) : [];

        require_once __DIR__ . '/../../views/Internal/admissions/create.php';
    }

    public function showTransferForm()
    {
        $user = PortalAccess::user();
        if (($user['role_key'] ?? '') !== 'head_nurse') {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Bạn không có quyền chuyển ca nội trú"));
            exit;
        }

        $admissionId = (int) ($_GET['id'] ?? 0);
        if (!$admissionId) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Thiếu ID ca nội trú"));
            exit;
        }

        $admission = $this->admissionModel->getAdmissionById($admissionId, $user['user_id'], $user['role_key']);
        if (!$admission) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Không tìm thấy ca nội trú"));
            exit;
        }

        if (strtolower(trim($admission['status'] ?? '')) !== 'active') {
            header("Location: index.php?url=internal/admissions/show&id={$admissionId}&error=" . urlencode("Chỉ có thể chuyển ca nội trú đang hoạt động"));
            exit;
        }

        $departments = $this->admissionModel->getDepartments();
        $selectedDepartmentId = $this->resolveSelectedDepartmentId($departments, $_GET['department_id'] ?? null, $admission['department_id'] ?? null);

        $availableBeds = $selectedDepartmentId ? $this->admissionModel->getAvailableBeds($selectedDepartmentId, $admission['bed_id'] ?? null) : [];
        $doctors = $selectedDepartmentId ? $this->admissionModel->getDoctors($selectedDepartmentId) : [];
        $nurses = $selectedDepartmentId ? $this->admissionModel->getNurses($selectedDepartmentId) : [];

        require_once __DIR__ . '/../../views/Internal/admissions/transfer.php';
    }

    public function storeAdmission()
    {
        $error = [];
        $patientId = $_POST['patient_id'] ?? null;
        $bedInput = $_POST['bed_id'] ?? null;
        $useTemporaryBed = $bedInput === '__temporary__';
        $bedId = $useTemporaryBed ? null : (int) ($bedInput ?? 0);
        $doctorId = $_POST['doctor_id'] ?? null;
        $departmentId = $_POST['department_id'] ?? null;

        if (!$patientId)
            $error[] = "Thiếu thông tin bệnh nhân";
        if (!$departmentId)
            $error[] = "Vui lòng chọn khoa điều trị";
        if (!$bedId && !$useTemporaryBed)
            $error[] = "Vui lòng chọn giường bệnh";
        if (!$doctorId)
            $error[] = "Vui lòng chọn bác sĩ phụ trách";

        if ($patientId && empty($error)) {
            if ($this->admissionModel->isPatientAlreadyAdmitted($patientId)) {
                $error[] = "Bệnh nhân này đang có ca nhập viện chưa xuất viện";
            }
        }

        if ($bedId && empty($error)) {
            if (!$this->admissionModel->isBedAvailable($bedId)) {
                $error[] = "Giường bệnh này đã được sử dụng, vui lòng chọn giường khác";
            }
        }

        if ($bedId && $departmentId && empty($error)) {
            $bedDepartmentId = $this->admissionModel->getBedDepartmentId($bedId);
            if ((int) $bedDepartmentId !== (int) $departmentId) {
                $error[] = "Giường bệnh không thuộc khoa điều trị đã chọn";
            }
        }

        if ($doctorId && $departmentId && empty($error)) {
            $doctorDepartmentId = $this->admissionModel->getDoctorDepartmentId($doctorId);
            if ((int) $doctorDepartmentId !== (int) $departmentId) {
                $error[] = "Bác sĩ phụ trách không thuộc khoa điều trị đã chọn";
            }
        }

        $user = PortalAccess::user();

        if (empty($error)) {
            $data = [
                'patient_id' => $patientId,
                'bed_id' => $bedId,
                'department_id' => $departmentId,
                'use_temporary_bed' => $useTemporaryBed,
                'assigned_nurse_id' => $user['user_id'],
                'assigned_doctor_id' => $doctorId ? $doctorId : null
            ];

            $admissionId = $this->admissionModel->createAdmission($data);

            if ($admissionId) {
                $auditNote = $useTemporaryBed ? ' bằng giường tạm' : '';
                AuditLogger::log($user['user_id'], 'CREATE', 'ADMISSION', "Lập hồ sơ nhập viện mới{$auditNote} (ID: $admissionId) cho bệnh nhân ID: $patientId");
                $message = $useTemporaryBed
                    ? "Đã lập ca nhập viện bằng giường tạm. Cần chuyển sang giường chính thức khi có giường trống."
                    : "Đã lập ca nhập viện thành công!";
                header("Location: index.php?url=internal/dashboard&success=" . urlencode($message));
                exit;
            } else {
                $error[] = 'Có lỗi xảy ra khi lưu vào database.';
            }
        }
        $departmentParam = $departmentId ? "&department_id=" . urlencode((string) $departmentId) : "";
        header("Location: index.php?url=internal/admissions/create&patient_id=" . ($patientId ?? '') . $departmentParam . "&error=" . urlencode(implode(' | ', $error)));
        exit;
    }

    public function updateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=internal/monitoring");
            exit;
        }

        $admissionId = $_POST['admission_id'] ?? null;
        $status = trim((string) ($_POST['status'] ?? ''));
        $allowedStatuses = ['active', 'discharged'];
        $returnUrl = $this->resolveReturnUrl($_POST['return_url'] ?? null);

        if (!$admissionId || !in_array($status, $allowedStatuses, true)) {
            header("Location: " . $this->appendFlash($returnUrl, 'error', "Trạng thái nhập/xuất viện không hợp lệ"));
            exit;
        }

        $user = PortalAccess::user();
        if (!$this->admissionModel->canManageAdmissionStatus((int) $admissionId, $user['user_id'], $user['role_key'])) {
            header("Location: " . $this->appendFlash($returnUrl, 'error', "Bạn không có quyền cập nhật ca nội trú này"));
            exit;
        }

        $label = $status === 'discharged' ? 'xuất viện' : 'nhập viện';
        $extraLog = '';

        if ($status === 'discharged') {
            $forceDischarge = isset($_POST['force_discharge']) && $_POST['force_discharge'] === '1';
            $dischargeReason = trim($_POST['discharge_reason'] ?? '');
            
            if ($forceDischarge) {
                if ($dischargeReason === '') {
                    header("Location: " . $this->appendFlash($returnUrl, 'error', "Vui lòng nhập lý do xuất viện khẩn cấp/chuyển viện"));
                    exit;
                }
                
                $this->alertModel->resolveAlertsByAdmissionId((int) $admissionId);
                $this->shiftTaskModel->skipTasksByAdmissionId((int) $admissionId, $user['user_id']);
                $this->carePlanModel->rejectPendingCarePlansByAdmissionId((int) $admissionId);
                
                $label = 'xuất viện khẩn cấp/chuyển viện';
                $extraLog = ". Lý do: " . $dischargeReason;
            } else {
                if ($this->alertModel->hasOpenAlerts((int) $admissionId)) {
                    header("Location: " . $this->appendFlash($returnUrl, 'error', "Không thể xuất viện: còn cảnh báo sinh tồn chưa được xử lý"));
                    exit;
                }
                if ($this->admissionModel->hasPendingTasks((int) $admissionId)) {
                    header("Location: " . $this->appendFlash($returnUrl, 'error', "Không thể xuất viện: còn task chăm sóc chưa hoàn tất"));
                    exit;
                }
                if ($this->admissionModel->hasPendingCarePlan((int) $admissionId)) {
                    header("Location: " . $this->appendFlash($returnUrl, 'error', "Không thể xuất viện: còn kế hoạch chăm sóc đang chờ duyệt"));
                    exit;
                }
            }
        }

        $updated = $this->admissionModel->updateAdmissionStatus((int) $admissionId, $status);
        if ($updated) {
            AuditLogger::log($user['user_id'], 'UPDATE', 'ADMISSION', "Cập nhật trạng thái $label cho admission ID: $admissionId" . $extraLog);
            header("Location: " . $this->appendFlash($returnUrl, 'success', "Đã cập nhật trạng thái " . $label));
            exit;
        }

        header("Location: " . $this->appendFlash($returnUrl, 'error', "Không thể cập nhật trạng thái nhập/xuất viện"));
        exit;
    }

    public function transferAdmission()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=internal/monitoring");
            exit;
        }

        $user = PortalAccess::user();
        if (($user['role_key'] ?? '') !== 'head_nurse') {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Bạn không có quyền chuyển ca nội trú"));
            exit;
        }

        $admissionId = (int) ($_POST['admission_id'] ?? 0);
        $departmentId = (int) ($_POST['department_id'] ?? 0);
        $bedId = (int) ($_POST['bed_id'] ?? 0);
        $doctorId = (int) ($_POST['doctor_id'] ?? 0);
        $nurseId = (int) ($_POST['nurse_id'] ?? 0);
        $transferNote = trim((string) ($_POST['transfer_note'] ?? ''));
        $error = [];

        if (!$admissionId)
            $error[] = "Thiếu ID ca nội trú";
        if (!$departmentId)
            $error[] = "Vui lòng chọn khoa điều trị";
        if (!$bedId)
            $error[] = "Vui lòng chọn giường bệnh";
        if (!$doctorId)
            $error[] = "Vui lòng chọn bác sĩ phụ trách";
        if (!$nurseId)
            $error[] = "Vui lòng chọn điều dưỡng phụ trách";
        if ($transferNote === '')
            $error[] = "Vui lòng nhập lý do chuyển";
        if (mb_strlen($transferNote, 'UTF-8') > 255)
            $error[] = "Lý do chuyển không được vượt quá 255 ký tự";

        $admission = null;
        if ($admissionId) {
            $admission = $this->admissionModel->getAdmissionById($admissionId, $user['user_id'], $user['role_key']);
            if (!$admission) {
                $error[] = "Không tìm thấy ca nội trú";
            } elseif (strtolower(trim($admission['status'] ?? '')) !== 'active') {
                $error[] = "Chỉ có thể chuyển ca nội trú đang hoạt động";
            }
        }

        if (empty($error) && $bedId) {
            $bedDepartmentId = $this->admissionModel->getBedDepartmentId($bedId);
            if ((int) $bedDepartmentId !== $departmentId) {
                $error[] = "Giường bệnh không thuộc khoa điều trị đã chọn";
            } elseif ($admission && $bedId !== (int) $admission['bed_id'] && !$this->admissionModel->isBedAvailable($bedId)) {
                $error[] = "Giường bệnh này đã được sử dụng, vui lòng chọn giường khác";
            }
        }

        if (empty($error) && $doctorId) {
            $doctorDepartmentId = $this->admissionModel->getDoctorDepartmentId($doctorId);
            if ((int) $doctorDepartmentId !== $departmentId) {
                $error[] = "Bác sĩ phụ trách không thuộc khoa điều trị đã chọn";
            }
        }

        if (empty($error) && $nurseId) {
            $nurseDepartmentId = $this->admissionModel->getNurseDepartmentId($nurseId);
            if ((int) $nurseDepartmentId !== $departmentId) {
                $error[] = "Điều dưỡng phụ trách không thuộc khoa điều trị đã chọn";
            }
        }

        if (
            empty($error)
            && $admission
            && $bedId === (int) $admission['bed_id']
            && $doctorId === (int) ($admission['doctor_id'] ?? 0)
            && $nurseId === (int) ($admission['nurse_id'] ?? 0)
        ) {
            $error[] = "Không có thay đổi để chuyển ca nội trú";
        }

        $transferUrl = "index.php?url=internal/admissions/transfer&id=" . $admissionId;
        if ($departmentId) {
            $transferUrl .= "&department_id=" . urlencode((string) $departmentId);
        }

        if (!empty($error)) {
            header("Location: " . $transferUrl . "&error=" . urlencode(implode(' | ', $error)));
            exit;
        }

        $updated = $this->admissionModel->transferAdmission($admissionId, [
            'department_id' => $departmentId,
            'bed_id' => $bedId,
            'assigned_doctor_id' => $doctorId,
            'assigned_nurse_id' => $nurseId,
            'transfer_note' => $transferNote,
        ]);

        if ($updated) {
            AuditLogger::log(
                $user['user_id'],
                'TRANSFER',
                'ADMISSION',
                "Chuyển ca nội trú ID: {$admissionId}; giường {$admission['bed_id']} -> {$bedId}; bác sĩ {$admission['doctor_id']} -> {$doctorId}; điều dưỡng {$admission['nurse_id']} -> {$nurseId}. Lý do: {$transferNote}"
            );
            header("Location: index.php?url=internal/admissions/show&id={$admissionId}&success=" . urlencode("Đã chuyển ca nội trú thành công"));
            exit;
        }

        header("Location: " . $transferUrl . "&error=" . urlencode("Không thể chuyển ca nội trú, vui lòng kiểm tra lại dữ liệu"));
        exit;
    }


    private function resolveSelectedDepartmentId(array $departments, $requestedDepartmentId, $fallbackDepartmentId): ?int
    {
        $availableIds = array_map(static fn($department) => (int) $department['department_id'], $departments);

        $requestedDepartmentId = $requestedDepartmentId !== null && $requestedDepartmentId !== ''
            ? (int) $requestedDepartmentId
            : null;

        if ($requestedDepartmentId && in_array($requestedDepartmentId, $availableIds, true)) {
            return $requestedDepartmentId;
        }

        $fallbackDepartmentId = $fallbackDepartmentId !== null && $fallbackDepartmentId !== ''
            ? (int) $fallbackDepartmentId
            : null;

        if ($fallbackDepartmentId && in_array($fallbackDepartmentId, $availableIds, true)) {
            return $fallbackDepartmentId;
        }

        return $availableIds[0] ?? null;
    }

    private function resolveReturnUrl(?string $url, string $default = 'index.php?url=internal/monitoring'): string
    {
        $url = trim((string) $url);
        if ($url !== '' && str_starts_with($url, 'index.php?url=internal/')) {
            return $url;
        }
        return $default;
    }

    private function appendFlash(string $url, string $key, string $message): string
    {
        $sep = str_contains($url, '?') ? '&' : '?';
        return $url . $sep . $key . '=' . urlencode($message);
    }

    public function bedHistory()
    {
        $admissionId = (int) ($_GET['admission_id'] ?? 0);
        if (!$admissionId) {
            header("Location: index.php?url=internal/monitoring");
            exit;
        }

        $user      = PortalAccess::user();
        $admission = $this->admissionModel->getAdmissionById($admissionId, $user['user_id'], $user['role_key']);

        if (!$admission) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Không có quyền xem ca nội trú này"));
            exit;
        }

        $bedHistory = $this->admissionModel->getBedHistory($admissionId);

        require_once __DIR__ . '/../../views/Internal/admissions/bed-history.php';
    }
}
