<?php

class VitalRiskScorer
{
    public static function assess(array $record): array
    {
        $score = 0;
        $signals = [];

        $spo2 = self::num($record['spo2'] ?? null);
        if ($spo2 !== null) {
            if ($spo2 < 90) {
                $score += 3;
                $signals[] = 'SpO2 nguy hiểm';
            } elseif ($spo2 < 92) {
                $score += 2;
                $signals[] = 'SpO2 thấp';
            } elseif ($spo2 < 95) {
                $score += 1;
                $signals[] = 'SpO2 cần theo dõi';
            }
        }

        $sbp = self::num($record['blood_pressure_systolic'] ?? null);
        $dbp = self::num($record['blood_pressure_diastolic'] ?? null);
        if ($sbp !== null || $dbp !== null) {
            if (($sbp !== null && $sbp >= 180) || ($dbp !== null && $dbp >= 110)) {
                $score += 3;
                $signals[] = 'Huyết áp nguy hiểm';
            } elseif (($sbp !== null && $sbp >= 160) || ($dbp !== null && $dbp >= 100)) {
                $score += 2;
                $signals[] = 'Huyết áp cao';
            } elseif (($sbp !== null && $sbp < 90) || ($dbp !== null && $dbp < 60)) {
                $score += 2;
                $signals[] = 'Huyết áp thấp';
            }
        }

        $pulse = self::num($record['pulse'] ?? null);
        if ($pulse !== null) {
            if ($pulse >= 130 || $pulse <= 40) {
                $score += 3;
                $signals[] = 'Mạch nguy hiểm';
            } elseif ($pulse >= 120 || $pulse <= 50) {
                $score += 2;
                $signals[] = 'Mạch bất thường';
            } elseif ($pulse >= 100 || $pulse <= 60) {
                $score += 1;
                $signals[] = 'Mạch cần theo dõi';
            }
        }

        $rr = self::num($record['respiratory_rate'] ?? null);
        if ($rr !== null) {
            if ($rr >= 30 || $rr <= 8) {
                $score += 3;
                $signals[] = 'Nhịp thở nguy hiểm';
            } elseif ($rr >= 24 || $rr <= 10) {
                $score += 2;
                $signals[] = 'Nhịp thở bất thường';
            }
        }

        $temperature = self::num($record['temperature'] ?? null);
        if ($temperature !== null) {
            if ($temperature >= 39.5 || $temperature <= 35.0) {
                $score += 3;
                $signals[] = 'Thân nhiệt nguy hiểm';
            } elseif ($temperature >= 38.5 || $temperature <= 35.5) {
                $score += 2;
                $signals[] = 'Thân nhiệt bất thường';
            } elseif ($temperature >= 37.8) {
                $score += 1;
                $signals[] = 'Sốt nhẹ';
            }
        }

        $level = self::levelForScore($score);

        return [
            'score' => $score,
            'level' => $level,
            'label' => self::labelForLevel($level),
            'class' => 'risk-' . $level,
            'signals' => array_values(array_unique($signals)),
            'recommendation' => self::recommendationForLevel($level),
            'next_interval_minutes' => self::nextIntervalForLevel($level),
            'next_label' => self::nextLabelForLevel($level),
        ];
    }

    public static function trend(array $latest, ?array $previous): array
    {
        if (!$previous) {
            return [
                'label' => 'Chưa đủ dữ liệu xu hướng',
                'class' => 'risk-stable',
                'detail' => 'Cần ít nhất 2 lần ghi sinh tồn để đánh giá xu hướng.',
            ];
        }

        $latestRisk = self::assess($latest);
        $previousRisk = self::assess($previous);
        $delta = $latestRisk['score'] - $previousRisk['score'];

        if ($delta >= 2) {
            return [
                'label' => 'Xu hướng xấu đi',
                'class' => 'risk-critical',
                'detail' => 'Điểm nguy cơ tăng ' . $delta . ' điểm so với lần đo trước.',
            ];
        }

        if ($delta <= -2) {
            return [
                'label' => 'Xu hướng cải thiện',
                'class' => 'risk-stable',
                'detail' => 'Điểm nguy cơ giảm ' . abs($delta) . ' điểm so với lần đo trước.',
            ];
        }

        return [
            'label' => 'Xu hướng ổn định',
            'class' => 'risk-watch',
            'detail' => 'Điểm nguy cơ thay đổi nhẹ so với lần đo trước.',
        ];
    }

    private static function num($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private static function levelForScore(int $score): string
    {
        if ($score >= 7) {
            return 'critical';
        }

        if ($score >= 4) {
            return 'high';
        }

        if ($score >= 1) {
            return 'watch';
        }

        return 'stable';
    }

    private static function labelForLevel(string $level): string
    {
        return [
            'critical' => 'Nguy cơ cao',
            'high' => 'Cần theo dõi sát',
            'watch' => 'Cần chú ý',
            'stable' => 'Ổn định',
        ][$level] ?? 'Ổn định';
    }

    private static function recommendationForLevel(string $level): string
    {
        return [
            'critical' => 'Báo bác sĩ phụ trách và đo lại sinh tồn sau 15 phút.',
            'high' => 'Theo dõi sát, xử trí theo y lệnh và đo lại sau 30 phút.',
            'watch' => 'Theo dõi thêm triệu chứng và đo lại sau 1 giờ.',
            'stable' => 'Tiếp tục theo dõi định kỳ theo ca.',
        ][$level] ?? 'Tiếp tục theo dõi định kỳ theo ca.';
    }

    private static function nextIntervalForLevel(string $level): int
    {
        return [
            'critical' => 15,
            'high' => 30,
            'watch' => 60,
            'stable' => 240,
        ][$level] ?? 240;
    }

    private static function nextLabelForLevel(string $level): string
    {
        return [
            'critical' => '15 phút',
            'high' => '30 phút',
            'watch' => '1 giờ',
            'stable' => '4 giờ',
        ][$level] ?? '4 giờ';
    }
}
