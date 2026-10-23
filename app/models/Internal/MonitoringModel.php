<?php

class MonitoringModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }

    public function getActiveAdmissions($userId, $role, $deptId = null)
    {
        $sql = "select a.admission_id, 
                    a.admission_date, 
                    a.status,
                    p.patient_id,
                    p.full_name as patient_name, 
                    p.patient_code,
                    p.gender,
                    b.bed_code, 
                    r.room_name,
                    u_doc.full_name as doctor_name,
                    u_nurse.full_name as nurse_name
                from admissions a
                join patients p on a.patient_id = p.patient_id
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                left join users u_doc on a.assigned_doctor_id = u_doc.user_id
                left join users u_nurse on a.assigned_nurse_id = u_nurse.user_id
                where lower(trim(a.status)) = 'active'";

        if ($role === 'nurse') {
            $sql .= " and a.assigned_nurse_id = :user_id";
        } elseif ($role === 'head_nurse' && $deptId) {
            $sql .= " and r.department_id = :dept_id";
        }

        $sql .= " order by r.room_name, b.bed_code";

        $stmt = $this->conn->prepare($sql);
        if ($role === 'nurse') {
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        } elseif ($role === 'head_nurse' && $deptId) {
            $stmt->bindParam(':dept_id', $deptId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
