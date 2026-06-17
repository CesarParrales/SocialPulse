<?php

namespace Modules\Ingestion\Meta;

class MetaOrganicFacebookClient extends MetaGraphApiClient
{
    public static function make(): self
    {
        return new self(self::apiVersion());
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchPage(string $pageId, string $pageAccessToken): array
    {
        return $this->get("{$pageId}", [
            'fields' => 'fan_count,name',
        ], $pageAccessToken);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchRecentPosts(string $pageId, string $pageAccessToken, int $limit = 25): array
    {
        $response = $this->get("{$pageId}/posts", [
            'fields' => 'id,message,created_time,full_picture,permalink_url,status_type',
            'limit' => $limit,
        ], $pageAccessToken);

        return $response['data'] ?? [];
    }

    /**
     * @return array<string, float|int>
     */
    public function fetchPostInsights(string $postId, string $pageAccessToken): array
    {
        $metrics = [
            'post_impressions',
            'post_impressions_unique',
            'post_engaged_users',
            'post_reactions_by_type_total',
            'post_clicks',
            'post_video_views',
        ];

        $response = $this->tryGet("{$postId}/insights", [
            'metric' => implode(',', $metrics),
        ], $pageAccessToken);

        if ($response === null) {
            return [];
        }

        $parsed = [];

        foreach ($response['data'] ?? [] as $row) {
            $name = $row['name'] ?? null;
            $values = $row['values'] ?? [];
            $value = $values[0]['value'] ?? null;

            if ($name === null || $value === null) {
                continue;
            }

            if (is_array($value)) {
                $parsed[$name] = array_sum(array_map('intval', $value));
            } else {
                $parsed[$name] = is_numeric($value) ? (float) $value : $value;
            }
        }

        return $parsed;
    }
}
