<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Services\ReportSuiteBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DetailedExecutionResource extends JsonResource
{
    /**
     * @var array<int, string>
     */
    private array $filterStates;

    private ?string $search;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  array<int, string>  $filterStates
     */
    public function __construct($resource, array $filterStates = [], ?string $search = null)
    {
        parent::__construct($resource);
        $this->filterStates = $filterStates ?: ReportSuiteBuilder::FILTER_STATES;
        $this->search = $search;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $reportSuiteBuilder = app(ReportSuiteBuilder::class)
            ->filterStates($this->filterStates)
            ->filterSearch($this->search)
            ->filterEmptyArrays(true)
            ->build($this->resource);

        return [
            'id' => $this->id,
            'date' => $this->start_date->format('Y-m-d'),
            'version' => $this->version,
            'campaign' => $this->campaign,
            'platform' => $this->platform,
            'database' => $this->database,
            'start_date' => $this->start_date->format('Y-m-d H:i:s'),
            'end_date' => $this->end_date->format('Y-m-d H:i:s'),
            'duration' => $this->duration,
            'suites' => $this->suites,
            'tests' => $this->tests,
            'broken_since_last' => $this->broken_since_last,
            'fixed_since_last' => $this->fixed_since_last,
            'equal_since_last' => $this->equal_since_last,
            'skipped' => $this->skipped,
            'pending' => $this->pending,
            'passes' => $this->passes,
            'failures' => $this->failures,
            'suites_data' => $reportSuiteBuilder->toArray(),
        ];
    }
}
