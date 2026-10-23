<?php

class BedModel
{
    private $conn;
    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }
    public function createBed($data)
    {
        $sql = "insert into beds (room_id, bed_code, status, is_active)
                values (:room_id, :bed_code, :status, 1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':room_id', $data['room_id'], PDO::PARAM_INT);
        $stmt->bindParam(':bed_code', $data['bed_code']);
        $stmt->bindParam(':status', $data['status']);
        return $stmt->execute();
    }
    public function getBedByID($id)
    {
        $sql = "select * from beds where bed_id = :bed_id and is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':bed_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function updateBed($id, $data)
    {
        $sql = "update beds
                set room_id = :room_id,
                    bed_code = :bed_code,
                    status = :status
                where bed_id = :bed_id and is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':room_id', $data['room_id'], PDO::PARAM_INT);
        $stmt->bindParam(':bed_code', $data['bed_code']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':bed_id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function deleteBed($id)
    {
        $sql = "update beds set is_active = 0, status = 'available' where bed_id = :bed_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':bed_id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function getBedsByRoom($room_id)
    {
        $sql = "select b.*, p.full_name as patient_name, a.admission_id
                from beds b
                left join admissions a on a.bed_id = b.bed_id and a.status = 'active'
                left join patients p on a.patient_id = p.patient_id
                where b.room_id = :room_id and b.is_active = 1
                order by b.bed_code asc";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
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
    public function findByBedCodeInRoom($room_id, $bed_code)
    {
        $sql = "select * from beds
                where room_id = :room_id and bed_code = :bed_code";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
        $stmt->bindParam(':bed_code', $bed_code);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function findByBedCodeInRoomExceptID($room_id, $bed_code, $bed_id)
    {
        $sql = "select * from beds
                where room_id = :room_id and bed_code = :bed_code
                  and bed_id != :bed_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
        $stmt->bindParam(':bed_code', $bed_code);
        $stmt->bindParam(':bed_id', $bed_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function canDeleteBed($bed_id)
    {
        $sql = "select count(*) as total from admissions where bed_id = :bed_id and status <> 'discharged'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':bed_id', $bed_id, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($res['total'] ?? 0) === 0;
    }

    public function getStatuses()
    {
        return [
            'available' => ['label' => 'Sẵn sàng', 'icon' => 'tick-circle', 'class' => 'is-available'],
            'occupied' => ['label' => 'Có bệnh nhân', 'icon' => 'user-2', 'class' => 'is-occupied'],
            'maintenance' => ['label' => 'Bảo trì', 'icon' => 'setting-2', 'class' => 'is-maintenance'],
        ];
    }
}
?>