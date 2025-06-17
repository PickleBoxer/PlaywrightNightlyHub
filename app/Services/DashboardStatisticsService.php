<?php

namespace App\Services;

use App\Models\Execution;
use Carbon\Carbon;

class DashboardStatisticsService
{
    /**
     * Calculate dashboard statistics
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        // Calculate dashboard statistics
        $totalReports = Execution::count();

        $recentReports = Execution::recent()->count();

        $totalTests = Execution::sum('tests') ?? 0;

        // Calculate average success rate for last 30 days
        $recentExecutions = Execution::where('start_date', '>=', Carbon::now()->subDays(30))
            ->selectRaw('SUM(passes) as total_passes, SUM(tests) as total_tests')
            ->first();

        $avgSuccessRate = 0;
        if ($recentExecutions && $recentExecutions->total_tests > 0) {
            $avgSuccessRate = ($recentExecutions->total_passes / $recentExecutions->total_tests) * 100;
        }

        return [
            'totalReports' => $totalReports,
            'recentReports' => $recentReports,
            'totalTests' => $totalTests,
            'avgSuccessRate' => $avgSuccessRate,
        ];
    }
}
