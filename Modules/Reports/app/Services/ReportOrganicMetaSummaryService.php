<?php

namespace Modules\Reports\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Analytics\Enums\ComparisonType;
use Modules\Analytics\Services\WorkspaceComparisonService;
use Modules\Analytics\Support\ComparisonContext;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Dashboard\Support\DashboardPeriod;

class ReportOrganicMetaSummaryService
{
    public function __construct(
        private readonly WorkspaceComparisonService $comparisons,
    ) {}

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @param  list<array<string, mixed>>  $channelSections
     * @return array<string, mixed>|null
     */
    public function build(Collection $assets, DashboardPeriod $period, array $channelSections): ?array
    {
        $facebook = collect($channelSections)->firstWhere('key', 'facebook');
        $instagram = collect($channelSections)->firstWhere('key', 'instagram');

        if ($facebook === null || $instagram === null) {
            return null;
        }

        $comparison = $this->comparisons->build(
            $assets,
            new ComparisonContext(
                ComparisonType::FacebookVsInstagram,
                $period->start,
                $period->end,
                $period->start,
                $period->end,
                'Facebook',
                'Instagram',
            ),
        );

        $rowsByMetric = collect($comparison['rows'] ?? [])->keyBy('metric');
        $reachRow = $rowsByMetric->get('reach');
        $engagementRow = $rowsByMetric->get('engagement_rate');
        $postsRow = $rowsByMetric->get('posts_count');
        $followersRow = $rowsByMetric->get('follower_growth');

        $fbReach = (float) ($reachRow['left'] ?? 0);
        $igReach = (float) ($reachRow['right'] ?? 0);
        $totalReach = $fbReach + $igReach;

        $paragraphs = [];
        $signals = [];
        $leaders = [];

        if ($totalReach > 0) {
            $igShare = round(($igReach / $totalReach) * 100, 1);
            $fbShare = round(100 - $igShare, 1);
            $reachLeader = $igReach >= $fbReach ? 'instagram' : 'facebook';
            $reachLeaderLabel = $igReach >= $fbReach
                ? (string) ($instagram['label'] ?? 'Instagram')
                : (string) ($facebook['label'] ?? 'Facebook');

            $paragraphs[] = sprintf(
                'En el ecosistema Meta orgánico, %s aportó el %s%% del alcance (%s personas) frente a %s%% en %s (%s personas).',
                $reachLeaderLabel,
                $reachLeader === 'instagram' ? (string) $igShare : (string) $fbShare,
                number_format($reachLeader === 'instagram' ? $igReach : $fbReach, 0, '.', ','),
                $reachLeader === 'instagram' ? (string) $fbShare : (string) $igShare,
                $reachLeader === 'instagram'
                    ? (string) ($facebook['label'] ?? 'Facebook')
                    : (string) ($instagram['label'] ?? 'Instagram'),
                number_format($reachLeader === 'instagram' ? $fbReach : $igReach, 0, '.', ','),
            );

            $leaders[] = [
                'metric' => 'reach',
                'label' => 'Alcance',
                'winner' => $reachLeader,
                'winner_label' => $reachLeaderLabel,
            ];
        }

        $fbEngagement = (float) ($engagementRow['left'] ?? 0);
        $igEngagement = (float) ($engagementRow['right'] ?? 0);

        if ($fbEngagement > 0 || $igEngagement > 0) {
            $engagementLeader = $igEngagement >= $fbEngagement ? 'instagram' : 'facebook';
            $engagementLeaderLabel = $engagementLeader === 'instagram'
                ? (string) ($instagram['label'] ?? 'Instagram')
                : (string) ($facebook['label'] ?? 'Facebook');

            $paragraphs[] = sprintf(
                '%s registró mayor engagement rate (%.2f%% vs %.2f%%).',
                $engagementLeaderLabel,
                $engagementLeader === 'instagram' ? $igEngagement : $fbEngagement,
                $engagementLeader === 'instagram' ? $fbEngagement : $igEngagement,
            );

            $leaders[] = [
                'metric' => 'engagement_rate',
                'label' => 'Engagement',
                'winner' => $engagementLeader,
                'winner_label' => $engagementLeaderLabel,
            ];
        }

        $growthInsight = $this->growthInsight($facebook, $instagram);

        if ($growthInsight !== null) {
            $paragraphs[] = $growthInsight['text'];
            $signals[] = $growthInsight['signal'];
        }

        $cadenceInsight = $this->cadenceInsight($postsRow, $facebook, $instagram);

        if ($cadenceInsight !== null) {
            $paragraphs[] = $cadenceInsight;
        }

        $formatInsight = $this->formatInsight($facebook, $instagram);

        if ($formatInsight !== null) {
            $paragraphs[] = $formatInsight;
        }

        $followerInsight = $this->followerInsight($followersRow, $facebook, $instagram);

        if ($followerInsight !== null) {
            $paragraphs[] = $followerInsight;
        }

        if ($paragraphs === []) {
            $paragraphs[] = 'Facebook e Instagram están conectados, pero aún no hay actividad orgánica suficiente en el período para un resumen integrado.';
        }

        return [
            'facebook' => [
                'key' => 'facebook',
                'label' => (string) ($facebook['label'] ?? 'Facebook'),
            ],
            'instagram' => [
                'key' => 'instagram',
                'label' => (string) ($instagram['label'] ?? 'Instagram'),
            ],
            'comparison' => $comparison,
            'narrative' => [
                'title' => 'Qué nos dicen Facebook e Instagram juntos',
                'paragraphs' => $paragraphs,
                'signals' => $signals,
                'leaders' => $leaders,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $facebook
     * @param  array<string, mixed>  $instagram
     * @return array{text: string, signal: array{type: string, text: string}}|null
     */
    private function growthInsight(array $facebook, array $instagram): ?array
    {
        $fbReach = $facebook['kpis']['reach'] ?? null;
        $igReach = $instagram['kpis']['reach'] ?? null;

        if (! is_array($fbReach) || ! is_array($igReach)) {
            return null;
        }

        if (! ($fbReach['comparable'] ?? false) || ! ($igReach['comparable'] ?? false)) {
            return null;
        }

        $fbChange = (float) ($fbReach['change_pct'] ?? 0);
        $igChange = (float) ($igReach['change_pct'] ?? 0);
        $fbLabel = (string) ($facebook['label'] ?? 'Facebook');
        $igLabel = (string) ($instagram['label'] ?? 'Instagram');

        if ($fbChange > $igChange && $fbChange > 0) {
            return [
                'text' => sprintf(
                    '%s lideró el crecimiento de alcance (+%s%% vs +%s%% en %s).',
                    $fbLabel,
                    number_format($fbChange, 1),
                    number_format(max($igChange, 0), 1),
                    $igLabel,
                ),
                'signal' => [
                    'type' => 'positive',
                    'text' => "{$fbLabel} ↑ crecimiento alcance",
                ],
            ];
        }

        if ($igChange > $fbChange && $igChange > 0) {
            return [
                'text' => sprintf(
                    '%s lideró el crecimiento de alcance (+%s%% vs +%s%% en %s).',
                    $igLabel,
                    number_format($igChange, 1),
                    number_format(max($fbChange, 0), 1),
                    $fbLabel,
                ),
                'signal' => [
                    'type' => 'positive',
                    'text' => "{$igLabel} ↑ crecimiento alcance",
                ],
            ];
        }

        if ($fbChange < 0 && $igChange < 0) {
            return [
                'text' => 'Ambos canales redujeron alcance respecto al período anterior; conviene revisar cadencia y formatos en paralelo.',
                'signal' => [
                    'type' => 'negative',
                    'text' => 'FB + IG alcance ↓',
                ],
            ];
        }

        if ($fbChange >= 0 && $igChange < 0) {
            return [
                'text' => "{$fbLabel} sostuvo o mejoró alcance mientras {$igLabel} retrocedió: oportunidad de replicar formatos ganadores cross-canal.",
                'signal' => [
                    'type' => 'warning',
                    'text' => "{$igLabel} alcance ↓",
                ],
            ];
        }

        if ($igChange >= 0 && $fbChange < 0) {
            return [
                'text' => "{$igLabel} sostuvo o mejoró alcance mientras {$fbLabel} retrocedió: evaluar adaptar reels o formatos visuales al feed de Facebook.",
                'signal' => [
                    'type' => 'warning',
                    'text' => "{$fbLabel} alcance ↓",
                ],
            ];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $postsRow
     */
    private function cadenceInsight(?array $postsRow, array $facebook, array $instagram): ?string
    {
        if ($postsRow === null) {
            return null;
        }

        $fbPosts = (float) ($postsRow['left'] ?? 0);
        $igPosts = (float) ($postsRow['right'] ?? 0);

        if ($fbPosts === 0.0 && $igPosts === 0.0) {
            return null;
        }

        $fbLabel = (string) ($facebook['label'] ?? 'Facebook');
        $igLabel = (string) ($instagram['label'] ?? 'Instagram');

        if ($fbPosts === $igPosts) {
            return sprintf(
                'La cadencia fue equilibrada: %d publicaciones en %s y %d en %s.',
                (int) $fbPosts,
                $fbLabel,
                (int) $igPosts,
                $igLabel,
            );
        }

        if ($igPosts > $fbPosts) {
            return sprintf(
                '%s publicó con mayor frecuencia (%d vs %d en %s).',
                $igLabel,
                (int) $igPosts,
                (int) $fbPosts,
                $fbLabel,
            );
        }

        return sprintf(
            '%s publicó con mayor frecuencia (%d vs %d en %s).',
            $fbLabel,
            (int) $fbPosts,
            (int) $igPosts,
            $igLabel,
        );
    }

    /**
     * @param  array<string, mixed>  $facebook
     * @param  array<string, mixed>  $instagram
     */
    private function formatInsight(array $facebook, array $instagram): ?string
    {
        $fbTopReel = $facebook['top_reels'][0] ?? null;
        $igTopReel = $instagram['top_reels'][0] ?? null;
        $fbTopPost = $facebook['top_posts'][0] ?? null;
        $igTopPost = $instagram['top_posts'][0] ?? null;

        $fbReelReach = (float) ($fbTopReel['metrics']['reach'] ?? 0);
        $igReelReach = (float) ($igTopReel['metrics']['reach'] ?? 0);
        $fbPostReach = (float) ($fbTopPost['metrics']['reach'] ?? 0);
        $igPostReach = (float) ($igTopPost['metrics']['reach'] ?? 0);

        if ($igReelReach > 0 && $igReelReach >= max($fbReelReach, $fbPostReach, $igPostReach)) {
            $preview = Str::limit((string) ($igTopReel['content_preview'] ?? 'reel'), 60);

            return "El formato reel destacó en Instagram («{$preview}»). Replicar hooks similares en Facebook puede equilibrar visibilidad cross-canal.";
        }

        if ($fbPostReach > 0 && $fbPostReach >= max($igReelReach, $igPostReach)) {
            $preview = Str::limit((string) ($fbTopPost['content_preview'] ?? 'publicación'), 60);

            return "El contenido estático lideró en Facebook («{$preview}»). Adaptarlo a carrusel o reel en Instagram puede ampliar alcance.";
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $followersRow
     */
    private function followerInsight(?array $followersRow, array $facebook, array $instagram): ?string
    {
        if ($followersRow === null) {
            return null;
        }

        $fbGrowth = (float) ($followersRow['left'] ?? 0);
        $igGrowth = (float) ($followersRow['right'] ?? 0);

        if ($fbGrowth === 0.0 && $igGrowth === 0.0) {
            return null;
        }

        $fbLabel = (string) ($facebook['label'] ?? 'Facebook');
        $igLabel = (string) ($instagram['label'] ?? 'Instagram');

        if ($igGrowth > $fbGrowth) {
            return sprintf(
                '%s sumó más seguidores netos en el período (+%s vs +%s en %s).',
                $igLabel,
                number_format($igGrowth, 0, '.', ','),
                number_format($fbGrowth, 0, '.', ','),
                $fbLabel,
            );
        }

        if ($fbGrowth > $igGrowth) {
            return sprintf(
                '%s sumó más seguidores netos en el período (+%s vs +%s en %s).',
                $fbLabel,
                number_format($fbGrowth, 0, '.', ','),
                number_format($igGrowth, 0, '.', ','),
                $igLabel,
            );
        }

        return null;
    }
}
