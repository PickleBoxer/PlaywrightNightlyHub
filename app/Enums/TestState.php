<?php

declare(strict_types=1);

namespace App\Enums;

enum TestState: string
{
    case FAILED = 'failed';
    case PASSED = 'passed';
    case SKIPPED = 'skipped';
    case PENDING = 'pending';
}
