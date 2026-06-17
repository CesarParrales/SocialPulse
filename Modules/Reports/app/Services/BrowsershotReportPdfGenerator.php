<?php

namespace Modules\Reports\Services;

use Modules\Reports\Contracts\ReportPdfGenerator;
use Spatie\Browsershot\Browsershot;

class BrowsershotReportPdfGenerator implements ReportPdfGenerator
{
    public function generate(string $html, string $outputPath): void
    {
        $directory = dirname($outputPath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        Browsershot::html($html)
            ->landscape()
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->save($outputPath);
    }
}
