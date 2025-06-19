<?php

declare(strict_types=1);

namespace App\Enums;

enum DatabaseType: string
{
    case MYSQL = 'mysql';
    case POSTGRES = 'postgres';
}
