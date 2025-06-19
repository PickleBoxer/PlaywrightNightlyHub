<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ReportShowRequest;
use App\Http\Requests\ReportsIndexRequest;
use App\Http\Resources\DetailedExecutionResource;
use App\Http\Resources\ExecutionCollection;
use App\Models\Execution;
use App\Repositories\ExecutionRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReportController extends Controller
{
    public function __construct(
        private readonly ExecutionRepository $executionRepository,
    ) {}

    public function index(ReportsIndexRequest $request): InertiaResponse
    {
        $validated = $request->validated();

        // Build optimized query using model scopes
        $executions = Execution::select([
            'id', 'filename', 'version', 'campaign', 'platform', 'database',
            'start_date', 'end_date', 'duration', 'suites', 'tests',
            'passes', 'failures', 'pending', 'skipped',
            'broken_since_last', 'fixed_since_last', 'equal_since_last',
        ])
            ->platform($validated['platform'] ?? null)
            ->campaign($validated['campaign'] ?? null)
            ->version($validated['version'] ?? null)
            ->search($validated['search'] ?? null)
            ->orderBy('start_date', 'desc')
            ->paginate($validated['per_page'] ?? 15)
            ->withQueryString();

        // Cache filter options for better performance
        $platforms = Cache::remember('execution_platforms', 300, fn () => Execution::distinct('platform')->pluck('platform')->sort()->values());

        $campaigns = Cache::remember('execution_campaigns', 300, fn () => Execution::distinct('campaign')->pluck('campaign')->sort()->values());

        $versions = Cache::remember('execution_versions', 300, fn (): array => $this->executionRepository->findAllVersions());

        return Inertia::render('reports/index', [
            'reports' => (new ExecutionCollection($executions)),
            'filters' => $request->only(['platform', 'campaign', 'version', 'search', 'per_page']),
            'platforms' => $platforms,
            'campaigns' => $campaigns,
            'versions' => $versions,
        ]);
    }

    public function download(int $id): StreamedResponse
    {
        $execution = Execution::findOrFail($id);
        $path = 'reports/'.$execution->filename;

        if (! Storage::exists($path)) {
            abort(404, 'Report file not found');
        }

        return Storage::download($path, $execution->filename, [
            'Content-Type' => 'application/json',
        ]);
    }    public function show(int $idReport, ReportShowRequest $request): InertiaResponse
    {
        $execution = Execution::findOrFail($idReport);

        $validated = $request->validated();
        $filterStates = $validated['filter_state'] ?? [];
        $search = $validated['search'] ?? null;

        // Create the resource and unwrap the 'data' wrapper to maintain the expected structure
        $resource = new DetailedExecutionResource($execution, $filterStates, $search);

        return Inertia::render('reports/show', [
            'report' => $resource->resolve($request),
        ]);
    }
}
