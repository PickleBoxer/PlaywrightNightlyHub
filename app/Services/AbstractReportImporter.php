<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ReportImporter;
use App\Enums\DatabaseType;
use App\Enums\PlatformType;
use App\Enums\TestState;
use App\Models\Execution;
use App\Repositories\ExecutionRepository;
use App\Repositories\TestRepository;

abstract class AbstractReportImporter implements ReportImporter
{
    public const REGEX_FILE = '/[0-9]{4}-[0-9]{2}-[0-9]{2}-([^-]*)[-]?(.*)]?\.json/';

    /** @var array<string> */
    private readonly array $platforms;

    /** @var array<string> */
    private readonly array $databases;

    public function __construct(
        protected readonly ExecutionRepository $executionRepository,
        protected readonly TestRepository $testRepository
    ) {
        $this->platforms = array_column(PlatformType::cases(), 'value');
        $this->databases = array_column(DatabaseType::cases(), 'value');
    }

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
        }
        if ($type === 'file') {
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

        if (! $executionPrevious instanceof Execution) {
            return $execution;
        }

        $data = $this->testRepository->findComparisonData($execution, $executionPrevious);

        if ($data->isEmpty()) {
            return $execution;
        }

        // Reset
        $execution->fixed_since_last = 0;
        $execution->broken_since_last = 0;
        $execution->equal_since_last = 0;

        // A test is "fixed" if it went from failed to passed
        $execution->fixed_since_last = $data->filter(function ($datum) {
            return $datum->old_test_state === TestState::FAILED->value
                && $datum->current_test_state === TestState::PASSED->value;
        })->count();

        // A test is "broken" if it went from passed to failed
        $execution->broken_since_last = $data->filter(function ($datum) {
            return $datum->old_test_state === TestState::PASSED->value
                && $datum->current_test_state === TestState::FAILED->value;
        })->count();

        // A test is "equal" if it's failed in both executions
        $execution->equal_since_last = $data->filter(function ($datum) {
            return $datum->old_test_state === TestState::FAILED->value
                && $datum->current_test_state === TestState::FAILED->value;
        })->count();

        $execution->save();

        return $execution;
    }
}
