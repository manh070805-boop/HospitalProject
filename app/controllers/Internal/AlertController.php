<?php

require_once __DIR__ . '/../../../app/core/PortalAccess.php';
require_once __DIR__ . '/../../../app/core/AuditLogger.php';
require_once __DIR__ . '/../../models/Internal/AlertModel.php';
require_once __DIR__ . '/../../models/Internal/InternalAdmissionModel.php';

class AlertController
{
    private $alertModel;
    private $admissionModel;

    public function __construct()
    {
        PortalAccess::requireAuth(['nurse', 'head_nurse']);
        $this->alertModel = new AlertModel();
        $this->admissionModel = new InternalAdmissionModel();
    }

    public function index()
    {
        $user = PortalAccess::user();
        $admissionId = (int) ($_GET['admission_id'] ?? 0);

        if ($admissionId) {
            $admission = $this->admissionModel->getAdmissionById($admissionId, $user['user_id'], $user['role_key']);
            if (!$admission) {
                header("Location: index.php?url=internal/monitoring&error=" . urlencode("Không có quyền xem"));
                exit;
            }
            $alerts = $this->alertModel->getByAdmission($admissionId);
            $alertGroups = [];
            $isGroupedView = false;
        } else {
            $admission = null;
            $alerts = [];
            $alertGroups = $this->alertModel->getGroupedOpenForUser($user['user_id'], $user['role_key'], $user['department_id']);
            $isGroupedView = true;
        }

        require_once __DIR__ . '/../../views/Internal/alerts/index.php';
    }

    public function acknowledge()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=internal/alerts");
            exit;
        }

        $alertId = (int) ($_POST['alert_id'] ?? 0);
        $returnUrl = $this->resolveReturnUrl($_POST['return_url'] ?? null);
        $user = PortalAccess::user();

        if (!$alertId) {
            header("Location: " . $this->appendFlash($returnUrl, 'error', "Thiếu ID cảnh báo"));
            exit;
        }

        $alert = $this->alertModel->getById($alertId);
        if (!$alert) {
            header("Location: " . $this->appendFlash($returnUrl, 'error', "Không tìm thấy cảnh báo"));
            exit;
        }
        $updated = $this->alertModel->acknowledgeAlert($alertId);
        if ($updated) {
            AuditLogger::log($user['user_id'], 'UPDATE', 'ALERT', "Acknowledge alert ID: {$alertId}");
            header("Location: " . $this->appendFlash($returnUrl, 'success', "Đã xác nhận cảnh báo"));
            exit;
        }

        header("Location: " . $this->appendFlash($returnUrl, 'error', "Không thể cập nhật cảnh báo"));
        exit;
    }

    public function resolve()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=internal/alerts");
            exit;
        }

        $alertId = (int) ($_POST['alert_id'] ?? 0);
        $returnUrl = $this->resolveReturnUrl($_POST['return_url'] ?? null);
        $user = PortalAccess::user();

        if (!$alertId) {
            header("Location: " . $this->appendFlash($returnUrl, 'error', "Thiếu ID cảnh báo"));
            exit;
        }

        $updated = $this->alertModel->resolveAlert($alertId, $user['user_id']);
        if ($updated) {
            AuditLogger::log($user['user_id'], 'UPDATE', 'ALERT', "Resolve alert ID: {$alertId}");
            header("Location: " . $this->appendFlash($returnUrl, 'success', "Đã xử lý xong cảnh báo"));
            exit;
        }

        header("Location: " . $this->appendFlash($returnUrl, 'error', "Không thể xử lý cảnh báo"));
        exit;
    }

    private function resolveReturnUrl(?string $url, string $default = 'index.php?url=internal/alerts'): string
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
}
