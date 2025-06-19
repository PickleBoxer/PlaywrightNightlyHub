<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Execution;
use DateTime;

interface ReportImporter
{
    public function import(
        string $filename,
        string $platform,
        string $database,
        string $campaign,
        string $version,
        DateTime $startDate,
        object $jsonContent,
    ): Execution;
}
