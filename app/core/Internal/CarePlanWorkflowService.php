<?php

require_once __DIR__ . '/VitalRiskScorer.php';

class CarePlanWorkflowService
{
    public static function processWorklist(array $worklist, string $filter): array
    {
        foreach ($worklist as &$item) {
            $item['plan_state'] = self::resolvePlanState($item);
            $item['vital_risk'] = !empty($item['latest_vital_record_id'])
                ? VitalRiskScorer::assess($item)
                : [
                    'score' => 0,
                    'level' => 'none',
                    'label' => 'Chưa có sinh tồn',
                    'class' => 'risk-watch',
                    'signals' => [],
                ];
            $item['sla'] = self::resolveSlaState($item);
            $item['priority_score'] = self::calculatePriorityScore($item);
        }
        unset($item);

        usort($worklist, static function ($a, $b) {
            $priorityCompare = ($b['priority_score'] ?? 0) <=> ($a['priority_score'] ?? 0);
            if ($priorityCompare !== 0) {
                return $priorityCompare;
            }

            return strtotime($b['admission_date'] ?? '1970-01-01') <=> strtotime($a['admission_date'] ?? '1970-01-01');
        });

        $stats = [
            'totalAdmissions' => count($worklist),
            'attentionCount' => count(array_filter($worklist, static fn($item) => in_array($item['plan_state']['key'], ['needs_vital', 'no_treatment', 'missing', 'outdated', 'vital_changed', 'rejected', 'draft'], true))),
            'pendingCount' => count(array_filter($worklist, static fn($item) => ($item['care_plan_status'] ?? '') === 'pending')),
            'approvedCount' => count(array_filter($worklist, static fn($item) => ($item['care_plan_status'] ?? '') === 'approved')),
            'overdueCount' => count(array_filter($worklist, static fn($item) => ($item['sla']['level'] ?? '') === 'overdue')),
        ];

        if ($filter === 'attention') {
            $worklist = array_values(array_filter($worklist, static fn($item) => in_array($item['plan_state']['key'], ['needs_vital', 'no_treatment', 'missing', 'outdated', 'vital_changed', 'rejected', 'draft'], true)));
        } elseif ($filter === 'pending') {
            $worklist = array_values(array_filter($worklist, static fn($item) => ($item['care_plan_status'] ?? '') === 'pending'));
        } elseif ($filter === 'approved') {
            $worklist = array_values(array_filter($worklist, static fn($item) => ($item['care_plan_status'] ?? '') === 'approved'));
        }

        return ['worklist' => $worklist, 'stats' => $stats];
    }

    private static function resolvePlanState(array $item): array
    {
        if (empty($item['latest_vital_record_id'])) {
            return [
                'key' => 'needs_vital',
                'label' => 'Cần sinh tồn',
                'class' => 'risk-critical',
            ];
        }

        if (empty($item['treatment_plan_id'])) {
            return [
                'key' => 'no_treatment',
                'label' => 'Thiếu y lệnh',
                'class' => 'risk-high',
            ];
        }

        if (empty($item['care_plan_id'])) {
            return [
                'key' => 'missing',
                'label' => 'Cần lập',
                'class' => 'risk-watch',
            ];
        }

        if (self::hasHighRiskVitalAfterCarePlan($item)) {
            return [
                'key' => 'vital_changed',
                'label' => 'Cần đánh giá lại',
                'class' => 'risk-critical',
            ];
        }

        if (
            !empty($item['treatment_plan_id'])
            && !empty($item['care_plan_treatment_plan_id'])
            && (int) $item['care_plan_treatment_plan_id'] !== (int) $item['treatment_plan_id']
        ) {
            return [
                'key' => 'outdated',
                'label' => 'Cần cập nhật',
                'class' => 'risk-high',
            ];
        }

        $status = $item['care_plan_status'] ?? 'draft';
        return [
            'draft' => [
                'key' => 'draft',
                'label' => 'Nháp',
                'class' => 'risk-watch',
            ],
            'pending' => [
                'key' => 'pending',
                'label' => 'Chờ duyệt',
                'class' => 'risk-high',
            ],
            'approved' => [
                'key' => 'approved',
                'label' => 'Đã duyệt',
                'class' => 'risk-stable',
            ],
            'rejected' => [
                'key' => 'rejected',
                'label' => 'Cần chỉnh sửa',
                'class' => 'risk-critical',
            ],
        ][$status] ?? [
            'key' => $status,
            'label' => $status,
            'class' => 'risk-watch',
        ];
    }

