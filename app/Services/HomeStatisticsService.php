<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Execution;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final readonly class HomeStatisticsService
{
    /**
     * Get statistics for the home page
     *
     * @return array{
     *     totalReports: int,
     *     totalTests: int,
     *     avgSuccessRate: float,
     *     recentReports: int
     * }
     */
    public function getStatistics(): array
    {
        return Cache::remember('home_page_stats', now()->addDay(), function (): array {
            // Get all stats in a single efficient query
            $basicStats = DB::table('executions')
                ->selectRaw('COUNT(*) as total_reports')
                ->selectRaw('SUM(tests) as total_tests')
                ->selectRaw('COALESCE(AVG(CASE WHEN tests > 0 THEN passes * 100.0 / tests ELSE NULL END), 0) as success_rate')
                ->selectRaw('SUM(CASE WHEN start_date >= ? THEN 1 ELSE 0 END) as recent_reports', [now()->subDays(7)])
                ->first();

            return [
                'totalReports' => (int) $basicStats->total_reports,
                'totalTests' => (int) $basicStats->total_tests,
                'avgSuccessRate' => (float) $basicStats->success_rate,
                'recentReports' => (int) $basicStats->recent_reports,
            ];
        });
    }

    /**
     * Get recent reports for the home page
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Execution>
     */
    public function getRecentReports(): mixed
    {
        return Cache::remember('home_page_reports', now()->addDay(), fn () => Execution::select([
            'id', 'filename', 'version', 'campaign', 'platform', 'database',
            'start_date', 'duration', 'tests', 'passes', 'failures', 'pending', 'skipped',
        ])
            ->orderBy('start_date', 'desc')
            ->limit(5)
            ->get());
    }
}
