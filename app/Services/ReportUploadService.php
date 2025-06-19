<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ReportProcessor;
use App\Enums\DatabaseType;
use App\Enums\PlatformType;
use App\Exceptions\ReportProcessingException;
use App\Models\Execution;
use App\Repositories\ExecutionRepository;
use DateTime;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function str_replace;

final readonly class ReportUploadService implements ReportProcessor
{
    private string $reportPath;

    public function __construct(
        private ExecutionRepository $executionRepository,
        private ReportPlaywrightImporter $playwrightImporter,
        ?string $reportPath = null
    ) {
        $this->reportPath = $reportPath ?? config('app.nightly_report_path', 'reports');
    }

    /**
     * Upload and store a report file
     *
     * @param  array<string, mixed>  $options
     * @return array{
     *     filename: string,
     *     path: string,
     *     size: int,
     *     mime: string|null
     * }
     *
     * @throws ReportProcessingException
     */
    public function uploadReport(UploadedFile $file, array $options = []): array
    {
        // Get original filename or use a custom one if provided
        $filename = $options['filename'] ?? $file->getClientOriginalName();

        // Store the file in the reports directory
        $path = Storage::putFileAs($this->reportPath, $file, $filename);

        if (! $path) {
            throw new ReportProcessingException('Failed to store the uploaded file.');
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
     * @throws ReportProcessingException
     */
    public function processReport(string $filename, array $options = []): Execution
    {
        try {
            // Using Storage::json() which automatically decodes as array
            // This is more efficient for large files as it combines reading and decoding
            $jsonContent = Storage::json($this->reportPath.'/'.$filename);
        } catch (\JsonException $e) {
            throw ReportProcessingException::invalidJson($filename);
        } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $e) {
            throw ReportProcessingException::fileNotReadable($filename);
        }

        // Extract information from filename or use provided options
        $platform = $options['platform'] ?? PlatformType::CHROMIUM->value;
        $database = $options['database'] ?? DatabaseType::MYSQL->value;
        $campaign = $options['campaign'] ?? 'unknown';
        $version = $options['version'] ?? $this->extractVersionFromFilename($filename);

        // Create DateTime from report or use current time
        $startDateString = $jsonContent['stats']['start']
            ?? $jsonContent['stats']['startTime']
            ?? date(DateTime::RFC3339_EXTENDED);
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
            throw ReportProcessingException::similarEntryExists(
                $version,
                $platform,
                $campaign,
                $database,
                $startDate->format('Y-m-d')
            );
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
     * @throws ReportProcessingException
     */
    private function extractVersionFromFilename(string $filename): string
    {
        preg_match(AbstractReportImporter::REGEX_FILE, $filename, $matchesVersion);
        if (! isset($matchesVersion[1])) {
            throw ReportProcessingException::fileNotReadable($filename);
        }

        $version = str_replace('_', ' ', $matchesVersion[1]);
        if (mb_strlen($version) < 1) {
            throw ReportProcessingException::invalidVersion($version, $filename);
        }

        return $version;
    }
}
