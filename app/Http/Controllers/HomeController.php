<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\HomeStatisticsService;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

final class HomeController extends Controller
{
    public function __construct(
        private readonly HomeStatisticsService $homeStatisticsService
    ) {}

    /**
     * Display the home page with public reports and stats
     */
    public function index(): InertiaResponse
    {
        $stats = $this->homeStatisticsService->getStatistics();
        $reports = $this->homeStatisticsService->getRecentReports();

        return Inertia::render('home', [
            'reports' => $reports,
            'stats' => $stats,
        ]);
    }
}
