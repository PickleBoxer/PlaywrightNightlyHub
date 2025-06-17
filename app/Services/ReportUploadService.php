<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Execution;
use App\Repositories\ExecutionRepository;
use DateTime;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function str_replace;

final class ReportUploadService
{
    private string $reportPath;

    public function __construct(
        private readonly ExecutionRepository $executionRepository,
        private readonly ReportPlaywrightImporter $playwrightImporter
    ) {
        $this->reportPath = config('app.nightly_report_path', 'reports');
    }

    /**
     * Upload and store a report file
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function uploadReport(UploadedFile $file, array $options = []): array
    {
        // Get original filename or use a custom one if provided
        $filename = $options['filename'] ?? $file->getClientOriginalName();

        // Store the file in the reports directory
        $path = Storage::putFileAs($this->reportPath, $file, $filename);

        if (! $path) {
            throw new Exception('Failed to store the uploaded file.');
        }

        return [
            'filename' => $filename,
            'path' => $path,
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ];
    }

    /**
     * Process an uploaded report file and import it
     *
     * @param  array<string, mixed>  $options
     *
     * @throws Exception
     */
    public function processReport(string $filename, array $options = []): Execution
    {
        $fileContent = Storage::get($this->reportPath.'/'.$filename);
        if (! $fileContent) {
            throw new Exception(sprintf('Could not read the file %s', $filename));
        }

        $jsonContent = json_decode($fileContent);
        if (! $jsonContent) {
            throw new Exception(sprintf('Could not parse the file %s', $filename));
        }

        // Extract information from filename or use provided options
        $platform = $options['platform'] ?? AbstractReportImporter::FILTER_PLATFORMS[0];
        $database = $options['database'] ?? AbstractReportImporter::FILTER_DATABASES[0];
        $campaign = $options['campaign'] ?? '';
        $version = $options['version'] ?? $this->extractVersionFromFilename($filename);

        // Create DateTime from report or use current time
        $startDateString = $jsonContent->stats->start ?? $jsonContent->stats->startTime ?? date(DateTime::RFC3339_EXTENDED);
        $startDate = DateTime::createFromFormat(DateTime::RFC3339_EXTENDED, $startDateString);

        if ($startDate === false) {
            // Fallback to current time if parsing fails
            $startDate = new DateTime();
        }

        // Check if similar report exists if not forcing
        if (
            empty($options['force']) && $this->executionRepository->findByNightly(
                $version,
                $platform,
                $campaign,
                $database,
                $startDate->format('Y-m-d')
            )
        ) {
            throw new Exception(sprintf(
                'A similar entry was found (criteria: version %s, platform %s, campaign %s, database %s, date %s).',
                $version,
                $platform,
                $campaign,
                $database,
                $startDate->format('Y-m-d')
            ));
        }

        // Import the report using the playwright importer
        return $this->playwrightImporter->import(
            $filename,
            $platform,
            $database,
            $campaign,
            $version,
            $startDate,
            $jsonContent
        );
    }

    /**
     * Extract version from filename using regex
     *
     * @throws Exception
     */
    private function extractVersionFromFilename(string $filename): string
    {
        preg_match(AbstractReportImporter::REGEX_FILE, $filename, $matchesVersion);
        if (! isset($matchesVersion[1])) {
            throw new Exception('Could not retrieve version from filename');
        }

        $version = str_replace('_', ' ', $matchesVersion[1]);
        if (mb_strlen($version) < 1) {
            throw new Exception(sprintf(
                'Version found not correct (%s) from filename %s',
                $version,
                $filename
            ));
        }

        return $version;
    }
}
