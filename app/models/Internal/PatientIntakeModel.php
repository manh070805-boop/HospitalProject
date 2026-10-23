<?php

class PatientIntakeModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }


    public function generatePatientCode(): string
    {
        $sql = "select top 1 patient_code from patients order by patient_id desc";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $last = $stmt->fetch(PDO::FETCH_ASSOC);

        $nextNum = $last ? (int) substr($last['patient_code'], 2) + 1 : 1;
        return 'BN' . sprintf('%04d', $nextNum);
    }



    public function findPatientsForIntake($keyword, $role, $userId, $deptId = null): array
    {
        $keyword = trim((string) $keyword);
        $isNurse = $role !== 'head_nurse';

        if ($keyword !== '') {
            $sql = "select p.*, active_admission.admission_id as active_admission_id
                    from patients p
                    outer apply (
                        select top 1 a.admission_id
                        from admissions a
                        left join beds b on a.bed_id = b.bed_id
                        left join rooms r on b.room_id = r.room_id
                        where a.patient_id = p.patient_id and a.status = 'active'
                        " . ($role === 'head_nurse' && $deptId ? "and r.department_id = :dept_id_active" : "") . "
                        order by a.admission_date desc
                    ) active_admission
                    where (
                        p.patient_code = :kw_code
                        or p.full_name like :kw_like
                        or p.phone = :kw_phone
                        or p.citizen_id = :kw_citizen_id
                    )
                    order by
                        case when p.citizen_id = :kw_citizen_id_order then 0 else 1 end,
                        p.patient_id desc";
        } elseif ($isNurse) {
            $sql = "select top 5 p.*, a.admission_id as active_admission_id
                    from patients p
                    join admissions a on a.patient_id = p.patient_id
                    where a.status = 'active'
                    and a.assigned_nurse_id = :user_id
                    order by a.admission_date desc, p.patient_id desc";
        } else {
            $sql = "select p.*, a.admission_id as active_admission_id
                    from patients p
                    join admissions a on a.patient_id = p.patient_id and a.status = 'active'
                    left join beds b on a.bed_id = b.bed_id
                    left join rooms r on b.room_id = r.room_id
                    " . ($deptId ? "where r.department_id = :dept_id" : "") . "
                    order by a.admission_date desc, p.patient_id desc";
        }

        $stmt = $this->conn->prepare($sql);

        if ($keyword === '' && $isNurse) {
            $stmt->bindValue(':user_id', (int) $userId, PDO::PARAM_INT);
        }
        if ($keyword === '' && !$isNurse && $deptId) {
            $stmt->bindValue(':dept_id', (int) $deptId, PDO::PARAM_INT);
        }

        if ($keyword !== '') {
            $stmt->bindValue(':kw_code', $keyword);
            $stmt->bindValue(':kw_like', '%' . $keyword . '%');
            $stmt->bindValue(':kw_phone', $keyword);
            $stmt->bindValue(':kw_citizen_id', $keyword);
            $stmt->bindValue(':kw_citizen_id_order', $keyword);
            if (!$isNurse && $deptId) {
                $stmt->bindValue(':dept_id_active', (int) $deptId, PDO::PARAM_INT);
            }
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByCitizenId(string $citizenId): ?array
    {
        $sql = "select * from patients where citizen_id = :citizen_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':citizen_id', $citizenId);
        $stmt->execute();

        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        return $patient ?: null;
    }

    public function getPatientById($id): ?array
    {
        $sql = "select * from patients where patient_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getActiveAdmissionForPatient($patientId, string $role, $userId, $deptId = null): ?array
    {
        $sql = "select top 1
                    a.admission_id,
                    a.admission_date,
                    a.status,
                    b.bed_code,
                    r.room_name
                from admissions a
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                where a.patient_id = :patient_id
                  and a.status = 'active'";

        if ($role !== 'head_nurse') {
            $sql .= " and a.assigned_nurse_id = :user_id";
        } elseif ($deptId) {
            $sql .= " and r.department_id = :dept_id";
        }

        $sql .= " order by a.admission_date desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':patient_id', (int) $patientId, PDO::PARAM_INT);
        if ($role !== 'head_nurse') {
            $stmt->bindValue(':user_id', (int) $userId, PDO::PARAM_INT);
        } elseif ($deptId) {
            $stmt->bindValue(':dept_id', (int) $deptId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAdmissionsForPatient($patientId, string $role, $userId, $deptId = null): array
    {
        $sql = "select a.admission_id,
                    a.admission_date,
                    a.status,
                    b.bed_code,
                    r.room_name,
                    u.full_name as nurse_name
                from admissions a
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                left join users u on a.assigned_nurse_id = u.user_id
                where a.patient_id = :patient_id";

        if ($role !== 'head_nurse') {
            $sql .= " and a.assigned_nurse_id = :user_id";
        } elseif ($deptId) {
            $sql .= " and r.department_id = :dept_id";
        }

        $sql .= " order by a.admission_date desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':patient_id', (int) $patientId, PDO::PARAM_INT);
        if ($role !== 'head_nurse') {
            $stmt->bindValue(':user_id', (int) $userId, PDO::PARAM_INT);
        } elseif ($deptId) {
            $stmt->bindValue(':dept_id', (int) $deptId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isDuplicate($phone): bool
    {
        $sql = "select count(*) as total from patients where phone = :phone";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['total'] ?? 0) > 0;
    }
    public function createPatient($data): string|false
    {
        $sql = "insert into patients (patient_code, full_name, date_of_birth, gender, phone, address, citizen_id) 
                values (:patient_code, :full_name, :date_of_birth, :gender, :phone, :address, :citizen_id)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':patient_code', $data['patient_code']);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':date_of_birth', $data['date_of_birth']);
        $stmt->bindParam(':gender', $data['gender']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':citizen_id', $data['citizen_id']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
}
