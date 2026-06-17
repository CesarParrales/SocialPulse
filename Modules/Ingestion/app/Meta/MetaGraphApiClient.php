<?php

namespace Modules\Ingestion\Meta;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

abstract class MetaGraphApiClient
{
    private const RETRY_DELAYS_MS = [1000, 5000, 15000];

    public function __construct(
        protected readonly string $apiVersion,
    ) {}

    public static function apiVersion(): string
    {
        return config('connections.meta.api_version', 'v22.0');
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    protected function get(string $path, array $query, string $accessToken): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$path}";

        $attempt = 0;
        $lastException = null;

        while ($attempt < count(self::RETRY_DELAYS_MS) + 1) {
            try {
                $response = Http::timeout(30)
                    ->acceptJson()
                    ->get($url, array_merge($query, [
                        'access_token' => $accessToken,
                    ]));

                if ($response->status() === 429 || $response->status() >= 500) {
                    throw new RequestException($response);
                }

                if ($response->failed()) {
                    $message = $response->json('error.message') ?? $response->body();
                    throw new RuntimeException("Meta Graph API error: {$message}");
                }

                return $response->json();
            } catch (ConnectionException|RequestException $exception) {
                $lastException = $exception;

                if ($attempt >= count(self::RETRY_DELAYS_MS)) {
                    break;
                }

                usleep(self::RETRY_DELAYS_MS[$attempt] * 1000);
                $attempt++;
            }
        }

        throw new RuntimeException(
            'Meta Graph API request failed after retries: '.($lastException?->getMessage() ?? 'unknown error'),
            0,
            $lastException,
        );
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|null
     */
    protected function tryGet(string $path, array $query, string $accessToken): ?array
    {
        try {
            return $this->get($path, $query, $accessToken);
        } catch (RuntimeException) {
            return null;
        }
    }
}
