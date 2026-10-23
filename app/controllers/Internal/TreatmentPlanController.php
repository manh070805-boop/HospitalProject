<?php

require_once __DIR__ . '/../../../app/core/PortalAccess.php';
require_once __DIR__ . '/../../models/Internal/TreatmentPlanModel.php';
require_once __DIR__ . '/../../models/Internal/InternalAdmissionModel.php';

class TreatmentPlanController
{
    private $treatmentPlanModel;
    private $admissionModel;

    public function __construct()
    {
        PortalAccess::requireAuth(['nurse', 'head_nurse']);
        $this->treatmentPlanModel = new TreatmentPlanModel();
        $this->admissionModel     = new InternalAdmissionModel();
    }

    public function show()
    {
        $planId = (int) ($_GET['id'] ?? 0);
        if (!$planId) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Thiếu ID kế hoạch điều trị"));
            exit;
        }

        $user          = PortalAccess::user();
        $treatmentPlan = $this->treatmentPlanModel->getById($planId);

        if (!$treatmentPlan) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Không tìm thấy kế hoạch điều trị"));
            exit;
        }

        $admission = $this->admissionModel->getAdmissionById(
            (int) $treatmentPlan['admission_id'],
            $user['user_id'],
            $user['role_key']
        );

        if (!$admission) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Bạn không có quyền xem kế hoạch này"));
            exit;
        }

        require_once __DIR__ . '/../../views/Internal/treatment-plans/show.php';
    }

    public function history()
    {
        $admissionId = (int) ($_GET['admission_id'] ?? 0);
        if (!$admissionId) {
            header("Location: index.php?url=internal/monitoring");
            exit;
        }

        $user      = PortalAccess::user();
        $admission = $this->admissionModel->getAdmissionById($admissionId, $user['user_id'], $user['role_key']);

        if (!$admission) {
            header("Location: index.php?url=internal/monitoring&error=" . urlencode("Không có quyền xem"));
            exit;
        }

        $plans = $this->treatmentPlanModel->getAllByAdmission($admissionId);

        require_once __DIR__ . '/../../views/Internal/treatment-plans/history.php';
    }
}
