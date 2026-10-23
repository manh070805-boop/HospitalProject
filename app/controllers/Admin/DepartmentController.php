<?php
require_once __DIR__ . '/../../models/Admin/DepartmentModel.php';
require_once __DIR__ . '/../../core/AuditLogger.php';
class DepartmentController
{
    private $departmentModel;
    public function __construct()
    {
        $this->departmentModel = new DepartmentModel();
    }
    public function index()
    {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = 5;
        $offset = ($page - 1) * $limit;
        $totalDepartments = $this->departmentModel->countDepartments($keyword);
        $totalPages = ceil($totalDepartments / $limit);
        if ($totalPages > 0 && $page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }
        $departments = $this->departmentModel->getDepartmentsByPage($keyword, $limit, $offset);
        require_once __DIR__ . '/../../views/Admin/departments/index.php';
    }
    public function showDepartment()
    {
        require_once __DIR__ . '/../../views/Admin/departments/create.php';
    }
    public function storeDepartment()
    {
        $department_name = trim($_POST['department_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $error = [];
        if ($department_name === '') {
            $error[] = 'Tên khoa không được để trống';
        } elseif (strlen($department_name) > 50) {
            $error[] = 'Tên khoa không được vượt quá 50 kí tự';
        } elseif ($this->departmentModel->findByDepartmentName($department_name)) {
            $error[] = 'Tên khoa đã tồn tại';
        }
        if ($description === '') {
            $error[] = 'Mô tả không được để trống';
        } elseif (strlen($description) > 200) {
            $error[] = 'Mô tả không được vượt quá 200 kí tự';
        }
        if (empty($error)) {
            $data = [
                'department_name' => $department_name,
                'description' => $description
            ];
            $result = $this->departmentModel->createDepartment($data);
            if ($result) {
                AuditLogger::log($_SESSION['user_id'] ?? null, 'CREATE', 'DEPARTMENT', 'Thêm khoa: ' . $department_name);
                header("Location: index.php?url=departments&success=" . urlencode("Thêm khoa/phòng thành công"));
                exit;
            } else {
                $error[] = 'Có lỗi xảy ra khi thêm khoa/phòng';
            }
        }
        require_once __DIR__ . '/../../views/Admin/departments/create.php';
    }
    public function editDepartment()
    {
        if (!isset($_GET['id'])) {
            header("Location: index.php?url=departments&error=" . urlencode("Thiếu ID khoa/phòng"));
            exit;
        }
        $id = $_GET['id'];
        $department = $this->departmentModel->getDepartmentByID($id);
        if (!$department) {
            header("Location: index.php?url=departments&error=" . urlencode("Khoa/phòng không tồn tại"));
            exit;
        }
        require_once __DIR__ . '/../../views/Admin/departments/edit.php';
    }
    public function updateDepartment()
    {
        $department_id = $_POST['id'] ?? '';
        $department_name = trim($_POST['department_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $error = [];
        if ($department_name === '') {
            $error[] = 'Tên khoa không được để trống';
        } elseif (strlen($department_name) > 50) {
            $error[] = 'Tên khoa không được vượt quá 50 kí tự';
        } elseif ($this->departmentModel->findByDepartmentNameExceptID($department_name, $department_id)) {
            $error[] = 'Tên khoa đã tồn tại';
        }
        if ($description === '') {
            $error[] = 'Mô tả không được để trống';
        } elseif (strlen($description) > 200) {
            $error[] = 'Mô tả không được vượt quá 200 kí tự';
        }

        if (empty($error)) {
            $data = [
                'department_id' => $department_id,
                'department_name' => $department_name,
                'description' => $description
            ];
            $result = $this->departmentModel->updateDepartment($department_id, $data);

            if ($result) {
                AuditLogger::log($_SESSION['user_id'] ?? null, 'UPDATE', 'DEPARTMENT', 'Cập nhật khoa ID: ' . $department_id);
                header("Location: index.php?url=departments&success=" . urlencode("Cập nhật khoa/phòng thành công"));
                exit;
            } else {
                $error[] = 'Có lỗi xảy ra khi cập nhật khoa/phòng';
            }
        }
        require_once __DIR__ . '/../../views/Admin/departments/edit.php';
    }
    public function deleteDepartment()
    {
        if (!isset($_GET['id'])) {
            header("Location: index.php?url=departments&error=Thiếu ID khoa/phòng");
            exit;
        }
        $id = $_GET['id'];
        $department = $this->departmentModel->getDepartmentByID($id);
        if (!$department) {
            header("Location: index.php?url=departments&error=Khoa/phòng không tồn tại");
            exit;
        }
        $result = $this->departmentModel->deleteDepartment($id);
        if ($result) {
            AuditLogger::log($_SESSION['user_id'] ?? null, 'DELETE', 'DEPARTMENT', 'Xóa khoa: ' . ($department['department_name'] ?? 'ID:' . $id));
            header("Location: index.php?url=departments&success=Xóa khoa/phòng thành công");
            exit;
        } else {
            header("Location: index.php?url=departments&error=Lỗi khi xóa");
            exit;
        }
    }
}

?>
