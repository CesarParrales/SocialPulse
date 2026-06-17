<?php

namespace Modules\Content\Services;

use Illuminate\Support\Facades\DB;
use Modules\Content\Contracts\MetaContentPublisher;
use Modules\Content\Enums\ContentDraftStatus;
use Modules\Content\Models\ContentDraft;
use Modules\Content\Models\PublishedContentLink;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Workspaces\Models\Workspace;
use Throwable;

class ContentPublishService
{
    public function __construct(
        private readonly ContentAssetResolver $assets,
        private readonly MetaContentPublisher $publisher,
        private readonly PublishedContentLinkService $links,
    ) {}

    public function publish(Workspace $workspace, ContentDraft $draft): ContentDraft
    {
        if ($draft->status !== ContentDraftStatus::Approved) {
            throw new \RuntimeException(__('app.content.errors.not_approved'));
        }

        $asset = $this->assets->resolveForDraft($workspace, $draft->channel);

        try {
            DB::transaction(function () use ($draft, $asset) {
                $result = $this->publisher->publish($draft, $asset);
                $publishedAt = now();

                $organicPost = OrganicPost::query()->updateOrCreate(
                    [
                        'asset_id' => $asset->id,
                        'platform_post_id' => $result->platformPostId,
                    ],
                    [
                        'post_type' => $draft->content_type->value,
                        'published_at' => $publishedAt,
                        'content_preview' => mb_substr((string) $draft->caption, 0, 500),
                        'raw_metrics' => [],
                        'captured_at' => $publishedAt,
                    ],
                );

                PublishedContentLink::query()->updateOrCreate(
                    [
                        'asset_id' => $asset->id,
                        'platform_post_id' => $result->platformPostId,
                    ],
                    [
                        'content_draft_id' => $draft->id,
                        'organic_post_id' => $organicPost->id,
                        'platform_permalink' => $result->permalink,
                        'published_at' => $publishedAt,
                    ],
                );

                $draft->update([
                    'status' => ContentDraftStatus::Published,
                    'platform_post_id' => $result->platformPostId,
                    'published_to_platform_at' => $publishedAt,
                    'publish_error' => null,
                ]);
            });

            return $draft->refresh();
        } catch (Throwable $exception) {
            $draft->update([
                'publish_error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
