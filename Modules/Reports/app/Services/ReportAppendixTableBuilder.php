<?php

namespace Modules\Reports\Services;

class ReportAppendixTableBuilder
{
    /**
     * @param  array<string, mixed>  $appendix
     * @return list<array{title: string, headers: list<string>, rows: list<list<string|float|int>>}>
     */
    public function build(array $appendix): array
    {
        $sections = [];

        if (! empty($appendix['summary']['rows'])) {
            $sections[] = [
                'title' => 'Resumen general',
                'headers' => $appendix['summary']['columns'] ?? [],
                'rows' => collect($appendix['summary']['rows'])->map(fn (array $row) => [
                    $row['label'] ?? '',
                    $row['current'] ?? '',
                    $row['previous'] ?? '',
                    $row['change'] ?? '',
                ])->all(),
            ];
        }

        foreach ($appendix['assets'] ?? [] as $assetTable) {
            $sections[] = [
                'title' => ($assetTable['channel'] ?? 'Activo').' — '.($assetTable['asset_name'] ?? ''),
                'headers' => $assetTable['columns'] ?? [],
                'rows' => collect($assetTable['rows'] ?? [])->map(fn (array $row) => [
                    $row['label'] ?? '',
                    $row['current'] ?? '',
                    $row['previous'] ?? '',
                    $row['change'] ?? '',
                ])->all(),
            ];
        }

        if (! empty($appendix['campaigns']['rows'])) {
            $sections[] = [
                'title' => 'Campañas pagadas',
                'headers' => $appendix['campaigns']['columns'] ?? [],
                'rows' => collect($appendix['campaigns']['rows'])->map(fn (array $row) => [
                    $row['campaign'] ?? '',
                    $row['asset'] ?? '',
                    $row['channel'] ?? '',
                    $row['spend'] ?? '',
                    $row['impressions'] ?? '',
                    $row['reach'] ?? '',
                    $row['clicks'] ?? '',
                ])->all(),
            ];
        }

        if (! empty($appendix['posts']['rows'])) {
            $sections[] = [
                'title' => 'Publicaciones orgánicas',
                'headers' => $appendix['posts']['columns'] ?? [],
                'rows' => collect($appendix['posts']['rows'])->map(fn (array $row) => [
                    $row['channel'] ?? '',
                    $row['asset'] ?? '',
                    $row['type'] ?? '',
                    $row['published_at'] ?? '',
                    $row['reach'] ?? '',
                    $row['impressions'] ?? '',
                    $row['interactions'] ?? '',
                    $row['link_clicks'] ?? '',
                    $row['video_views'] ?? '',
                    $row['preview'] ?? '',
                ])->all(),
            ];
        }

        if (! empty($appendix['daily_reach']['rows'])) {
            $sections[] = [
                'title' => 'Alcance diario',
                'headers' => $appendix['daily_reach']['columns'] ?? [],
                'rows' => collect($appendix['daily_reach']['rows'])->map(fn (array $row) => [
                    $row['date'] ?? '',
                    $row['organic'] ?? '',
                    $row['paid'] ?? '',
                    $row['total'] ?? '',
                ])->all(),
            ];
        }

        return $sections;
    }
}
