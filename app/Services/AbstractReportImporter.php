<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Execution;
use App\Repositories\ExecutionRepository;
use App\Repositories\TestRepository;

abstract class AbstractReportImporter
{
    public const REGEX_FILE = '/([0-9]{4}-[0-9]{2}-[0-9]{2})-(.*)?\.json/';

    public const FILTER_PLATFORMS = ['chromium', 'firefox', 'edge', 'cli'];

    public const FILTER_DATABASES = ['mysql', 'postgres'];

    public function __construct(
        protected ExecutionRepository $executionRepository,
        protected TestRepository $testRepository
    ) {}

    protected function extractDataFromFile(string $filename, string $type): string
    {
        if ($type === 'campaign') {
            if (str_contains($filename, 'campaigns/sanity')) {
                return 'sanity';
            }
            if (str_contains($filename, 'campaigns/functional')) {
                return 'functional';
            }
            if (str_contains($filename, 'campaigns/e2e')) {
                return 'e2e';
            }
            if (str_contains($filename, 'campaigns/regression')) {
                return 'regression';
            }
            if (str_contains($filename, 'campaigns/modules/autoupgrade')) {
                return 'autoupgrade';
            }
            if (str_contains($filename, 'modules/ps_emailsubscription')) {
                return 'ps_emailsubscription';
            }
        } elseif ($type === 'file') {
            // Get the file name without the campaign part
            $parts = explode('/', $filename);

            return end($parts);
        }

        return '';
    }

    protected function processComparison(Execution $execution): Execution
    {
        if (! $execution->start_date) {
            return $execution;
        }

        $executionPrevious = $this->executionRepository->findByNightlyBefore(
            $execution->version,
            $execution->platform,
            $execution->campaign,
            $execution->database,
            $execution->start_date
        );

        if (! $executionPrevious) {
            return $execution;
        }

        $data = $this->testRepository->findComparisonData($execution, $executionPrevious);
        if (empty($data)) {
            return $execution;
        }

        // Reset
        $execution->fixed_since_last = 0;
        $execution->broken_since_last = 0;
        $execution->equal_since_last = 0;

        foreach ($data as $datum) {
            if ($datum->old_test_state === 'failed' && $datum->current_test_state === 'failed') {
                $execution->equal_since_last += 1;
            }
            if ($datum->old_test_state === 'failed' && $datum->current_test_state !== 'failed') {
                $execution->fixed_since_last += 1;
            }
            if ($datum->old_test_state !== 'failed' && $datum->current_test_state === 'failed') {
                $execution->broken_since_last += 1;
            }
        }

        $execution->save();

        return $execution;
    }
}
