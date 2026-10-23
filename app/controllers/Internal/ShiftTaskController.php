<?php

require_once __DIR__ . '/../../../app/core/PortalAccess.php';
require_once __DIR__ . '/../../../app/core/AuditLogger.php';
require_once __DIR__ . '/../../models/Internal/ShiftTaskModel.php';

class ShiftTaskController
{
    private $taskModel;

    public function __construct()
    {
        PortalAccess::requireAuth(['nurse', 'head_nurse']);
        $this->taskModel = new ShiftTaskModel();
    }

    public function index()
    {
        $user = PortalAccess::user();
        $role = $user['role_key'];
        $deptId = (int) ($user['department_id'] ?? 0) ?: null;
        $keyword = trim($_GET['keyword'] ?? '');
        $shiftFilter = trim($_GET['shift'] ?? 'all');
        $statusFilter = trim($_GET['status'] ?? 'open');
        $admissionId = (int) ($_GET['admission_id'] ?? 0);
        $allowedShifts = ['all', 'morning', 'afternoon', 'night'];
        $allowedStatuses = ['open', 'pending', 'in_progress', 'completed', 'skipped', 'overdue', 'all'];

        if (!in_array($shiftFilter, $allowedShifts, true)) {
            $shiftFilter = 'all';
        }
        if (!in_array($statusFilter, $allowedStatuses, true)) {
            $statusFilter = 'open';
        }

        $this->taskModel->autoSkipPastTasks((int) $user['user_id']);

        if ($role === 'head_nurse') {
            $tasks = $this->taskModel->getAllToday($deptId);
            $nurses = $this->taskModel->getNursesForDept($deptId);
        } else {
            $tasks = $this->taskModel->getByNurse((int) $user['user_id']);
            $nurses = [];
        }

        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $now = time();
        foreach ($tasks as &$task) {
            $task['task_type'] = $this->classifyTaskType($task);
            $task['priority'] = $this->resolvePriority($task, $now);
            $task['is_overdue'] = $this->isOverdue($task, $now);
        }
        unset($task);

        if ($admissionId) {
            $tasks = array_values(array_filter($tasks, static fn($task) => (int) ($task['admission_id'] ?? 0) === $admissionId));
        }


        $totalTasks = count($tasks);
        $openCount = count(array_filter($tasks, static fn($task) => in_array($task['status'] ?? '', ['pending', 'in_progress'], true)));
        $overdueCount = count(array_filter($tasks, static fn($task) => !empty($task['is_overdue'])));
        $pendingCount = count(array_filter($tasks, static fn($task) => ($task['status'] ?? '') === 'pending'));
        $inProgressCount = count(array_filter($tasks, static fn($task) => ($task['status'] ?? '') === 'in_progress'));
        $completedCount = count(array_filter($tasks, static fn($task) => ($task['status'] ?? '') === 'completed'));
        $skippedCount = count(array_filter($tasks, static fn($task) => ($task['status'] ?? '') === 'skipped'));

        $tasks = array_values(array_filter($tasks, function ($task) use ($keyword, $shiftFilter, $statusFilter) {
            if ($shiftFilter !== 'all' && ($task['shift_type'] ?? '') !== $shiftFilter) {
                return false;
            }

            if ($statusFilter === 'open' && in_array($task['status'] ?? '', ['completed', 'skipped'], true)) {
                return false;
            }
            if ($statusFilter === 'overdue' && empty($task['is_overdue'])) {
                return false;
            }
            if (!in_array($statusFilter, ['open', 'overdue', 'all'], true) && ($task['status'] ?? '') !== $statusFilter) {
                return false;
            }

            if ($keyword === '') {
                return true;
            }

            $haystack = strtolower(implode(' ', [
                $task['patient_name'] ?? '',
                $task['patient_code'] ?? '',
                $task['room_name'] ?? '',
                $task['bed_code'] ?? '',
                $task['task_name'] ?? '',
                $task['task_description'] ?? '',
                $task['assigned_nurse_name'] ?? '',
            ]));

            return str_contains($haystack, strtolower($keyword));
        }));

        require_once __DIR__ . '/../../views/Internal/tasks/index.php';
    }

    public function assign()
    {
        PortalAccess::requireAuth(['head_nurse']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=internal/tasks");
            exit;
        }

        $taskId = (int) ($_POST['task_id'] ?? 0);
        $nurseId = (int) ($_POST['nurse_id'] ?? 0);
        $user = PortalAccess::user();

        if (!$taskId || !$nurseId) {
            header("Location: " . $this->appendFlash($this->resolveReturnUrl($_POST['return_url'] ?? null), 'error', "Thiếu thông tin phân công"));
            exit;
        }
        $updated = $this->taskModel->assignNurse($taskId, $nurseId);

        if ($updated) {
            AuditLogger::log($user['user_id'], 'ASSIGN', 'TASK', "Phân công task ID: {$taskId} cho nurse ID: {$nurseId}");
            header("Location: " . $this->appendFlash($this->resolveReturnUrl($_POST['return_url'] ?? null), 'success', "Đã phân công task thành công!"));
            exit;
        }

        header("Location: " . $this->appendFlash($this->resolveReturnUrl($_POST['return_url'] ?? null), 'error', "Không thể phân công task này"));
        exit;
    }

