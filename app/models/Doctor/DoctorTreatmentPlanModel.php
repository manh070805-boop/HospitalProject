<?php

class DoctorTreatmentPlanModel
{
    private $conn;
    private $lastError = null;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }

    public function createActivePlan(array $data): int
    {
        try {
            $this->conn->beginTransaction();

            $sqlAdmission = "select assigned_doctor_id, status
                             from admissions with (updlock, holdlock)
                             where admission_id = :admission_id";
            $stmtAdmission = $this->conn->prepare($sqlAdmission);
            $stmtAdmission->bindValue(':admission_id', (int) $data['admission_id'], PDO::PARAM_INT);
            $stmtAdmission->execute();
            $admission = $stmtAdmission->fetch(PDO::FETCH_ASSOC);

            $admissionStatus = strtolower(trim((string) ($admission['status'] ?? '')));
            if (
                !$admission
                || (int) ($admission['assigned_doctor_id'] ?? 0) !== (int) $data['created_by']
                || $admissionStatus === 'discharged'
            ) {
                $this->conn->rollBack();
                return 0;
            }

            $sqlCurrent = "select top 1 *
                           from treatment_plans with (updlock, holdlock)
                           where admission_id = :admission_id
                             and status = 'active'
                           order by
                                coalesce(updated_at, created_at) desc,
                                treatment_plan_id desc";
            $stmtCurrent = $this->conn->prepare($sqlCurrent);
            $stmtCurrent->bindValue(':admission_id', (int) $data['admission_id'], PDO::PARAM_INT);
            $stmtCurrent->execute();
            $currentPlan = $stmtCurrent->fetch(PDO::FETCH_ASSOC) ?: null;
            $currentPlanId = (int) ($currentPlan['treatment_plan_id'] ?? 0);

            if ($currentPlanId > 0) {
                if (!$this->hasPlanContentChanged($currentPlan, $data)) {
                    $this->conn->commit();
                    return $currentPlanId;
                }

                $this->insertHistorySnapshot($currentPlan, (int) $data['created_by'], 'content_update');

                $sqlCloseCurrent = "update treatment_plans
                                    set status = 'updated',
                                        updated_at = GETDATE()
                                    where admission_id = :admission_id
                                      and status = 'active'";
                $stmtCloseCurrent = $this->conn->prepare($sqlCloseCurrent);
                $stmtCloseCurrent->bindValue(':admission_id', (int) $data['admission_id'], PDO::PARAM_INT);
                $stmtCloseCurrent->execute();

                $this->skipOpenCareTasksForTreatmentPlan($currentPlanId);
            }

            $sqlInsert = "insert into treatment_plans
                            (admission_id, diagnosis, treatment_goal, medical_order,
                             treatment_note, status, created_by, created_at, updated_at)
                          values
                            (:admission_id, :diagnosis, :treatment_goal, :medical_order,
                             :treatment_note, 'active', :created_by, GETDATE(), null)";
            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->bindValue(':admission_id', (int) $data['admission_id'], PDO::PARAM_INT);
            $stmtInsert->bindValue(':diagnosis', $data['diagnosis']);
            $stmtInsert->bindValue(':treatment_goal', $data['treatment_goal'] ?? '');
            $stmtInsert->bindValue(':medical_order', $data['medical_order']);
            $stmtInsert->bindValue(':treatment_note', $data['treatment_note'] ?? '');
            $stmtInsert->bindValue(':created_by', (int) $data['created_by'], PDO::PARAM_INT);
            $stmtInsert->execute();

            $planId = (int) $this->conn->lastInsertId();
            $this->conn->commit();

            return $planId;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return 0;
        }
    }

    public function getByIdForDoctor(int $planId, int $doctorId): ?array
    {
        $sql = "select tp.*,
                       p.full_name as patient_name,
                       p.patient_code,
                       p.gender,
                       p.date_of_birth,
                       a.admission_id,
                       a.status as admission_status,
                       coalesce(open_alerts.open_alert_count, 0) as open_alert_count,
                       coalesce(r.room_name, r.room_code, N'Chưa xếp phòng') as room_name,
                       coalesce(b.bed_code, N'---') as bed_code,
                       d.department_name
                from treatment_plans tp
                join admissions a on tp.admission_id = a.admission_id
                join patients p on a.patient_id = p.patient_id
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                left join departments d on r.department_id = d.department_id
                outer apply (
                    select count(*) as open_alert_count
                    from alerts al
                    where al.admission_id = a.admission_id
                      and al.status in ('new', 'acknowledged')
                ) open_alerts
                where tp.treatment_plan_id = :plan_id
                  and a.assigned_doctor_id = :doctor_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':plan_id', $planId, PDO::PARAM_INT);
        $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAllByAdmissionForDoctor(int $admissionId, int $doctorId): array
    {
        $sql = "select top 1 tp.*
                from treatment_plans tp
                join admissions a on tp.admission_id = a.admission_id
                where tp.admission_id = :admission_id
                  and a.assigned_doctor_id = :doctor_id
                  and tp.status = 'active'
                order by
                    coalesce(tp.updated_at, tp.created_at) desc,
                    tp.treatment_plan_id desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllForDoctor(int $doctorId, string $keyword = '', string $status = ''): array
    {
        $keyword = trim($keyword);

        $sql = "select a.admission_id,
                       a.admission_date,
                       tp.treatment_plan_id,
                       tp.diagnosis,
                       tp.treatment_goal,
                       tp.medical_order,
                       tp.treatment_note,
                       tp.status,
                       tp.created_at,
                       tp.updated_at,
                       p.full_name as patient_name,
                       p.patient_code,
                       a.status as admission_status,
                       coalesce(open_alerts.open_alert_count, 0) as open_alert_count,
                       coalesce(r.room_name, r.room_code, N'Chưa xếp phòng') as room_name,
                       coalesce(b.bed_code, N'---') as bed_code
                from admissions a
                join patients p on a.patient_id = p.patient_id
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                outer apply (
                    select top 1 current_tp.*
                    from treatment_plans current_tp
                    where current_tp.admission_id = a.admission_id
                      and current_tp.status = 'active'
                    order by
                        coalesce(current_tp.updated_at, current_tp.created_at) desc,
                        current_tp.treatment_plan_id desc
                ) tp
                outer apply (
                    select count(*) as open_alert_count
                    from alerts al
                    where al.admission_id = a.admission_id
                      and al.status in ('new', 'acknowledged')
                ) open_alerts
                where a.assigned_doctor_id = :doctor_id
                  and a.status <> 'discharged'";

        if ($status === 'active') {
            $sql .= " and tp.treatment_plan_id is not null";
        } elseif ($status === 'missing') {
            $sql .= " and tp.treatment_plan_id is null";
        }

        if ($keyword !== '') {
            $sql .= " and (
                        p.patient_code = :kw_exact
                        or p.full_name like :kw_like1
                        or tp.diagnosis like :kw_like2
                        or tp.medical_order like :kw_like3
                    )";
        }

        $sql .= " order by
                    case when tp.treatment_plan_id is null then 0 else 1 end,
                    coalesce(tp.updated_at, tp.created_at, a.admission_date) desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        if ($keyword !== '') {
            $stmt->bindValue(':kw_exact', $keyword);
            $stmt->bindValue(':kw_like1', '%' . $keyword . '%');
            $stmt->bindValue(':kw_like2', '%' . $keyword . '%');
            $stmt->bindValue(':kw_like3', '%' . $keyword . '%');
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatusForDoctor(int $planId, int $doctorId, string $status): bool
    {
        $this->lastError = null;

        if (!in_array($status, ['stopped', 'completed'], true)) {
            $this->lastError = 'Trạng thái kế hoạch điều trị không hợp lệ';
            return false;
        }

        try {
            $this->conn->beginTransaction();

            $sqlCurrent = "select tp.*
                           from treatment_plans tp with (updlock, holdlock)
                           join admissions a on tp.admission_id = a.admission_id
                           where tp.treatment_plan_id = :plan_id
                             and a.assigned_doctor_id = :doctor_id
                             and tp.status = 'active'";
            $stmtCurrent = $this->conn->prepare($sqlCurrent);
            $stmtCurrent->bindValue(':plan_id', $planId, PDO::PARAM_INT);
            $stmtCurrent->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
            $stmtCurrent->execute();
            $currentPlan = $stmtCurrent->fetch(PDO::FETCH_ASSOC) ?: null;

            if (!$currentPlan) {
                $this->conn->rollBack();
                $this->lastError = 'Chỉ có thể cập nhật kế hoạch điều trị đang active';
                return false;
            }

            if ($status === 'completed') {
                $openAlertCount = $this->countOpenAlertsForAdmission((int) $currentPlan['admission_id']);
                if ($openAlertCount > 0) {
                    $this->conn->rollBack();
                    $this->lastError = "Không thể hoàn tất kế hoạch điều trị vì ca này còn {$openAlertCount} cảnh báo chưa xử lý. Vui lòng xử lý cảnh báo trước.";
                    return false;
                }
            }

            $historyPlan = $currentPlan;
            $historyPlan['status'] = $status;
            $this->insertHistorySnapshot($historyPlan, $doctorId, $status);

            $sql = "update tp
                    set tp.status = :status,
                        tp.updated_at = GETDATE()
                    from treatment_plans tp
                    join admissions a on tp.admission_id = a.admission_id
                    where tp.treatment_plan_id = :plan_id
                      and a.assigned_doctor_id = :doctor_id
                      and tp.status = 'active'";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':plan_id', $planId, PDO::PARAM_INT);
            $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
            $stmt->execute();

            $updated = $stmt->rowCount() > 0;
            if ($updated) {
                $this->skipOpenCareTasksForTreatmentPlan((int) $currentPlan['treatment_plan_id']);
            }
            $this->conn->commit();

            return $updated;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->lastError = 'Không thể cập nhật kế hoạch điều trị';
            return false;
        }
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function getChangeLogsByAdmissionForDoctor(int $admissionId, int $doctorId, int $limit = 20)
    {
        $sqlAdmission = "select count(*) as total
                         from admissions
                         where admission_id = :admission_id
                           and assigned_doctor_id = :doctor_id";
        $stmtAdmission = $this->conn->prepare($sqlAdmission);
        $stmtAdmission->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmtAdmission->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        $stmtAdmission->execute();

        if ((int) ($stmtAdmission->fetchColumn() ?: 0) === 0) {
            return [];
        }

        return $this->getChangeLogRowsByAdmission($admissionId, $limit);
    }

    private function getChangeLogRowsByAdmission(int $admissionId, int $limit = 20)
    {
        $maxRows = max(1, (int) $limit);

        $sqlHistory = "select top {$maxRows}
                       h.history_id,
                       h.treatment_plan_id,
                       h.admission_id,
                       h.diagnosis,
                       h.treatment_goal,
                       h.medical_order,
                       h.treatment_note,
                       h.status,
                       h.created_by,
                       h.created_at,
                       h.updated_at,
                       h.changed_by,
                       h.changed_at,
                       h.change_type,
                       u.full_name,
                       u.username
                from treatment_plan_change_logs h
                left join users u on h.changed_by = u.user_id
                where h.admission_id = :admission_id
                order by h.changed_at desc, h.history_id desc";

        $stmtHistory = $this->conn->prepare($sqlHistory);
        $stmtHistory->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmtHistory->execute();
        $historyRows = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

        $sqlLegacy = "select top {$maxRows}
                       null as history_id,
                       tp.treatment_plan_id,
                       tp.admission_id,
                       tp.diagnosis,
                       tp.treatment_goal,
                       tp.medical_order,
                       tp.treatment_note,
                       tp.status,
                       tp.created_by,
                       tp.created_at,
                       tp.updated_at,
                       tp.created_by as changed_by,
                       coalesce(tp.updated_at, tp.created_at) as changed_at,
                       'legacy_plan' as change_type,
                       u.full_name,
                       u.username
                from treatment_plans tp
                left join users u on tp.created_by = u.user_id
                where tp.admission_id = :admission_id
                  and tp.status <> 'active'
                  and not exists (
                      select 1
                      from treatment_plan_change_logs h
                      where h.treatment_plan_id = tp.treatment_plan_id
                        and h.status = tp.status
                  )
                order by coalesce(tp.updated_at, tp.created_at) desc, tp.treatment_plan_id desc";

        $stmtLegacy = $this->conn->prepare($sqlLegacy);
        $stmtLegacy->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmtLegacy->execute();
        $legacyRows = $stmtLegacy->fetchAll(PDO::FETCH_ASSOC);

        $rows = array_merge($historyRows, $legacyRows);
        usort($rows, static function (array $a, array $b): int {
            return strtotime($b['changed_at'] ?? '1970-01-01') <=> strtotime($a['changed_at'] ?? '1970-01-01');
        });

        return array_slice($rows, 0, $maxRows);
    }


    private function insertHistorySnapshot(array $plan, int $changedBy, string $changeType): void
    {
        $sql = "insert into treatment_plan_change_logs
                    (treatment_plan_id, admission_id, diagnosis, treatment_goal, medical_order,
                     treatment_note, status, created_by, created_at, updated_at, changed_by, changed_at, change_type)
                values
                    (:treatment_plan_id, :admission_id, :diagnosis, :treatment_goal, :medical_order,
                     :treatment_note, :status, :created_by, :created_at, :updated_at, :changed_by, GETDATE(), :change_type)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':treatment_plan_id', (int) $plan['treatment_plan_id'], PDO::PARAM_INT);
        $stmt->bindValue(':admission_id', (int) $plan['admission_id'], PDO::PARAM_INT);
        $stmt->bindValue(':diagnosis', $plan['diagnosis'] ?? '');
        $stmt->bindValue(':treatment_goal', $plan['treatment_goal'] ?? null);
        $stmt->bindValue(':medical_order', $plan['medical_order'] ?? '');
        $stmt->bindValue(':treatment_note', $plan['treatment_note'] ?? null);
        $stmt->bindValue(':status', $plan['status'] ?? 'active');
        $stmt->bindValue(':created_by', (int) $plan['created_by'], PDO::PARAM_INT);
        $stmt->bindValue(':created_at', $plan['created_at'] ?? null);
        $stmt->bindValue(':updated_at', $plan['updated_at'] ?? null);
        $stmt->bindValue(':changed_by', $changedBy, PDO::PARAM_INT);
        $stmt->bindValue(':change_type', $changeType);
        $stmt->execute();
    }

    private function countOpenAlertsForAdmission(int $admissionId): int
    {
        $sql = "select count(*) as total
                from alerts with (updlock, holdlock)
                where admission_id = :admission_id
                  and status in ('new', 'acknowledged')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function skipOpenCareTasksForTreatmentPlan(int $treatmentPlanId)
    {
        $sql = "update st
                set st.status = 'skipped'
                from shift_tasks st
                join care_plans cp on st.care_plan_id = cp.care_plan_id
                where cp.treatment_plan_id = :treatment_plan_id
                  and st.status in ('pending', 'in_progress')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':treatment_plan_id', $treatmentPlanId, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function hasPlanContentChanged(array $currentPlan, array $newData)
    {
        $fields = [
            'diagnosis' => 'diagnosis',
            'treatment_goal' => 'treatment_goal',
            'medical_order' => 'medical_order',
            'treatment_note' => 'treatment_note',
        ];

        foreach ($fields as $currentKey => $newKey) {
            if (trim((string) ($currentPlan[$currentKey] ?? '')) !== trim((string) ($newData[$newKey] ?? ''))) {
                return true;
            }
        }

        return ($currentPlan['status'] ?? '') !== 'active';
    }
}
