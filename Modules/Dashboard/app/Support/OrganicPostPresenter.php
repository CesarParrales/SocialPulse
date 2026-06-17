<?php

namespace Modules\Dashboard\Support;

use Modules\Ingestion\Models\OrganicPost;
use Modules\Ingestion\Models\StorySnapshot;

class OrganicPostPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function present(OrganicPost $post): array
    {
        $metrics = $post->raw_metrics ?? [];

        return [
            'id' => $post->id,
            'asset_name' => $post->asset?->name,
            'asset_type' => $post->asset?->asset_type?->value,
            'post_type' => $post->post_type,
            'published_at' => $post->published_at?->toIso8601String(),
            'content_preview' => $post->content_preview,
            'thumbnail_url' => $post->thumbnail_url,
            'permalink_url' => isset($metrics['permalink_url']) && is_string($metrics['permalink_url'])
                ? $metrics['permalink_url']
                : null,
            'metrics' => self::normalizedMetrics($metrics),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function presentStory(StorySnapshot $story): array
    {
        return [
            'id' => $story->id,
            'story_id' => $story->story_id,
            'asset_name' => $story->asset?->name,
            'captured_at' => $story->captured_at?->toIso8601String(),
            'expires_at' => $story->expires_at?->toIso8601String(),
            'reach' => $story->reach,
            'impressions' => $story->impressions,
            'replies' => $story->replies,
        ];
    }

    /**
     * @param  array<string, mixed>  $metrics
     * @return array<string, float>
     */
    private static function normalizedMetrics(array $metrics): array
    {
        $numeric = collect($metrics)
            ->except('permalink_url')
            ->map(fn ($value) => is_numeric($value) ? (float) $value : null)
            ->filter(fn ($value) => $value !== null)
            ->all();

        $numeric['interactions'] = (float) (($numeric['likes'] ?? 0)
            + ($numeric['comments'] ?? 0)
            + ($numeric['shares'] ?? 0)
            + ($numeric['reactions'] ?? 0));

        return $numeric;
    }
}
