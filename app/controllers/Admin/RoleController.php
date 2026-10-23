<?php
require_once __DIR__ . '/../../models/Admin/RoleModel.php';
require_once __DIR__ . '/../../core/AuditLogger.php';
class RoleController
{
    private $roleModel;
    public function __construct()
    {
        $this->roleModel = new RoleModel();
    }
    public function index()
    {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = 5;
        $offset = ($page - 1) * $limit;
        $totalRoles = $this->roleModel->countRoles($keyword);
        $totalPages = ceil($totalRoles / $limit);
        if ($totalPages > 0 && $page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }
        $roles = $this->roleModel->getRolesByPage($keyword, $limit, $offset);
        require_once __DIR__ . '/../../views/Admin/roles/index.php';
    }
    public function showRole()
    {
        require_once __DIR__ . '/../../views/Admin/roles/create.php';
    }
    public function storeRole()
    {
        $role_name = trim($_POST['role_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $error = [];
        if ($role_name === '') {
            $error[] = 'Tên chức vụ không được để trống';
        } elseif (strlen($role_name) > 50) {
            $error[] = 'Tên chức vụ không được vượt quá 50 kí tự';
        } elseif ($this->roleModel->findByRoleName($role_name)) {
            $error[] = 'Tên chức vụ đã tồn tại';
        }
        if ($description === '') {
            $error[] = 'Mô tả không được để trống';
        } elseif (strlen($description) > 200) {
            $error[] = 'Mô tả không được vượt quá 200 kí tự';
        }
        if (empty($error)) {
            $data = [
                'role_name' => $role_name,
                'description' => $description
            ];
            $result = $this->roleModel->createRole($data);
            if ($result) {
                AuditLogger::log($_SESSION['user_id'] ?? null, 'CREATE', 'ROLE', 'Thêm chức vụ: ' . $role_name);
                header("Location: index.php?url=roles&success=" . urlencode("Thêm chức vụ thành công"));
                exit;
            } else {
                $error[] = 'Có lỗi xảy ra khi thêm chức vụ';
            }
        }
        require_once __DIR__ . '/../../views/Admin/roles/create.php';
    }
    public function editRole()
    {
        if (!isset($_GET['id'])) {
            header("Location: index.php?url=roles&error=" . urlencode("Thiếu ID chức vụ"));
            exit;
        }
        $id = $_GET['id'];
        $role = $this->roleModel->getRoleByID($id);
        if (!$role) {
            header("Location: index.php?url=roles&error=" . urlencode("Chức vụ không tồn tại"));
            exit;
        }
        require_once __DIR__ . '/../../views/Admin/roles/edit.php';
    }
    public function updateRole()
    {
        $role_id = $_POST['id'] ?? '';
        $role_name = trim($_POST['role_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $error = [];
        if ($role_name === '') {
            $error[] = 'Tên chức vụ không được để trống';
        } elseif (strlen($role_name) > 50) {
            $error[] = 'Tên chức vụ không được vượt quá 50 kí tự';
        } elseif ($this->roleModel->findByRoleNameExceptID($role_name, $role_id)) {
            $error[] = 'Tên chức vụ đã tồn tại';
        }
        if ($description === '') {
            $error[] = 'Mô tả không được để trống';
        } elseif (strlen($description) > 200) {
            $error[] = 'Mô tả không được vượt quá 200 kí tự';
        }

        if (empty($error)) {
            $data = [
                'role_id' => $role_id,
                'role_name' => $role_name,
                'description' => $description
            ];
            $result = $this->roleModel->updateRole($role_id, $data);

            if ($result) {
                AuditLogger::log($_SESSION['user_id'] ?? null, 'UPDATE', 'ROLE', 'Cập nhật chức vụ ID: ' . $role_id);
                header("Location: index.php?url=roles&success=" . urlencode("Cập nhật chức vụ thành công"));
                exit;
            } else {
                $error[] = 'Có lỗi xảy ra khi cập nhật chức vụ';
            }
        }
        require_once __DIR__ . '/../../views/Admin/roles/edit.php';
    }
    public function deleteRole()
    {
        if (!isset($_GET['id'])) {
            header("Location: index.php?url=roles&error=Thiếu ID chức vụ");
            exit;
        }
        $id = $_GET['id'];
        $role = $this->roleModel->getRoleByID($id);
        if (!$role) {
            header("Location: index.php?url=roles&error=Chức vụ không tồn tại");
            exit;
        }
        $result = $this->roleModel->deleteRole($id);
        if ($result) {
            AuditLogger::log($_SESSION['user_id'] ?? null, 'DELETE', 'ROLE', 'Xóa chức vụ: ' . ($role['role_name'] ?? 'ID:' . $id));
            header("Location: index.php?url=roles&success=Xóa chức vụ thành công");
            exit;
        } else {
            header("Location: index.php?url=roles&error=Lỗi khi xóa");
            exit;
        }
    }
}

?>

