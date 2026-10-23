<?php

class RoomModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }

    public function createRoom($data)
    {
        $sql = "insert into rooms (department_id, room_code, room_name, room_type, status, is_active)
                values (:department_id, :room_code, :room_name, :room_type, :status, 1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':department_id', $data['department_id'], PDO::PARAM_INT);
        $stmt->bindParam(':room_code', $data['room_code']);
        $stmt->bindParam(':room_name', $data['room_name']);
        $stmt->bindParam(':room_type', $data['room_type']);
        $stmt->bindParam(':status', $data['status']);
        return $stmt->execute();
    }

    public function getRoomByID($id)
    {
        $sql = "select r.*, d.department_name from rooms r
                left join departments d on r.department_id = d.department_id
                where r.room_id = :room_id and r.is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':room_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateRoom($id, $data)
    {
        $sql = "update rooms set department_id = :department_id, room_code = :room_code,
                room_name = :room_name, room_type = :room_type, status = :status
                where room_id = :room_id and is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':department_id', $data['department_id'], PDO::PARAM_INT);
        $stmt->bindParam(':room_code', $data['room_code']);
        $stmt->bindParam(':room_name', $data['room_name']);
        $stmt->bindParam(':room_type', $data['room_type']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':room_id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteRoom($id)
    {
        $sql = "update rooms set is_active = 0 where room_id = :room_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':room_id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function findByRoomCode($room_code)
    {
        $sql = "select * from rooms where room_code = :room_code";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':room_code', $room_code);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByRoomCodeExceptID($room_code, $room_id)
    {
        $sql = "select * from rooms where room_code = :room_code and room_id != :room_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':room_code', $room_code);
        $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function countRooms($keyword = '', $status = '')
    {
        $sql = "select count(*) as total from rooms r
                left join departments d on r.department_id = d.department_id
                where r.is_active = 1";
        $params = [];
        $condition = [];
        if (!empty($keyword)) {
            $condition[] = "(r.room_code like :kw1 or r.room_name like :kw2 or d.department_name like :kw3)";
            $params[':kw1'] = '%' . $keyword . '%';
            $params[':kw2'] = '%' . $keyword . '%';
            $params[':kw3'] = '%' . $keyword . '%';
        }
        if (!empty($status)) {
            $condition[] = "r.status = :status";
            $params[':status'] = $status;
        }
        if (!empty($condition)) {
            $sql .= " and " . implode(" and ", $condition);
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getRoomsByPage($keyword = '', $status = '', $limit = 7, $offset = 0)
    {
        $sql = "select r.*, d.department_name,
                (select count(*) from beds b where b.room_id = r.room_id and b.is_active = 1) as capacity,
                (select count(*) from beds b where b.room_id = r.room_id and b.status = 'occupied' and b.is_active = 1) as occupancy
                from rooms r
                left join departments d on r.department_id = d.department_id
                where r.is_active = 1";
        $params = [];
        $condition = [];
        if (!empty($keyword)) {
            $condition[] = "(r.room_code like :kw1 or r.room_name like :kw2 or d.department_name like :kw3)";
            $params[':kw1'] = '%' . $keyword . '%';
            $params[':kw2'] = '%' . $keyword . '%';
            $params[':kw3'] = '%' . $keyword . '%';
        }
        if (!empty($status)) {
            $condition[] = "r.status = :status";
            $params[':status'] = $status;
        }
        if (!empty($condition)) {
            $sql .= " and " . implode(" and ", $condition);
        }
        $sql .= " order by r.room_id desc offset :offset rows fetch next :limit rows only";
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllDepartments()
    {
        $sql = "select department_id, department_name from departments order by department_name asc";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countBedsByRoom($room_id)
    {
        $sql = "select count(*) as total from beds where room_id = :room_id and is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function countOccupiedBedsByRoom($room_id)
    {
        $sql = "select count(*) as total from beds where room_id = :room_id and status = 'occupied' and is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function canDeleteRoom($room_id)
    {
        return $this->countOccupiedBedsByRoom($room_id) == 0;
    }

    public function getRoomStatsByID($room_id)
    {
        return [
            'capacity' => $this->countBedsByRoom($room_id),
            'occupancy' => $this->countOccupiedBedsByRoom($room_id)
        ];
    }

    public function getStatuses()
    {
        return [
            'active' => ['label' => 'Hoạt động', 'icon' => 'tick-circle', 'class' => 'status-active'],
            'inactive' => ['label' => 'Ngừng hoạt động', 'icon' => 'x-circle', 'class' => 'status-inactive'],
            'maintenance' => ['label' => 'Bảo trì', 'icon' => 'setting-2', 'class' => 'status-maintenance'],
        ];
    }
}
