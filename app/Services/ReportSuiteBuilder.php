<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Execution;
use App\Models\Suite;
use App\Models\Test;

final class ReportSuiteBuilder
{
    public const FILTER_STATE_FAILED = 'failed';

    public const FILTER_STATE_PASSED = 'passed';

    public const FILTER_STATE_SKIPPED = 'skipped';

    public const FILTER_STATE_PENDING = 'pending';

    public const FILTER_STATES = [
        self::FILTER_STATE_FAILED,
        self::FILTER_STATE_PASSED,
        self::FILTER_STATE_SKIPPED,
        self::FILTER_STATE_PENDING,
    ];

    /** @var array<int, string> */
    private array $filterStates = self::FILTER_STATES;

    private ?string $filterSearch = null;

    private ?int $filterSuiteId = null;

    private bool $filterEmptyArrays = true;

    /** @var array<int, Suite> */
    private array $suites = [];

    /** @var array<int, array<int, Test>> */
    private array $tests = [];

    /** @var array<int, array<string, int>> */
    private array $stats = [];

    /** @var array<int, mixed> */
    private array $result = [];

    /**
     * @param  array<int, string>  $filterStates
     */
    public function filterStates(array $filterStates): self
    {
        $this->filterStates = $filterStates;

        return $this;
    }

    public function filterSearch(?string $filterSearch): self
    {
        $this->filterSearch = $filterSearch;

        return $this;
    }

    public function filterSuiteId(?int $filterSuiteId): self
    {
        $this->filterSuiteId = $filterSuiteId;

        return $this;
    }

    public function filterEmptyArrays(bool $filterEmptyArrays): self
    {
        $this->filterEmptyArrays = $filterEmptyArrays;

        return $this;
    }

    public function build(Execution $execution): self
    {
        $this->stats = [];
        $this->result = [];

        $query = Suite::with(['tests'])
            ->where('execution_id', $execution->id);

        if ($this->filterSuiteId !== null && $this->filterSuiteId !== 0) {
            $query->where('id', $this->filterSuiteId);
        }

        $suites = $query->get();

        foreach ($suites as $suite) {
            $this->suites[$suite->id] = $suite;
        }

        // Get all tests for all suites
        foreach ($this->suites as $suite) {
            $this->tests[$suite->id] = [];
            foreach ($suite->tests as $test) {
                if (! in_array($test->state, $this->filterStates)) {
                    continue;
                }

                if ($this->filterSearch &&
                    ! str_contains(mb_strtolower((string) $test->title), mb_strtolower($this->filterSearch)) &&
                    ! str_contains(mb_strtolower($test->error_message ?? ''), mb_strtolower($this->filterSearch))
                ) {
                    continue;
                }

                $this->tests[$suite->id][] = $test;

                // If parent exists, add stat
                if ($suite->parent_id) {
                    if (! isset($this->stats[$suite->parent_id])) {
                        $this->stats[$suite->parent_id] = [
                            'failed' => 0,
                            'passed' => 0,
                            'skipped' => 0,
                            'pending' => 0,
                        ];
                    }

                    $this->stats[$suite->parent_id][$test->state]++;
                }
            }
        }

        // Format the result with only root suites
        foreach ($this->suites as $suite) {
            if (! $suite->parent_id) {
                $this->result[$suite->id] = $this->formatSuite($suite);
            }
        }

        return $this;
    }

    /**
     * @return array<int, mixed>
     */
    public function toArray(): array
    {
        return $this->result;
    }

    public function buildSingleSuite(Suite $suite): array
    {
        $this->suites = [];
        $this->tests = [];
        $this->stats = [];

        // Get suite with all tests
        $suite->load('tests', 'childSuites');
        $this->suites[$suite->id] = $suite;

        // Add child suites
        foreach ($suite->childSuites as $childSuite) {
            $childSuite->load('tests');
            $this->suites[$childSuite->id] = $childSuite;
        }

        // Format tests
        foreach ($this->suites as $s) {
            $this->tests[$s->id] = [];
            foreach ($s->tests as $test) {
                $this->tests[$s->id][] = $test;
            }
        }

        return $this->formatSuite($suite);
    }

    private function formatSuite(Suite $suite): array
    {
        $suites = [];
        $tests = [];

        // Add child suites
        foreach ($this->suites as $suiteChild) {
            if ($suiteChild->parent_id === $suite->id) {
                $suites[$suiteChild->id] = $this->formatSuite($suiteChild);
            }
        }

        // Add tests
        if (isset($this->tests[$suite->id])) {
            foreach ($this->tests[$suite->id] as $test) {
                $tests[] = $this->formatTest($test);
            }
        }

        $data = [
            'id' => $suite->id,
            'execution_id' => $suite->execution_id,
            'uuid' => $suite->uuid,
            'title' => $suite->title,
            'campaign' => $suite->campaign,
            'file' => $suite->file,
            'duration' => $suite->duration,
            'hasSkipped' => $suite->has_skipped ? 1 : 0,
            'hasPending' => $suite->has_pending ? 1 : 0,
            'hasPasses' => $suite->has_passes ? 1 : 0,
            'hasFailures' => $suite->has_failures ? 1 : 0,
            'totalSkipped' => $suite->total_skipped,
            'totalPending' => $suite->total_pending,
            'totalPasses' => $suite->total_passes,
            'totalFailures' => $suite->total_failures,
            'hasSuites' => $suite->has_suites ? 1 : 0,
            'hasTests' => $suite->has_tests ? 1 : 0,
            'parent_id' => $suite->parent_id,
            'insertion_date' => $suite->insertion_date->format('Y-m-d H:i:s'),
            'suites' => $suites,
            'tests' => $tests,
            'childrenData' => $this->stats[$suite->id] ?? [],
        ];

        if ($this->filterEmptyArrays) {
            return array_filter($data, fn ($value): bool => ! is_array($value) || $value !== []);
        }

        return $data;
    }

    private function formatTest(Test $test): array
    {
        return [
            'id' => $test->id,
            'suite_id' => $test->suite_id,
            'uuid' => $test->uuid,
            'title' => $test->title,
            'state' => $test->state,
            'duration' => $test->duration,
            'error_message' => $test->error_message,
            'stack_trace' => $test->stack_trace,
            'diff' => $test->diff,
            'insertion_date' => $test->insertion_date->format('Y-m-d H:i:s'),
        ];
    }
}
