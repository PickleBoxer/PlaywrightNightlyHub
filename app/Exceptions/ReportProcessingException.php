<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class ReportProcessingException extends Exception
{
    public static function fileNotReadable(string $filename): self
    {
        return new self(sprintf('Could not read the file %s', $filename));
    }

    public static function invalidJson(string $filename): self
    {
        return new self(sprintf('Could not parse the file %s', $filename));
    }

    public static function invalidVersion(string $version, string $filename): self
    {
        return new self(sprintf(
            'Version found not correct (%s) from filename %s',
            $version,
            $filename
        ));
    }

    public static function similarEntryExists(
        string $version,
        string $platform,
        string $campaign,
        string $database,
        string $date
    ): self {
        return new self(sprintf(
            'A similar entry was found (criteria: version %s, platform %s, campaign %s, database %s, date %s).',
            $version,
            $platform,
            $campaign,
            $database,
            $date
        ));
    }
}
