<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Execution;
use App\Models\Suite;
use App\Models\Test;
use DateTimeImmutable;
use Exception;
use Illuminate\Support\Facades\DB;
use Str;

final class ReportPlaywrightImporter extends AbstractReportImporter
{
    public const FILTER_CAMPAIGNS = [
        'blockwishlist',
        'ps_cashondelivery',
        'autoupgrade',
        'vapeinitaly',
    ];

    public function import(
        string $filename,
        string $platform,
        string $database,
        string $campaign,
        string $version,
        DateTimeImmutable $startDate,
        object $jsonContent,
    ): Execution {
        $endDate = clone $startDate;
        $endDate->modify('+ '.(int) $jsonContent->stats->duration.' milliseconds');

        $execution = new Execution;
        $execution->ref = date('YmdHis');
        $execution->filename = $filename;
        $execution->platform = $platform;
        $execution->database = $database;
        $execution->campaign = $campaign;
        $execution->start_date = $startDate;
        $execution->end_date = $endDate;
        $execution->duration = (int) $jsonContent->stats->duration;
        $execution->version = $version;
        $execution->suites = count($jsonContent->suites);
        $execution->failures = 0;
        $execution->insertion_start_date = now();
        $execution->save();

        $countFailures = $countPasses = $countPending = $countSkipped = $countTests = 0;

        DB::beginTransaction();
        try {
            foreach ($jsonContent->suites as $suite) {
                foreach ($suite->suites as $suiteChild) {
                    $executionSuite = $this->insertExecutionSuite($execution, $suiteChild);
                    $countFailures += $executionSuite->total_failures;
                    $countPasses += $executionSuite->total_passes;
                    $countPending += $executionSuite->total_pending;
                    $countSkipped += $executionSuite->total_skipped;
                    $countTests += $executionSuite->total_failures + $executionSuite->total_passes + $executionSuite->total_pending + $executionSuite->total_skipped;
                }
            }

            $execution->tests = $countTests;
            $execution->failures = $countFailures;
            $execution->passes = $countPasses;
            $execution->pending = $countPending;
            $execution->skipped = $countSkipped;
            $execution->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->processComparison($execution);
    }

    private function insertExecutionSuite(Execution $execution, object $suite, ?int $parentSuiteId = null): Suite
    {
        $executionSuite = new Suite;
        $executionSuite->execution_id = $execution->id;
        $executionSuite->uuid = Str::uuid()->toString(); // To be generated
        $executionSuite->title = $suite->title;
        $executionSuite->has_suites = false;
        $executionSuite->has_tests = ! empty($suite->specs);
        $executionSuite->parent_id = $parentSuiteId;
        $executionSuite->campaign = $this->extractDataFromFile('/'.$suite->file, 'campaign');
        $executionSuite->file = $this->extractDataFromFile('/'.$suite->file, 'file');
        $executionSuite->insertion_date = now();
        $executionSuite->has_failures = false;
        $executionSuite->save();

        // Insert tests
        $countFailures = $countPasses = $countPending = $countSkipped = $duration = 0;

        foreach ($suite->specs as $spec) {
            $identifier = '';
            $attachments = $spec->tests[0]->results[0]->attachments;

            foreach ($attachments as $attachment) {
                if ($attachment->name === 'testInfo') {
                    $info = json_decode($attachment->body);
                    $identifier = $info->testId ?? '';
                    break;
                }
            }

            $state = 'passed';
            $errorMessage = $stackTrace = $diff = null;

            // Get last result which is the real state
            $result = end($spec->tests[0]->results);

            if ($result->status === 'failed') {
                $state = 'failed';
                $errorMessage = $result->error->message ?? null;
                $stackTrace = $result->error->stack ?? null;
            } elseif ($result->status === 'skipped') {
                $state = 'skipped';
            }

            $test = new Test;
            $test->suite_id = $executionSuite->id;
            $test->uuid = ''; // To be generated
            $test->title = $spec->title;
            $test->duration = $spec->tests[0]->results[0]->duration;
            $test->identifier = $identifier;
            $test->state = $state;
            $test->error_message = $errorMessage;
            $test->stack_trace = $stackTrace;
            $test->diff = $diff;
            $test->insertion_date = now();
            $test->save();

            $duration += $spec->tests[0]->results[0]->duration;

            if ($state === 'failed') {
                $countFailures++;
                $executionSuite->has_failures = true;
            } elseif ($state === 'skipped') {
                $countSkipped++;
                $executionSuite->has_skipped = true;
            } elseif ($state === 'pending') {
                $countPending++;
                $executionSuite->has_pending = true;
            } else {
                $countPasses++;
                $executionSuite->has_passes = true;
            }
        }

        $executionSuite->total_failures = $countFailures;
        $executionSuite->total_passes = $countPasses;
        $executionSuite->total_pending = $countPending;
        $executionSuite->total_skipped = $countSkipped;
        $executionSuite->duration = $duration;
        $executionSuite->save();

        return $executionSuite;
    }
}
