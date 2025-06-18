<?php

declare(strict_types=1);

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportPlaywrightRequest;
use App\Http\Resources\UploadReportResource;
use App\Services\ReportUploadService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class ImportPlaywrightController extends Controller
{
    public function __construct(
        private readonly ReportUploadService $uploadService
    ) {}

    /**
     * API endpoint for importing a playwright report
     */
    public function __invoke(ImportPlaywrightRequest $request): JsonResponse
    {
        try {
            // Process the report
            $execution = $this->uploadService->processReport(
                $request->validated('filename'),
                $request->validatedData()
            );

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
}
