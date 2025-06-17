<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class ExecutionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $downloadUrl = Storage::exists('reports/'.$this->filename)
            ? route('reports.download', ['id' => $this->id])
            : null;

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
            'tests' => [
                'total' => $this->tests,
                'passed' => $this->passes,
                'failed' => $this->failures,
                'pending' => $this->pending,
                'skipped' => $this->skipped,
            ],
            'success_rate' => $this->tests > 0
                ? round(($this->passes / $this->tests) * 100, 1)
                : 0,
            'broken_since_last' => $this->broken_since_last,
            'fixed_since_last' => $this->fixed_since_last,
            'equal_since_last' => $this->equal_since_last,
            'download' => $downloadUrl,
            'status' => $this->failures > 0 ? 'failed' : 'passed',
        ];
    }
}
