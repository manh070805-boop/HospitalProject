<?php

class TaskGenerator
{
    private const TASK_DEFINITIONS = [
        'vital' => [
            'name' => 'Ghi sinh tồn và đánh giá dấu hiệu bất thường',
            'times' => ['07:00', '13:00', '19:00', '21:00'],
        ],
        'spo2' => [
            'name' => 'Theo dõi SpO2, nhịp thở và mức độ khó thở',
            'times' => ['08:00', '14:00', '22:00'],
        ],
        'wound' => [
            'name' => 'Chăm sóc vết thương / thay băng theo y lệnh',
            'times' => ['08:00', '20:00'],
        ],
        'blood_sugar' => [
            'name' => 'Kiểm tra đường huyết và dấu hiệu hạ/tăng đường',
            'times' => ['06:30', '11:00', '16:30', '21:00'],
        ],
    ];

    public static function generateFromCarePlan(int $carePlanId, array $carePlan, $shiftTaskModel): void
    {
        $assignedTo = (int) ($carePlan['assigned_nurse_id'] ?? $carePlan['created_by']);

        $tasks = self::buildConcreteTasks($carePlan);

        foreach ($tasks as $task) {
            $shiftTaskModel->createTask([
                'care_plan_id' => $carePlanId,
                'admission_id' => $carePlan['admission_id'],
                'assigned_to' => $assignedTo,
                'shift_type' => $task['shift_type'],
                'task_name' => self::limit($task['task_name'], 145),
                'task_description' => $task['task_description'] ?: null,
                'scheduled_time' => $task['scheduled_time'],
            ]);
        }
    }

    private static function buildConcreteTasks(array $carePlan): array
    {
        $plannedTasks = self::plannedTasksFromCarePlan($carePlan);
        if (empty($plannedTasks)) {
            $plannedTasks = self::defaultPlannedTasks();
        }

        $tasks = [];
        foreach ($plannedTasks as $taskKey => $taskData) {
            $label = "";
            $times = [];
            $note = "";

            if (isset($taskData['times']) && is_array($taskData['times'])) {
                $label = !empty($taskData['label']) ? $taskData['label'] : (self::TASK_DEFINITIONS[$taskKey]['name'] ?? $taskKey);
                $times = $taskData['times'];
                $note = $taskData['note'] ?? '';
            } else if (is_array($taskData)) {
                $label = self::TASK_DEFINITIONS[$taskKey]['name'] ?? $taskKey;
                $times = $taskData;
                $note = '';
            } else {
                continue;
            }

            $fullDescription = !empty($note) ? "Ghi chú: " . $note : "";

            foreach ($times as $selectedTime) {
                $time = self::normalizeTime($selectedTime);
                if ($time === null) {
                    continue;
                }

                $tasks[] = self::task($label, $fullDescription, $time);
            }
        }

        if (empty($tasks)) {
            foreach (self::defaultPlannedTasks() as $taskKey => $selectedTimes) {
                $definition = self::TASK_DEFINITIONS[$taskKey];
                foreach ($selectedTimes as $time) {
                    $tasks[] = self::task($definition['name'], "", $time);
                }
            }
        }

        return self::uniqueTasks($tasks);
    }

    private static function plannedTasksFromCarePlan(array $carePlan): array
    {
        if (empty($carePlan['planned_tasks'])) {
            return [];
        }

        if (is_array($carePlan['planned_tasks'])) {
            return $carePlan['planned_tasks'];
        }

        $decoded = json_decode((string) $carePlan['planned_tasks'], true);
        return is_array($decoded) ? $decoded : [];
    }

    private static function defaultPlannedTasks(): array
    {
        return [
            'vital' => ['07:00', '13:00', '19:00', '21:00'],
        ];
    }

    private static function normalizeTime($time): ?string
    {
        $time = trim((string) $time);
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time;
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            return substr($time, 0, 5);
        }

        return null;
    }

    private static function task(string $name, string $description, string $time): array
    {
        $scheduledTime = strlen($time) === 5 ? $time . ':00' : $time;

        return [
            'task_name' => $name,
            'task_description' => $description,
            'scheduled_time' => date('Y-m-d') . ' ' . $scheduledTime,
            'shift_type' => self::shiftForTime($time),
        ];
    }

    private static function shiftForTime(string $time): string
    {
        $hour = (int) substr($time, 0, 2);
        if ($hour >= 6 && $hour < 12) {
            return 'morning';
        }

        if ($hour >= 12 && $hour < 18) {
            return 'afternoon';
        }

        return 'night';
    }

    private static function uniqueTasks(array $tasks): array
    {
        $seen = [];
        $unique = [];
        foreach ($tasks as $task) {
            $key = $task['task_name'] . '|' . $task['scheduled_time'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $task;
        }

        return $unique;
    }

    private static function limit(string $text, int $length): string
    {
        return mb_strlen($text, 'UTF-8') > $length
            ? mb_substr($text, 0, $length - 3, 'UTF-8') . '...'
            : $text;
    }
}
