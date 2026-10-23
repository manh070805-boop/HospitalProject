<?php

class InternalAdmissionModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }

    public function getDepartments()
    {
        $sql = "select department_id, department_name from departments order by department_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableBeds($deptId = null, $currentBedId = null)
    {
        $sql = "select b.bed_id, b.bed_code, b.status, r.room_name from beds b join rooms r on b.room_id = r.room_id where b.is_active = 1 and r.is_active = 1 and (b.status = 'available'";

        if ($currentBedId !== null) {
            $sql .= " or b.bed_id = :current_bed_id";
        }

        $sql .= ")";

        if ($deptId !== null) {
            $sql .= " and r.department_id = :dept_id";
        }

        $sql .= " order by r.room_name, b.bed_code";

        $stmt = $this->conn->prepare($sql);
        if ($currentBedId !== null) {
            $stmt->bindValue(':current_bed_id', (int) $currentBedId, PDO::PARAM_INT);
        }
        if ($deptId !== null) {
            $stmt->bindValue(':dept_id', (int) $deptId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNurses($deptId = null)
    {
        $sql = "select u.user_id, u.full_name, u.username from users u join roles r on u.role_id = r.role_id where r.role_name in ('nurse', 'Điều dưỡng') and u.status = 'active'";

        if ($deptId !== null) {
            $sql .= " and u.department_id = :dept_id";
        }

        $sql .= " order by full_name";

        $stmt = $this->conn->prepare($sql);
        if ($deptId !== null) {
            $stmt->bindValue(':dept_id', (int) $deptId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDoctors($deptId = null)
    {
        $sql = "select user_id, full_name, username from users u join roles r on u.role_id = r.role_id where r.role_name in ('doctor', 'Bác sĩ chính') and u.status = 'active'";

        if ($deptId !== null) {
            $sql .= " and u.department_id = :dept_id";
        }

        $sql .= " order by full_name";

        $stmt = $this->conn->prepare($sql);
        if ($deptId !== null) {
            $stmt->bindValue(':dept_id', (int) $deptId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBedDepartmentId($bedId)
    {
        $sql = "select r.department_id from beds b join rooms r on b.room_id = r.room_id where b.bed_id = :bed_id and b.is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':bed_id', $bedId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($res['department_id']) ? (int) $res['department_id'] : null;
    }

    public function getDoctorDepartmentId($doctorId)
    {
        $sql = "select u.department_id from users u join roles r on u.role_id = r.role_id where u.user_id = :doctor_id and u.status = 'active' and r.role_name in ('doctor', 'Bác sĩ chính')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':doctor_id', $doctorId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($res['department_id']) ? (int) $res['department_id'] : null;
    }

    public function getNurseDepartmentId($nurseId)
    {
        $sql = "select u.department_id from users u join roles r on u.role_id = r.role_id where u.user_id = :nurse_id and u.status = 'active' and r.role_name in ('nurse', 'Điều dưỡng')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nurse_id', $nurseId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($res['department_id']) ? (int) $res['department_id'] : null;
    }

    public function isPatientAlreadyAdmitted($patientId)
    {
        $sql = "select count(*) as total from admissions where patient_id = :patient_id and status = 'active'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':patient_id', $patientId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($res['total'] ?? 0) > 0;
    }

    public function isBedAvailable($bedId)
    {
        $sql = "select b.status
                from beds b
                join rooms r on b.room_id = r.room_id
                where b.bed_id = :bed_id
                  and b.is_active = 1
                  and r.is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':bed_id', $bedId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($res['status'] ?? '') === 'available';
    }
    public function createAdmission($data)
    {
        try {
            $this->conn->beginTransaction();

            $bedId = (int) ($data['bed_id'] ?? 0);
            $isTemporaryBed = !empty($data['use_temporary_bed']);

            if ($isTemporaryBed) {
                $departmentId = (int) ($data['department_id'] ?? 0);
                if (!$departmentId) {
                    $this->conn->rollBack();
                    return false;
                }

                $bedId = $this->getOrCreateTemporaryBed($departmentId);
                if (!$bedId) {
                    $this->conn->rollBack();
                    return false;
                }
            } elseif (!$bedId) {
                $this->conn->rollBack();
                return false;
            }

            $sql = "insert into admissions (patient_id, bed_id, admission_date, assigned_nurse_id, assigned_doctor_id, status) 
                    values (:patient_id, :bed_id, GETDATE(), :assigned_nurse_id, :assigned_doctor_id, 'active')";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':patient_id', $data['patient_id'], PDO::PARAM_INT);
            $stmt->bindValue(':bed_id', $bedId, PDO::PARAM_INT);
            $stmt->bindParam(':assigned_nurse_id', $data['assigned_nurse_id'], PDO::PARAM_INT);
            $stmt->bindParam(':assigned_doctor_id', $data['assigned_doctor_id'], PDO::PARAM_INT);
            $stmt->execute();

            $admissionId = $this->conn->lastInsertId();

            $sqlBed = "update beds set status = 'occupied' where bed_id = :bed_id";
            $stmtBed = $this->conn->prepare($sqlBed);
            $stmtBed->bindValue(':bed_id', $bedId, PDO::PARAM_INT);
            $stmtBed->execute();

            $sqlHistory = "insert into admission_bed_history (admission_id, bed_id, start_time, note)
                           values (:admission_id, :bed_id, GETDATE(), :note)";
            $stmtHistory = $this->conn->prepare($sqlHistory);
            $stmtHistory->bindValue(':admission_id', (int) $admissionId, PDO::PARAM_INT);
            $stmtHistory->bindValue(':bed_id', $bedId, PDO::PARAM_INT);
            $stmtHistory->bindValue(':note', $isTemporaryBed ? 'Nhập viện bằng giường tạm' : 'Nhập viện ban đầu');
            $stmtHistory->execute();

            $this->conn->commit();
            return $admissionId;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('InternalAdmissionModel::createAdmission failed: ' . $e->getMessage());
            return false;
        }
    }

    private function getOrCreateTemporaryBed(int $departmentId): int
    {
        $roomCode = 'TMP-D' . $departmentId;

        $sqlRoom = "select top 1 room_id
                    from rooms with (updlock, holdlock)
                    where room_code = :room_code";
        $stmtRoom = $this->conn->prepare($sqlRoom);
        $stmtRoom->bindValue(':room_code', $roomCode);
        $stmtRoom->execute();
        $room = $stmtRoom->fetch(PDO::FETCH_ASSOC);

        if ($room) {
            $roomId = (int) $room['room_id'];
        } else {
            $sqlCreateRoom = "insert into rooms (department_id, room_code, room_name, room_type, status, is_active)
                              values (:department_id, :room_code, :room_name, :room_type, 'inactive', 0)";
            $stmtCreateRoom = $this->conn->prepare($sqlCreateRoom);
            $stmtCreateRoom->bindValue(':department_id', $departmentId, PDO::PARAM_INT);
            $stmtCreateRoom->bindValue(':room_code', $roomCode);
            $stmtCreateRoom->bindValue(':room_name', 'Khu giường tạm');
            $stmtCreateRoom->bindValue(':room_type', 'Tạm');
            $stmtCreateRoom->execute();
            $roomId = (int) $this->conn->lastInsertId();
        }

        $sqlBed = "select top 1 bed_id
                   from beds with (updlock, holdlock)
                   where room_id = :room_id
                     and is_active = 0
                     and status = 'available'
                   order by bed_id";
        $stmtBed = $this->conn->prepare($sqlBed);
        $stmtBed->bindValue(':room_id', $roomId, PDO::PARAM_INT);
        $stmtBed->execute();
        $bed = $stmtBed->fetch(PDO::FETCH_ASSOC);

        if ($bed) {
            return (int) $bed['bed_id'];
        }

        $sqlNextBed = "select count(*) + 1 as next_number
                       from beds with (updlock, holdlock)
                       where room_id = :room_id";
        $stmtNextBed = $this->conn->prepare($sqlNextBed);
        $stmtNextBed->bindValue(':room_id', $roomId, PDO::PARAM_INT);
        $stmtNextBed->execute();
        $nextBed = $stmtNextBed->fetch(PDO::FETCH_ASSOC);
        $bedCode = sprintf('TMP-%d-%02d', $departmentId, max(1, (int) ($nextBed['next_number'] ?? 1)));

        $sqlCreateBed = "insert into beds (room_id, bed_code, status, is_active)
                         values (:room_id, :bed_code, 'available', 0)";
        $stmtCreateBed = $this->conn->prepare($sqlCreateBed);
        $stmtCreateBed->bindValue(':room_id', $roomId, PDO::PARAM_INT);
        $stmtCreateBed->bindValue(':bed_code', $bedCode);
        $stmtCreateBed->execute();

        return (int) $this->conn->lastInsertId();
    }

    public function transferAdmission($admissionId, array $data): bool
    {
        try {
            $this->conn->beginTransaction();

            $sqlAdmission = "select admission_id, bed_id, assigned_nurse_id, assigned_doctor_id, status, admission_date
                             from admissions with (updlock, holdlock)
                             where admission_id = :admission_id";
            $stmtAdmission = $this->conn->prepare($sqlAdmission);
            $stmtAdmission->bindValue(':admission_id', (int) $admissionId, PDO::PARAM_INT);
            $stmtAdmission->execute();
            $admission = $stmtAdmission->fetch(PDO::FETCH_ASSOC);

            if (!$admission || strtolower(trim($admission['status'] ?? '')) !== 'active') {
                $this->conn->rollBack();
                return false;
            }

            $newBedId = (int) ($data['bed_id'] ?? 0);
            $newDoctorId = (int) ($data['assigned_doctor_id'] ?? 0);
            $newNurseId = (int) ($data['assigned_nurse_id'] ?? 0);
            $departmentId = (int) ($data['department_id'] ?? 0);
            $note = trim((string) ($data['transfer_note'] ?? ''));

            $oldBedId = (int) $admission['bed_id'];
            $oldDoctorId = (int) ($admission['assigned_doctor_id'] ?? 0);
            $oldNurseId = (int) ($admission['assigned_nurse_id'] ?? 0);

            if (!$newBedId || !$newDoctorId || !$newNurseId || !$departmentId || $note === '') {
                $this->conn->rollBack();
                return false;
            }

            if ($newBedId === $oldBedId && $newDoctorId === $oldDoctorId && $newNurseId === $oldNurseId) {
                $this->conn->rollBack();
                return false;
            }

            $sqlBed = "select b.bed_id, b.status, r.department_id
                       from beds b with (updlock, holdlock)
                       join rooms r on b.room_id = r.room_id
                       where b.bed_id = :bed_id and b.is_active = 1";
            $stmtBed = $this->conn->prepare($sqlBed);
            $stmtBed->bindValue(':bed_id', $newBedId, PDO::PARAM_INT);
            $stmtBed->execute();
            $bed = $stmtBed->fetch(PDO::FETCH_ASSOC);

            if (!$bed || (int) $bed['department_id'] !== $departmentId || ($newBedId !== $oldBedId && ($bed['status'] ?? '') !== 'available')) {
                $this->conn->rollBack();
                return false;
            }

            if ($this->getDoctorDepartmentId($newDoctorId) !== $departmentId || $this->getNurseDepartmentId($newNurseId) !== $departmentId) {
                $this->conn->rollBack();
                return false;
            }

            if ($newBedId !== $oldBedId) {
                $sqlOldBed = "update beds set status = 'available' where bed_id = :bed_id";
                $stmtOldBed = $this->conn->prepare($sqlOldBed);
                $stmtOldBed->bindValue(':bed_id', $oldBedId, PDO::PARAM_INT);
                $stmtOldBed->execute();

                $sqlNewBed = "update beds set status = 'occupied' where bed_id = :bed_id";
                $stmtNewBed = $this->conn->prepare($sqlNewBed);
                $stmtNewBed->bindValue(':bed_id', $newBedId, PDO::PARAM_INT);
                $stmtNewBed->execute();
            }

            $sqlUpdate = "update admissions
                          set bed_id = :bed_id,
                              assigned_nurse_id = :assigned_nurse_id,
                              assigned_doctor_id = :assigned_doctor_id
                          where admission_id = :admission_id";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->bindValue(':bed_id', $newBedId, PDO::PARAM_INT);
            $stmtUpdate->bindValue(':assigned_nurse_id', $newNurseId, PDO::PARAM_INT);
            $stmtUpdate->bindValue(':assigned_doctor_id', $newDoctorId, PDO::PARAM_INT);
            $stmtUpdate->bindValue(':admission_id', (int) $admissionId, PDO::PARAM_INT);
            $stmtUpdate->execute();

            if ($newNurseId !== $oldNurseId) {
                $sqlTasks = "update shift_tasks
                             set assigned_to = :assigned_to
                             where admission_id = :admission_id
                               and status in ('pending', 'in_progress')";
                $stmtTasks = $this->conn->prepare($sqlTasks);
                $stmtTasks->bindValue(':assigned_to', $newNurseId, PDO::PARAM_INT);
                $stmtTasks->bindValue(':admission_id', (int) $admissionId, PDO::PARAM_INT);
                $stmtTasks->execute();
            }

            if ($newBedId !== $oldBedId) {
                $sqlCloseHistory = "update admission_bed_history
                                    set end_time = GETDATE()
                                    where admission_id = :admission_id and end_time is null";
                $stmtCloseHistory = $this->conn->prepare($sqlCloseHistory);
                $stmtCloseHistory->bindValue(':admission_id', (int) $admissionId, PDO::PARAM_INT);
                $stmtCloseHistory->execute();

                if ($stmtCloseHistory->rowCount() === 0) {
                    $sqlBackfillHistory = "insert into admission_bed_history (admission_id, bed_id, start_time, end_time, note)
                                           values (:admission_id, :bed_id, :start_time, GETDATE(), :note)";
                    $stmtBackfillHistory = $this->conn->prepare($sqlBackfillHistory);
                    $stmtBackfillHistory->bindValue(':admission_id', (int) $admissionId, PDO::PARAM_INT);
                    $stmtBackfillHistory->bindValue(':bed_id', $oldBedId, PDO::PARAM_INT);
                    $stmtBackfillHistory->bindValue(':start_time', $admission['admission_date']);
                    $stmtBackfillHistory->bindValue(':note', 'Tự động bổ sung lịch sử trước khi chuyển giường');
                    $stmtBackfillHistory->execute();
                }

                $sqlNewHistory = "insert into admission_bed_history (admission_id, bed_id, start_time, note)
                                  values (:admission_id, :bed_id, GETDATE(), :note)";
                $stmtNewHistory = $this->conn->prepare($sqlNewHistory);
                $stmtNewHistory->bindValue(':admission_id', (int) $admissionId, PDO::PARAM_INT);
                $stmtNewHistory->bindValue(':bed_id', $newBedId, PDO::PARAM_INT);
                $stmtNewHistory->bindValue(':note', $note);
                $stmtNewHistory->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('InternalAdmissionModel::transferAdmission failed: ' . $e->getMessage());
            return false;
        }
    }

    public function updateAdmissionStatus($admissionId, string $status)
    {
        $allowedStatuses = ['active', 'discharged'];
        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        try {
            $this->conn->beginTransaction();

            $sqlAdmission = "select bed_id, status, admission_date from admissions where admission_id = :admission_id";
            $stmtAdmission = $this->conn->prepare($sqlAdmission);
            $stmtAdmission->bindValue(':admission_id', (int) $admissionId, PDO::PARAM_INT);
            $stmtAdmission->execute();
            $admission = $stmtAdmission->fetch(PDO::FETCH_ASSOC);

            if (!$admission) {
                $this->conn->rollBack();
                return false;
            }

            $sqlUpdate = "update admissions set status = :status where admission_id = :admission_id";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->bindValue(':status', $status);
            $stmtUpdate->bindValue(':admission_id', (int) $admissionId, PDO::PARAM_INT);
            $stmtUpdate->execute();

            $bedStatus = $status === 'discharged' ? 'available' : 'occupied';
            $sqlBed = "update beds set status = '$bedStatus' where bed_id = :bed_id";
            $stmtBed = $this->conn->prepare($sqlBed);
            $stmtBed->bindValue(':bed_id', (int) $admission['bed_id'], PDO::PARAM_INT);
            $stmtBed->execute();

            if ($status === 'discharged') {
                $sqlCloseHistory = "update admission_bed_history
                                    set end_time = GETDATE()
                                    where admission_id = :admission_id and end_time is null";
                $stmtCloseHistory = $this->conn->prepare($sqlCloseHistory);
                $stmtCloseHistory->bindValue(':admission_id', (int) $admissionId, PDO::PARAM_INT);
                $stmtCloseHistory->execute();

                if ($stmtCloseHistory->rowCount() === 0) {
                    $sqlBackfillHistory = "insert into admission_bed_history (admission_id, bed_id, start_time, end_time, note)
                                           values (:admission_id, :bed_id, :start_time, GETDATE(), :note)";
                    $stmtBackfillHistory = $this->conn->prepare($sqlBackfillHistory);
                    $stmtBackfillHistory->bindValue(':admission_id', (int) $admissionId, PDO::PARAM_INT);
                    $stmtBackfillHistory->bindValue(':bed_id', (int) $admission['bed_id'], PDO::PARAM_INT);
                    $stmtBackfillHistory->bindValue(':start_time', $admission['admission_date'] ?? date('Y-m-d H:i:s'));
                    $stmtBackfillHistory->bindValue(':note', 'Tự động bổ sung lịch sử khi xuất viện');
                    $stmtBackfillHistory->execute();
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getAdmissionById(int $admissionId, int $userId, string $role): ?array
    {
        $sql = "select
                    a.admission_id,
                    a.status,
                    a.admission_date,
                    p.patient_id,
                    p.patient_code,
                    p.full_name as patient_name,
                    p.citizen_id,
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
                    doc.user_id as doctor_id,
                    doc.full_name as doctor_name,
                    nur.user_id as nurse_id,
                    nur.full_name as nurse_name
                from admissions a
                join patients p on a.patient_id = p.patient_id
                left join beds b on a.bed_id = b.bed_id
                left join rooms r on b.room_id = r.room_id
                left join departments d on r.department_id = d.department_id
                left join users doc on a.assigned_doctor_id = doc.user_id
                left join users nur on a.assigned_nurse_id = nur.user_id
                where a.admission_id = :admission_id";

        if ($role !== 'head_nurse') {
            $sql .= " and a.assigned_nurse_id = :user_id";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        if ($role !== 'head_nurse') {
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        }
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function hasPendingTasks(int $admissionId): bool
    {
        $sql = "select count(*) as total from shift_tasks
                where admission_id = :admission_id and status in ('pending', 'in_progress')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($res['total'] ?? 0) > 0;
    }

    public function hasPendingCarePlan(int $admissionId): bool
    {
        $sql = "select count(*) as total from care_plans
                where admission_id = :admission_id and status = 'pending'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($res['total'] ?? 0) > 0;
    }

    public function canManageAdmissionStatus($admissionId, $userId, string $role): bool
    {
        if ($role === 'head_nurse') {
            return true;
        }

        $sql = "select count(*) as allowed from admissions where admission_id = :admission_id and assigned_nurse_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', (int) $admissionId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', (int) $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($result['allowed'] ?? 0) > 0;
    }
    public function getBedHistory(int $admissionId): array
    {
        $sql = "select
                    h.history_id,
                    h.start_time as changed_at,
                    h.end_time,
                    h.note as reason,
                    new_b.bed_code as new_bed_code,
                    new_r.room_name as new_room_name,
                    new_d.department_name as new_department_name,
                    NULL as changed_by_name
                from admission_bed_history h
                left join beds new_b on h.bed_id = new_b.bed_id
                left join rooms new_r on new_b.room_id = new_r.room_id
                left join departments new_d on new_r.department_id = new_d.department_id
                where h.admission_id = :admission_id
                order by h.start_time desc";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':admission_id', $admissionId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
