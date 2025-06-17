<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Execution;
use Illuminate\Support\Facades\DB;

final class TestRepository
{
    public function findComparisonData(Execution $current, Execution $previous): array
    {
        return DB::table('tests as t1')
            ->select([
                't1.state as old_test_state',
                't2.state as current_test_state',
            ])
            ->join('suites as s1', 's1.id', '=', 't1.suite_id')
            ->leftJoin('tests as t2', function ($join) {
                $join->on('t2.identifier', '=', 't1.identifier')
                    ->whereNotNull('t2.identifier');
            })
            ->join('suites as s2', 's2.id', '=', 't2.suite_id')
            ->where('s1.execution_id', '=', $previous->id)
            ->where('s2.execution_id', '=', $current->id)
            ->where('t1.identifier', '!=', 'loginBO')
            ->where('t2.identifier', '!=', 'loginBO')
            ->whereRaw('(t1.state = "failed" OR t2.state = "failed")')
            ->get()
            ->toArray();
    }
}
