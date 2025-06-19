<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Execution;
use App\Models\Test;
use Illuminate\Support\Collection;

final class TestRepository
{
    /**
     * @return Collection<Test>
     */
    public function findComparisonData(Execution $current, Execution $previous): Collection
    {
        return Test::query()
            ->select([
                'tests.state as old_test_state',
                't2.state as current_test_state',
            ])
            ->join('suites', 'suites.id', '=', 'tests.suite_id')
            ->leftJoin('tests as t2', function ($join): void {
                $join->on('t2.identifier', '=', 'tests.identifier')
                    ->whereNotNull('t2.identifier');
            })
            ->join('suites as s2', 's2.id', '=', 't2.suite_id')
            ->where('suites.execution_id', $previous->id)
            ->where('s2.execution_id', $current->id)
            ->distinct()
            ->get();
    }
}
