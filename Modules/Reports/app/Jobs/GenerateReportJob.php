<?php

namespace Modules\Reports\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Modules\Reports\Contracts\ReportPdfGenerator;
use Modules\Reports\Enums\ReportStatus;
use Modules\Reports\Models\Report;
use Modules\Reports\Services\ReportDataAssembler;
use Modules\Workspaces\Models\Workspace;
use Throwable;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    /** @var array<int, int> */
    public array $backoff = [30, 120];

    public function __construct(
        public readonly int $reportId,
    ) {
        $this->onQueue('reports');
    }

    public function handle(
        ReportDataAssembler $assembler,
        ReportPdfGenerator $pdfGenerator,
    ): void {
        $report = Report::query()->with('workspace')->findOrFail($this->reportId);
        $workspace = $report->workspace;

        if (! $workspace instanceof Workspace) {
            throw new \RuntimeException('Report workspace not found.');
        }

        $report->update(['status' => ReportStatus::Generating]);

        $data = $assembler->assemble($report, $workspace);
        $html = view('reports::pdf.report', $data)->render();

        $relativePath = sprintf('reports/%d/%d.pdf', $workspace->id, $report->id);
        $absolutePath = Storage::disk('local')->path($relativePath);

        $pdfGenerator->generate($html, $absolutePath);

        $report->update([
            'status' => ReportStatus::Ready,
            'file_path' => $relativePath,
            'generated_at' => now(),
            'error_message' => null,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Report::query()
            ->whereKey($this->reportId)
            ->update([
                'status' => ReportStatus::Error,
                'error_message' => $exception?->getMessage() ?? 'Error desconocido al generar el reporte.',
            ]);
    }
}
