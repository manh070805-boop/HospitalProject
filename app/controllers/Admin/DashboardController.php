<?php
require_once __DIR__ . '/../../models/Admin/DashboardModel.php';

class DashboardController
{
    private $dashboardModel;
    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }
    public function indexDashboard()
    {
        $totalUsers = $this->dashboardModel->countUsers();
        $activeUsers = $this->dashboardModel->countUserByStatus('active');
        $inactiveUsers = $this->dashboardModel->countUserByStatus('inactive');
        $lockedUsers = $this->dashboardModel->countUserByStatus('locked');
        $totalRoles = $this->dashboardModel->countRoles();
        $totalDepartments = $this->dashboardModel->countDepartments();
        $recentUsers = $this->dashboardModel->getRecentUsers(8);
        require_once __DIR__ . '/../../views/Admin/dashboard/index.php';
    }
}

?>