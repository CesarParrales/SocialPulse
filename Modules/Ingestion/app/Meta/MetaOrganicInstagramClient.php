<?php

namespace Modules\Ingestion\Meta;

use Carbon\Carbon;

class MetaOrganicInstagramClient extends MetaGraphApiClient
{
    public static function make(): self
    {
        return new self(self::apiVersion());
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchAccount(string $igUserId, string $accessToken): array
    {
        return $this->get($igUserId, [
            'fields' => 'followers_count,username',
        ], $accessToken);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchRecentMedia(string $igUserId, string $accessToken, int $limit = 25): array
    {
        $response = $this->get("{$igUserId}/media", [
            'fields' => 'id,caption,timestamp,media_type,media_url,thumbnail_url,permalink',
            'limit' => $limit,
        ], $accessToken);

        return $response['data'] ?? [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchActiveStories(string $igUserId, string $accessToken): array
    {
        $response = $this->tryGet("{$igUserId}/stories", [
            'fields' => 'id,timestamp',
        ], $accessToken);

        if ($response === null) {
            return [];
        }

        $cutoff = now()->subHours(24);

        return collect($response['data'] ?? [])
            ->filter(function (array $story) use ($cutoff): bool {
                $timestamp = $story['timestamp'] ?? null;

                if (! is_string($timestamp) || $timestamp === '') {
                    return false;
                }

                return Carbon::parse($timestamp)->greaterThanOrEqualTo($cutoff);
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, float|int>
     */
    public function fetchMediaInsights(string $mediaId, string $mediaType, string $accessToken): array
    {
        $metrics = match (strtoupper($mediaType)) {
            'REELS', 'VIDEO' => 'plays,reach,likes,comments,shares,saved',
            default => 'reach,impressions,likes,comments,shares,saved,profile_visits',
        };

        $response = $this->tryGet("{$mediaId}/insights", [
            'metric' => $metrics,
        ], $accessToken);

        if ($response === null) {
            return [];
        }

        return $this->parseInsightValues($response);
    }

    /**
     * @return array<string, float|int>
     */
    public function fetchStoryInsights(string $storyId, string $accessToken): array
    {
        $response = $this->tryGet("{$storyId}/insights", [
            'metric' => 'exits,impressions,reach,replies,taps_forward,taps_back',
        ], $accessToken);

        if ($response === null) {
            return [];
        }

        return $this->parseInsightValues($response);
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<string, float|int>
     */
    private function parseInsightValues(array $response): array
    {
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
