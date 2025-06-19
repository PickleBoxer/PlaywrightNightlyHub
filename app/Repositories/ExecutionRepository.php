<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Execution;
use DateTime;

final class ExecutionRepository
{
    public function findByNightly(
        string $version,
        string $platform,
        string $campaign,
        string $database,
        string $date
    ): ?Execution {
        return Execution::query()
            ->where('version', $version)
            ->where('platform', $platform)
            ->where('campaign', $campaign)
            ->where('database', $database)
            ->whereDate('start_date', $date)
            ->latest('start_date')
            ->first();
    }

    public function findByNightlyBefore(
        string $version,
        string $platform,
        string $campaign,
        string $database,
        DateTime $dateUntil
    ): ?Execution {
        return Execution::query()
            ->where('version', $version)
            ->where('platform', $platform)
            ->where('campaign', $campaign)
            ->where('database', $database)
            ->where('start_date', '<', $dateUntil)
            ->latest('start_date')
            ->first();
    }

    /**
     * @return array<string>
     */
    public function findAllVersions(): array
    {
        return array_merge(
            ['develop'],
            Execution::query()
                ->select('version')
                ->where('version', '!=', 'develop')
                ->distinct()
                ->pluck('version')
                ->toArray()
        );
    }

    /**
     * @param array<string, string> $criteria
     */
    public function countByCriteria(array $criteria): int
    {
        $query = Execution::query();

        if (isset($criteria['platform'])) {
            $query->where('platform', $criteria['platform']);
        }

        if (isset($criteria['campaign'])) {
            $query->where('campaign', $criteria['campaign']);
        }

        if (isset($criteria['version'])) {
            $query->where('version', $criteria['version']);
        }

        return $query->count();
    }
}
