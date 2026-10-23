<?php

class ShiftTaskModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }
    public function createTask(array $data): int
    {
        $sql = "insert into shift_tasks
                    (care_plan_id, admission_id, assigned_to, shift_type,
                     task_name, task_description, scheduled_time, status, created_at)
                values
                    (:care_plan_id, :admission_id, :assigned_to, :shift_type,
                     :task_name, :task_description, :scheduled_time, 'pending', GETDATE())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':care_plan_id', (int) $data['care_plan_id'], PDO::PARAM_INT);
        $stmt->bindValue(':admission_id', (int) $data['admission_id'], PDO::PARAM_INT);
        $stmt->bindValue(':assigned_to', (int) $data['assigned_to'], PDO::PARAM_INT);
        $stmt->bindValue(':shift_type', $data['shift_type']);
        $stmt->bindValue(':task_name', $data['task_name']);
        $stmt->bindValue(':task_description', $data['task_description'] ?? null);
        $stmt->bindValue(':scheduled_time', $data['scheduled_time']);
        $stmt->execute();

        return (int) $this->conn->lastInsertId();
    }

    public function getByNurse(int $nurseId): array
    {
        $sql = "select st.*,
                       p.patient_code,
                       p.full_name as patient_name,
                       r.room_name,
                       b.bed_code,
                       u.full_name as assigned_nurse_name
                from shift_tasks st
                join admissions ad on st.admission_id = ad.admission_id
                join patients p   on ad.patient_id = p.patient_id
                join beds b       on ad.bed_id = b.bed_id
                join rooms r      on b.room_id = r.room_id
                join users u      on st.assigned_to = u.user_id
                where st.assigned_to = :nurse_id
                  and (CAST(st.scheduled_time as date) = CAST(GETDATE() as date)
                       or (CAST(st.scheduled_time as date) < CAST(GETDATE() as date) and st.status in ('pending', 'in_progress')))
                order by st.scheduled_time, st.shift_type";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nurse_id', $nurseId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllToday(?int $deptId = null): array
    {
        $sql = "select st.*,
                       p.patient_code,
                       p.full_name as patient_name,
                       r.room_name,
                       b.bed_code,
                       u.full_name as assigned_nurse_name
                from shift_tasks st
                join admissions ad on st.admission_id = ad.admission_id
                join patients p   on ad.patient_id = p.patient_id
                join beds b       on ad.bed_id = b.bed_id
                join rooms r      on b.room_id = r.room_id
                join users u      on st.assigned_to = u.user_id
                where (CAST(st.scheduled_time as date) = CAST(GETDATE() as date)
                       or (CAST(st.scheduled_time as date) < CAST(GETDATE() as date) and st.status in ('pending', 'in_progress')))";

        if ($deptId) {
            $sql .= " and r.department_id = :dept_id";
        }

        $sql .= " order by st.scheduled_time, st.shift_type";

        $stmt = $this->conn->prepare($sql);
        if ($deptId) {
            $stmt->bindValue(':dept_id', $deptId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasPendingTasks(int $admissionId): bool
    {
        $sql = "select count(*) as total from shift_tasks
                where admission_id = :admission_id
                  and status in ('pending', 'in_progress')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($res['total'] ?? 0) > 0;
    }

    public function updateStatus(int $taskId, string $status): bool
    {
        $allowed = ['pending', 'in_progress', 'completed', 'skipped'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $sql = "update shift_tasks set status = :status
                where task_id = :task_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->bindValue(':status', $status);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function skipTasksByAdmissionId(int $admissionId, int $userId): void
    {
        $sql = "
            begin try
                begin transaction;
                
                declare @PendingTasks table (task_id int);

                insert into @PendingTasks
                select task_id from shift_tasks
                where admission_id = :admission_id
                  and status in ('pending', 'in_progress');

                if exists (select 1 from @PendingTasks)
                begin
                    update shift_tasks
                    set status = 'skipped',
                        task_description = COALESCE(task_description + CHAR(13) + CHAR(10), '') + N'(Hệ thống tự động bỏ qua do xuất viện khẩn cấp/chuyển viện)'
                    where task_id in (select task_id from @PendingTasks);
                end

                commit transaction;
            end try
            begin catch
                if @@trancount > 0
                    rollback transaction;
            end catch
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function assignNurse(int $taskId, int $nurseId): bool
    {
        $sql = "update shift_tasks set assigned_to = :nurse_id where task_id = :task_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nurse_id', $nurseId, PDO::PARAM_INT);
        $stmt->bindValue(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function getById(int $taskId): ?array
    {
        $sql = "select * from shift_tasks where task_id = :task_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getNursesForDept(?int $deptId): array
    {
        $sql = "select u.user_id, u.full_name
                from users u
                join roles r on u.role_id = r.role_id
                where r.role_name in ('nurse', 'Điều dưỡng')
                  and u.status = 'active'";
        if ($deptId) {
            $sql .= " and u.department_id = :dept_id";
        }
        $sql .= " order by u.full_name";

        $stmt = $this->conn->prepare($sql);
        if ($deptId) {
            $stmt->bindValue(':dept_id', $deptId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function autoSkipPastTasks(int $executedBy): void
    {
        $sql = "
           begin try
                begin transaction;
                
                declare @PastTasks table (task_id int);

                insert into @PastTasks
                select task_id from shift_tasks
                where cast(scheduled_time as date) < cast(getdate() as date)
                  and status in ('pending', 'in_progress');

                if exists (select 1 from @PastTasks)
                begin
                    update shift_tasks
                        set status = 'skipped',
                            task_description = COALESCE(task_description + char(13) + char(10), '') + '(Hệ thống tự động bỏ qua do quá ngày)'
                        where task_id in (select task_id from @PastTasks);
                end

                commit transaction;
            end try
            begin catch
                if @@trancount > 0
                    rollback transaction;
            end catch
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }
}
