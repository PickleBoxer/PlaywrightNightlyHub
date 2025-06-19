<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Execution;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Execution
 */
final class UploadReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'version' => $this->version,
            'campaign' => $this->campaign,
            'platform' => $this->platform,
            'database' => $this->database,
            'start_date' => $this->start_date->format('Y-m-d H:i:s'),
            'tests' => $this->tests,
            'passes' => $this->passes,
            'failures' => $this->failures,
            'success_rate' => $this->tests > 0
                ? round(($this->passes / $this->tests) * 100, 1)
                : 0,
            'view_url' => route('reports.show', ['idReport' => $this->id]),
        ];
    }
}
