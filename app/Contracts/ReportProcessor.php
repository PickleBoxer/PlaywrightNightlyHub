<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Execution;

interface ReportProcessor
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function processReport(string $filename, array $options = []): Execution;
}
