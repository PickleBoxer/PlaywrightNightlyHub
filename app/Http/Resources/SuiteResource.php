<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SuiteResource extends JsonResource
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
            'title' => $this->title,
            'file' => $this->file,
            'tests' => $this->tests,
            'passes' => $this->passes,
            'failures' => $this->failures,
            'pending' => $this->pending,
            'skipped' => $this->skipped,
            'has_failures' => $this->failures > 0,
            'tests_data' => TestResource::collection($this->whenLoaded('tests')),
        ];
    }
}
