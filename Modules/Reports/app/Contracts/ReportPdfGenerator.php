<?php

namespace Modules\Reports\Contracts;

interface ReportPdfGenerator
{
    public function generate(string $html, string $outputPath): void;
}
