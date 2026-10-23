<?php

require_once __DIR__ . '/../../../app/core/PortalAccess.php';
require_once __DIR__ . '/../../../app/core/AuditLogger.php';
require_once __DIR__ . '/../../models/Internal/PatientIntakeModel.php';
require_once __DIR__ . '/../../models/Internal/InternalAdmissionModel.php';

class PatientIntakeController
{
    private $patientModel;
    private $admissionModel;

    public function __construct()
    {
        PortalAccess::requireAuth(['nurse', 'head_nurse']);
        $this->patientModel = new PatientIntakeModel();
        $this->admissionModel = new InternalAdmissionModel();
    }
    public function index()
    {
        $keyword = trim($_GET['keyword'] ?? '');
        $patients = [];
        $user = PortalAccess::user();
        $role = $user['role_key'];
        $userId = (int) $user['user_id'];
        $deptId = isset($user['department_id']) ? (int) $user['department_id'] : null;

        $patients = $this->patientModel->findPatientsForIntake($keyword, $role, $userId, $deptId);

        if ($keyword !== '') {
            $resultTitle = 'Kết quả tìm kiếm cho: "' . $keyword . '"';
        } elseif ($role === 'head_nurse') {
            $resultTitle = 'Bệnh nhân đang nội trú';
        } else {
            $resultTitle = '5 bệnh nhân gần nhất đang chăm sóc';
        }

        require_once __DIR__ . '/../../views/Internal/patients/search.php';
    }

    public function checkCccd()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $citizen_id = trim($_POST['citizen_id'] ?? '');

            if ($citizen_id === '') {
                header("Location: " . $this->appendFlash('index.php?url=internal/patients/check-cccd', 'error', "Vui lòng nhập CCCD."));
                exit;
            }
            if (!preg_match('/^[0-9]{12}$/', $citizen_id)) {
                header("Location: " . $this->appendFlash('index.php?url=internal/patients/check-cccd', 'error', "CCCD không hợp lệ (phải gồm 12 chữ số)."));
                exit;
            }

            $existingPatient = $this->patientModel->findByCitizenId($citizen_id);
            if ($existingPatient) {
                $patientId = (int) $existingPatient['patient_id'];
                if ($this->admissionModel->isPatientAlreadyAdmitted($patientId)) {
                    header("Location: " . $this->appendFlash("index.php?url=internal/patients/check-cccd", 'error', "CCCD này đã có hồ sơ và đang có ca nhập viện chưa xuất viện."));
                    exit;
                }
                header("Location: index.php?url=internal/admissions/create&patient_id=" . $patientId);
                exit;
            } else {
                header("Location: index.php?url=internal/patients/create&cccd=" . urlencode($citizen_id));
                exit;
            }
        }

        $user = PortalAccess::user();
        $role = $user['role_key'];
        $error = $_GET['error'] ?? null;
        require_once __DIR__ . '/../../views/Internal/patients/check_cccd.php';
    }

    public function showCreateForm()
    {
        $user = PortalAccess::user();
        $role = $user['role_key'];
        $patientCode = $this->patientModel->generatePatientCode();
        $citizen_id = $_GET['cccd'] ?? '';
        require_once __DIR__ . '/../../views/Internal/patients/create.php';
    }

    public function storePatient()
    {
        $full_name = trim($_POST['full_name'] ?? '');
        $date_of_birth = trim($_POST['date_of_birth'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $citizen_id = trim($_POST['citizen_id'] ?? '');

        $error = [];

        if ($full_name === '')
            $error[] = 'Họ tên không được để trống';
        if ($date_of_birth === '')
            $error[] = 'Ngày sinh không được để trống';
        if ($gender === '')
            $error[] = 'Vui lòng chọn giới tính';
        if ($phone === '')
            $error[] = 'Số điện thoại không được để trống';
        if ($citizen_id === '')
            $error[] = 'CCCD không được để trống';

        if ($phone !== '' && !preg_match('/^0[0-9]{9}$/', $phone)) {
            $error[] = 'Số điện thoại không hợp lệ (phải từ 10 chữ số)';
        }
        if ($citizen_id !== '' && !preg_match('/^[0-9]{12}$/', $citizen_id)) {
            $error[] = 'CCCD không hợp lệ (phải gồm 12 chữ số)';
        }

        if ($citizen_id !== '' && empty($error)) {
            $existingPatient = $this->patientModel->findByCitizenId($citizen_id);
            if ($existingPatient) {
                $error[] = 'CCCD này đã tồn tại trong hệ thống. Vui lòng sử dụng chức năng kiểm tra CCCD để tiếp nhận lại.';
            }
        }

        if ($phone !== '' && empty($error)) {
            if ($this->patientModel->isDuplicate($phone)) {
                $error[] = 'Số điện thoại này đã tồn tại trong hệ thống. Vui lòng tìm kiếm lại trước khi tạo hồ sơ mới.';
            }
        }

        if (empty($error)) {
            $data = [
                'patient_code' => $this->patientModel->generatePatientCode(),
                'full_name' => $full_name,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'phone' => $phone,
                'address' => $address,
                'citizen_id' => $citizen_id
            ];

            $newId = $this->patientModel->createPatient($data);

            if ($newId) {
                AuditLogger::log(PortalAccess::user()['user_id'], 'CREATE', 'PATIENT', "Tiếp nhận bệnh nhân mới: $full_name (ID: $newId)");
                header("Location: index.php?url=internal/admissions/create&patient_id=" . $newId);
                exit;
            } else {
                $error[] = 'Có lỗi xảy ra khi lưu thông tin vào cơ sở dữ liệu.';
            }
        }

        $user = PortalAccess::user();
        $role = $user['role_key'];
        $patientCode = $this->patientModel->generatePatientCode();
        require_once __DIR__ . '/../../views/Internal/patients/create.php';
    }
    public function showPatient()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: " . $this->appendFlash('index.php?url=internal/patients/search', 'error', "Thiếu ID bệnh nhân"));
            exit;
        }

        $user = PortalAccess::user();
        $role = $user['role_key'];
        $patient = $this->patientModel->getPatientById($id);
        if (!$patient) {
            header("Location: " . $this->appendFlash('index.php?url=internal/patients/search', 'error', "Bệnh nhân không tồn tại"));
            exit;
        }

        $deptId = isset($user['department_id']) ? (int) $user['department_id'] : null;
        $activeAdmission = $this->patientModel->getActiveAdmissionForPatient($id, $role, (int) $user['user_id'], $deptId);
        $admissions = $this->patientModel->getAdmissionsForPatient($id, $role, (int) $user['user_id'], $deptId);

        require_once __DIR__ . '/../../views/Internal/patients/show.php';
    }


    private function appendFlash(string $url, string $key, string $message): string
    {
        $sep = str_contains($url, '?') ? '&' : '?';
        return $url . $sep . $key . '=' . urlencode($message);
    }
}
