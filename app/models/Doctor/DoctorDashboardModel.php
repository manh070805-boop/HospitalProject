<?php

class DoctorDashboardModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }
    public function countActiveAdmissions(int $doctorId)
    {
        $sql = "select count(*) as total from admissions 
                where assigned_doctor_id = :doctor_id
                and status in ('active', 'stable')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($row['total'] ?? 0);
    }
    public function countOpenAlerts(int $doctorId)
    {
        $sql = "select count(*) as total
                from alerts al
                join admissions a on al.admission_id = a.admission_id
                where a.assigned_doctor_id = :doctor_id
                  and a.status in ('active', 'stable')
                  and al.status in ('new', 'acknowledged')";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }
    public function countActiveTreatmentPlans(int $doctorId)
    {
        $sql = "select count(*) as total
                from treatment_plans tp
                join admissions a on tp.admission_id = a.admission_id
                where a.assigned_doctor_id = :doctor_id
                  and a.status in ('active', 'stable')
                  and tp.status = 'active'";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }
    public function getAttentionPatients(int $doctorId, int $limit = 6)
    {
        $sql = "select
                    a.admission_id,
                    p.patient_code,
                    p.full_name as patient_name,
                    p.gender,
                    coalesce(r.room_name, r.room_code, N'Chưa xếp phòng') as room_name,
                    coalesce(b.bed_code, N'---') as bed_code,
                    latest_alert.alert_type,
                    latest_alert.alert_message,
                    latest_alert.severity,
                    latest_vital.temperature,
                    latest_vital.pulse,
                    latest_vital.blood_pressure_systolic,
                    latest_vital.blood_pressure_diastolic,
                    latest_vital.respiratory_rate,
                    latest_vital.spo2,
                    latest_vital.recorded_at,
                    current_tp.treatment_plan_id,
                    current_tp.diagnosis
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
                    select top 1 tp.*
                    from treatment_plans tp
                    where tp.admission_id = a.admission_id
                      and tp.status = 'active'
                    order by tp.created_at desc
                ) current_tp
                where a.assigned_doctor_id = :doctor_id
                  and a.status in ('active', 'stable')
                order by
                    case
                        when latest_alert.severity = 'critical' then 90
                        when latest_alert.severity = 'high' then 80
                        when current_tp.treatment_plan_id is null then 70
                        when latest_vital.spo2 < 92 then 60
                        when latest_vital.blood_pressure_systolic >= 160 then 55
                        when latest_vital.blood_pressure_diastolic >= 100 then 55
                        else 10
                    end desc,
                    latest_vital.recorded_at desc
                offset 0 rows fetch next :limit rows only";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getRecentOrders(int $doctorId, int $limit = 6)
    {
        $sql = "select
                    tp.treatment_plan_id,
                    tp.admission_id,
                    tp.diagnosis,
                    tp.medical_order,
                    tp.status,
                    tp.created_at,
                    p.full_name as patient_name,
                    p.patient_code,
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
                where a.assigned_doctor_id = :doctor_id
                  and a.status in ('active', 'stable')
                  and tp.treatment_plan_id is not null
                order by coalesce(tp.updated_at, tp.created_at) desc
                offset 0 rows fetch next :limit rows only";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
