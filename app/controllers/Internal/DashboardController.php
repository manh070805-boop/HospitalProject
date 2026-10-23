<?php

require_once __DIR__ . '/../../../app/core/PortalAccess.php';
require_once __DIR__ . '/../../models/Internal/DashboardModel.php';

class InternalDashboardController
{
    private $dashboardModel;

    public function __construct()
    {
        PortalAccess::requireAuth(['nurse', 'head_nurse']);
        $this->dashboardModel = new DashboardModel();
    }

    public function index()
    {
        $user = PortalAccess::user();
        $userId = $user['user_id'];
        $role = $user['role_key'];
        $deptId = $user['department_id'];

        $activeAdmissions = $this->dashboardModel->countActiveAdmissions($userId, $role, $deptId);
        $activeAlerts = $this->dashboardModel->countActiveAlerts($userId, $role, $deptId);
        $pendingTasks = $this->dashboardModel->countPendingTasks($userId, $role, $deptId);
        $attentionPatients = $this->dashboardModel->getAttentionPatients($userId, $role, $deptId, 6);
        $dashboardActivities = $this->dashboardModel->getDashboardActivities($userId, $role, $deptId, 8);
        $carePlanStats = $this->dashboardModel->getCarePlanStats($userId, $role, $deptId);

        $bedStats = null;
        if ($role === 'head_nurse' && $deptId) {
            $bedStats = $this->dashboardModel->getBedStats($deptId);
        }

        require_once __DIR__ . '/../../views/Internal/dashboard/index.php';
    }
}
