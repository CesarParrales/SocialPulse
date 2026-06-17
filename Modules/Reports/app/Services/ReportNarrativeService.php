<?php

namespace Modules\Reports\Services;

use Illuminate\Support\Str;

class ReportNarrativeService
{
    /**
     * @param  array<string, mixed>  $analytics
     * @param  list<array<string, mixed>>  $channelSections
     * @param  array<string, mixed>|null  $comparison
     * @return array{executive: array<string, mixed>, channels: list<array<string, mixed>>}
     */
    public function build(array $analytics, array $channelSections, ?array $comparison = null): array
    {
        $executiveParagraphs = [];
        $signals = [];

        $globalKpis = $analytics['kpis'] ?? [];
        $reach = $globalKpis['reach'] ?? null;
        $engagement = $globalKpis['engagement_rate'] ?? null;
        $spend = $globalKpis['spend'] ?? null;

        if ($reach !== null) {
            $executiveParagraphs[] = $this->metricTrendSentence(
                'En conjunto, el alcance',
                $reach,
                'personas',
                'number',
            );
        }

        if ($engagement !== null && ($engagement['comparable'] ?? false)) {
            $executiveParagraphs[] = $this->metricTrendSentence(
                'La tasa de engagement global',
                $engagement,
                '',
                'percent',
            );
        }

        if ($reach !== null && $engagement !== null) {
            $divergence = $this->divergenceInsight($reach, $engagement);

            if ($divergence !== null) {
                $executiveParagraphs[] = $divergence['text'];
                $signals[] = $divergence['signal'];
            }
        }

        $organicSections = array_values(array_filter(
            $channelSections,
            fn (array $section) => ($section['kind'] ?? '') === 'organic',
        ));

        if (count($organicSections) >= 2) {
            $executiveParagraphs[] = $this->crossChannelSentence($organicSections);
        }

        if ($comparison !== null) {
            $organicPaid = $this->organicVsPaidInsight($comparison);

            if ($organicPaid !== null) {
                $executiveParagraphs[] = $organicPaid['text'];
                $signals[] = $organicPaid['signal'];
            }
        }

        if ($spend !== null && (float) ($spend['current'] ?? 0) > 0) {
            $executiveParagraphs[] = $this->metricTrendSentence(
                'La inversión en medios pagados',
                $spend,
                '',
                'currency',
            );
        }

        if ($executiveParagraphs === []) {
            $executiveParagraphs[] = 'Aún no hay suficientes datos en el período para generar conclusiones automáticas.';
        }

        $channels = [];

        foreach ($channelSections as $section) {
            $channels[] = ($section['kind'] ?? '') === 'paid'
                ? $this->paidChannelNarrative($section)
                : $this->organicChannelNarrative($section);
        }

        return [
            'executive' => [
                'title' => 'Qué nos dice el período',
                'paragraphs' => $executiveParagraphs,
                'signals' => $signals,
            ],
            'channels' => $channels,
        ];
    }

