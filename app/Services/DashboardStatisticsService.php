<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Execution;
use Carbon\Carbon;

final readonly class DashboardStatisticsService
{
    /**
     * Calculate dashboard statistics
     *
     * @return array{
     *     totalReports: int,
     *     recentReports: int,
     *     totalTests: int,
     *     avgSuccessRate: float
     * }
     */
    public function getStatistics(): array
    {
        // Calculate dashboard statistics
        $totalReports = Execution::count();

        $recentReports = Execution::recent()->count();

        $totalTests = (int) Execution::sum('tests') ?? 0;

        // Calculate average success rate for last 30 days
        $recentExecutions = Execution::where('start_date', '>=', Carbon::now()->subDays(30))
            ->selectRaw('SUM(passes) as total_passes, SUM(tests) as total_tests')
            ->first();

        $avgSuccessRate = 0.0;
        if ($recentExecutions && $recentExecutions->total_tests > 0) {
            $avgSuccessRate = round(($recentExecutions->total_passes / $recentExecutions->total_tests) * 100, 2);
        }

        return [
            'totalReports' => $totalReports,
            'recentReports' => $recentReports,
            'totalTests' => $totalTests,
            'avgSuccessRate' => $avgSuccessRate,
        ];
    }
}
