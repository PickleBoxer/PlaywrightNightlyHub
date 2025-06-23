<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Execution;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

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
        // Return cached statistics or calculate and cache them
        return Cache::remember('dashboard_statistics', now()->addDay(), function (): array {
            $now = Carbon::now();
            $recentStart = $now->copy()->subDays(7); // Assuming "recent" means last 7 days
            $last30Days = $now->copy()->subDays(30);

            $result = Execution::selectRaw('
            COUNT(*) as total_reports,
            SUM(CASE WHEN start_date >= ? THEN 1 ELSE 0 END) as recent_reports,
            SUM(tests) as total_tests,
            SUM(CASE WHEN start_date >= ? THEN passes ELSE 0 END) as total_passes_30d,
            SUM(CASE WHEN start_date >= ? THEN tests ELSE 0 END) as total_tests_30d
        ', [
                $recentStart->toDateTimeString(),
                $last30Days->toDateTimeString(),
                $last30Days->toDateTimeString(),
            ])->first();

            $avgSuccessRate = 0.0;
            if ($result->total_tests_30d > 0) {
                $avgSuccessRate = round(($result->total_passes_30d / $result->total_tests_30d) * 100, 2);
            }

            return [
                'totalReports' => (int) $result->total_reports,
                'recentReports' => (int) $result->recent_reports,
                'totalTests' => (int) $result->total_tests,
                'avgSuccessRate' => $avgSuccessRate,
            ];
        });
    }
}
