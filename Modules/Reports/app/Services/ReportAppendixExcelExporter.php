<?php

namespace Modules\Reports\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportAppendixExcelExporter
{
    public function __construct(
        private readonly ReportAppendixTableBuilder $tables,
    ) {}

    /**
     * @param  array<string, mixed>  $appendix
     */
    public function export(array $appendix): string
    {
        $sections = $this->tables->build($appendix);

        if ($sections === []) {
            return '';
        }

        $spreadsheet = new Spreadsheet;
        $usedSheetTitles = [];

        foreach ($sections as $index => $section) {
            $sheet = $index === 0
                ? $spreadsheet->getActiveSheet()
                : new Worksheet($spreadsheet);

            if ($index > 0) {
                $spreadsheet->addSheet($sheet);
            }

            $sheet->setTitle($this->sheetTitle($section['title'], $index, $usedSheetTitles));
            $this->fillSheet($sheet, $section);
        }

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');

        return (string) ob_get_clean();
    }

    /**
     * @param  array{title: string, headers: list<string>, rows: list<list<string|float|int>>}  $section
     */
    private function fillSheet(Worksheet $sheet, array $section): void
    {
        $sheet->setCellValue('A1', $section['title']);
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $headerRow = 3;
        $dataRow = 4;

        foreach ($section['headers'] as $columnIndex => $header) {
            $column = $columnIndex + 1;
            $sheet->setCellValue([$column, $headerRow], $header);
            $sheet->getStyle([$column, $headerRow])->getFont()->setBold(true);
        }

        foreach ($section['rows'] as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $sheet->setCellValue(
                    [$columnIndex + 1, $dataRow + $rowIndex],
                    is_scalar($value) ? $value : '',
                );
            }
        }

        if ($section['headers'] !== []) {
            $lastColumn = count($section['headers']);
            $lastRow = max($dataRow, $dataRow + count($section['rows']) - 1);
            $sheet->setAutoFilter([1, $headerRow, $lastColumn, $lastRow]);
        }
    }

    /**
     * @param  array<string, true>  $usedSheetTitles
     */
    private function sheetTitle(string $title, int $index, array &$usedSheetTitles): string
    {
        $sanitized = preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', '-', $title) ?? 'Hoja';
        $sanitized = trim($sanitized);
        $sanitized = $sanitized !== '' ? $sanitized : 'Hoja';
        $sanitized = mb_substr($sanitized, 0, 28);

        $candidate = $sanitized;

        if (isset($usedSheetTitles[$candidate])) {
            $suffix = '-'.($index + 1);
            $candidate = mb_substr($sanitized, 0, 31 - mb_strlen($suffix)).$suffix;
        }

        $usedSheetTitles[$candidate] = true;

        return $candidate;
    }
}