    private static function hasHighRiskVitalAfterCarePlan(array $item): bool
    {
        if (empty($item['latest_vital_record_id']) || empty($item['care_plan_created_at']) || empty($item['vital_recorded_at'])) {
            return false;
        }

        if (!in_array($item['care_plan_status'] ?? '', ['pending', 'approved'], true)) {
            return false;
        }

        if (strtotime($item['vital_recorded_at']) <= strtotime($item['care_plan_created_at'])) {
            return false;
        }

        $risk = VitalRiskScorer::assess($item);
        return in_array($risk['level'] ?? '', ['critical', 'high'], true);
    }

    private static function calculatePriorityScore(array $item): int
    {
        $stateScores = [
            'needs_vital' => 100,
            'no_treatment' => 90,
            'outdated' => 88,
            'vital_changed' => 86,
            'rejected' => 85,
            'missing' => 80,
            'draft' => 65,
            'pending' => 45,
            'approved' => 10,
        ];

        $score = $stateScores[$item['plan_state']['key'] ?? ''] ?? 20;

        if (($item['sla']['level'] ?? '') === 'overdue') {
            $score += 40;
        } elseif (($item['sla']['level'] ?? '') === 'due_soon') {
            $score += 15;
        }

        $riskLevel = $item['vital_risk']['level'] ?? '';
        if ($riskLevel === 'critical') {
            $score += 30;
        } elseif ($riskLevel === 'high') {
            $score += 20;
        } elseif ($riskLevel === 'watch') {
            $score += 10;
        }

        if (!empty($item['admission_date'])) {
            $hoursSinceAdmission = (time() - strtotime($item['admission_date'])) / 3600;
            if ($hoursSinceAdmission >= 8 && in_array($item['plan_state']['key'] ?? '', ['needs_vital', 'missing', 'outdated', 'vital_changed', 'no_treatment'], true)) {
                $score += 15;
            }
        }

        return $score;
    }

    private static function resolveSlaState(array $item): array
    {
        $state = $item['plan_state']['key'] ?? '';
        $now = time();

        $admissionAt = !empty($item['admission_date']) ? strtotime($item['admission_date']) : null;
        $carePlanAt = !empty($item['care_plan_created_at']) ? strtotime($item['care_plan_created_at']) : null;

        if ($state === 'needs_vital') {
            return self::buildSlaState($admissionAt, 1, 'Quá hạn ghi sinh tồn', 'Sắp đến hạn ghi sinh tồn', $now);
        }

        if ($state === 'no_treatment') {
            return self::buildSlaState($admissionAt, 2, 'Quá hạn y lệnh điều trị', 'Sắp đến hạn y lệnh điều trị', $now);
        }

        if (in_array($state, ['missing', 'outdated', 'vital_changed'], true)) {
            return self::buildSlaState($admissionAt, 4, 'Quá hạn lập kế hoạch', 'Sắp đến hạn lập kế hoạch', $now);
        }

        if ($state === 'draft') {
            return self::buildSlaState($carePlanAt, 2, 'Quá hạn gửi duyệt', 'Sắp đến hạn gửi duyệt', $now);
        }

        if ($state === 'pending') {
            return self::buildSlaState($carePlanAt, 2, 'Quá hạn duyệt', 'Sắp đến hạn duyệt', $now);
        }

        if ($state === 'rejected') {
            return self::buildSlaState($carePlanAt, 1, 'Quá hạn chỉnh sửa', 'Sắp đến hạn chỉnh sửa', $now);
        }

        return [
            'level' => 'ok',
            'label' => 'Đúng hạn',
            'class' => 'risk-stable',
            'deadline_at' => null,
        ];
    }

    private static function buildSlaState(?int $startAt, int $hours, string $overdueLabel, string $dueSoonLabel, int $now): array
    {
        if (!$startAt) {
            return [
                'level' => 'unknown',
                'label' => 'Chưa rõ hạn',
                'class' => 'risk-watch',
                'deadline_at' => null,
            ];
        }

        $deadlineAt = $startAt + ($hours * 3600);
        $minutesLeft = (int) floor(($deadlineAt - $now) / 60);

        if ($deadlineAt <= $now) {
            return [
                'level' => 'overdue',
                'label' => $overdueLabel,
                'class' => 'risk-critical',
                'deadline_at' => $deadlineAt,
            ];
        }

        if ($minutesLeft <= 60) {
            return [
                'level' => 'due_soon',
                'label' => $dueSoonLabel,
                'class' => 'risk-high',
                'deadline_at' => $deadlineAt,
            ];
        }

        return [
            'level' => 'ok',
            'label' => 'Đúng hạn',
            'class' => 'risk-stable',
            'deadline_at' => $deadlineAt,
        ];
    }

}
