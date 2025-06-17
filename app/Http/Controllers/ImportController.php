<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UploadReportRequest;
use App\Http\Resources\UploadReportResource;
use App\Services\ReportUploadService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ImportController extends Controller
{
    private string $nightlyToken;

    public function __construct(
        private readonly ReportUploadService $uploadService
    ) {
        $this->nightlyToken = config('app.nightly_token');
    }

    /**
     * API endpoint for importing a playwright report
     */
    public function importPlaywright(Request $request): JsonResponse
    {
        // Validate basic request requirements
        if ($request->getContent()) {
            return response()->json([
                'message' => 'This endpoint does not accept body content.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $token = $request->query('token');
        $filename = $request->query('filename');

        if (! $token || ! $filename) {
            return response()->json([
                'message' => 'Missing required parameters: token and filename.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($token !== $this->nightlyToken) {
            return response()->json([
                'message' => 'Invalid token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            // Process the report
            $execution = $this->uploadService->processReport($filename, [
                'platform' => $request->query('platform'),
                'database' => $request->query('database'),
                'campaign' => $request->query('campaign'),
                'force' => $request->query->has('force'),
            ]);

            // Return success with resource
            UploadReportResource::withoutWrapping();

            return response()->json([
                'status' => 'ok',
                'report' => new UploadReportResource($execution),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * API endpoint for uploading a report file
     */
    public function uploadReport(UploadReportRequest $request): JsonResponse
    {
        $token = $request->validated('token');

        if ($token !== $this->nightlyToken) {
            return response()->json([
                'message' => 'Invalid token',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $file = $request->file('report');
            $result = $this->uploadService->uploadReport($file);

            return response()->json([
                'status' => 'ok',
                'filename' => $result['filename'],
                'path' => $result['path'],
                'message' => 'Report uploaded successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to upload file: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
