<?php
class DoctorAlertModel
{
    private $conn;
    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }

    public function getOpenAlerts(int $admissionId): array
    {
        $sql = "select al.*, v.temperature, v.pulse, v.blood_pressure_systolic,
                   v.blood_pressure_diastolic, v.respiratory_rate, v.spo2
            from alerts al
            left join vital_sign_records v on al.record_id = v.record_id
            where al.admission_id = :admission_id
              and al.status in ('new', 'acknowledged')
            order by
                case al.severity
                    when 'critical' then 4
                    when 'high' then 3
                    when 'medium' then 2
                    when 'low' then 1
                    else 0
                end desc,
                al.created_at desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOpenAlertsForDoctor(int $doctorId, string $keyword = '', string $severity = '', int $patientId = 0): array
    {
        $keyword = trim($keyword);
        $severity = trim($severity);

        $sql = "select al.*,
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
                       v.recorded_at
                from alerts al
                join admissions a on al.admission_id = a.admission_id
                join patients p on a.patient_id = p.patient_id
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                left join vital_sign_records v on al.record_id = v.record_id
                where a.assigned_doctor_id = :doctor_id
                  and a.status in ('active', 'stable')
                  and al.status in ('new', 'acknowledged')";

        if ($patientId > 0) {
            $sql .= " and a.patient_id = :patient_id";
        }

        if ($severity !== '') {
            $sql .= " and al.severity = :severity";
        }

        if ($keyword !== '') {
            $sql .= " and (
                        p.patient_code = :kw_exact
                        or p.full_name like :kw_like1
                        or al.alert_type like :kw_like2
                        or al.alert_message like :kw_like3
                    )";
        }

        $sql .= " order by
                    case al.severity
                        when 'critical' then 1
                        when 'high' then 2
                        when 'medium' then 3
                        when 'low' then 4
                        else 5
                    end,
                    al.created_at desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        if ($patientId > 0) {
            $stmt->bindValue(':patient_id', $patientId, PDO::PARAM_INT);
        }
        if ($severity !== '') {
            $stmt->bindValue(':severity', $severity);
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

    public function getOpenAlertGroupsForDoctor(int $doctorId, string $keyword = '', string $severity = ''): array
    {
        $keyword = trim($keyword);
        $severity = trim($severity);

        $sql = "with scoped_alerts as (
                    select al.*,
                           p.patient_id,
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
                    join admissions a on al.admission_id = a.admission_id
                    join patients p on a.patient_id = p.patient_id
                    left join beds b on a.bed_id = b.bed_id
                    left join rooms r on b.room_id = r.room_id
                    left join vital_sign_records v on al.record_id = v.record_id
                    where a.assigned_doctor_id = :doctor_id
                      and a.status in ('active', 'stable')
                      and al.status in ('new', 'acknowledged')";

        if ($severity !== '') {
            $sql .= " and al.severity = :severity";
        }

        if ($keyword !== '') {
            $sql .= " and (
                        p.patient_code = :kw_exact
                        or p.full_name like :kw_like1
                        or al.alert_type like :kw_like2
                        or al.alert_message like :kw_like3
                    )";
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
                           patient_id,
                           patient_name,
                           patient_code,
                           room_name,
                           bed_code,
                           count(*) as alert_count,
                           sum(is_new) as new_count,
                           max(severity_rank) as max_severity_rank,
                           max(created_at) as latest_alert_at
                    from scoped_alerts
                    group by admission_id, patient_id, patient_name, patient_code, room_name, bed_code
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
        $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        if ($severity !== '') {
            $stmt->bindValue(':severity', $severity);
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
}
