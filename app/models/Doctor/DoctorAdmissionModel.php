<?php
class DoctorAdmissionModel
{
    private $conn;
    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }

    public function getPatientWorklist(int $doctorId, string $keyword = '', string $filter = 'all'): array
    {
        $keyword = trim($keyword);
        $sql = "select
                    a.admission_id,
                    a.admission_date,
                    a.status as admission_status,
                    p.patient_id,
                    p.patient_code,
                    p.full_name as patient_name,
                    p.date_of_birth,
                    p.gender,
                    p.phone,
                    p.address,
                    b.bed_code,
                    r.room_name,
                    d.department_name,
                    latest_vital.record_id as latest_record_id,
                    latest_vital.temperature,
                    latest_vital.pulse,
                    latest_vital.blood_pressure_systolic,
                    latest_vital.blood_pressure_diastolic,
                    latest_vital.respiratory_rate,
                    latest_vital.spo2,
                    latest_vital.recorded_at,
                    latest_alert.alert_id,
                    latest_alert.alert_type,
                    latest_alert.alert_message,
                    latest_alert.severity,
                    latest_alert.status as alert_status,
                    current_tp.treatment_plan_id,
                    current_tp.diagnosis,
                    current_tp.created_at as treatment_created_at
                from admissions a
                join patients p on a.patient_id = p.patient_id
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                left join departments d on r.department_id = d.department_id
                outer apply (
                    select top 1 v.*
                    from vital_sign_records v
                    where v.admission_id = a.admission_id
                    order by v.recorded_at desc
                ) latest_vital
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
                    select top 1 tp.*
                    from treatment_plans tp
                    where tp.admission_id = a.admission_id
                      and tp.status = 'active'
                    order by tp.created_at desc
                ) current_tp
                where a.assigned_doctor_id = :doctor_id
                  and a.status in ('active', 'stable')";

        if ($keyword !== '') {
            $sql .= " and (
                        p.patient_code = :kw_exact
                        or p.full_name like :kw_like1
                        or p.phone = :kw_exact
                        or r.room_name like :kw_like2
                        or b.bed_code like :kw_like3
                    )";
        }

        if ($filter === 'alerts') {
            $sql .= " and latest_alert.alert_id is not null";
        } elseif ($filter === 'no-plan') {
            $sql .= " and current_tp.treatment_plan_id is null";
        } elseif ($filter === 'with-plan') {
            $sql .= " and current_tp.treatment_plan_id is not null";
        }

        $sql .= " order by
                    case
                        when latest_alert.severity = 'critical' then 1
                        when latest_alert.severity = 'high' then 2
                        when latest_alert.alert_id is not null then 3
                        when current_tp.treatment_plan_id is null then 4
                        else 5
                    end,
                    r.room_name,
                    b.bed_code,
                    a.admission_date desc";

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

    public function getAdmissionById(int $admission, int $doctorId): ?array
    {
        $sql = "select
                a.admission_id,
                a.status,
                a.admission_date,
                p.patient_id,
                p.patient_code,
                p.full_name as patient_name,
                p.date_of_birth,
                p.gender,
                p.phone,
                p.address,
                b.bed_id,
                b.bed_code,
                r.room_id,
                r.room_name,
                d.department_id,
                d.department_name,
                doc.full_name as doctor_name,
                nur.full_name as nurse_name
            from admissions a
            join patients p on a.patient_id = p.patient_id
            left join beds b on a.bed_id = b.bed_id
            left join rooms r on b.room_id = r.room_id
            left join departments d on r.department_id = d.department_id
            left join users doc on a.assigned_doctor_id = doc.user_id
            left join users nur on a.assigned_nurse_id = nur.user_id
            where a.admission_id = :admission_id
              and a.assigned_doctor_id = :doctor_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admission, PDO::PARAM_INT);
        $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getLatestVital(int $admissionId): ?array
    {
        $sql = "select top 1 v.*, u.full_name as recorded_by_name
            from vital_sign_records v
            left join users u on v.recorded_by = u.user_id
            where v.admission_id = :admission_id
            order by v.recorded_at desc";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }


    public function getCurrentTreatmentPlan(int $admissionId): ?array
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

}
?>