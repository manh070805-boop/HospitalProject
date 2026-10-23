<?php

class TreatmentPlanModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }

    public function getCurrentByAdmission(int $admissionId): ?array
    {
        $sql = "select top 1 tp.*, u.full_name as doctor_name
                from treatment_plans tp
                left join users u on tp.created_by = u.user_id
                where tp.admission_id = :admission_id
                  and tp.status = 'active'
                order by tp.created_at desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAllByAdmission(int $admissionId): array
    {
        $sql = "select top 1 tp.*, u.full_name as doctor_name
                from treatment_plans tp
                left join users u on tp.created_by = u.user_id
                where tp.admission_id = :admission_id
                order by
                    case when tp.status = 'active' then 0 else 1 end,
                    coalesce(tp.updated_at, tp.created_at) desc,
                    tp.treatment_plan_id desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $planId): ?array
    {
        $sql = "select tp.*, u.full_name as doctor_name,
                       a.admission_id,
                       p.full_name as patient_name, p.patient_code
                from treatment_plans tp
                left join users u on tp.created_by = u.user_id
                left join admissions a on tp.admission_id = a.admission_id
                left join patients p on a.patient_id = p.patient_id
                where tp.treatment_plan_id = :plan_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':plan_id', $planId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function hasCurrentPlan(int $admissionId): bool
    {
        return $this->getCurrentByAdmission($admissionId) !== null;
    }

    public function validatePlanForAdmission(int $treatmentPlanId, int $admissionId): bool
    {
        $sql = "select count(*) as total from treatment_plans
                where treatment_plan_id = :plan_id and admission_id = :admission_id
                  and status = 'active'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':plan_id',      $treatmentPlanId, PDO::PARAM_INT);
        $stmt->bindValue(':admission_id', $admissionId,     PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($res['total'] ?? 0) > 0;
    }
}