    /**
     * Removes generic cross-channel sentences when the integrated FB+IG block is shown.
     *
     * @param  array{executive: array<string, mixed>, channels: list<array<string, mixed>>}  $narrative
     * @return array{executive: array<string, mixed>, channels: list<array<string, mixed>>}
     */
    public function withoutCrossChannelDuplicate(array $narrative): array
    {
        $paragraphs = collect($narrative['executive']['paragraphs'] ?? [])
            ->reject(fn (string $paragraph) => str_contains($paragraph, 'concentró mayor alcance')
                || str_contains($paragraph, 'lideró el crecimiento de alcance')
                || str_contains($paragraph, 'Los canales orgánicos activos muestran comportamientos distintos'))
            ->values()
            ->all();

        $narrative['executive']['paragraphs'] = $paragraphs;

        return $narrative;
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>
     */
    private function organicChannelNarrative(array $section): array
    {
        $label = (string) ($section['label'] ?? $section['key'] ?? 'Canal');
        $kpis = $section['kpis'] ?? [];
        $reach = $kpis['reach'] ?? null;
        $engagement = $kpis['engagement_rate'] ?? null;
        $posts = $kpis['posts_published'] ?? null;

        $paragraphs = [];
        $bullets = [];

        if ($reach !== null) {
            $paragraphs[] = $this->metricTrendSentence(
                "En {$label}, el alcance",
                $reach,
                'personas',
                'number',
            );
        }

        if ($engagement !== null && ($engagement['comparable'] ?? false)) {
            $paragraphs[] = $this->metricTrendSentence(
                'El engagement rate',
                $engagement,
                '',
                'percent',
            );
        }

        if ($reach !== null && $engagement !== null) {
            $divergence = $this->divergenceInsight($reach, $engagement);

            if ($divergence !== null) {
                $paragraphs[] = $divergence['text'];
                $bullets[] = $divergence['signal']['text'];
            }
        }

        if ($posts !== null && ($posts['comparable'] ?? false) && ($posts['direction'] ?? 'flat') === 'down') {
            $paragraphs[] = 'La cadencia de publicación disminuyó respecto al período anterior; mantener frecuencia ayuda a sostener visibilidad orgánica.';
            $bullets[] = 'Menor volumen de publicaciones vs período anterior.';
        }

        $topPost = $section['top_posts'][0] ?? null;

        if ($topPost !== null) {
            $preview = Str::limit((string) ($topPost['content_preview'] ?? 'publicación'), 80);
            $topReach = number_format((float) ($topPost['metrics']['reach'] ?? 0), 0, '.', ',');
            $paragraphs[] = "La publicación de mayor alcance fue «{$preview}» ({$topReach} personas).";
        }

        $topReel = $section['top_reels'][0] ?? null;

        if ($topReel !== null) {
            $preview = Str::limit((string) ($topReel['content_preview'] ?? 'reel'), 80);
            $topReach = number_format((float) ($topReel['metrics']['reach'] ?? 0), 0, '.', ',');
            $paragraphs[] = "El reel destacado del período fue «{$preview}» ({$topReach} personas).";
        }

        if ($paragraphs === []) {
            $paragraphs[] = "No hay actividad orgánica registrada en {$label} durante este período.";
        }

        return [
            'key' => $section['key'] ?? '',
            'label' => $label,
            'title' => "Qué nos dice {$label}",
            'paragraphs' => $paragraphs,
            'bullets' => $bullets,
        ];
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>
     */
    private function paidChannelNarrative(array $section): array
    {
        $label = (string) ($section['label'] ?? $section['key'] ?? 'Paid');
        $paid = $section['paid_summary'] ?? [];
        $spend = (float) ($paid['spend'] ?? 0);
        $impressions = (float) ($paid['impressions'] ?? 0);
        $topCampaign = $paid['top_campaigns'][0] ?? null;

        $paragraphs = [];
        $bullets = [];

        if ($spend <= 0 && $impressions <= 0) {
            $paragraphs[] = "No hubo actividad pagada registrada en {$label} durante este período.";
        } else {
            $paragraphs[] = sprintf(
                'En %s se invirtieron $%s con %s impresiones en el período.',
                $label,
                number_format($spend, 2),
                number_format($impressions, 0, '.', ','),
            );

            if ($topCampaign !== null) {
                $name = (string) ($topCampaign['campaign_name'] ?? 'Campaña principal');
                $campaignSpend = number_format((float) ($topCampaign['spend'] ?? 0), 2);
                $paragraphs[] = "La campaña con mayor inversión fue «{$name}» ($".$campaignSpend.').';
            }

            if ($spend > 0 && $impressions > 0) {
                $cpm = ($spend / $impressions) * 1000;
                $bullets[] = 'CPM estimado del período: $'.number_format($cpm, 2).'.';
            }
        }

        return [
            'key' => $section['key'] ?? '',
            'label' => $label,
            'title' => "Qué nos dice {$label}",
            'paragraphs' => $paragraphs,
            'bullets' => $bullets,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $organicSections
     */
    private function crossChannelSentence(array $organicSections): string
    {
        $ranked = collect($organicSections)
            ->map(function (array $section) {
                $reach = $section['kpis']['reach'] ?? null;

                return [
                    'label' => $section['label'] ?? $section['key'],
                    'change' => is_array($reach) ? ($reach['change_pct'] ?? null) : null,
                    'current' => is_array($reach) ? (float) ($reach['current'] ?? 0) : 0.0,
                    'comparable' => is_array($reach) ? (bool) ($reach['comparable'] ?? false) : false,
                ];
            })
            ->filter(fn (array $row) => $row['comparable'] && $row['change'] !== null)
            ->sortByDesc('change')
            ->values();

        if ($ranked->count() >= 2) {
            $leader = $ranked->first();
            $laggard = $ranked->last();

            if ($leader['change'] > $laggard['change']) {
                return sprintf(
                    '%s lideró el crecimiento de alcance (+%s%%), mientras %s creció menos (+%s%%).',
                    $leader['label'],
                    number_format((float) $leader['change'], 1),
                    $laggard['label'],
                    number_format((float) $laggard['change'], 1),
                );
            }
        }

        $byVolume = collect($organicSections)
            ->sortByDesc(fn (array $section) => (float) ($section['kpis']['reach']['current'] ?? 0))
            ->values();

        $top = $byVolume->first();
        $second = $byVolume->get(1);

        if ($top !== null && $second !== null) {
            return sprintf(
                '%s concentró mayor alcance (%s personas) frente a %s (%s personas).',
                $top['label'] ?? 'Canal principal',
                number_format((float) ($top['kpis']['reach']['current'] ?? 0), 0, '.', ','),
                $second['label'] ?? 'otro canal',
                number_format((float) ($second['kpis']['reach']['current'] ?? 0), 0, '.', ','),
            );
        }

        return 'Los canales orgánicos activos muestran comportamientos distintos; conviene revisar cada uno por separado.';
    }

    /**
     * @param  array<string, mixed>  $comparison
     * @return array{text: string, signal: array{type: string, text: string}}|null
     */
    private function organicVsPaidInsight(array $comparison): ?array
    {
        $reachRow = collect($comparison['rows'] ?? [])->firstWhere('metric', 'reach');

        if ($reachRow === null) {
            return null;
        }

        $organic = (float) ($reachRow['left'] ?? 0);
        $paid = (float) ($reachRow['right'] ?? 0);
        $total = $organic + $paid;

        if ($total <= 0) {
            return null;
        }

        $organicShare = round(($organic / $total) * 100, 1);

        if ($organicShare >= 70) {
            return [
                'text' => "El alcance orgánico representó el {$organicShare}% del alcance total (orgánico + pagado), señal de base comunitaria sólida.",
                'signal' => [
                    'type' => 'positive',
                    'text' => 'Predominio orgánico en alcance.',
                ],
            ];
        }

        if ($organicShare <= 30 && $paid > 0) {
            return [
                'text' => "El alcance pagado aportó la mayor parte de la visibilidad (orgánico: {$organicShare}%). Conviene equilibrar con contenido orgánico de mayor interacción.",
                'signal' => [
                    'type' => 'warning',
                    'text' => 'Alta dependencia de alcance pagado.',
                ],
            ];
        }

        return [
            'text' => "Orgánico y pagado aportaron de forma equilibrada al alcance (orgánico: {$organicShare}%, pagado: ".round(100 - $organicShare, 1).'%).',
            'signal' => [
                'type' => 'neutral',
                'text' => 'Mix orgánico/pagado balanceado.',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $reach
     * @param  array<string, mixed>  $engagement
     * @return array{text: string, signal: array{type: string, text: string}}|null
     */
    private function divergenceInsight(array $reach, array $engagement): ?array
    {
        if (! ($reach['comparable'] ?? false) || ! ($engagement['comparable'] ?? false)) {
            return null;
        }

        $reachDirection = $reach['direction'] ?? 'flat';
        $engagementDirection = $engagement['direction'] ?? 'flat';

        if ($reachDirection === 'up' && $engagementDirection === 'down') {
            return [
                'text' => 'El alcance creció, pero la interacción relativa cayó: hay más exposición, pero el contenido genera menos respuesta proporcional. Conviene revisar formatos, hooks y llamados a la acción.',
                'signal' => [
                    'type' => 'warning',
                    'text' => 'Alcance ↑ · Interacción ↓',
                ],
            ];
        }

        if ($reachDirection === 'down' && $engagementDirection === 'up') {
            return [
                'text' => 'Aunque el alcance bajó, la interacción relativa mejoró: el contenido llegó a menos personas, pero resonó mejor con quienes lo vieron.',
                'signal' => [
                    'type' => 'positive',
                    'text' => 'Alcance ↓ · Interacción ↑',
                ],
            ];
        }

        if ($reachDirection === 'up' && $engagementDirection === 'up') {
            return [
                'text' => 'Alcance e interacción crecieron en paralelo: señal de contenido que amplifica visibilidad y genera respuesta.',
                'signal' => [
                    'type' => 'positive',
                    'text' => 'Alcance ↑ · Interacción ↑',
                ],
            ];
        }

        if ($reachDirection === 'down' && $engagementDirection === 'down') {
            return [
                'text' => 'Tanto el alcance como la interacción disminuyeron respecto al período anterior. Revisar frecuencia, formatos y alineación con audiencia.',
                'signal' => [
                    'type' => 'negative',
                    'text' => 'Alcance ↓ · Interacción ↓',
                ],
            ];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $metric
     */
    private function metricTrendSentence(
        string $subject,
        array $metric,
        string $unitSuffix,
        string $format,
    ): string {
        $current = $this->formatValue((float) ($metric['current'] ?? 0), $format);
        $suffix = $unitSuffix !== '' ? " {$unitSuffix}" : '';

        if (! ($metric['comparable'] ?? false)) {
            return "{$subject} fue de {$current}{$suffix} en el período (sin histórico comparable).";
        }

        $change = $metric['change_pct'] ?? null;
        $direction = $metric['direction'] ?? 'flat';

        if ($change === null) {
            return "{$subject} se mantuvo en {$current}{$suffix} respecto al período anterior.";
        }

        $verb = match ($direction) {
            'up' => 'aumentó',
            'down' => 'disminuyó',
            default => 'se mantuvo en',
        };

        $sign = $change >= 0 ? '+' : '';

        if ($direction === 'flat') {
            return "{$subject} se mantuvo estable en {$current}{$suffix} ({$sign}".number_format((float) $change, 1).'% vs período anterior).';
        }

        return "{$subject} {$verb} a {$current}{$suffix} ({$sign}".number_format((float) $change, 1).'% vs período anterior).';
    }

    private function formatValue(float $value, string $format): string
    {
        return match ($format) {
            'currency' => '$'.number_format($value, 2),
            'percent' => number_format($value, 2).'%',
            default => number_format($value, 0, '.', ','),
        };
    }
}
