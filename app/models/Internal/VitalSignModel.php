<?php

class VitalSignModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }

    public function createRecord(array $data): int
    {
        $sql = "insert into vital_sign_records
                    (admission_id, temperature, pulse, blood_pressure_systolic, blood_pressure_diastolic,
                     respiratory_rate, spo2, symptoms_note, recorded_by, recorded_at)
                values
                    (:admission_id, :temperature, :pulse, :bp_sys, :bp_dia,
                     :rr, :spo2, :symptoms_note, :recorded_by, GETDATE())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', (int) $data['admission_id'], PDO::PARAM_INT);
        $stmt->bindValue(':temperature', $data['temperature']);
        $stmt->bindValue(':pulse', (int) $data['pulse'], PDO::PARAM_INT);
        $stmt->bindValue(':bp_sys', (int) $data['blood_pressure_systolic'], PDO::PARAM_INT);
        $stmt->bindValue(':bp_dia', (int) $data['blood_pressure_diastolic'], PDO::PARAM_INT);
        $stmt->bindValue(':rr', (int) $data['respiratory_rate'], PDO::PARAM_INT);
        $stmt->bindValue(':spo2', (int) $data['spo2'], PDO::PARAM_INT);
        $stmt->bindValue(':symptoms_note', $data['symptoms_note'] ?? '');
        $stmt->bindValue(':recorded_by', (int) $data['recorded_by'], PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->conn->lastInsertId();
    }

    public function getByAdmission(int $admissionId): array
    {
        $sql = "select v.*, u.full_name as recorded_by_name
                from vital_sign_records v
                left join users u on v.recorded_by = u.user_id
                where v.admission_id = :admission_id
                order by v.recorded_at desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatestByAdmission(int $admissionId): ?array
    {
        $sql = "select top 1 v.*, u.full_name as recorded_by_name
                from vital_sign_records v
                left join users u on v.recorded_by = u.user_id
                where v.admission_id = :admission_id
                order by v.recorded_at desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getById(int $recordId): ?array
    {
        $sql = "select * from vital_sign_records where record_id = :record_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':record_id', $recordId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getActiveAdmissionsForVitals(int $userId, string $role, ?int $deptId, string $keyword = ''): array
    {
        $keyword = trim($keyword);

        $sql = "select
                    a.admission_id,
                    a.status,
                    a.admission_date,
                    p.patient_id,
                    p.patient_code,
                    p.full_name as patient_name,
                    p.citizen_id,
                    p.phone,
                    p.gender,
                    b.bed_code,
                    r.room_name,
                    d.department_name,
                    latest.record_id as latest_record_id,
                    latest.temperature,
                    latest.pulse,
                    latest.blood_pressure_systolic,
                    latest.blood_pressure_diastolic,
                    latest.respiratory_rate,
                    latest.spo2,
                    latest.symptoms_note,
                    latest.recorded_at,
                    latest.recorded_by,
                    u.full_name as recorded_by_name
                from admissions a
                join patients p on a.patient_id = p.patient_id
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                left join departments d on r.department_id = d.department_id
                outer apply (
                    select top 1 v.*
                    from vital_sign_records v
                    where v.admission_id = a.admission_id
                    order by v.recorded_at desc
                ) latest
                left join users u on latest.recorded_by = u.user_id
                where a.status <> 'discharged'";

        if ($role !== 'head_nurse') {
            $sql .= " and a.assigned_nurse_id = :user_id";
        } elseif ($deptId) {
            $sql .= " and r.department_id = :dept_id";
        }

        if ($keyword !== '') {
            $sql .= " and (
                        p.patient_code = :kw_exact1
                        or p.citizen_id = :kw_exact2
                        or p.phone = :kw_exact3
                        or p.full_name like :kw_like1
                        or r.room_name like :kw_like2
                        or b.bed_code like :kw_like3
                    )";
        }

        $sql .= " order by
                    case when latest.record_id is null then 0 else 1 end,
                    latest.recorded_at asc,
                    r.room_name,
                    b.bed_code";

        $stmt = $this->conn->prepare($sql);
        if ($role !== 'head_nurse') {
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        } elseif ($deptId) {
            $stmt->bindValue(':dept_id', $deptId, PDO::PARAM_INT);
        }
        if ($keyword !== '') {
            $stmt->bindValue(':kw_exact1', $keyword);
            $stmt->bindValue(':kw_exact2', $keyword);
            $stmt->bindValue(':kw_exact3', $keyword);
            $stmt->bindValue(':kw_like1', '%' . $keyword . '%');
            $stmt->bindValue(':kw_like2', '%' . $keyword . '%');
            $stmt->bindValue(':kw_like3', '%' . $keyword . '%');
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
