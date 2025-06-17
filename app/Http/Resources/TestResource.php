<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TestResource extends JsonResource
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
            'full_title' => $this->full_title,
            'file' => $this->file,
            'duration' => $this->duration,
            'state' => $this->state,
            'speed' => $this->speed,
            'error' => $this->error,
            'code' => $this->code,
            'diff' => $this->diff,
            'stack' => $this->stack,
            'error_message' => $this->error_message,
        ];
    }
}
