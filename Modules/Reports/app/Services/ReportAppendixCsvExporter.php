<?php

namespace Modules\Reports\Services;

class ReportAppendixCsvExporter
{
    public function __construct(
        private readonly ReportAppendixTableBuilder $tables,
    ) {}

    /**
     * @param  array<string, mixed>  $appendix
     */
    public function export(array $appendix): string
    {
        $sections = collect($this->tables->build($appendix))
            ->map(fn (array $section) => $this->tableSection(
                $section['title'],
                $section['headers'],
                $section['rows'],
            ))
            ->all();

        $body = implode("\n\n", $sections);

        return "\xEF\xBB\xBF".$body;
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string|float|int>>  $rows
     */
    private function tableSection(string $title, array $headers, array $rows): string
    {
        $lines = ['# '.$title];

        if ($headers !== []) {
            $lines[] = $this->csvLine($headers);
        }

        foreach ($rows as $row) {
            $lines[] = $this->csvLine(array_map(
                fn ($value) => is_scalar($value) ? (string) $value : '',
                $row,
            ));
        }

        return implode("\n", $lines);
    }

    /**
     * @param  list<string>  $fields
     */
    private function csvLine(array $fields): string
    {
        return implode(',', array_map(function (string $field): string {
            $escaped = str_replace('"', '""', $field);

            return str_contains($escaped, ',') || str_contains($escaped, '"') || str_contains($escaped, "\n")
                ? '"'.$escaped.'"'
                : $escaped;
        }, $fields));
    }
}
