<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Execution;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

final class HomeController extends Controller
{
    /**
     * Display the home page with public reports and stats
     */
    public function index(): InertiaResponse
    {
        // Get stats for the home page using cache to improve performance
        $stats = Cache::remember('home_page_stats', now()->addMinutes(30), function (): array {
            // Get all stats in a single efficient query
            $basicStats = DB::table('executions')
                ->selectRaw('COUNT(*) as total_reports')
                ->selectRaw('SUM(tests) as total_tests')
                ->selectRaw('COALESCE(AVG(CASE WHEN tests > 0 THEN passes * 100.0 / tests ELSE NULL END), 0) as success_rate')
                ->first();

            $recentReports = Execution::where('start_date', '>=', now()->subDays(7))->count();

            return [
                'totalReports' => (int) $basicStats->total_reports,
                'totalTests' => (int) $basicStats->total_tests,
                'avgSuccessRate' => (float) $basicStats->success_rate,
                'recentReports' => $recentReports,
            ];
        });

        // Get recent reports for the public view
        $reports = Cache::remember('home_page_reports', now()->addMinutes(15), fn () => Execution::select([
            'id', 'filename', 'version', 'campaign', 'platform', 'database',
            'start_date', 'duration', 'tests', 'passes', 'failures', 'pending', 'skipped',
        ])
            ->orderBy('start_date', 'desc')
            ->limit(5)
            ->get());

        return Inertia::render('home', [
            'reports' => $reports,
            'stats' => $stats,
        ]);
    }
}
