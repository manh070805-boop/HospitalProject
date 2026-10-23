<?php

class PatientModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }

    public function generatePatientCode()
    {
        $sql = "select top 1 patient_code from patients order by patient_id desc";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $last = $stmt->fetch(PDO::FETCH_ASSOC);

        $nextNum = $last ? (int) substr($last['patient_code'], 2) + 1 : 1;

        return 'BN' . sprintf('%04d', $nextNum);
    }

    public function getPatientByID($id)
    {
        $sql = "select * from patients where patient_id = :patient_id and deleted_at is null";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':patient_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByPatientCodeExceptID($code, $id)
    {
        $sql = "select * from patients where patient_code = :patient_code and patient_id != :patient_id and deleted_at is null";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':patient_code', $code);
        $stmt->bindParam(':patient_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePatient($id, $data)
    {
        $sql = "update patients set full_name = :full_name, date_of_birth = :date_of_birth,
                gender = :gender, phone = :phone, address = :address, citizen_id = :citizen_id
                where patient_id = :patient_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':date_of_birth', $data['date_of_birth']);
        $stmt->bindParam(':gender', $data['gender']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':citizen_id', $data['citizen_id']);
        $stmt->bindParam(':patient_id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function findByCitizenIdExceptID(string $citizenId, $id)
    {
        $sql = "select * from patients where citizen_id = :citizen_id and patient_id != :patient_id and deleted_at is null";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':citizen_id', $citizenId);
        $stmt->bindParam(':patient_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deletePatient($id)
    {
        $sql = "update patients set deleted_at = GETDATE() where patient_id = :patient_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':patient_id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function countPatients($keyword = '', $gender = '')
    {
        $sql = "select count(*) as total from patients";
        $conditions = ["deleted_at is null"];
        $params = [];

        if (!empty($keyword)) {
            $conditions[] = "(patient_code like :kw1 or full_name like :kw2 or phone like :kw3 or address like :kw4 or citizen_id like :kw5)";
            $params[':kw1'] = '%' . $keyword . '%';
            $params[':kw2'] = '%' . $keyword . '%';
            $params[':kw3'] = '%' . $keyword . '%';
            $params[':kw4'] = '%' . $keyword . '%';
            $params[':kw5'] = '%' . $keyword . '%';
        }

        if (!empty($gender)) {
            $conditions[] = "gender = :gender";
            $params[':gender'] = $gender;
        }

        if (!empty($conditions)) {
            $sql .= " where " . implode(" and ", $conditions);
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getPatientsByPage($keyword = '', $gender = '', $limit = 5, $offset = 0)
    {
        $sql = "select * from patients";
        $conditions = ["deleted_at is null"];
        $params = [];

        if (!empty($keyword)) {
            $conditions[] = "(patient_code like :kw1 or full_name like :kw2 or phone like :kw3 or address like :kw4 or citizen_id like :kw5)";
            $params[':kw1'] = '%' . $keyword . '%';
            $params[':kw2'] = '%' . $keyword . '%';
            $params[':kw3'] = '%' . $keyword . '%';
            $params[':kw4'] = '%' . $keyword . '%';
            $params[':kw5'] = '%' . $keyword . '%';
        }

        if (!empty($gender)) {
            $conditions[] = "gender = :gender";
            $params[':gender'] = $gender;
        }

        if (!empty($conditions)) {
            $sql .= " where " . implode(" and ", $conditions);
        }

        $sql .= " order by patient_id desc offset :offset rows fetch next :limit rows only";
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAdmissionsByPatientId($patientId)
    {
        $sql = "select a.admission_id, a.admission_date, a.status,
                       d.department_name,
                       b.bed_code as bed_number, r.room_name as room_number,
                       u_doctor.full_name as doctor_name
                from admissions a
                left join beds b on b.bed_id = a.bed_id
                left join rooms r on r.room_id = b.room_id
                left join departments d on d.department_id = r.department_id
                left join users u_doctor on u_doctor.user_id = a.assigned_doctor_id
                where a.patient_id = :patient_id
                order by a.admission_date desc";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':patient_id', $patientId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveAdmission($patientId)
    {
        $sql = "select top 1 a.admission_id, a.admission_date, a.status,
                       d.department_name,
                       b.bed_code as bed_number, r.room_name as room_number,
                       u_doctor.full_name as doctor_name
                from admissions a
                left join beds b on b.bed_id = a.bed_id
                left join rooms r on r.room_id = b.room_id
                left join departments d on d.department_id = r.department_id
                left join users u_doctor on u_doctor.user_id = a.assigned_doctor_id
                where a.patient_id = :patient_id
                  and a.status in ('active', 'stable')
                order by a.admission_date desc";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':patient_id', $patientId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function countPatientStats()
    {
        $sql = "select gender, count(*) as count from patients where deleted_at is null group by gender";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $counts = [
            'total' => 0,
            'Nam' => 0,
            'Nữ' => 0,
        ];

        foreach ($results as $row) {
            $g = $row['gender'] ?? '';
            if (isset($counts[$g])) {
                $counts[$g] += (int) $row['count'];
            }
            $counts['total'] += (int) $row['count'];
        }
        return $counts;
    }
}
?>
