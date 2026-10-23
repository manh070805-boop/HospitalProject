<?php
require_once __DIR__ . '/../../models/Admin/UserModel.php';
require_once __DIR__ . '/../../core/AuditLogger.php';
class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $department_id = isset($_GET['department_id']) ? trim($_GET['department_id']) : '';
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $totalUsers = $this->userModel->countUsers($keyword, $status, $department_id);
        $totalPages = ceil($totalUsers / $limit);

        if ($totalPages > 0 && $page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }
        $users = $this->userModel->getUsersByPage($keyword, $status, $department_id, $limit, $offset);
        $departments = $this->userModel->getAllDepartments();

        require_once __DIR__ . '/../../views/Admin/users/index.php';
    }
    public function showCreateForm()
    {
        $roles = $this->userModel->getAllRoles();
        $departments = $this->userModel->getAllDepartments();
        require_once __DIR__ . '/../../views/Admin/users/create.php';
    }
    public function storeUser()
    {
        $full_name = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role_id = trim($_POST['role_id'] ?? '');
        $department_id = trim($_POST['department_id'] ?? '');
        $status = trim($_POST['status'] ?? '');

        $errors = [];
        if ($full_name === '') {
            $errors[] = 'Họ tên không được để trống';
        }
        if ($username === '') {
            $errors[] = 'Tên đăng nhập không được để trống';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Tên đăng nhập phải có ít nhất 3 ký tự';
        } elseif ($this->userModel->findByUsername($username)) {
            $errors[] = 'Tên đăng nhập đã tồn tại';
        }
        if ($password === '') {
            $errors[] = 'Mật khẩu không được để trống';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Mật khẩu phải có nhất 6 ký tự';
        }
        if ($role_id === '') {
            $errors[] = 'Vui lòng chọn vai trò';
        }
        if (!in_array($status, ['active', 'locked'])) {
            $errors[] = 'Trạng thái không hợp lệ.';
        }

        if (!empty($errors)) {
            $roles = $this->userModel->getAllRoles();
            $departments = $this->userModel->getAllDepartments();

            $oldData = [
                'full_name' => $full_name,
                'username' => $username,
                'role_id' => $role_id,
                'department_id' => $department_id,
                'status' => $status
            ];
            require_once __DIR__ . '/../../views/Admin/users/create.php';
            return;
        }
        $data = [
            'full_name' => $full_name,
            'username' => $username,
            'password' => $password,
            'role_id' => $role_id,
            'department_id' => $department_id !== '' ? $department_id : null,
            'status' => $status
        ];
        $result = $this->userModel->createUser($data);
        if ($result) {
            AuditLogger::log($_SESSION['auth_user']['user_id'] ?? null, 'CREATE', 'USER', 'Thêm người dùng: ' . $username);
            header('Location: index.php?url=users&success=Thêm người dùng thành công');
        } else {
            header('Location: index.php?url=users&error=Lỗi khi thêm người dùng');
        }
        exit;

    }
    public function editUser()
    {
        if (!isset($_GET['id'])) {
            header("Location: index.php?url=users&error=" . urlencode("Thiếu ID người dùng"));
            exit;
        }

        $id = $_GET['id'];
        $user = $this->userModel->getUserByID($id);
        if (!$user) {
            header("Location: index.php?url=users&error=" . urlencode("Người dùng không tồn tại"));
            exit;
        }
        $roles = $this->userModel->getAllRoles();
        $departments = $this->userModel->getAllDepartments();
        require_once __DIR__ . '/../../views/Admin/users/edit.php';
    }
    public function updateUser()
    {
        $user_id = $_POST['user_id'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role_id = $_POST['role_id'] ?? '';
        $department_id = trim($_POST['department_id'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $errors = [];
        if ($full_name === '') {
            $errors[] = 'Họ tên không được để trống';
        }
        if ($username === '') {
            $errors[] = 'Tên đăng nhập không được để trống';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Tên đăng nhập phải có ít nhất 3 ký tự';
        } elseif ($this->userModel->findByUsernameExceptID($username, $user_id)) {
            $errors[] = 'Tên đăng nhập đã tồn tại';
        }
        if ($password !== '' && strlen($password) < 6) {
            $errors[] = 'Nếu đổi mật khẩu, mật khẩu mới phải có ít nhất 6 ký tự.';
        }
        if ($role_id === '') {
            $errors[] = 'Vui lòng chọn vai trò';
        }
        if (!in_array($status, ['active', 'locked'])) {
            $errors[] = 'Trạng thái không hợp lệ';
        } elseif (isset($_SESSION['auth_user']['user_id']) && (int) $user_id === (int) $_SESSION['auth_user']['user_id'] && $status === 'locked') {
            $errors[] = 'Bạn không thể tự khóa tài khoản của chính mình';
        }
        if (!empty($errors)) {
            $roles = $this->userModel->getAllRoles();
            $departments = $this->userModel->getAllDepartments();

            $user = [
                'user_id' => $user_id,
                'full_name' => $full_name,
                'username' => $username,
                'role_id' => $role_id,
                'department_id' => $department_id,
                'status' => $status
            ];
            require_once __DIR__ . '/../../views/Admin/users/edit.php';
            return;
        }
        $data = [
            'user_id' => $user_id,
            'full_name' => $full_name,
            'username' => $username,
            'password' => $password,
            'role_id' => $role_id,
            'department_id' => $department_id !== '' ? $department_id : null,
            'status' => $status
        ];
        if ($password !== '') {
            $data['password'] = $password;
        }
        $result = $this->userModel->updateUser($user_id, $data);
        if ($result) {
            AuditLogger::log($_SESSION['auth_user']['user_id'] ?? null, 'UPDATE', 'USER', 'Cập nhật người dùng ID: ' . $user_id);
            header("Location: index.php?url=users&success=Cập nhật người dùng thành công");
            exit;
        } else {
            header("Location: index.php?url=users&error=Lỗi khi cập nhật");
            exit;
        }
    }
    public function lockUser(): void
    {
        if (!isset($_GET['id'])) {
            header("Location: index.php?url=users&error=" . urlencode("Thiếu ID người dùng"));
            exit;
        }

        $id = (int) $_GET['id'];

        if (isset($_SESSION['auth_user']['user_id']) && $id === (int) $_SESSION['auth_user']['user_id']) {
            header("Location: index.php?url=users&error=" . urlencode("Bạn không thể tự khóa tài khoản của chính mình"));
            exit;
        }

        $user = $this->userModel->getUserByID($id);
        if (!$user) {
            header("Location: index.php?url=users&error=" . urlencode("Người dùng không tồn tại"));
            exit;
        }

        if (strtolower($user['status']) === 'locked') {
            header("Location: index.php?url=users&error=" . urlencode("Tài khoản này đã bị khóa trước đó"));
            exit;
        }

        $result = $this->userModel->lockUser($id);
        if ($result) {
            AuditLogger::log($_SESSION['auth_user']['user_id'] ?? null, 'LOCK', 'USER', 'Khóa tài khoản: ' . ($user['username'] ?? 'ID:' . $id));
            header("Location: index.php?url=users&success=" . urlencode("Đã khóa tài khoản " . ($user['username'] ?? '')));
            exit;
        } else {
            header("Location: index.php?url=users&error=" . urlencode("Lỗi khi khóa tài khoản"));
            exit;
        }
    }
}

?>
