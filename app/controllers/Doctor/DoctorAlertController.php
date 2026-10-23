<?php

require_once __DIR__ . '/../../core/PortalAccess.php';
require_once __DIR__ . '/../../models/Doctor/DoctorAlertModel.php';

class DoctorAlertController
{
    private $alertModel;

    public function __construct()
    {
        PortalAccess::requireAuth(['doctor']);
        $this->alertModel = new DoctorAlertModel();
    }

    public function index()
    {
        $user = PortalAccess::user();
        $doctorId = (int) $user['user_id'];
        $keyword = trim($_GET['keyword'] ?? '');
        $severity = trim($_GET['severity'] ?? '');
        $patientId = max(0, (int) ($_GET['patient_id'] ?? 0));
        $allowedSeverities = ['', 'low', 'medium', 'high', 'critical'];

        if (!in_array($severity, $allowedSeverities, true)) {
            $severity = '';
        }

        $isPatientScoped = $patientId > 0;
        $alerts = $isPatientScoped
            ? $this->alertModel->getOpenAlertsForDoctor($doctorId, $keyword, $severity, $patientId)
            : [];
        $alertGroups = $isPatientScoped
            ? []
            : $this->alertModel->getOpenAlertGroupsForDoctor($doctorId, $keyword, $severity);

        require_once __DIR__ . '/../../views/Doctor/alerts/index.php';
    }
}
