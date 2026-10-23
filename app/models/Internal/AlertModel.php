<?php

class AlertModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }

    public function createAlert(array $data): void
    {
        $existingSql = "select top 1 alert_id
                from alerts
                where admission_id = :admission_id
                  and alert_type = :alert_type
                  and status in ('new', 'acknowledged')
                  and created_at >= dateadd(minute, -30, getdate())
                order by created_at desc, alert_id desc";

        $existingStmt = $this->conn->prepare($existingSql);
        $existingStmt->bindValue(':admission_id', (int) $data['admission_id'], PDO::PARAM_INT);
        $existingStmt->bindValue(':alert_type', $data['alert_type']);
        $existingStmt->execute();
        $existingAlert = $existingStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingAlert) {
            $severityRank = $this->severityRank($data['severity'] ?? '');
            $updateSql = "update alerts
                    set record_id = :record_id,
                        severity = case
                            when :severity_rank > case severity
                                when 'critical' then 4
                                when 'high' then 3
                                when 'medium' then 2
                                when 'low' then 1
                                else 0
                            end then :severity
                            else severity
                        end,
                        status = case
                            when :status_severity_rank > case severity
                                when 'critical' then 4
                                when 'high' then 3
                                when 'medium' then 2
                                when 'low' then 1
                                else 0
                            end then 'new'
                            else status
                        end,
                        alert_message = :alert_message,
                        created_at = getdate()
                    where alert_id = :alert_id";

            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->bindValue(':record_id', (int) $data['record_id'], PDO::PARAM_INT);
            $updateStmt->bindValue(':severity_rank', $severityRank, PDO::PARAM_INT);
            $updateStmt->bindValue(':status_severity_rank', $severityRank, PDO::PARAM_INT);
            $updateStmt->bindValue(':severity', $data['severity']);
            $updateStmt->bindValue(':alert_message', $data['alert_message'] ?? $data['message'] ?? '');
            $updateStmt->bindValue(':alert_id', (int) $existingAlert['alert_id'], PDO::PARAM_INT);
            $updateStmt->execute();
            return;
        }

        $sql = "insert into alerts
                    (admission_id, record_id, alert_type, severity, alert_message, status, created_at)
                values
                    (:admission_id, :record_id, :alert_type, :severity, :alert_message, 'new', GETDATE())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', (int) $data['admission_id'], PDO::PARAM_INT);
        $stmt->bindValue(':record_id', (int) $data['record_id'], PDO::PARAM_INT);
        $stmt->bindValue(':alert_type', $data['alert_type']);
        $stmt->bindValue(':severity', $data['severity']);
        $stmt->bindValue(':alert_message', $data['alert_message'] ?? $data['message'] ?? '');
        $stmt->execute();
    }

    public function getOpenByAdmission(int $admissionId): array
    {
        $sql = "select * from alerts
                where admission_id = :admission_id and status in ('new', 'acknowledged')
                order by created_at desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGroupedOpenForUser(int $userId, string $role, ?int $deptId): array
    {
        $sql = "with scoped_alerts as (
                    select al.*,
                           p.full_name as patient_name,
                           p.patient_code,
                           coalesce(r.room_name, r.room_code, N'Chưa xếp phòng') as room_name,
                           coalesce(b.bed_code, N'---') as bed_code,
                           v.temperature,
                           v.pulse,
                           v.blood_pressure_systolic,
                           v.blood_pressure_diastolic,
                           v.respiratory_rate,
                           v.spo2,
                           v.recorded_at,
                           case al.severity
                               when 'critical' then 4
                               when 'high' then 3
                               when 'medium' then 2
                               when 'low' then 1
                               else 0
                           end as severity_rank,
                           case when al.status = 'new' then 1 else 0 end as is_new
                    from alerts al
                    join admissions ad on al.admission_id = ad.admission_id
                    join patients p on ad.patient_id = p.patient_id
                    left join beds b on ad.bed_id = b.bed_id
                    left join rooms r on b.room_id = r.room_id
                    left join vital_sign_records v on al.record_id = v.record_id
                    where al.status in ('new', 'acknowledged')";

        if ($role !== 'head_nurse') {
            $sql .= " and ad.assigned_nurse_id = :user_id";
        } elseif ($deptId) {
            $sql .= " and r.department_id = :dept_id";
        }

        $sql .= "
                ),
                ranked_alerts as (
                    select scoped_alerts.*,
                           row_number() over (
                               partition by scoped_alerts.admission_id
                               order by scoped_alerts.severity_rank desc,
                                        scoped_alerts.created_at desc,
                                        scoped_alerts.alert_id desc
                           ) as rn
                    from scoped_alerts
                ),
                grouped_alerts as (
                    select admission_id,
                           patient_name,
                           patient_code,
                           room_name,
                           bed_code,
                           count(*) as alert_count,
                           sum(is_new) as new_count,
                           max(severity_rank) as max_severity_rank,
                           max(created_at) as latest_alert_at
                    from scoped_alerts
                    group by admission_id, patient_name, patient_code, room_name, bed_code
                )
                select grouped_alerts.*,
                       ranked_alerts.alert_id,
                       ranked_alerts.alert_type,
                       ranked_alerts.alert_message,
                       ranked_alerts.severity,
                       ranked_alerts.status,
                       ranked_alerts.created_at,
                       ranked_alerts.temperature,
                       ranked_alerts.pulse,
                       ranked_alerts.blood_pressure_systolic,
                       ranked_alerts.blood_pressure_diastolic,
                       ranked_alerts.respiratory_rate,
                       ranked_alerts.spo2,
                       ranked_alerts.recorded_at
                from grouped_alerts
                join ranked_alerts on grouped_alerts.admission_id = ranked_alerts.admission_id
                                  and ranked_alerts.rn = 1
                order by grouped_alerts.max_severity_rank desc,
                         grouped_alerts.new_count desc,
                         grouped_alerts.latest_alert_at desc";

        $stmt = $this->conn->prepare($sql);
        if ($role !== 'head_nurse') {
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        } elseif ($deptId) {
            $stmt->bindValue(':dept_id', $deptId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByAdmission(int $admissionId): array
    {
        $sql = "select * from alerts
                where admission_id = :admission_id
                order by created_at desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function hasOpenAlerts(int $admissionId): bool
    {
        $sql = "select count(*) as total from alerts
                where admission_id = :admission_id and status in ('new', 'acknowledged')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($res['total'] ?? 0) > 0;
    }

    public function acknowledgeAlert(int $alertId): bool
    {
        $sql = "update alerts set status = 'acknowledged'
                where alert_id = :alert_id and status = 'new'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':alert_id', $alertId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function resolveAlert(int $alertId, int $userId): bool
    {
        $sql = "update alerts set status = 'resolved'
                where alert_id = :alert_id and status in ('new', 'acknowledged')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':alert_id', $alertId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function resolveAlertsByAdmissionId(int $admissionId): int
    {
        $sql = "update alerts set status = 'resolved'
                where admission_id = :admission_id and status in ('new', 'acknowledged')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function getById(int $alertId): ?array
    {
        $sql = "select * from alerts where alert_id = :alert_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':alert_id', $alertId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function severityRank(string $severity): int
    {
        return match ($severity) {
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 0,
        };
    }
}
