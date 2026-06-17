<?php

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\PeriodOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Reports\Enums\ReportStatus;
use Modules\Reports\Http\Requests\StoreReportRequest;
use Modules\Reports\Jobs\GenerateReportJob;
use Modules\Reports\Models\Report;
use Modules\Reports\Services\ReportAppendixCsvExporter;
use Modules\Reports\Services\ReportAppendixExcelExporter;
use Modules\Reports\Services\ReportDataAssembler;
use Modules\Reports\Support\ReportConfig;
use Modules\Workspaces\Models\Workspace;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkspaceReportController extends Controller
{
    public function index(Workspace $workspace): Response
    {
        $this->authorize('view', $workspace);

        $reports = Report::query()
            ->where('workspace_id', $workspace->id)
            ->latest()
            ->limit(20)
            ->get(['id', 'name', 'period_start', 'period_end', 'status', 'generated_at', 'created_at']);

        $hasConnectedAssets = ConnectedAsset::query()
            ->whereHas('connection', fn ($query) => $query->where('workspace_id', $workspace->id))
            ->where('is_active', true)
            ->exists();

        return Inertia::render('Reports/Index', [
            'workspace' => $workspace->only(['id', 'name']),
            'hasConnectedAssets' => $hasConnectedAssets,
            'reports' => $reports->map(fn (Report $report) => [
                'id' => $report->id,
                'name' => $report->name,
                'period_start' => $report->period_start->toDateString(),
                'period_end' => $report->period_end->toDateString(),
                'status' => $report->status->value,
                'status_label' => $report->status->label(),
                'generated_at' => $report->generated_at?->toIso8601String(),
                'created_at' => $report->created_at?->toIso8601String(),
            ]),
        ]);
    }

    public function create(Workspace $workspace): Response
    {
        $this->authorize('view', $workspace);

        $hasConnectedAssets = ConnectedAsset::query()
            ->whereHas('connection', fn ($query) => $query->where('workspace_id', $workspace->id))
            ->where('is_active', true)
            ->exists();

        return Inertia::render('Reports/Create', [
            'workspace' => $workspace->only(['id', 'name']),
            'hasConnectedAssets' => $hasConnectedAssets,
            'periodOptions' => PeriodOptions::presets(includeCustom: true),
            'sectionOptions' => collect(ReportConfig::SECTIONS)->map(fn (string $section) => [
                'key' => $section,
                'label' => __('app.reports.section_labels.'.$section),
            ])->values(),
            'metricOptions' => collect(ReportConfig::METRICS)->map(fn (string $metric) => [
                'key' => $metric,
                'label' => __('app.reports.metric_labels.'.$metric),
            ])->values(),
        ]);
    }

    public function store(StoreReportRequest $request, Workspace $workspace): RedirectResponse
    {
        $dates = $request->periodDates();

        $logoPath = null;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store("reports/logos/{$workspace->id}", 'local');
        }

        $config = $request->reportConfig($logoPath);

        $name = $request->string('name')->value()
            ?: sprintf(
                'Reporte %s (%s — %s)',
                $workspace->name,
                $dates['start']->toDateString(),
                $dates['end']->toDateString(),
            );

        $report = Report::query()->create([
            'workspace_id' => $workspace->id,
            'created_by' => $request->user()?->id,
            'name' => $name,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
            'config' => $config,
            'status' => ReportStatus::Pending,
        ]);

        GenerateReportJob::dispatch($report->id);

        return redirect()
            ->route('workspaces.reports.show', [$workspace, $report])
            ->with('success', __('app.flash.reports.queued'));
    }

    public function show(Workspace $workspace, Report $report): Response
    {
        $this->authorize('view', $workspace);
        $this->ensureReportBelongsToWorkspace($workspace, $report);

        return Inertia::render('Reports/Show', [
            'workspace' => $workspace->only(['id', 'name']),
            'report' => [
                'id' => $report->id,
                'name' => $report->name,
                'title' => $report->config['title'] ?? $report->name,
                'period_start' => $report->period_start->toDateString(),
                'period_end' => $report->period_end->toDateString(),
                'status' => $report->status->value,
                'status_label' => $report->status->label(),
                'error_message' => $report->error_message,
                'generated_at' => $report->generated_at?->toIso8601String(),
                'created_at' => $report->created_at?->toIso8601String(),
                'config' => $report->config,
                'download_ready' => $report->isReady(),
                'preview_url' => $report->isReady() && Storage::disk('local')->exists($report->file_path)
                    ? route('workspaces.reports.preview', [$workspace, $report])
                    : null,
                'appendix_download_url' => $report->isReady()
                    ? route('workspaces.reports.appendix', [$workspace, $report])
                    : null,
                'appendix_excel_download_url' => $report->isReady()
                    ? route('workspaces.reports.appendix.excel', [$workspace, $report])
                    : null,
            ],
        ]);
    }

    public function preview(Workspace $workspace, Report $report): BinaryFileResponse
    {
        $this->authorize('view', $workspace);
        $this->ensureReportBelongsToWorkspace($workspace, $report);

        if (! $report->isReady() || ! Storage::disk('local')->exists($report->file_path)) {
            abort(404, 'El reporte aún no está disponible.');
        }

        $filename = str($report->name)->slug().'.pdf';

        return response()->file(
            Storage::disk('local')->path($report->file_path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ],
        );
    }

    public function download(Workspace $workspace, Report $report): StreamedResponse|HttpResponse
    {
        $this->authorize('view', $workspace);
        $this->ensureReportBelongsToWorkspace($workspace, $report);

        if (! $report->isReady() || ! Storage::disk('local')->exists($report->file_path)) {
            abort(404, 'El reporte aún no está disponible.');
        }

        $filename = str($report->name)->slug().'.pdf';

        return Storage::disk('local')->download($report->file_path, $filename);
    }

    public function downloadAppendix(
        Workspace $workspace,
        Report $report,
        ReportDataAssembler $assembler,
        ReportAppendixCsvExporter $exporter,
    ): StreamedResponse {
        $this->authorize('view', $workspace);
        $this->ensureReportBelongsToWorkspace($workspace, $report);

        if (! $report->isReady()) {
            abort(404, 'El reporte aún no está disponible.');
        }

        $appendix = $assembler->assemble($report, $workspace)['appendix'] ?? [];

        if ($appendix === []) {
            abort(404, 'No hay datos de anexo para exportar.');
        }

        $csv = $exporter->export($appendix);
        $filename = str($report->name)->slug().'-anexo.csv';

        return response()->streamDownload(
            static function () use ($csv): void {
                echo $csv;
            },
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    public function downloadAppendixExcel(
        Workspace $workspace,
        Report $report,
        ReportDataAssembler $assembler,
        ReportAppendixExcelExporter $exporter,
    ): StreamedResponse {
        $this->authorize('view', $workspace);
        $this->ensureReportBelongsToWorkspace($workspace, $report);

        if (! $report->isReady()) {
            abort(404, 'El reporte aún no está disponible.');
        }

        $appendix = $assembler->assemble($report, $workspace)['appendix'] ?? [];

        if ($appendix === []) {
            abort(404, 'No hay datos de anexo para exportar.');
        }

        $xlsx = $exporter->export($appendix);

        if ($xlsx === '') {
            abort(404, 'No hay datos de anexo para exportar.');
        }

        $filename = str($report->name)->slug().'-anexo.xlsx';

        return response()->streamDownload(
            static function () use ($xlsx): void {
                echo $xlsx;
            },
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        );
    }

    private function ensureReportBelongsToWorkspace(Workspace $workspace, Report $report): void
    {
        if ($report->workspace_id !== $workspace->id) {
            abort(404);
        }
    }
}
