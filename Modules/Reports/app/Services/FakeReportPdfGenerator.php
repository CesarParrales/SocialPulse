<?php

namespace Modules\Reports\Services;

use Modules\Reports\Contracts\ReportPdfGenerator;

class FakeReportPdfGenerator implements ReportPdfGenerator
{
    public function generate(string $html, string $outputPath): void
    {
        $directory = dirname($outputPath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $content = "%PDF-1.4\n% SocialPulse test report\n".$html;

        file_put_contents($outputPath, $content);
    }
}
