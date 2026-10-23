<?php

require_once __DIR__ . '/../../core/PortalAccess.php';
require_once __DIR__ . '/../../models/Doctor/DoctorCarePlanModel.php';

class DoctorCarePlanController
{
    private $carePlanModel;

    public function __construct()
    {
        PortalAccess::requireAuth(['doctor']);
        $this->carePlanModel = new DoctorCarePlanModel();
    }

    public function index(): void
    {
        $user = PortalAccess::user();
        $doctorId = (int) $user['user_id'];
        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $allowedStatuses = ['', 'draft', 'pending', 'approved', 'rejected'];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $carePlans = $this->carePlanModel->getCarePlansForDoctor($doctorId, $keyword, $status);

        require_once __DIR__ . '/../../views/Doctor/care-plans/index.php';
    }
    public function show()
    {
        $carePlanId = (int) ($_GET['id'] ?? 0);
        $user = PortalAccess::user();
        $doctorId = (int) $user['user_id'];

        if (!$carePlanId) {
            header("Location: index.php?url=doctor-portal/care-plans&error=" . urlencode("Thiếu ID kế hoạch chăm sóc"));
            exit;
        }

        $carePlan = $this->carePlanModel->getCarePlanById($carePlanId, $doctorId);

        if (!$carePlan) {
            header("Location: index.php?url=doctor-portal/care-plans&error=" . urlencode("Không tìm thấy kế hoạch chăm sóc hoặc bạn không có quyền xem"));
            exit;
        }

        $latestApproval = $this->carePlanModel->getCarePlanApproval($carePlanId);

        require_once __DIR__ . '/../../views/Doctor/care-plans/show.php';
    }
}
