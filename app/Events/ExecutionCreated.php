<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Execution;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ExecutionCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Execution $execution
    ) {}
}
