<?php

declare(strict_types=1);

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadReportRequest;
use App\Services\ReportUploadService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class UploadReportController extends Controller
{
    private readonly string $nightlyToken;

    public function __construct(
        private readonly ReportUploadService $uploadService
    ) {
        $this->nightlyToken = config('app.nightly_token');
    }

    /**
     * API endpoint for uploading a report file
     */
    public function __invoke(UploadReportRequest $request): JsonResponse
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
