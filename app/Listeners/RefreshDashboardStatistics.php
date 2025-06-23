<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ExecutionCreated;
use Illuminate\Support\Facades\Cache;

final class RefreshDashboardStatistics
{
    /**
     * Handle the event.
     */
    public function handle(ExecutionCreated $event): void
    {
        // Clear dashboard statistics cache when a new execution is created
        Cache::forget('dashboard_statistics');
    }
}
