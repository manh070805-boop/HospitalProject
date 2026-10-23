<?php

require_once __DIR__ . '/../../core/PortalAccess.php';
require_once __DIR__ . '/../../models/Doctor/DoctorDashboardModel.php';

class DoctorPortalController
{
    private $dashboardModel;

    public function __construct()
    {
        PortalAccess::requireAuth(['doctor']);
        $this->dashboardModel = new DoctorDashboardModel();
    }

    public function index()
    {
        $user = PortalAccess::user();
        $doctorId = (int) $user['user_id'];

        $stats = [
            'active_admissions' => $this->dashboardModel->countActiveAdmissions($doctorId),
            'open_alerts' => $this->dashboardModel->countOpenAlerts($doctorId),
            'active_plans' => $this->dashboardModel->countActiveTreatmentPlans($doctorId),

        ];

        $attentionPatients = $this->dashboardModel->getAttentionPatients($doctorId);
        $recentOrders = $this->dashboardModel->getRecentOrders($doctorId);

        require_once __DIR__ . '/../../views/Doctor/dashboard/index.php';
    }
}
