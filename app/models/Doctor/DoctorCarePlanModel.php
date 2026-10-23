<?php

class DoctorCarePlanModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }

    public function getCarePlansForDoctor(int $doctorId, string $keyword = '', string $status = ''): array
    {
        $keyword = trim($keyword);
        $status = trim($status);

        $sql = "select cp.*,
                       p.full_name as patient_name,
                       p.patient_code,
                       u.full_name as created_by_name,
                       tp.diagnosis,
                       active_tp.treatment_plan_id as active_treatment_plan_id,
                       coalesce(r.room_name, r.room_code, N'Chưa xếp phòng') as room_name,
                       coalesce(b.bed_code, N'---') as bed_code
                from admissions a
                join patients p on a.patient_id = p.patient_id
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                outer apply (
                    select top 1 latest_cp.*
                    from care_plans latest_cp
                    join treatment_plans linked_tp on latest_cp.treatment_plan_id = linked_tp.treatment_plan_id
                                                   and linked_tp.status = 'active'
                    where latest_cp.admission_id = a.admission_id
                    order by latest_cp.created_at desc
                ) cp
                left join users u on cp.created_by = u.user_id
                left join treatment_plans tp on cp.treatment_plan_id = tp.treatment_plan_id
                outer apply (
                    select top 1 current_tp.*
                    from treatment_plans current_tp
                    where current_tp.admission_id = a.admission_id
                      and current_tp.status = 'active'
                    order by current_tp.created_at desc
                ) active_tp
                where a.assigned_doctor_id = :doctor_id
                  and a.status in ('active', 'stable')
                  and cp.care_plan_id is not null";

        if ($status !== '') {
            $sql .= " and cp.status = :status";
        }

        if ($keyword !== '') {
            $sql .= " and (
                        p.patient_code = :kw_exact
                        or p.full_name like :kw_like1
                        or cp.plan_title like :kw_like2
                        or tp.diagnosis like :kw_like3
                    )";
        }

        $sql .= " order by cp.created_at desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        if ($status !== '') {
            $stmt->bindValue(':status', $status);
        }
        if ($keyword !== '') {
            $stmt->bindValue(':kw_exact', $keyword);
            $stmt->bindValue(':kw_like1', '%' . $keyword . '%');
            $stmt->bindValue(':kw_like2', '%' . $keyword . '%');
            $stmt->bindValue(':kw_like3', '%' . $keyword . '%');
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCarePlanById(int $carePlanId, int $doctorId): ?array
    {
        $sql = "select cp.*, 
                       p.full_name as patient_name, 
                       p.patient_code,
                        u.full_name as created_by_name,
                        tp.diagnosis,
                        tp.treatment_goal,
                        tp.status as treatment_plan_status,
                        a.admission_id
                from care_plans cp
                join admissions a on cp.admission_id = a.admission_id
                join patients p on a.patient_id = p.patient_id
                left join users u on cp.created_by = u.user_id
                left join treatment_plans tp on cp.treatment_plan_id = tp.treatment_plan_id
                where cp.care_plan_id = :care_plan_id
                  and a.assigned_doctor_id = :doctor_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':care_plan_id', $carePlanId, PDO::PARAM_INT);
        $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getCarePlanApproval(int $carePlanId): ?array
    {
        $sql = "select top 1 cpa.*, u.full_name as approved_by_name
                from care_plan_approvals cpa
                left join users u on cpa.approved_by = u.user_id
                where cpa.care_plan_id = :care_plan_id
                order by cpa.approved_at desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':care_plan_id', $carePlanId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getLatestCarePlan(int $admissionId): ?array
    {
        $sql = "select top 1 cp.*, u.full_name as created_by_name
            from care_plans cp
            join treatment_plans tp on cp.treatment_plan_id = tp.treatment_plan_id
                                   and tp.status = 'active'
            left join users u on cp.created_by = u.user_id
            where cp.admission_id = :admission_id
            order by cp.created_at desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
