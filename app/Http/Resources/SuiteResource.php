<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Suite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Suite
 */
final class SuiteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /*return [
            'id' => $this->id,
            'title' => $this->title,
            'file' => $this->file,
            'tests' => $this->tests,
            'passes' => $this->passes,
            'failures' => $this->failures,
            'pending' => $this->pending,
            'skipped' => $this->skipped,
            'has_failures' => $this->failures > 0,
            'tests_data' => TestResource::collection($this->whenLoaded('tests')),
        ];*/
        return [
            'id' => $this->id,
            'execution_id' => $this->execution_id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'file' => $this->file,
            'duration' => $this->duration,
            'campaign' => $this->campaign,
            'insertion_date' => $this->insertion_date,

            // Status flags
            'has_skipped' => $this->has_skipped,
            'has_pending' => $this->has_pending,
            'has_passes' => $this->has_passes,
            'has_failures' => $this->has_failures,
            'has_suites' => $this->has_suites,
            'has_tests' => $this->has_tests,

            // Totals
            'total_skipped' => $this->total_skipped,
            'total_pending' => $this->total_pending,
            'total_passes' => $this->total_passes,
            'total_failures' => $this->total_failures,

            // Relationships
            'tests' => TestResource::collection($this->whenLoaded('tests')),
            'child_suites' => SuiteResource::collection($this->whenLoaded('childSuites')),
            'parent_suite' => new SuiteResource($this->whenLoaded('parentSuite')),
        ];
    }
}
