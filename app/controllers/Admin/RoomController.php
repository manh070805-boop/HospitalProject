<?php
require_once __DIR__ . '/../../models/Admin/RoomModel.php';
require_once __DIR__ . '/../../models/Admin/BedModel.php';
require_once __DIR__ . '/../../core/AuditLogger.php';

class RoomController
{
    private $roomModel;
    private $bedModel;

    public function __construct()
    {
        $this->roomModel = new RoomModel();
        $this->bedModel = new BedModel();
    }

    public function index()
    {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = 7;
        $offset = ($page - 1) * $limit;

        $totalRooms = $this->roomModel->countRooms($keyword, $status);
        $totalPages = ceil($totalRooms / $limit);

        if ($totalPages > 0 && $page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        $rooms = $this->roomModel->getRoomsByPage($keyword, $status, $limit, $offset);
        $statuses = $this->roomModel->getStatuses();
        require_once __DIR__ . '/../../views/Admin/rooms/index.php';
    }

    public function show()
    {
        if (!isset($_GET['id'])) {
            header("Location: index.php?url=rooms&error=" . urlencode("Thiếu ID phòng"));
            exit;
        }

        $id = $_GET['id'];
        $room = $this->roomModel->getRoomByID($id);

        if (!$room) {
            header("Location: index.php?url=rooms&error=" . urlencode("Phòng không tồn tại"));
            exit;
        }

        $roomStats = $this->roomModel->getRoomStatsByID($id);
        $beds = $this->bedModel->getBedsByRoom($id);
        $roomStatuses = $this->roomModel->getStatuses();
        $bedStatuses = $this->bedModel->getStatuses();
        require_once __DIR__ . '/../../views/Admin/rooms/show.php';
    }

    public function showCreateForm()
    {
        $departments = $this->roomModel->getAllDepartments();
        $statuses = $this->roomModel->getStatuses();
        require_once __DIR__ . '/../../views/Admin/rooms/create.php';
    }

    public function storeRoom()
    {
        $department_id = trim($_POST['department_id'] ?? '');
        $room_code = trim($_POST['room_code'] ?? '');
        $room_name = trim($_POST['room_name'] ?? '');
        $room_type = trim($_POST['room_type'] ?? '');
        $status = trim($_POST['status'] ?? '');

        $error = [];

        if ($department_id === '') {
            $error[] = 'Vui lòng chọn khoa';
        }
        if ($room_code === '') {
            $error[] = 'Mã phòng không được để trống';
        } elseif (strlen($room_code) > 20) {
            $error[] = 'Mã phòng không được vượt quá 20 ký tự';
        } elseif ($this->roomModel->findByRoomCode($room_code)) {
            $error[] = 'Mã phòng đã tồn tại';
        }
        if ($room_name !== '' && mb_strlen($room_name) > 100) {
            $error[] = 'Tên phòng không được vượt quá 100 ký tự';
        }
        if ($room_type !== '' && mb_strlen($room_type) > 50) {
            $error[] = 'Loại phòng không được vượt quá 50 ký tự';
        }
        if (!in_array($status, ['active', 'inactive', 'maintenance'])) {
            $error[] = 'Trạng thái không hợp lệ';
        }

        if (empty($error)) {
            $data = [
                'department_id' => $department_id,
                'room_code' => $room_code,
                'room_name' => $room_name !== '' ? $room_name : null,
                'room_type' => $room_type !== '' ? $room_type : null,
                'status' => $status
            ];
            $result = $this->roomModel->createRoom($data);
            if ($result) {
                AuditLogger::log($_SESSION['user_id'] ?? null, 'CREATE', 'ROOM', 'Tạo phòng mới: ' . $room_code);
                header("Location: index.php?url=rooms&success=" . urlencode("Thêm phòng thành công"));
                exit;
            } else {
                $error[] = 'Có lỗi xảy ra khi thêm phòng';
            }
        }
        $departments = $this->roomModel->getAllDepartments();
        $statuses = $this->roomModel->getStatuses();
        require_once __DIR__ . '/../../views/Admin/rooms/create.php';
    }

    public function editRoom()
    {
        if (!isset($_GET['id'])) {
            header("Location: index.php?url=rooms&error=" . urlencode("Thiếu ID phòng"));
            exit;
        }

        $id = $_GET['id'];
        $room = $this->roomModel->getRoomByID($id);

        if (!$room) {
            header("Location: index.php?url=rooms&error=" . urlencode("Phòng không tồn tại"));
            exit;
        }

        $departments = $this->roomModel->getAllDepartments();
        $statuses = $this->roomModel->getStatuses();
        require_once __DIR__ . '/../../views/Admin/rooms/edit.php';
    }

    public function updateRoom()
    {
        $room_id = $_POST['id'] ?? '';
        $department_id = trim($_POST['department_id'] ?? '');
        $room_code = trim($_POST['room_code'] ?? '');
        $room_name = trim($_POST['room_name'] ?? '');
        $room_type = trim($_POST['room_type'] ?? '');
        $status = trim($_POST['status'] ?? '');

        $error = [];

        if ($department_id === '') {
            $error[] = 'Vui lòng chọn khoa';
        }
        if ($room_code === '') {
            $error[] = 'Mã phòng không được để trống';
        } elseif (strlen($room_code) > 20) {
            $error[] = 'Mã phòng không được vượt quá 20 ký tự';
        } elseif ($this->roomModel->findByRoomCodeExceptID($room_code, $room_id)) {
            $error[] = 'Mã phòng đã tồn tại';
        }
        if ($room_name !== '' && mb_strlen($room_name) > 100) {
            $error[] = 'Tên phòng không được vượt quá 100 ký tự';
        }
        if ($room_type !== '' && mb_strlen($room_type) > 50) {
            $error[] = 'Loại phòng không được vượt quá 50 ký tự';
        }
        if (!in_array($status, ['active', 'inactive', 'maintenance'])) {
            $error[] = 'Trạng thái không hợp lệ';
        }

        if (empty($error)) {
            $data = [
                'department_id' => $department_id,
                'room_code' => $room_code,
                'room_name' => $room_name !== '' ? $room_name : null,
                'room_type' => $room_type !== '' ? $room_type : null,
                'status' => $status
            ];

            $result = $this->roomModel->updateRoom($room_id, $data);

            if ($result) {
                AuditLogger::log($_SESSION['user_id'] ?? null, 'UPDATE', 'ROOM', 'Cập nhật phòng ID: ' . $room_id);
                header("Location: index.php?url=rooms&success=" . urlencode("Cập nhật phòng thành công"));
                exit;
            } else {
                $error[] = 'Có lỗi xảy ra khi cập nhật phòng';
            }
        }
        $room = [
            'room_id' => $room_id,
            'department_id' => $department_id,
            'room_code' => $room_code,
            'room_name' => $room_name,
            'room_type' => $room_type,
            'status' => $status
        ];
        $departments = $this->roomModel->getAllDepartments();
        $statuses = $this->roomModel->getStatuses();
        require_once __DIR__ . '/../../views/Admin/rooms/edit.php';
    }

    public function deleteRoom()
    {
        if (!isset($_POST['id'])) {
            header("Location: index.php?url=rooms&error=" . urlencode("Thiếu ID phòng"));
            exit;
        }

        $id = $_POST['id'];
        $room = $this->roomModel->getRoomByID($id);

        if (!$room) {
            header("Location: index.php?url=rooms&error=" . urlencode("Phòng không tồn tại"));
            exit;
        }

        if (!$this->roomModel->canDeleteRoom($id)) {
            header("Location: index.php?url=rooms&error=" . urlencode("Không thể xóa phòng đang có giường được sử dụng"));
            exit;
        }

        $result = $this->roomModel->deleteRoom($id);

        if ($result) {
            AuditLogger::log($_SESSION['user_id'] ?? null, 'DELETE', 'ROOM', 'Xóa phòng: ' . ($room['room_code'] ?? 'ID:' . $id));
            header("Location: index.php?url=rooms&success=" . urlencode("Xóa phòng thành công"));
            exit;
        } else {
            header("Location: index.php?url=rooms&error=" . urlencode("Lỗi khi xóa phòng"));
            exit;
        }
    }
    public function showCreateBedForm()
    {
        if (!isset($_GET['room_id'])) {
            header("Location: index.php?url=rooms&error=" . urlencode("Thiếu ID phòng"));
            exit;
        }

        $room_id = $_GET['room_id'];
        $room = $this->roomModel->getRoomByID($room_id);

        if (!$room) {
            header("Location: index.php?url=rooms&error=" . urlencode("Phòng không tồn tại"));
            exit;
        }

        $statuses = $this->bedModel->getStatuses();
        require_once __DIR__ . '/../../views/Admin/rooms/create-bed.php';
    }
    public function storeBed()
    {
        $room_id = trim($_POST['room_id'] ?? '');
        $bed_code = trim($_POST['bed_code'] ?? '');
        $status = trim($_POST['status'] ?? '');

        $error = [];
        $room = $this->roomModel->getRoomByID($room_id);

        if ($room_id === '' || !$room) {
            $error[] = 'Phòng không hợp lệ';
        }
        if ($bed_code === '') {
            $error[] = 'Mã giường không được để trống';
        } elseif (strlen($bed_code) > 20) {
            $error[] = 'Mã giường không được vượt quá 20 ký tự';
        } elseif ($this->bedModel->findByBedCodeInRoom($room_id, $bed_code)) {
            $error[] = 'Mã giường đã tồn tại trong phòng này';
        }
        if (!in_array($status, ['available', 'occupied', 'maintenance'])) {
            $error[] = 'Trạng thái không hợp lệ';
        }

        if (empty($error)) {
            $data = [
                'room_id' => $room_id,
                'bed_code' => $bed_code,
                'status' => $status
            ];

            $result = $this->bedModel->createBed($data);

            if ($result) {
                AuditLogger::log($_SESSION['user_id'] ?? null, 'CREATE', 'BED', 'Thêm giường: ' . $bed_code . ' - phòng ID ' . $room_id);
                header("Location: index.php?url=rooms/show&id=" . $room_id . "&success=" . urlencode("Thêm giường thành công"));
                exit;
            } else {
                $error[] = 'Có lỗi xảy ra khi thêm giường';
            }
        }
        $statuses = $this->bedModel->getStatuses();
        require_once __DIR__ . '/../../views/Admin/rooms/create-bed.php';
    }
    public function editBed()
    {
        if (!isset($_GET['id'])) {
            header("Location: index.php?url=rooms&error=" . urlencode("Thiếu ID giường"));
            exit;
        }

        $id = $_GET['id'];
        $bed = $this->bedModel->getBedByID($id);

        if (!$bed) {
            header("Location: index.php?url=rooms&error=" . urlencode("Giường không tồn tại"));
            exit;
        }

        $room = $this->roomModel->getRoomByID($bed['room_id']);
        $statuses = $this->bedModel->getStatuses();
        require_once __DIR__ . '/../../views/Admin/rooms/edit-bed.php';
    }
    public function updateBed()
    {
        $bed_id = $_POST['id'] ?? '';
        $room_id = trim($_POST['room_id'] ?? '');
        $bed_code = trim($_POST['bed_code'] ?? '');
        $status = trim($_POST['status'] ?? '');

        $error = [];
        $room = $this->roomModel->getRoomByID($room_id);

        if ($room_id === '' || !$room) {
            $error[] = 'Phòng không hợp lệ';
        }
        if ($bed_code === '') {
            $error[] = 'Mã giường không được để trống';
        } elseif (strlen($bed_code) > 20) {
            $error[] = 'Mã giường không được vượt quá 20 ký tự';
        } elseif ($this->bedModel->findByBedCodeInRoomExceptID($room_id, $bed_code, $bed_id)) {
            $error[] = 'Mã giường đã tồn tại trong phòng này';
        }
        if (!in_array($status, ['available', 'occupied', 'maintenance'])) {
            $error[] = 'Trạng thái không hợp lệ';
        }

        if (empty($error)) {
            $data = [
                'room_id' => $room_id,
                'bed_code' => $bed_code,
                'status' => $status
            ];

            $result = $this->bedModel->updateBed($bed_id, $data);

            if ($result) {
                AuditLogger::log($_SESSION['user_id'] ?? null, 'UPDATE', 'BED', 'Cập nhật giường ID: ' . $bed_id);
                header("Location: index.php?url=rooms/show&id=" . $room_id . "&success=" . urlencode("Cập nhật giường thành công"));
                exit;
            } else {
                $error[] = 'Có lỗi xảy ra khi cập nhật giường';
            }
        }
        $bed = [
            'bed_id' => $bed_id,
            'room_id' => $room_id,
            'bed_code' => $bed_code,
            'status' => $status
        ];
        $statuses = $this->bedModel->getStatuses();
        require_once __DIR__ . '/../../views/Admin/rooms/edit-bed.php';
    }

    public function deleteBed()
    {
        if (!isset($_POST['id'])) {
            header("Location: index.php?url=rooms&error=" . urlencode("Thiếu ID giường"));
            exit;
        }

        $id = $_POST['id'];
        $bed = $this->bedModel->getBedByID($id);

        if (!$bed) {
            header("Location: index.php?url=rooms&error=" . urlencode("Giường không tồn tại"));
            exit;
        }

        if (!$this->bedModel->canDeleteBed($id)) {
            header("Location: index.php?url=rooms/show&id=" . $bed['room_id'] . "&error=" . urlencode("Không thể xóa giường đang có bệnh nhân"));
            exit;
        }

        $result = $this->bedModel->deleteBed($id);

        if ($result) {
            AuditLogger::log($_SESSION['user_id'] ?? null, 'DELETE', 'BED', 'Xóa giường: ' . ($bed['bed_code'] ?? 'ID:' . $id));
            header("Location: index.php?url=rooms/show&id=" . $bed['room_id'] . "&success=" . urlencode("Xóa giường thành công"));
            exit;
        } else {
            header("Location: index.php?url=rooms/show&id=" . $bed['room_id'] . "&error=" . urlencode("Lỗi khi xóa giường"));
            exit;
        }
    }




}


?>