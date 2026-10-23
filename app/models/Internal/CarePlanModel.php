<?php

class CarePlanModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }

    public function create(array $data): int
    {
        $sql = "insert into care_plans
                    (admission_id, treatment_plan_id, created_by, plan_title, assessment_summary,
                     nursing_diagnosis, care_goal, intervention_plan, monitoring_frequency, planned_tasks, status, created_at)
                values
                    (:admission_id, :treatment_plan_id, :created_by, :plan_title, :assessment_summary,
                     :nursing_diagnosis, :care_goal, :intervention_plan, :monitoring_frequency, :planned_tasks, 'draft', GETDATE())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', (int) $data['admission_id'], PDO::PARAM_INT);
        $stmt->bindValue(':treatment_plan_id', $data['treatment_plan_id'] ? (int) $data['treatment_plan_id'] : null, $data['treatment_plan_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':created_by', (int) $data['created_by'], PDO::PARAM_INT);
        $stmt->bindValue(':plan_title', $data['plan_title']);
        $stmt->bindValue(':assessment_summary', $data['assessment_summary'] ?? '');
        $stmt->bindValue(':nursing_diagnosis', $data['nursing_diagnosis'] ?? '');
        $stmt->bindValue(':care_goal', $data['care_goal'] ?? '');
        $stmt->bindValue(':intervention_plan', $data['intervention_plan'] ?? '');
        $stmt->bindValue(':monitoring_frequency', $data['monitoring_frequency'] ?? '');
        $stmt->bindValue(':planned_tasks', $data['planned_tasks'] ?? null, !empty($data['planned_tasks']) ? PDO::PARAM_STR : PDO::PARAM_NULL);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            return 0;
        }

        return (int) $this->conn->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "update care_plans set
                    plan_title = :plan_title,
                    assessment_summary = :assessment_summary,
                    nursing_diagnosis = :nursing_diagnosis,
                    care_goal = :care_goal,
                    intervention_plan = :intervention_plan,
                    monitoring_frequency = :monitoring_frequency,
                    planned_tasks = :planned_tasks,
                    treatment_plan_id = :treatment_plan_id
                where care_plan_id = :care_plan_id and status = 'draft'";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':plan_title', $data['plan_title']);
        $stmt->bindValue(':assessment_summary', $data['assessment_summary'] ?? '');
        $stmt->bindValue(':nursing_diagnosis', $data['nursing_diagnosis'] ?? '');
        $stmt->bindValue(':care_goal', $data['care_goal'] ?? '');
        $stmt->bindValue(':intervention_plan', $data['intervention_plan'] ?? '');
        $stmt->bindValue(':monitoring_frequency', $data['monitoring_frequency'] ?? '');
        $stmt->bindValue(':planned_tasks', $data['planned_tasks'] ?? null, !empty($data['planned_tasks']) ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':treatment_plan_id', $data['treatment_plan_id'] ? (int) $data['treatment_plan_id'] : null, $data['treatment_plan_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':care_plan_id', $id, PDO::PARAM_INT);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }

        return $stmt->rowCount() > 0;
    }

    public function updateStatus(int $id, string $status): bool
    {
        $allowed = ['draft', 'pending', 'approved', 'rejected'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $sql = "update care_plans set status = :status where care_plan_id = :care_plan_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':care_plan_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function rejectPendingCarePlansByAdmissionId(int $admissionId): void
    {
        $sql = "update care_plans set status = 'rejected'
                where admission_id = :admission_id and status in ('draft', 'pending')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function deleteDraft(int $id): bool
    {
        $sql = "delete from care_plans
                where care_plan_id = :care_plan_id
                  and status = 'draft'";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':care_plan_id', $id, PDO::PARAM_INT);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }

        return $stmt->rowCount() > 0;
    }

    public function getById(int $id): ?array
    {
        $sql = "select cp.*,
                       u.full_name as created_by_name,
                       tp.diagnosis, tp.treatment_goal, tp.medical_order, tp.treatment_note,
                       tp.status as treatment_plan_status,
                       p.full_name as patient_name, p.patient_code,
                       a.admission_id,
                       a.assigned_nurse_id,
                       a.assigned_doctor_id,
                       a.status as admission_status
                from care_plans cp
                left join users u on cp.created_by = u.user_id
                left join treatment_plans tp on cp.treatment_plan_id = tp.treatment_plan_id
                left join admissions a on cp.admission_id = a.admission_id
                left join patients p on a.patient_id = p.patient_id
                where cp.care_plan_id = :care_plan_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':care_plan_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getLatestApproval(int $carePlanId): ?array
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

    public function getByAdmission(int $admissionId): array
    {
        $sql = "select cp.*, u.full_name as created_by_name
                from care_plans cp
                left join users u on cp.created_by = u.user_id
                where cp.admission_id = :admission_id
                order by cp.created_at desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatestByAdmission(int $admissionId): ?array
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

    public function getPendingForDepartment(int $deptId): array
    {
        $sql = "select cp.*,
                       u.full_name as created_by_name,
                       p.full_name as patient_name, p.patient_code,
                       tp.diagnosis
                from care_plans cp
                join admissions a on cp.admission_id = a.admission_id
                join patients p on a.patient_id = p.patient_id
                join users u on cp.created_by = u.user_id
                join treatment_plans tp on cp.treatment_plan_id = tp.treatment_plan_id
                                       and tp.status = 'active'
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                where cp.status = 'pending'
                  and r.department_id = :dept_id
                  and a.status in ('active', 'stable')
                order by cp.created_at asc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':dept_id', $deptId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasPending(int $admissionId): bool
    {
        $sql = "select count(*) as total
                from care_plans cp
                join treatment_plans tp on cp.treatment_plan_id = tp.treatment_plan_id
                                       and tp.status = 'active'
                where cp.admission_id = :admission_id
                  and cp.status = 'pending'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($res['total'] ?? 0) > 0;
    }

    public function getCarePlanWorklist(int $userId, string $role, ?int $deptId, string $keyword = ''): array
    {
        $keyword = trim($keyword);

        $sql = "select
                    a.admission_id,
                    a.admission_date,
                    p.full_name as patient_name,
                    p.patient_code,
                    latest_cp.care_plan_id,
                    latest_cp.plan_title,
                    latest_cp.status as care_plan_status,
                    latest_cp.created_at as care_plan_created_at,
                    latest_cp.treatment_plan_id as care_plan_treatment_plan_id,
                    cp_user.full_name as care_plan_created_by_name,
                    current_tp.treatment_plan_id,
                    current_tp.diagnosis,
                    current_tp.treatment_goal,
                    latest_vital.record_id as latest_vital_record_id,
                    latest_vital.blood_pressure_systolic,
                    latest_vital.blood_pressure_diastolic,
                    latest_vital.spo2,
                    latest_vital.recorded_at as vital_recorded_at
                from admissions a
                join patients p on a.patient_id = p.patient_id
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                outer apply (
                    select top 1 cp.*
                    from care_plans cp
                    join treatment_plans linked_tp on cp.treatment_plan_id = linked_tp.treatment_plan_id
                                                   and linked_tp.status = 'active'
                    where cp.admission_id = a.admission_id
                    order by cp.created_at desc
                ) latest_cp
                left join users cp_user on latest_cp.created_by = cp_user.user_id
                outer apply (
                    select top 1 tp.*
                    from treatment_plans tp
                    where tp.admission_id = a.admission_id
                      and tp.status = 'active'
                    order by tp.created_at desc
                ) current_tp
                outer apply (
                    select top 1 v.*
                    from vital_sign_records v
                    where v.admission_id = a.admission_id
                    order by v.recorded_at desc
                ) latest_vital
                where a.status in ('active', 'stable')";

        if ($role !== 'head_nurse') {
            $sql .= " and a.assigned_nurse_id = :user_id";
        } elseif ($deptId) {
            $sql .= " and r.department_id = :dept_id";
        }

        if ($keyword !== '') {
            $sql .= " and (
                        p.patient_code = :kw_exact
                        or p.full_name like :kw_like
                        or latest_cp.plan_title like :kw_like
                        or current_tp.diagnosis like :kw_like
                    )";
        }

        $sql .= " order by
                    case
                        when latest_cp.care_plan_id is null then 0
                        when latest_cp.status = 'rejected' then 1
                        when latest_cp.status = 'draft' then 2
                        when latest_cp.status = 'pending' then 3
                        else 4
                    end,
                    a.admission_date desc";

        $stmt = $this->conn->prepare($sql);
        if ($role !== 'head_nurse') {
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        } elseif ($deptId) {
            $stmt->bindValue(':dept_id', $deptId, PDO::PARAM_INT);
        }

        if ($keyword !== '') {
            $stmt->bindValue(':kw_exact', $keyword);
            $stmt->bindValue(':kw_like', '%' . $keyword . '%');
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
