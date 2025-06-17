<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\WebUploadReportRequest;
use App\Services\AbstractReportImporter;
use App\Services\ReportUploadService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

final class UploadController extends Controller
{
    public function __construct(
        private readonly ReportUploadService $uploadService
    ) {}

    /**
     * Display the upload form
     */
    public function index(): InertiaResponse
    {
        return Inertia::render('reports/upload', [
            'platforms' => AbstractReportImporter::FILTER_PLATFORMS,
            'databases' => AbstractReportImporter::FILTER_DATABASES,
            'campaigns' => \App\Services\ReportPlaywrightImporter::FILTER_CAMPAIGNS,
        ]);
    }

    /**
     * Handle report upload from web interface
     */
    public function store(WebUploadReportRequest $request): RedirectResponse
    {
        try {
            // Validate and upload file
            $uploadResult = $this->uploadService->uploadReport(
                $request->file('report')
            );

            // Process the uploaded report
            $execution = $this->uploadService->processReport(
                $uploadResult['filename'],
                [
                    'platform' => $request->input('platform'),
                    'database' => $request->input('database'),
                    'campaign' => $request->input('campaign'),
                    'version' => $request->input('version'),
                    'force' => $request->boolean('force'),
                ]
            );

            return redirect()->route('reports.show', ['idReport' => $execution->id])
                ->with('success', 'Report uploaded and processed successfully!');

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
