<?php

namespace Modules\Connections\Support;

use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Enums\PlatformCapability;

class PlatformCatalog
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::meta(),
            self::google(),
            self::tiktok(),
            self::linkedin(),
            self::youtube(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function available(): array
    {
        return array_values(array_filter(
            self::all(),
            fn (array $platform) => ($platform['status'] ?? '') === 'available',
        ));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function forAssetType(AssetType $assetType): ?array
    {
        foreach (self::all() as $platform) {
            foreach ($platform['channels'] as $channel) {
                if (($channel['asset_type'] ?? null) === $assetType->value) {
                    return $channel + [
                        'platform' => $platform['key'],
                        'platform_label' => $platform['label'],
                        'platform_status' => $platform['status'],
                    ];
                }
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function capabilityLabels(): array
    {
        return [
            PlatformCapability::AnalyticsOrganic->value => 'Analytics orgánico',
            PlatformCapability::AnalyticsPaid->value => 'Analytics pagado',
            PlatformCapability::StoriesCapture->value => 'Captura de stories',
            PlatformCapability::ContentPublish->value => 'Publicación de contenido',
            PlatformCapability::CompetitorTracking->value => 'Seguimiento competidores',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function meta(): array
    {
        return [
            'key' => Platform::Meta->value,
            'label' => 'Meta',
            'status' => 'available',
            'phase' => 'mvp',
            'channels' => [
                [
                    'key' => 'facebook',
                    'label' => 'Facebook Page',
                    'asset_type' => AssetType::FacebookPage->value,
                    'capabilities' => [
                        PlatformCapability::AnalyticsOrganic->value,
                        PlatformCapability::ContentPublish->value,
                    ],
                ],
                [
                    'key' => 'instagram',
                    'label' => 'Instagram',
                    'asset_type' => AssetType::InstagramAccount->value,
                    'capabilities' => [
                        PlatformCapability::AnalyticsOrganic->value,
                        PlatformCapability::StoriesCapture->value,
                        PlatformCapability::ContentPublish->value,
                    ],
                ],
                [
                    'key' => 'meta_ads',
                    'label' => 'Meta Ads',
                    'asset_type' => AssetType::MetaAds->value,
                    'capabilities' => [
                        PlatformCapability::AnalyticsPaid->value,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function google(): array
    {
        return [
            'key' => Platform::Google->value,
            'label' => 'Google',
            'status' => 'available',
            'phase' => 'mvp',
            'channels' => [
                [
                    'key' => 'google_ads',
                    'label' => 'Google Ads',
                    'asset_type' => AssetType::GoogleAds->value,
                    'capabilities' => [
                        PlatformCapability::AnalyticsPaid->value,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function tiktok(): array
    {
        return [
            'key' => Platform::TikTok->value,
            'label' => 'TikTok',
            'status' => 'available',
            'phase' => 'phase_3',
            'channels' => [
                [
                    'key' => 'tiktok_account',
                    'label' => 'TikTok Business',
                    'asset_type' => AssetType::TikTokAccount->value,
                    'capabilities' => [
                        PlatformCapability::AnalyticsOrganic->value,
                        PlatformCapability::ContentPublish->value,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function linkedin(): array
    {
        return [
            'key' => Platform::LinkedIn->value,
            'label' => 'LinkedIn',
            'status' => 'available',
            'phase' => 'phase_3',
            'channels' => [
                [
                    'key' => 'linkedin_page',
                    'label' => 'LinkedIn Page',
                    'asset_type' => AssetType::LinkedInPage->value,
                    'capabilities' => [
                        PlatformCapability::AnalyticsOrganic->value,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function youtube(): array
    {
        return [
            'key' => Platform::YouTube->value,
            'label' => 'YouTube',
            'status' => 'available',
            'phase' => 'phase_3',
            'channels' => [
                [
                    'key' => 'youtube_channel',
                    'label' => 'YouTube Channel',
                    'asset_type' => AssetType::YouTubeChannel->value,
                    'capabilities' => [
                        PlatformCapability::AnalyticsOrganic->value,
                    ],
                ],
            ],
        ];
    }
}
