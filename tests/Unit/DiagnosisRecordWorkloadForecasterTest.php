<?php

namespace Tests\Unit;

use App\Services\DiagnosisRecordWorkloadForecaster;
use PHPUnit\Framework\TestCase;

class DiagnosisRecordWorkloadForecasterTest extends TestCase
{
    public function test_it_withholds_forecasts_until_twelve_months_are_available(): void
    {
        $forecast = (new DiagnosisRecordWorkloadForecaster())->forecast(array_fill(0, 11, 4));

        $this->assertFalse($forecast['available']);
        $this->assertSame([], $forecast['forecast_values']);
        $this->assertSame(11, $forecast['history_months']);
    }

    public function test_it_selects_the_better_backtested_method_and_returns_three_months(): void
    {
        $forecast = (new DiagnosisRecordWorkloadForecaster())->forecast([4, 5, 4, 5, 4, 5, 4, 5, 4, 5, 4, 5]);

        $this->assertTrue($forecast['available']);
        $this->assertSame('Trailing 3-month average', $forecast['method']);
        $this->assertSame([4.7, 4.7, 4.7], $forecast['forecast_values']);
        $this->assertSame(3, $forecast['backtest_folds']);
        $this->assertLessThan($forecast['baseline_mae'], $forecast['mae']);
    }

    public function test_it_uses_the_last_month_baseline_when_smoothing_does_not_improve_backtest(): void
    {
        $forecast = (new DiagnosisRecordWorkloadForecaster())->forecast([1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 20]);

        $this->assertTrue($forecast['available']);
        $this->assertSame('Last completed month', $forecast['method']);
        $this->assertSame([20.0, 20.0, 20.0], $forecast['forecast_values']);
        $this->assertSame($forecast['baseline_mae'], $forecast['mae']);
    }
}