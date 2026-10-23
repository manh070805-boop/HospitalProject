<?php

class CarePlanApprovalModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }

    public function createApproval(int $carePlanId, int $approvedBy, string $status, ?string $note): int
    {
        $sql = "insert into care_plan_approvals
                    (care_plan_id, approved_by, approval_status, approval_note, approved_at)
                values
                    (:care_plan_id, :approved_by, :approval_status, :approval_note, GETDATE())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':care_plan_id', $carePlanId, PDO::PARAM_INT);
        $stmt->bindValue(':approved_by', $approvedBy, PDO::PARAM_INT);
        $stmt->bindValue(':approval_status', $status);
        $stmt->bindValue(':approval_note', $note);
        $stmt->execute();

        return (int) $this->conn->lastInsertId();
    }

    public function getByCarePlan(int $carePlanId): ?array
    {
        $sql = "select cpa.*, u.full_name as approved_by_name
                from care_plan_approvals cpa
                join users u on cpa.approved_by = u.user_id
                where cpa.care_plan_id = :care_plan_id
                order by cpa.approved_at desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':care_plan_id', $carePlanId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getHistoryByCarePlan(int $carePlanId): array
    {
        $sql = "select cpa.*, u.full_name as approved_by_name
                from care_plan_approvals cpa
                join users u on cpa.approved_by = u.user_id
                where cpa.care_plan_id = :care_plan_id
                order by cpa.approved_at desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':care_plan_id', $carePlanId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
