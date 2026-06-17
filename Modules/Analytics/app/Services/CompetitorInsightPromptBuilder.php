<?php

namespace Modules\Analytics\Services;

use Illuminate\Support\Collection;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Dashboard\Support\DashboardPeriod;
use Modules\Workspaces\Models\Workspace;

class CompetitorInsightPromptBuilder
{
    public function __construct(
        private readonly CompetitorBenchmarkService $benchmarks,
    ) {}

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     */
    public function build(Workspace $workspace, Collection $assets): string
    {
        $period = DashboardPeriod::fromPreset('30d');
        $overview = $this->benchmarks->buildOverview($workspace, $assets);
        $client = $overview['client'];
        $competitors = $overview['competitors'];

        $lines = [
            'Eres un analista de social media en una agencia. Redacta un análisis consultivo breve en español (3–5 párrafos) comparando la marca del cliente con sus competidores.',
            '',
            'REGLAS:',
            '- Usa solo los datos provistos; no inventes métricas ni fuentes.',
            '- Indica explícitamente que los datos de competidores son estimaciones manuales.',
            '- Incluye oportunidades accionables (contenido, frecuencia, formatos).',
            '- Tono profesional, estilo informe mensual para cliente.',
            '- No menciones que eres una IA.',
            '',
            '=== CLIENTE ===',
            'Marca: '.$workspace->name,
            'Industria: '.($workspace->industry_category ?: 'No especificada'),
            'Región: '.($workspace->region ?: 'No especificada'),
            'Período de referencia: '.$period->start->toDateString().' — '.$period->end->toDateString(),
            'Alcance orgánico total: '.$this->formatNumber($client['total_reach'] ?? null),
            'Publicaciones en período: '.$this->formatNumber($client['posts_count'] ?? null),
            'Alcance promedio por post (estimado): '.$this->formatNumber($client['avg_reach'] ?? null),
            'Engagement rate orgánico: '.$this->formatPercent($client['avg_engagement_rate'] ?? null),
            '',
            '=== COMPETIDORES (datos manuales) ===',
        ];

        if ($competitors === []) {
            $lines[] = '(Sin competidores registrados — pide al usuario que complete la tabla antes de analizar.)';
        }

        foreach ($competitors as $index => $competitor) {
            $lines[] = '';
            $lines[] = ($index + 1).'. '.$competitor['name'];
            $lines[] = '   Plataforma: '.($competitor['platform'] ?: '—');
            $lines[] = '   Handle: '.($competitor['handle'] ?: '—');
            $lines[] = '   Seguidores: '.$this->formatNumber($competitor['followers_count'] ?? null);
            $lines[] = '   Alcance promedio (manual): '.$this->formatNumber($competitor['avg_reach'] ?? null);
            $lines[] = '   Engagement rate (manual): '.$this->formatPercent($competitor['avg_engagement_rate'] ?? null);
            $lines[] = '   Fuente del dato: '.($competitor['data_source_note'] ?: 'No indicada');
            if (! empty($competitor['notes'])) {
                $lines[] = '   Notas: '.$competitor['notes'];
            }
        }

        $lines[] = '';
        $lines[] = '=== ENTREGABLE ===';
        $lines[] = '1) Resumen ejecutivo comparativo';
        $lines[] = '2) Fortalezas relativas del cliente';
        $lines[] = '3) Brechas vs competidores';
        $lines[] = '4) 3 recomendaciones concretas para el próximo mes';

        return implode("\n", $lines);
    }

    private function formatNumber(mixed $value): string
    {
        if (! is_numeric($value)) {
            return '—';
        }

        return number_format((float) $value, 0, '.', ',');
    }

    private function formatPercent(mixed $value): string
    {
        if (! is_numeric($value)) {
            return '—';
        }

        return number_format((float) $value, 2).'%';
    }
}
