<?php

namespace Modules\Reports\Support;

use Modules\Connections\Enums\AssetType;

class MetaMetricLabels
{
    /**
     * @return array<string, array{label: string, format: string}>
     */
    public static function kpiLabelsForAssetType(AssetType $assetType): array
    {
        return match ($assetType) {
            AssetType::FacebookPage => [
                'reach' => ['label' => 'Alcance (personas)', 'format' => 'number'],
                'impressions' => ['label' => 'Impresiones', 'format' => 'number'],
                'engagement_rate' => ['label' => 'Usuarios que interactuaron (%)', 'format' => 'percent'],
                'posts_published' => ['label' => 'Publicaciones', 'format' => 'number'],
            ],
            AssetType::InstagramAccount => [
                'reach' => ['label' => 'Alcance (cuentas)', 'format' => 'number'],
                'impressions' => ['label' => 'Impresiones', 'format' => 'number'],
                'engagement_rate' => ['label' => 'Tasa de interacción (%)', 'format' => 'percent'],
                'posts_published' => ['label' => 'Publicaciones', 'format' => 'number'],
            ],
            AssetType::TikTokAccount => [
                'reach' => ['label' => 'Visualizaciones', 'format' => 'number'],
                'impressions' => ['label' => 'Visualizaciones', 'format' => 'number'],
                'engagement_rate' => ['label' => 'Tasa de interacción (%)', 'format' => 'percent'],
                'posts_published' => ['label' => 'Vídeos publicados', 'format' => 'number'],
            ],
            AssetType::LinkedInPage => [
                'reach' => ['label' => 'Alcance (miembros únicos)', 'format' => 'number'],
                'impressions' => ['label' => 'Impresiones', 'format' => 'number'],
                'engagement_rate' => ['label' => 'Tasa de interacción (%)', 'format' => 'percent'],
                'posts_published' => ['label' => 'Publicaciones', 'format' => 'number'],
            ],
            AssetType::YouTubeChannel => [
                'reach' => ['label' => 'Visualizaciones', 'format' => 'number'],
                'impressions' => ['label' => 'Visualizaciones', 'format' => 'number'],
                'engagement_rate' => ['label' => 'Tasa de interacción (%)', 'format' => 'percent'],
                'posts_published' => ['label' => 'Vídeos publicados', 'format' => 'number'],
            ],
            AssetType::MetaAds => [
                'spend' => ['label' => 'Inversión', 'format' => 'currency'],
                'impressions' => ['label' => 'Impresiones', 'format' => 'number'],
                'reach' => ['label' => 'Alcance', 'format' => 'number'],
                'clicks' => ['label' => 'Clics', 'format' => 'number'],
            ],
            AssetType::GoogleAds => [
                'spend' => ['label' => 'Inversión', 'format' => 'currency'],
                'impressions' => ['label' => 'Impresiones', 'format' => 'number'],
                'reach' => ['label' => 'Alcance', 'format' => 'number'],
                'clicks' => ['label' => 'Clics', 'format' => 'number'],
            ],
            default => [
                'reach' => ['label' => 'Alcance', 'format' => 'number'],
                'impressions' => ['label' => 'Impresiones', 'format' => 'number'],
            ],
        };
    }

    /**
     * @return array<string, array{label: string, format: string}>
     */
    public static function supplementalLabelsForAssetType(AssetType $assetType): array
    {
        return match ($assetType) {
            AssetType::FacebookPage => [
                'link_clicks' => ['label' => 'Clics en el enlace', 'format' => 'number'],
                'video_views' => ['label' => 'Visualizaciones de video', 'format' => 'number'],
                'reactions' => ['label' => 'Reacciones', 'format' => 'number'],
                'comments' => ['label' => 'Comentarios', 'format' => 'number'],
                'shares' => ['label' => 'Compartidos', 'format' => 'number'],
            ],
            AssetType::InstagramAccount => [
                'video_views' => ['label' => 'Visualizaciones / reproducciones', 'format' => 'number'],
                'profile_views' => ['label' => 'Visitas al perfil', 'format' => 'number'],
                'likes' => ['label' => 'Me gusta', 'format' => 'number'],
                'comments' => ['label' => 'Comentarios', 'format' => 'number'],
                'shares' => ['label' => 'Compartidos', 'format' => 'number'],
                'saved' => ['label' => 'Guardados', 'format' => 'number'],
            ],
            AssetType::TikTokAccount => [
                'video_views' => ['label' => 'Visualizaciones', 'format' => 'number'],
                'likes' => ['label' => 'Me gusta', 'format' => 'number'],
                'comments' => ['label' => 'Comentarios', 'format' => 'number'],
                'shares' => ['label' => 'Compartidos', 'format' => 'number'],
            ],
            AssetType::LinkedInPage => [
                'link_clicks' => ['label' => 'Clics', 'format' => 'number'],
                'likes' => ['label' => 'Reacciones', 'format' => 'number'],
                'comments' => ['label' => 'Comentarios', 'format' => 'number'],
                'shares' => ['label' => 'Compartidos', 'format' => 'number'],
            ],
            AssetType::YouTubeChannel => [
                'video_views' => ['label' => 'Visualizaciones', 'format' => 'number'],
                'likes' => ['label' => 'Me gusta', 'format' => 'number'],
                'comments' => ['label' => 'Comentarios', 'format' => 'number'],
            ],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    public static function postColumnLabels(): array
    {
        return [
            'channel' => 'Canal',
            'asset' => 'Activo',
            'type' => 'Tipo',
            'published_at' => 'Fecha',
            'reach' => 'Alcance',
            'impressions' => 'Impresiones',
            'interactions' => 'Interacciones',
            'link_clicks' => 'Clics en enlace',
            'video_views' => 'Visualizaciones',
            'preview' => 'Contenido',
        ];
    }
}
