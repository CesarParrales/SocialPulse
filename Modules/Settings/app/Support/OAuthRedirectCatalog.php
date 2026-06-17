<?php

namespace Modules\Settings\Support;

class OAuthRedirectCatalog
{
    /**
     * @return list<array{platform: string, label_key: string, uri: string}>
     */
    public static function all(): array
    {
        return [
            [
                'platform' => 'meta',
                'label_key' => 'settings.meta',
                'uri' => config('connections.meta.redirect_uri') ?? url('/connections/meta/callback'),
            ],
            [
                'platform' => 'google',
                'label_key' => 'settings.google',
                'uri' => config('connections.google.redirect_uri') ?? url('/connections/google/callback'),
            ],
            [
                'platform' => 'tiktok',
                'label_key' => 'settings.tiktok',
                'uri' => config('connections.tiktok.redirect_uri') ?? url('/connections/tiktok/callback'),
            ],
            [
                'platform' => 'linkedin',
                'label_key' => 'settings.linkedin',
                'uri' => config('connections.linkedin.redirect_uri') ?? url('/connections/linkedin/callback'),
            ],
            [
                'platform' => 'youtube',
                'label_key' => 'settings.youtube',
                'uri' => config('connections.youtube.redirect_uri') ?? url('/connections/youtube/callback'),
            ],
        ];
    }

    /**
     * @return list<array{platform: string, label: string, uri: string}>
     */
    public static function payload(): array
    {
        return collect(self::all())
            ->map(fn (array $row) => [
                'platform' => $row['platform'],
                'label' => __($row['label_key']),
                'uri' => $row['uri'],
            ])
            ->all();
    }
}
