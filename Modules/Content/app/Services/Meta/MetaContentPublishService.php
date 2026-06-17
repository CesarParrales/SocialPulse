<?php

namespace Modules\Content\Services\Meta;

use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Content\Contracts\MetaContentPublisher;
use Modules\Content\Enums\ContentChannel;
use Modules\Content\Enums\ContentType;
use Modules\Content\Models\ContentDraft;
use Modules\Content\Support\MetaPublishResult;
use Modules\Ingestion\Services\InstagramAccessTokenResolver;
use RuntimeException;

class MetaContentPublishService implements MetaContentPublisher
{
    public function __construct(
        private readonly InstagramAccessTokenResolver $instagramTokens,
    ) {}

    public function publish(ContentDraft $draft, ConnectedAsset $asset): MetaPublishResult
    {
        return match ($draft->channel) {
            ContentChannel::Facebook => $this->publishFacebook($draft, $asset),
            ContentChannel::Instagram => $this->publishInstagram($draft, $asset),
        };
    }

    private function publishFacebook(ContentDraft $draft, ConnectedAsset $asset): MetaPublishResult
    {
        if ($asset->asset_type !== AssetType::FacebookPage) {
            throw new RuntimeException('El activo seleccionado no es una página de Facebook.');
        }

        $pageToken = $this->pageAccessToken($asset);
        $pageId = $asset->platform_asset_id;

        if ($draft->content_type === ContentType::Reel) {
            return $this->publishFacebookVideo($draft, $pageId, $pageToken);
        }

        $response = $this->post("{$pageId}/feed", [
            'message' => (string) $draft->caption,
        ], $pageToken);

        $postId = (string) ($response['id'] ?? '');

        if ($postId === '') {
            throw new RuntimeException('Meta no devolvió un ID de publicación.');
        }

        return new MetaPublishResult(
            platformPostId: $postId,
            permalink: "https://facebook.com/{$postId}",
        );
    }

    private function publishFacebookVideo(
        ContentDraft $draft,
        string $pageId,
        string $pageToken,
    ): MetaPublishResult {
        $mediaUrl = trim((string) $draft->media_url);

        if ($mediaUrl === '') {
            throw new RuntimeException('Se requiere media_url para publicar video/reel en Facebook.');
        }

        $response = $this->post("{$pageId}/videos", [
            'file_url' => $mediaUrl,
            'description' => (string) $draft->caption,
        ], $pageToken);

        $videoId = (string) ($response['id'] ?? '');

        if ($videoId === '') {
            throw new RuntimeException('Meta no devolvió un ID de video.');
        }

        return new MetaPublishResult(
            platformPostId: $videoId,
            permalink: "https://facebook.com/{$videoId}",
        );
    }

    private function publishInstagram(ContentDraft $draft, ConnectedAsset $asset): MetaPublishResult
    {
        if ($asset->asset_type !== AssetType::InstagramAccount) {
            throw new RuntimeException('El activo seleccionado no es una cuenta de Instagram.');
        }

        $mediaUrl = trim((string) $draft->media_url);

        if ($mediaUrl === '') {
            throw new RuntimeException('Se requiere media_url para publicar en Instagram.');
        }

        $token = $this->instagramTokens->resolve($asset);
        $igUserId = $asset->platform_asset_id;

        $containerParams = [
            'caption' => (string) $draft->caption,
        ];

        if ($draft->content_type === ContentType::Reel) {
            $containerParams['media_type'] = 'REELS';
            $containerParams['video_url'] = $mediaUrl;
        } else {
            $containerParams['image_url'] = $mediaUrl;
        }

        $container = $this->post("{$igUserId}/media", $containerParams, $token);
        $creationId = (string) ($container['id'] ?? '');

        if ($creationId === '') {
            throw new RuntimeException('Meta no devolvió creation_id para Instagram.');
        }

        $published = $this->post("{$igUserId}/media_publish", [
            'creation_id' => $creationId,
        ], $token);

        $mediaId = (string) ($published['id'] ?? '');

        if ($mediaId === '') {
            throw new RuntimeException('Meta no devolvió media_id publicado.');
        }

        return new MetaPublishResult(
            platformPostId: $mediaId,
            permalink: "https://instagram.com/p/{$mediaId}",
        );
    }

    private function pageAccessToken(ConnectedAsset $asset): string
    {
        $token = $asset->metadata['page_access_token'] ?? null;

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Falta page access token para publicar.');
        }

        return $token;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function post(string $path, array $params, string $accessToken): array
    {
        $version = config('connections.meta.api_version', 'v22.0');
        $url = "https://graph.facebook.com/{$version}/{$path}";

        $response = Http::timeout(60)
            ->acceptJson()
            ->asForm()
            ->post($url, array_merge($params, [
                'access_token' => $accessToken,
            ]));

        if ($response->failed()) {
            $message = $response->json('error.message') ?? $response->body();
            throw new RuntimeException("Meta Publishing API error: {$message}");
        }

        return $response->json();
    }
}
