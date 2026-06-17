<?php

namespace Modules\Content\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Content\Contracts\MetaContentPublisher;
use Modules\Content\Models\ContentDraft;
use Modules\Content\Policies\ContentDraftPolicy;
use Modules\Content\Services\ContentAssetResolver;
use Modules\Content\Services\ContentCalendarService;
use Modules\Content\Services\ContentPublishService;
use Modules\Content\Services\ContentWorkflowService;
use Modules\Content\Services\Meta\MetaContentPublishService;
use Modules\Content\Services\PublishedContentLinkService;
use Nwidart\Modules\Support\ModuleServiceProvider;

class ContentServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Content';

    protected string $nameLower = 'content';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(ContentCalendarService::class);
        $this->app->singleton(ContentWorkflowService::class);
        $this->app->singleton(ContentAssetResolver::class);
        $this->app->singleton(ContentPublishService::class);
        $this->app->singleton(PublishedContentLinkService::class);
        $this->app->singleton(MetaContentPublisher::class, MetaContentPublishService::class);
    }

    public function boot(): void
    {
        parent::boot();

        Gate::policy(ContentDraft::class, ContentDraftPolicy::class);
    }
}