    public function start()
    {
        $this->executeAction('in_progress', 'START', 'Bắt đầu thực hiện task');
    }

    public function complete()
    {
        $this->executeAction('completed', 'COMPLETE', 'Hoàn thành task');
    }

    public function skip()
    {
        $this->executeAction('skipped', 'SKIP', 'Bỏ qua task');
    }

    private function executeAction(string $newStatus, string $logAction, string $logLabel): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?url=internal/tasks");
            exit;
        }

        $taskId = (int) ($_POST['task_id'] ?? 0);
        $note = trim($_POST['note'] ?? '');
        $user = PortalAccess::user();

        if (!$taskId) {
            header("Location: " . $this->appendFlash($this->resolveReturnUrl($_POST['return_url'] ?? null), 'error', "Thiếu ID task"));
            exit;
        }

        $task = $this->taskModel->getById($taskId);
        if (!$task || $task['assigned_to'] != $user['user_id']) {
            header("Location: " . $this->appendFlash($this->resolveReturnUrl($_POST['return_url'] ?? null), 'error', "Bạn không phụ trách task này"));
            exit;
        }
        $this->taskModel->updateStatus($taskId, $newStatus);

        AuditLogger::log($user['user_id'], $logAction, 'TASK', "{$logLabel} ID: {$taskId}");

        header("Location: " . $this->appendFlash($this->resolveReturnUrl($_POST['return_url'] ?? null), 'success', "{$logLabel} thành công!"));
        exit;
    }

    private function classifyTaskType(array $task): array
    {
        $text = mb_strtolower(($task['task_name'] ?? '') . ' ' . ($task['task_description'] ?? ''), 'UTF-8');

        $types = [
            'vital' => ['label' => 'Sinh tồn', 'icon' => 'heart', 'keywords' => ['sinh tồn', 'huyết áp', 'spo2', 'mạch', 'nhiệt độ', 'nhịp thở']],
            'medication' => ['label' => 'Thuốc', 'icon' => 'heart-add', 'keywords' => ['thuốc', 'uống', 'tiêm', 'truyền', 'liều']],
            'wound' => ['label' => 'Vết thương', 'icon' => 'heart', 'keywords' => ['vết thương', 'thay băng', 'băng', 'dẫn lưu']],
            'hygiene' => ['label' => 'Chăm sóc cơ bản', 'icon' => 'user-2', 'keywords' => ['vệ sinh', 'tắm', 'ăn uống', 'xoay trở', 'phòng loét']],
            'monitoring' => ['label' => 'Theo dõi', 'icon' => 'eye', 'keywords' => ['theo dõi', 'đau', 'triệu chứng', 'bất thường', 'báo bác sĩ']],
            'education' => ['label' => 'Giáo dục', 'icon' => 'teacher', 'keywords' => ['hướng dẫn', 'giáo dục', 'tư vấn']],
        ];

        foreach ($types as $key => $type) {
            foreach ($type['keywords'] as $keyword) {
                if (str_contains($text, $keyword)) {
                    return ['key' => $key, 'label' => $type['label'], 'icon' => $type['icon']];
                }
            }
        }

        return ['key' => 'care', 'label' => 'Chăm sóc', 'icon' => 'clipboard-text-1'];
    }

    private function resolvePriority(array $task, int $now): array
    {
        if ($this->isOverdue($task, $now)) {
            return ['key' => 'critical', 'label' => 'Quá hạn', 'class' => 'risk-critical'];
        }

        $type = $task['task_type']['key'] ?? 'care';
        if (in_array($type, ['vital', 'medication', 'wound'], true)) {
            return ['key' => 'high', 'label' => 'Ưu tiên cao', 'class' => 'risk-high'];
        }

        if ($type === 'monitoring') {
            return ['key' => 'watch', 'label' => 'Theo dõi', 'class' => 'risk-watch'];
        }

        return ['key' => 'normal', 'label' => 'Bình thường', 'class' => 'risk-stable'];
    }

    private function isOverdue(array $task, int $now): bool
    {
        if (in_array($task['status'] ?? '', ['in_progress', 'completed', 'skipped'], true)) {
            return false;
        }

        $scheduledAt = !empty($task['scheduled_time']) ? strtotime($task['scheduled_time']) : false;
        return $scheduledAt && $scheduledAt < ($now - 15 * 60);
    }

    private function resolveReturnUrl(?string $url, string $default = 'index.php?url=internal/tasks'): string
    {
        $url = trim((string) $url);
        if ($url !== '' && str_starts_with($url, 'index.php?url=internal/')) {
            return $url;
        }
        return $default;
    }

    private function appendFlash(string $url, string $key, string $message): string
    {
        $sep = str_contains($url, '?') ? '&' : '?';
        return $url . $sep . $key . '=' . urlencode($message);
    }
}
