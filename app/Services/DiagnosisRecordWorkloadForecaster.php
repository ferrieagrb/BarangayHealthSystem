<?php

namespace App\Services;

class DiagnosisRecordWorkloadForecaster
{
    private const MINIMUM_MONTHS = 12;
    private const FORECAST_MONTHS = 3;
    private const BACKTEST_MONTHS = 3;

    public function forecast(array $monthlyCounts): array
    {
        $monthlyCounts = array_values(array_map(
            static fn ($count) => max(0, (int) $count),
            $monthlyCounts
        ));

        if (count($monthlyCounts) < self::MINIMUM_MONTHS) {
            return [
                'available' => false,
                'reason' => sprintf(
                    'At least %d completed months are required; only %d are available.',
                    self::MINIMUM_MONTHS,
                    count($monthlyCounts)
                ),
                'history_months' => count($monthlyCounts),
                'forecast_values' => [],
                'method' => null,
                'mae' => null,
                'baseline_mae' => null,
                'wape' => null,
                'backtest_folds' => 0,
            ];
        }

        $firstTestIndex = count($monthlyCounts) - self::BACKTEST_MONTHS;
        $movingAverageErrors = [];
        $lastMonthErrors = [];
        $backtestActuals = [];

        for ($testIndex = $firstTestIndex; $testIndex < count($monthlyCounts); $testIndex++) {
            $history = array_slice($monthlyCounts, 0, $testIndex);
            $recentHistory = array_slice($history, -3);
            $movingAverage = array_sum($recentHistory) / count($recentHistory);
            $lastMonth = (float) $history[array_key_last($history)];
            $actual = (float) $monthlyCounts[$testIndex];

            $movingAverageErrors[] = abs($actual - $movingAverage);
            $lastMonthErrors[] = abs($actual - $lastMonth);
            $backtestActuals[] = $actual;
        }

        $movingAverageMae = array_sum($movingAverageErrors) / count($movingAverageErrors);
        $lastMonthMae = array_sum($lastMonthErrors) / count($lastMonthErrors);
        $useMovingAverage = $movingAverageMae < $lastMonthMae;
        $method = $useMovingAverage ? 'Trailing 3-month average' : 'Last completed month';
        $selectedErrors = $useMovingAverage ? $movingAverageErrors : $lastMonthErrors;
        $forecastValue = $useMovingAverage
            ? array_sum(array_slice($monthlyCounts, -3)) / 3
            : (float) $monthlyCounts[array_key_last($monthlyCounts)];
        $actualTotal = array_sum($backtestActuals);

        return [
            'available' => true,
            'reason' => null,
            'history_months' => count($monthlyCounts),
            'forecast_values' => array_fill(0, self::FORECAST_MONTHS, round($forecastValue, 1)),
            'method' => $method,
            'mae' => round(array_sum($selectedErrors) / count($selectedErrors), 2),
            'baseline_mae' => round($lastMonthMae, 2),
            'wape' => $actualTotal > 0
                ? round(array_sum($selectedErrors) / $actualTotal * 100, 1)
                : null,
            'backtest_folds' => count($selectedErrors),
        ];
    }
}