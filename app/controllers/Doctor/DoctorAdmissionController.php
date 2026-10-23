<?php
require_once __DIR__ . '/../../core/PortalAccess.php';
require_once __DIR__ . '/../../core/Internal/VitalRiskScorer.php';
require_once __DIR__ . '/../../models/Doctor/DoctorAdmissionModel.php';
require_once __DIR__ . '/../../models/Doctor/DoctorAlertModel.php';
require_once __DIR__ . '/../../models/Doctor/DoctorCarePlanModel.php';

class DoctorAdmissionController
{
    private $admissionModel;
    private $alertModel;
    private $carePlanModel;

    public function __construct()
    {
        PortalAccess::requireAuth(['doctor']);
        $this->admissionModel = new DoctorAdmissionModel();
        $this->alertModel = new DoctorAlertModel();
        $this->carePlanModel = new DoctorCarePlanModel();
    }

    public function index()
    {
        $user = PortalAccess::user();
        $doctorId = (int) $user['user_id'];

        $keyword = trim($_GET['keyword'] ?? '');
        $filter = trim($_GET['filter'] ?? 'all');
        $allowedFilters = ['all', 'alerts', 'no-plan', 'with-plan'];

        if (!in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;

        $patients = $this->admissionModel->getPatientWorklist($doctorId, $keyword, $filter);
        $totalRows = count($patients);
        $totalPages = max(1, (int) ceil($totalRows / $perPage));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;
        $patients = array_slice($patients, $offset, $perPage);
        $displayStart = $totalRows > 0 ? $offset + 1 : 0;
        $displayEnd = min($offset + $perPage, $totalRows);

        require_once __DIR__ . '/../../views/Doctor/patients/index.php';
    }

    public function show()
    {
        $admissionId = (int) ($_GET['id'] ?? 0);
        $user = PortalAccess::user();
        $doctorId = (int) $user['user_id'];

        if (!$admissionId) {
            header("Location: index.php?url=doctor-portal/admissions&error=" . urlencode("Thiếu ID ca nội trú"));
            exit;
        }

        $admission = $this->admissionModel->getAdmissionById($admissionId, $doctorId);

        if (!$admission) {
            header("Location: index.php?url=doctor-portal/admissions&error=" . urlencode("Không tìm thấy ca nội trú hoặc bạn không có quyền xem"));
            exit;
        }

        $latestVital = $this->admissionModel->getLatestVital($admissionId);
        $latestVitalRisk = $latestVital ? VitalRiskScorer::assess($latestVital) : null;
        $openAlerts = $this->alertModel->getOpenAlerts($admissionId);
        $treatmentPlan = $this->admissionModel->getCurrentTreatmentPlan($admissionId);
        $latestCarePlan = $this->carePlanModel->getLatestCarePlan($admissionId);

        require_once __DIR__ . '/../../views/Doctor/admissions/show.php';
    }
}
