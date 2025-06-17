<?php

namespace App\Http\Controllers;

use App\Services\DashboardStatisticsService;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardStatisticsService $statisticsService
    ) {}

    public function index(): InertiaResponse
    {
        return Inertia::render('dashboard', [
            'stats' => $this->statisticsService->getStatistics(),
        ]);
    }
}
