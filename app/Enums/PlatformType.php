<?php

declare(strict_types=1);

namespace App\Enums;

enum PlatformType: string
{
    case CHROMIUM = 'chromium';
    case FIREFOX = 'firefox';
    case EDGE = 'edge';
    case CLI = 'cli';
}
