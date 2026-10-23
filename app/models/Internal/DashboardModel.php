<?php

class DashboardModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }

    public function countActiveAdmissions($userId, $role, $deptId = null)
    {
        $sql = "select count(*) as total
                from admissions a
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                where a.status in ('active', 'stable')";

        $sql = $this->appendAdmissionScope($sql, 'a', 'r', $role, $deptId);

        $stmt = $this->conn->prepare($sql);
        $this->bindAdmissionScope($stmt, $userId, $role, $deptId);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function countActiveAlerts($userId, $role, $deptId = null)
    {
        $sql = "select count(*) as total from alerts a 
                join admissions adm on a.admission_id = adm.admission_id
                left join beds b on adm.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                where a.status in ('new', 'acknowledged')
                  and adm.status in ('active', 'stable')";

        $sql = $this->appendAdmissionScope($sql, 'adm', 'r', $role, $deptId);

        $stmt = $this->conn->prepare($sql);
        $this->bindAdmissionScope($stmt, $userId, $role, $deptId);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function countPendingTasks($userId, $role, $deptId = null)
    {
        $sql = "select count(*) as total
                from shift_tasks st
                join admissions a on st.admission_id = a.admission_id
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                where st.status in ('pending', 'in_progress')
                  and CAST(st.scheduled_time as date) = CAST(GETDATE() as date)
                  and a.status in ('active', 'stable')";

        $sql = $this->appendAdmissionScope($sql, 'a', 'r', $role, $deptId);

        $stmt = $this->conn->prepare($sql);
        $this->bindAdmissionScope($stmt, $userId, $role, $deptId);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getBedStats($deptId)
    {
        $sql = "select 
                    count(b.bed_id) as total_beds,
                    coalesce(sum(case when b.status = 'occupied' then 1 else 0 end), 0) as occupied_beds,
                    coalesce(sum(case when b.status = 'available' then 1 else 0 end), 0) as available_beds
                from beds b
                join rooms r on b.room_id = r.room_id
                where r.department_id = :dept_id
                  and r.is_active = 1
                  and b.is_active = 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':dept_id', $deptId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCarePlanStats($userId, $role, $deptId = null): array
    {
        $sql = "select
                    count(cp.care_plan_id) as total,
                    sum(case when cp.status = 'approved' then 1 else 0 end) as approved,
                    sum(case when cp.status <> 'approved' then 1 else 0 end) as not_approved
                from care_plans cp
                join treatment_plans tp on cp.treatment_plan_id = tp.treatment_plan_id
                                       and tp.status = 'active'
                join admissions a on cp.admission_id = a.admission_id
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                where a.status in ('active', 'stable')";

        $sql = $this->appendAdmissionScope($sql, 'a', 'r', $role, $deptId);

        $stmt = $this->conn->prepare($sql);
        $this->bindAdmissionScope($stmt, $userId, $role, $deptId);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int) ($result['total'] ?? 0),
            'approved' => (int) ($result['approved'] ?? 0),
            'not_approved' => (int) ($result['not_approved'] ?? 0),
        ];
    }

    public function getAttentionPatients($userId, $role, $deptId = null, int $limit = 6): array
    {
        $sql = "select
                    a.admission_id,
                    p.patient_code,
                    p.full_name as patient_name,
                    coalesce(r.room_name, r.room_code, N'Chưa xếp phòng') as room_name,
                    coalesce(b.bed_code, N'---') as bed_code,
                    latest_alert.alert_type,
                    latest_alert.alert_message,
                    latest_alert.severity,
                    latest_alert.created_at as alert_created_at,
                    latest_vital.temperature,
                    latest_vital.pulse,
                    latest_vital.blood_pressure_systolic,
                    latest_vital.blood_pressure_diastolic,
                    latest_vital.respiratory_rate,
                    latest_vital.spo2,
                    latest_vital.symptoms_note,
                    latest_vital.recorded_at,
                    overdue_task.task_name as overdue_task_name,
                    overdue_task.task_description as overdue_task_description,
                    overdue_task.scheduled_time as overdue_task_time,
                    case
                        when latest_alert.severity = 'critical' then 90
                        when latest_alert.severity = 'high' then 80
                        when overdue_task.task_id is not null then 70
                        when latest_alert.severity = 'medium' then 60
                        when latest_alert.severity = 'low' then 50
                        when latest_vital.spo2 < 92
                          or latest_vital.temperature >= 38.5
                          or latest_vital.temperature <= 35.5
                          or latest_vital.blood_pressure_systolic >= 160
                          or latest_vital.blood_pressure_diastolic >= 100
                          or latest_vital.pulse >= 120
                          or latest_vital.pulse <= 50
                          or latest_vital.respiratory_rate >= 24
                          or latest_vital.respiratory_rate <= 10 then 40
                        else 0
                    end as attention_rank
                from admissions a
                join patients p on a.patient_id = p.patient_id
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                outer apply (
                    select top 1 al.*
                    from alerts al
                    where al.admission_id = a.admission_id
                      and al.status in ('new', 'acknowledged')
                    order by
                        case al.severity
                            when 'critical' then 4
                            when 'high' then 3
                            when 'medium' then 2
                            when 'low' then 1
                            else 0
                        end desc,
                        al.created_at desc
                ) latest_alert
                outer apply (
                    select top 1 v.*
                    from vital_sign_records v
                    where v.admission_id = a.admission_id
                    order by v.recorded_at desc
                ) latest_vital
                outer apply (
                    select top 1 st.*
                    from shift_tasks st
                    where st.admission_id = a.admission_id
                      and st.status in ('pending', 'in_progress')
                      and CAST(st.scheduled_time as date) = CAST(GETDATE() as date)
                      and st.scheduled_time < DATEADD(minute, -15, GETDATE())
                    order by st.scheduled_time asc
                ) overdue_task
                where a.status in ('active', 'stable')";

        $sql = $this->appendAdmissionScope($sql, 'a', 'r', $role, $deptId);

        $sql .= " and (
                    latest_alert.alert_id is not null
                    or overdue_task.task_id is not null
                    or latest_vital.spo2 < 92
                    or latest_vital.temperature >= 38.5
                    or latest_vital.temperature <= 35.5
                    or latest_vital.blood_pressure_systolic >= 160
                    or latest_vital.blood_pressure_diastolic >= 100
                    or latest_vital.pulse >= 120
                    or latest_vital.pulse <= 50
                    or latest_vital.respiratory_rate >= 24
                    or latest_vital.respiratory_rate <= 10
                )
                order by attention_rank desc, latest_alert.created_at desc, overdue_task.scheduled_time asc
                offset 0 rows fetch next :limit rows only";

        $stmt = $this->conn->prepare($sql);
        $this->bindAdmissionScope($stmt, $userId, $role, $deptId);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        $patients = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $patients[] = $this->formatAttentionPatient($row);
        }

        return $patients;
    }

    public function getDashboardActivities($userId, $role, $deptId = null, int $limit = 8): array
    {
        $activities = $this->getTaskActivities($userId, $role, $deptId, $limit);
        usort($activities, static function (array $a, array $b): int {
            $rankCompare = ($b['sort_rank'] ?? 0) <=> ($a['sort_rank'] ?? 0);
            if ($rankCompare !== 0) {
                return $rankCompare;
            }

            return strtotime($a['activity_at'] ?? '1970-01-01') <=> strtotime($b['activity_at'] ?? '1970-01-01');
        });

        return array_slice($activities, 0, max(1, $limit));
    }


    private function getTaskActivities($userId, $role, $deptId, int $limit): array
    {
        $sql = "select
                    st.task_id,
                    st.task_name,
                    st.task_description,
                    st.scheduled_time,
                    st.status,
                    a.admission_id,
                    p.patient_code,
                    p.full_name as patient_name,
                    coalesce(r.room_name, r.room_code, N'Chưa xếp phòng') as room_name,
                    coalesce(b.bed_code, N'---') as bed_code
                from shift_tasks st
                join admissions a on st.admission_id = a.admission_id
                join patients p on a.patient_id = p.patient_id
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                where st.status in ('pending', 'in_progress')
                  and CAST(st.scheduled_time as date) = CAST(GETDATE() as date)
                  and a.status in ('active', 'stable')";

        $sql = $this->appendAdmissionScope($sql, 'a', 'r', $role, $deptId);

        $sql .= " order by
                    case
                        when st.scheduled_time < DATEADD(minute, -15, GETDATE()) then 3
                        when st.status = 'in_progress' then 2
                        else 1
                    end desc,
                    st.scheduled_time asc
                  offset 0 rows fetch next :limit rows only";

        $stmt = $this->conn->prepare($sql);
        $this->bindAdmissionScope($stmt, $userId, $role, $deptId);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return array_map(function (array $row): array {
            $scheduledAt = !empty($row['scheduled_time']) ? strtotime($row['scheduled_time']) : false;
            $isOverdue = $scheduledAt && $scheduledAt < (time() - 15 * 60);

            return [
                'type' => 'task',
                'tone' => $isOverdue ? 'danger' : 'warning',
                'icon' => $isOverdue ? 'stopwatch-pause' : 'task-list',
                'title' => $row['task_name'] ?? 'Công việc chăm sóc',
                'description' => $row['task_description'] ?? '',
                'patient_name' => $row['patient_name'] ?? '',
                'patient_code' => $row['patient_code'] ?? '',
                'location' => $this->formatLocation($row),
                'status_label' => $isOverdue ? 'Quá hạn' : $this->formatTaskStatus($row['status'] ?? 'pending'),
                'activity_at' => $row['scheduled_time'] ?? null,
                'href' => 'index.php?url=internal/tasks&admission_id=' . (int) ($row['admission_id'] ?? 0),
                'sort_rank' => $isOverdue ? 70 : (($row['status'] ?? '') === 'in_progress' ? 55 : 45),
            ];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }


    private function formatAttentionPatient(array $row): array
    {
        if (!empty($row['alert_type'])) {
            $severity = $row['severity'] ?? 'medium';
            $tone = in_array($severity, ['critical', 'high'], true)
                ? 'danger'
                : ($severity === 'medium' ? 'warning' : 'info');

            return [
                'patient_code' => $row['patient_code'] ?? '',
                'patient_name' => $row['patient_name'] ?? '',
                'location' => $this->formatLocation($row),
                'signal' => $row['alert_type'] ?? 'Cảnh báo',
                'reason' => $row['alert_message'] ?? '',
                'priority' => $this->formatAlertSeverity($severity),
                'tone' => $tone,
                'icon' => 'warning-triangle',
            ];
        }

        if (!empty($row['overdue_task_name'])) {
            return [
                'patient_code' => $row['patient_code'] ?? '',
                'patient_name' => $row['patient_name'] ?? '',
                'location' => $this->formatLocation($row),
                'signal' => $this->formatTime($row['overdue_task_time'] ?? null),
                'reason' => 'Quá hạn: ' . ($row['overdue_task_name'] ?? 'Công việc chăm sóc'),
                'priority' => 'Quá hạn',
                'tone' => 'warning',
                'icon' => 'task-list',
            ];
        }

        $vital = $this->detectVitalAttention($row);

        return [
            'patient_code' => $row['patient_code'] ?? '',
            'patient_name' => $row['patient_name'] ?? '',
            'location' => $this->formatLocation($row),
            'signal' => $vital['signal'],
            'reason' => $vital['reason'],
            'priority' => $vital['priority'],
            'tone' => $vital['tone'],
            'icon' => 'activity-square',
        ];
    }

    private function detectVitalAttention(array $row): array
    {
        $checks = [
            ['field' => 'spo2', 'label' => 'SpO2', 'unit' => '%', 'critical' => fn($v) => $v < 90, 'warning' => fn($v) => $v < 92, 'reason' => 'SpO2 thấp, cần kiểm tra hô hấp.'],
            ['field' => 'temperature', 'label' => 'Nhiệt độ', 'unit' => '°C', 'critical' => fn($v) => $v >= 39.5 || $v <= 35.0, 'warning' => fn($v) => $v >= 38.5 || $v <= 35.5, 'reason' => 'Thân nhiệt ngoài ngưỡng, cần theo dõi sát.'],
            ['field' => 'blood_pressure_systolic', 'label' => 'HA tâm thu', 'unit' => ' mmHg', 'critical' => fn($v) => $v >= 180, 'warning' => fn($v) => $v >= 160, 'reason' => 'Huyết áp cao, cần kiểm tra lại chỉ số.'],
            ['field' => 'blood_pressure_diastolic', 'label' => 'HA tâm trương', 'unit' => ' mmHg', 'critical' => fn($v) => $v >= 110, 'warning' => fn($v) => $v >= 100, 'reason' => 'Huyết áp cao, cần kiểm tra lại chỉ số.'],
            ['field' => 'pulse', 'label' => 'Mạch', 'unit' => ' lần/phút', 'critical' => fn($v) => $v >= 130 || $v <= 40, 'warning' => fn($v) => $v >= 120 || $v <= 50, 'reason' => 'Mạch ngoài ngưỡng, cần đánh giá lại.'],
            ['field' => 'respiratory_rate', 'label' => 'Nhịp thở', 'unit' => ' lần/phút', 'critical' => fn($v) => $v >= 30 || $v <= 8, 'warning' => fn($v) => $v >= 24 || $v <= 10, 'reason' => 'Nhịp thở ngoài ngưỡng, cần theo dõi hô hấp.'],
        ];

        foreach ($checks as $check) {
            if (!isset($row[$check['field']]) || $row[$check['field']] === null || $row[$check['field']] === '') {
                continue;
            }

            $value = (float) $row[$check['field']];
            if ($check['critical']($value)) {
                return [
                    'signal' => $this->formatNumber($value) . $check['unit'],
                    'reason' => $check['reason'],
                    'priority' => 'Nguy hiểm',
                    'tone' => 'danger',
                ];
            }
            if ($check['warning']($value)) {
                return [
                    'signal' => $this->formatNumber($value) . $check['unit'],
                    'reason' => $check['reason'],
                    'priority' => 'Theo dõi',
                    'tone' => 'warning',
                ];
            }
        }

        return [
            'signal' => 'Theo dõi',
            'reason' => 'Có dấu hiệu cần lưu ý trong lần ghi nhận gần nhất.',
            'priority' => 'Theo dõi',
            'tone' => 'info',
        ];
    }

    private function appendAdmissionScope(string $sql, string $admissionAlias, string $roomAlias, string $role, $deptId): string
    {
        if ($role === 'nurse') {
            return $sql . " and {$admissionAlias}.assigned_nurse_id = :user_id";
        }

        if ($role === 'head_nurse' && $deptId) {
            return $sql . " and {$roomAlias}.department_id = :dept_id";
        }

        return $sql;
    }

    private function bindAdmissionScope(PDOStatement $stmt, $userId, string $role, $deptId): void
    {
        if ($role === 'nurse') {
            $stmt->bindValue(':user_id', (int) $userId, PDO::PARAM_INT);
        } elseif ($role === 'head_nurse' && $deptId) {
            $stmt->bindValue(':dept_id', (int) $deptId, PDO::PARAM_INT);
        }
    }

    private function formatLocation(array $row): string
    {
        return trim(($row['room_name'] ?? 'Chưa xếp phòng') . ' · ' . ($row['bed_code'] ?? '---'));
    }

    private function formatAlertSeverity(?string $severity): string
    {
        return match ($severity) {
            'critical' => 'Nguy hiểm',
            'high' => 'Cao',
            'medium' => 'Theo dõi',
            'low' => 'Thấp',
            default => 'Theo dõi',
        };
    }

    private function formatTaskStatus(?string $status): string
    {
        return match ($status) {
            'in_progress' => 'Đang làm',
            'completed' => 'Hoàn thành',
            'skipped' => 'Bỏ qua',
            default => 'Chờ làm',
        };
    }


    private function formatTime($value): string
    {
        $timestamp = !empty($value) ? strtotime((string) $value) : false;
        return $timestamp ? date('H:i', $timestamp) : '---';
    }

    private function formatNumber(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');
    }
}
