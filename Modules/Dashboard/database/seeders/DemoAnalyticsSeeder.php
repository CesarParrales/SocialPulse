<?php

namespace Modules\Dashboard\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Ingestion\Enums\IngestionJobType;
use Modules\Ingestion\Enums\IngestionStatus;
use Modules\Ingestion\Models\AccountMetricDaily;
use Modules\Ingestion\Models\AdCampaign;
use Modules\Ingestion\Models\AdMetricDaily;
use Modules\Ingestion\Models\IngestionLog;
use Modules\Ingestion\Models\OrganicMetricDaily;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Ingestion\Models\StorySnapshot;
use Modules\Workspaces\Models\Workspace;

class DemoAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        $workspace = Workspace::query()
            ->where('name', 'Cliente — Marca Alfa')
            ->first();

        if ($workspace === null) {
            return;
        }

        $connection = PlatformConnection::query()->firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'platform' => Platform::Meta,
            ],
            [
                'access_token' => 'demo-token',
                'status' => ConnectionStatus::Active,
            ],
        );

        $fbPage = ConnectedAsset::query()->firstOrCreate(
            [
                'connection_id' => $connection->id,
                'platform_asset_id' => 'demo-fb-page',
            ],
            [
                'asset_type' => AssetType::FacebookPage,
                'name' => 'Marca Alfa — Fanpage',
                'is_active' => true,
            ],
        );

        $igAccount = ConnectedAsset::query()->firstOrCreate(
            [
                'connection_id' => $connection->id,
                'platform_asset_id' => 'demo-ig-account',
            ],
            [
                'asset_type' => AssetType::InstagramAccount,
                'name' => 'Marca Alfa — Instagram',
                'is_active' => true,
            ],
        );

        $metaAds = ConnectedAsset::query()->firstOrCreate(
            [
                'connection_id' => $connection->id,
                'platform_asset_id' => 'act-demo-123',
            ],
            [
                'asset_type' => AssetType::MetaAds,
                'name' => 'Marca Alfa — Meta Ads',
                'is_active' => true,
            ],
        );

        $this->seedOrganicPosts($fbPage, $igAccount);
        $this->seedCommunityMetrics($fbPage, $igAccount);
        $this->seedStories($igAccount);
        $this->seedPaidMetrics($metaAds);
        $this->seedIngestionLogs($fbPage, $igAccount, $metaAds);
    }

    private function seedOrganicPosts(ConnectedAsset $fbPage, ConnectedAsset $igAccount): void
    {
        $samples = [
            [$fbPage, 'feed', 'Lanzamiento de temporada — descubre la nueva colección.', 1840, 420, 96],
            [$fbPage, 'feed', 'Tips de la semana para tu comunidad.', 920, 180, 41],
            [$fbPage, 'video', 'Behind the scenes del shoot de campaña.', 1310, 260, 58],
            [$igAccount, 'feed', 'Nuevo drop disponible en tiendas seleccionadas.', 2150, 510, 132],
            [$igAccount, 'reel', 'Reel del producto estrella del mes.', 3420, 890, 210],
            [$igAccount, 'feed', 'User generated content destacado.', 980, 240, 67],
        ];

        foreach ($samples as $index => [$asset, $type, $caption, $reach, $impressions, $engagement]) {
            $publishedAt = now()->subDays($index + 1)->setHour(12);

            OrganicPost::query()->updateOrCreate(
                [
                    'asset_id' => $asset->id,
                    'platform_post_id' => "demo-post-{$asset->id}-{$index}",
                ],
                [
                    'post_type' => $type,
                    'published_at' => $publishedAt,
                    'content_preview' => $caption,
                    'thumbnail_url' => "https://picsum.photos/seed/sp-demo-{$asset->id}-{$index}/480/600",
                    'raw_metrics' => [
                        'reach' => $reach,
                        'impressions' => $impressions,
                        'engagement' => $engagement,
                        'likes' => (int) round($engagement * 0.7),
                        'comments' => (int) round($engagement * 0.2),
                        'shares' => (int) round($engagement * 0.1),
                        'permalink_url' => 'https://www.example.com/demo/post/'.$index,
                    ],
                    'captured_at' => now(),
                ],
            );
        }
    }

    private function seedCommunityMetrics(ConnectedAsset $fbPage, ConnectedAsset $igAccount): void
    {
        for ($offset = 6; $offset >= 0; $offset--) {
            $date = now()->subDays($offset)->toDateString();

            OrganicMetricDaily::query()->updateOrCreate(
                [
                    'asset_id' => $fbPage->id,
                    'metric_type' => 'fan_count',
                    'date' => $date,
                ],
                [
                    'value' => 12500 + ($offset * 12),
                    'captured_at' => now(),
                ],
            );

            AccountMetricDaily::query()->updateOrCreate(
                [
                    'asset_id' => $igAccount->id,
                    'date' => $date,
                ],
                [
                    'followers' => 8900 + ($offset * 18),
                    'captured_at' => now(),
                ],
            );
        }
    }

    private function seedStories(ConnectedAsset $igAccount): void
    {
        StorySnapshot::query()->updateOrCreate(
            [
                'asset_id' => $igAccount->id,
                'story_id' => 'demo-story-active-1',
            ],
            [
                'captured_at' => now()->subHours(2),
                'reach' => 640,
                'impressions' => 910,
                'taps_forward' => 120,
                'taps_back' => 8,
                'exits' => 45,
                'replies' => 12,
                'expires_at' => now()->addHours(20),
                'is_expired' => false,
            ],
        );
    }

    private function seedPaidMetrics(ConnectedAsset $metaAds): void
    {
        $campaign = AdCampaign::query()->firstOrCreate(
            [
                'asset_id' => $metaAds->id,
                'platform_campaign_id' => 'demo-campaign-1',
            ],
            [
                'name' => 'Always-on conversiones',
            ],
        );

        for ($offset = 6; $offset >= 0; $offset--) {
            AdMetricDaily::query()->updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'date' => now()->subDays($offset)->toDateString(),
                    'placement' => 'Instagram Feed',
                ],
                [
                    'spend' => 45.5 + ($offset * 3.2),
                    'reach' => 2200 + ($offset * 140),
                    'impressions' => 4800 + ($offset * 260),
                    'is_preliminary' => false,
                    'captured_at' => now(),
                ],
            );
        }
    }

    private function seedIngestionLogs(
        ConnectedAsset $fbPage,
        ConnectedAsset $igAccount,
        ConnectedAsset $metaAds,
    ): void {
        $entries = [
            [$fbPage, IngestionJobType::OrganicFacebook, 3],
            [$igAccount, IngestionJobType::OrganicInstagram, 3],
            [$metaAds, IngestionJobType::PaidMeta, 7],
        ];

        foreach ($entries as [$asset, $jobType, $records]) {
            IngestionLog::query()->create([
                'asset_id' => $asset->id,
                'job_type' => $jobType,
                'status' => IngestionStatus::Success,
                'records_ingested' => $records,
                'executed_at' => now()->subHours(2),
                'duration_ms' => 850,
            ]);
        }
    }
}
