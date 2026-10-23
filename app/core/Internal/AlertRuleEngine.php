<?php

class AlertRuleEngine
{
    private static array $rules = [
        'spo2_critical' => ['field' => 'spo2', 'op' => '<', 'value' => 90, 'severity' => 'critical', 'label' => 'SpO2 nguy hiểm'],
        'spo2_high' => ['field' => 'spo2', 'op' => '<', 'value' => 92, 'severity' => 'high', 'label' => 'SpO2 thấp'],
        'sbp_critical' => ['field' => 'blood_pressure_systolic', 'op' => '>=', 'value' => 180, 'severity' => 'critical', 'label' => 'Huyết áp tâm thu nguy hiểm'],
        'sbp_high' => ['field' => 'blood_pressure_systolic', 'op' => '>=', 'value' => 160, 'severity' => 'high', 'label' => 'Huyết áp tâm thu cao'],
        'dbp_critical' => ['field' => 'blood_pressure_diastolic', 'op' => '>=', 'value' => 110, 'severity' => 'critical', 'label' => 'Huyết áp tâm trương nguy hiểm'],
        'dbp_high' => ['field' => 'blood_pressure_diastolic', 'op' => '>=', 'value' => 100, 'severity' => 'high', 'label' => 'Huyết áp tâm trương cao'],
        'pulse_high_cr' => ['field' => 'pulse', 'op' => '>=', 'value' => 130, 'severity' => 'critical', 'label' => 'Mạch nhanh nguy hiểm'],
        'pulse_low_cr' => ['field' => 'pulse', 'op' => '<=', 'value' => 40, 'severity' => 'critical', 'label' => 'Mạch chậm nguy hiểm'],
        'pulse_high_h' => ['field' => 'pulse', 'op' => '>=', 'value' => 120, 'severity' => 'high', 'label' => 'Mạch nhanh'],
        'pulse_low_h' => ['field' => 'pulse', 'op' => '<=', 'value' => 50, 'severity' => 'high', 'label' => 'Mạch chậm'],
        'rr_high_cr' => ['field' => 'respiratory_rate', 'op' => '>=', 'value' => 30, 'severity' => 'critical', 'label' => 'Nhịp thở nhanh nguy hiểm'],
        'rr_low_cr' => ['field' => 'respiratory_rate', 'op' => '<=', 'value' => 8, 'severity' => 'critical', 'label' => 'Nhịp thở chậm nguy hiểm'],
        'rr_high_h' => ['field' => 'respiratory_rate', 'op' => '>=', 'value' => 24, 'severity' => 'high', 'label' => 'Nhịp thở nhanh'],
        'rr_low_h' => ['field' => 'respiratory_rate', 'op' => '<=', 'value' => 10, 'severity' => 'high', 'label' => 'Nhịp thở chậm'],
        'temp_high_cr' => ['field' => 'temperature', 'op' => '>=', 'value' => 39.5, 'severity' => 'critical', 'label' => 'Sốt nguy hiểm'],
        'temp_low_cr' => ['field' => 'temperature', 'op' => '<=', 'value' => 35.0, 'severity' => 'critical', 'label' => 'Hạ thân nhiệt nguy hiểm'],
        'temp_high_h' => ['field' => 'temperature', 'op' => '>=', 'value' => 38.5, 'severity' => 'high', 'label' => 'Sốt cao'],
        'temp_low_h' => ['field' => 'temperature', 'op' => '<=', 'value' => 35.5, 'severity' => 'high', 'label' => 'Thân nhiệt thấp'],
    ];

    private static array $severityPriority = ['high' => 1, 'critical' => 2];

    public static function evaluateRecord(array $record): array
    {
        $triggered = [];

        foreach (self::$rules as $ruleKey => $rule) {
            $fieldValue = isset($record[$rule['field']]) ? (float) $record[$rule['field']] : null;
            if ($fieldValue === null) {
                continue;
            }

            $matches = match ($rule['op']) {
                '<' => $fieldValue < $rule['value'],
                '<=' => $fieldValue <= $rule['value'],
                '>' => $fieldValue > $rule['value'],
                '>=' => $fieldValue >= $rule['value'],
                default => false,
            };

            if ($matches) {
                $field = $rule['field'];
                $newPriority = self::$severityPriority[$rule['severity']] ?? 0;
                $currentPriority = self::$severityPriority[$triggered[$field]['severity'] ?? ''] ?? -1;

                if (!isset($triggered[$field]) || $newPriority > $currentPriority) {
                    $triggered[$field] = [
                        'rule_key' => $ruleKey,
                        'field' => $field,
                        'severity' => $rule['severity'],
                        'label' => $rule['label'],
                        'value' => $fieldValue,
                        'threshold' => $rule['value'],
                    ];
                }
            }
        }

        return array_values($triggered);
    }

    public static function createAlertsForRecord(int $admissionId, int $recordId, array $triggeredRules, $alertModel): void
    {
        foreach ($triggeredRules as $rule) {
            $message = sprintf(
                '%s: Giá trị hiện tại %s (ngưỡng: %s)',
                $rule['label'],
                $rule['value'],
                $rule['threshold']
            );

            $alertModel->createAlert([
                'admission_id' => $admissionId,
                'record_id' => $recordId,
                'alert_type' => $rule['label'],
                'severity' => $rule['severity'],
                'alert_message' => $message,
            ]);
        }
    }
}
