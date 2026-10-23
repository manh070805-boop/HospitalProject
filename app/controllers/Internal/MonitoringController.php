<?php

require_once __DIR__ . '/../../../app/core/PortalAccess.php';
require_once __DIR__ . '/../../models/Internal/MonitoringModel.php';

class MonitoringController
{
    private $monitoringModel;

    public function __construct()
    {
        PortalAccess::requireAuth(['nurse', 'head_nurse']);
        $this->monitoringModel = new MonitoringModel();
    }


    public function index()
    {
        $user = PortalAccess::user();
        $userId = $user['user_id'];
        $role = $user['role_key'];
        $deptId = $user['department_id'];

        $admissions = $this->monitoringModel->getActiveAdmissions($userId, $role, $deptId);

        require_once __DIR__ . '/../../views/Internal/monitoring/index.php';
    }
}
