<?php
require_once __DIR__ . '/../../models/Admin/PatientModel.php';
require_once __DIR__ . '/../../core/AuditLogger.php';

class PatientController
{
    private $patientModel;

    public function __construct()
    {
        $this->patientModel = new PatientModel();
    }

    public function index()
    {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = 5;
        $offset = ($page - 1) * $limit;
        $totalPatients = $this->patientModel->countPatients($keyword, $gender);
        $stats = $this->patientModel->countPatientStats();
        $totalPages = ceil($totalPatients / $limit);
        if ($totalPages > 0 && $page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;

        }
        $patients = $this->patientModel->getPatientsByPage($keyword, $gender, $limit, $offset);

        require_once __DIR__ . '/../../views/Admin/patients/index.php';
    }


    public function editPatient()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: index.php?url=patients&error=" . urlencode("Thiếu ID bệnh nhân"));
            exit;
        }

        $patient = $this->patientModel->getPatientByID($id);
        if (!$patient) {
            header("Location: index.php?url=patients&error=" . urlencode("Bệnh nhân không tồn tại"));
            exit;
        }
        require_once __DIR__ . '/../../views/Admin/patients/edit.php';
    }

    public function viewPatient()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: index.php?url=patients&error=" . urlencode("Thiếu ID bệnh nhân"));
            exit;
        }

        $patient = $this->patientModel->getPatientByID($id);
        if (!$patient) {
            header("Location: index.php?url=patients&error=" . urlencode("Bệnh nhân không tồn tại"));
            exit;
        }

        $activeAdmission  = $this->patientModel->getActiveAdmission($id);
        $admissions       = $this->patientModel->getAdmissionsByPatientId($id);

        require_once __DIR__ . '/../../views/Admin/patients/show.php';
    }


    public function updatePatient()
    {
        $patient_id = $_POST['id'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $date_of_birth = trim($_POST['date_of_birth'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $citizen_id = trim($_POST['citizen_id'] ?? '');

        $error = [];
        if ($full_name === '') {
            $error[] = 'Họ tên không được để trống';
        }

        if ($date_of_birth === '') {
            $error[] = 'Ngày sinh không được để trống';
        }

        if ($gender === '' || !in_array($gender, ['Nam', 'Nữ', 'Khác'])) {
            $error[] = 'Giới tính không hợp lệ';
        }

        if ($citizen_id !== '' && !preg_match('/^[0-9]{12}$/', $citizen_id)) {
            $error[] = 'CCCD không hợp lệ (phải gồm 12 chữ số)';
        }

        if ($citizen_id !== '' && empty($error) && $this->patientModel->findByCitizenIdExceptID($citizen_id, $patient_id)) {
            $error[] = 'CCCD này đã thuộc về một hồ sơ bệnh nhân khác';
        }

        if (empty($error)) {
            $data = [
                'full_name' => $full_name,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'phone' => $phone,
                'address' => $address,
                'citizen_id' => $citizen_id
            ];
            $result = $this->patientModel->updatePatient($patient_id, $data);

            if ($result) {
                AuditLogger::log($_SESSION['user_id'] ?? null, 'UPDATE', 'PATIENT', "Cập nhật bệnh nhân ID: $patient_id");
                header("Location: index.php?url=patients&success=" . urlencode("Cập nhật bệnh nhân thành công"));
                exit;
            } else {
                $error[] = 'Có lỗi xảy ra khi cập nhật';
            }
        }

        $patient = $this->patientModel->getPatientByID($patient_id);
        require_once __DIR__ . '/../../views/Admin/patients/edit.php';
    }

    public function deletePatient()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: index.php?url=patients&error=Thiếu ID bệnh nhân");
            exit;
        }

        $patient = $this->patientModel->getPatientByID($id);
        if (!$patient) {
            header("Location: index.php?url=patients&error=Bệnh nhân không tồn tại");
            exit;
        }

        $result = $this->patientModel->deletePatient($id);
        if ($result) {
            AuditLogger::log($_SESSION['user_id'] ?? null, 'DELETE', 'PATIENT', 'Xóa bệnh nhân: ' . ($patient['full_name'] ?? 'ID:' . $id));
            header("Location: index.php?url=patients&success=Xóa bệnh nhân thành công");
            exit;
        } else {
            header("Location: index.php?url=patients&error=Lỗi khi xóa bệnh nhân");
            exit;
        }
    }
}
