<?php
require_once __DIR__ . '/../../models/SystemLogModel.php';

class SystemLogController
{
    private $logModel;

    public function __construct()
    {
        $this->logModel = new SystemLogModel();
    }

    public function index()
    {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $actionFilter = isset($_GET['action']) ? trim($_GET['action']) : '';
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $totalLogs = $this->logModel->countLogs($keyword, $actionFilter);
        $totalPages = ceil($totalLogs / $limit);

        if ($totalPages > 0 && $page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        $logs = $this->logModel->getLogsByPage($keyword, $actionFilter, $limit, $offset);
        $actionCounts = $this->logModel->countLogsByAction();

        require_once __DIR__ . '/../../views/Admin/system-logs/index.php';
    }

    public function show()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: index.php?url=system-logs&error=" . urlencode("Thiếu ID nhật ký"));
            exit;
        }

        $log = $this->logModel->getLogById($id);
        if (!$log) {
            header("Location: index.php?url=system-logs&error=" . urlencode("Bản ghi không tồn tại"));
            exit;
        }

        $recentLogs = $this->logModel->getRecentLogs(5);

        require_once __DIR__ . '/../../views/Admin/system-logs/show.php';
    }

    public function stats()
    {
        $actionCounts = $this->logModel->countLogsByAction();
        $recentLogs = $this->logModel->getRecentLogs();

        $totalAll = array_sum($actionCounts);
        $countCreate = $actionCounts['CREATE'] ?? 0;
        $countUpdate = $actionCounts['UPDATE'] ?? 0;
        $countDelete = $actionCounts['DELETE'] ?? 0;
        $countLogin = $actionCounts['LOGIN'] ?? 0;
        $countLogout = $actionCounts['LOGOUT'] ?? 0;

        $pctCreate = $totalAll > 0 ? round(($countCreate / $totalAll) * 100) : 0;
        $pctUpdate = $totalAll > 0 ? round(($countUpdate / $totalAll) * 100) : 0;
        $pctDelete = $totalAll > 0 ? round(($countDelete / $totalAll) * 100) : 0;
        $pctLogin = $totalAll > 0 ? round(($countLogin / $totalAll) * 100) : 0;
        $pctLogout = $totalAll > 0 ? round(($countLogout / $totalAll) * 100) : 0;
        $pctOther = max(0, 100 - $pctCreate - $pctUpdate - $pctDelete - $pctLogin - $pctLogout);

        require_once __DIR__ . '/../../views/Admin/system-logs/stats.php';
    }
}

