<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ExecutionCreated;
use Illuminate\Support\Facades\Cache;

final class ClearStatisticsCache
{
    /**
     * Handle the event.
     */
    public function handle(ExecutionCreated $event): void
    {
        // Clear all statistics and report caches when a new execution is created
        Cache::forget('dashboard_statistics');
        Cache::forget('home_page_stats');
        Cache::forget('home_page_reports');
    }
}
