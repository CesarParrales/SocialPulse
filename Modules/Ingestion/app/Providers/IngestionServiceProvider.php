<?php

namespace Modules\Ingestion\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Ingestion\Console\IngestFacebookOrganicCommand;
use Modules\Ingestion\Console\IngestInstagramOrganicCommand;
use Modules\Ingestion\Console\IngestLinkedInOrganicCommand;
use Modules\Ingestion\Console\IngestPaidGoogleCommand;
use Modules\Ingestion\Console\IngestPaidMetaCommand;
use Modules\Ingestion\Console\IngestStoriesWatcherCommand;
use Modules\Ingestion\Console\IngestTikTokOrganicCommand;
use Modules\Ingestion\Console\IngestYouTubeOrganicCommand;
use Modules\Ingestion\Jobs\DispatchOrganicFacebookDailyJob;
use Modules\Ingestion\Jobs\DispatchOrganicInstagramDailyJob;
use Modules\Ingestion\Jobs\DispatchOrganicLinkedInDailyJob;
use Modules\Ingestion\Jobs\DispatchOrganicTikTokDailyJob;
use Modules\Ingestion\Jobs\DispatchOrganicYouTubeDailyJob;
use Modules\Ingestion\Jobs\DispatchPaidGoogleDailyJob;
use Modules\Ingestion\Jobs\DispatchPaidGoogleIntradayJob;
use Modules\Ingestion\Jobs\DispatchPaidMetaDailyJob;
use Modules\Ingestion\Jobs\DispatchPaidMetaIntradayJob;
use Modules\Ingestion\Jobs\DispatchStoriesWatcherJob;
use Modules\Ingestion\LinkedIn\LinkedInOrganicClient;
use Modules\Ingestion\Meta\MetaOrganicFacebookClient;
use Modules\Ingestion\Meta\MetaOrganicInstagramClient;
use Modules\Ingestion\Meta\MetaPaidAdsClient;
use Modules\Ingestion\Services\InstagramAccessTokenResolver;
use Modules\Ingestion\Services\OrganicFacebookIngestionService;
use Modules\Ingestion\Services\OrganicInstagramIngestionService;
use Modules\Ingestion\Services\OrganicLinkedInIngestionService;
use Modules\Ingestion\Services\OrganicTikTokIngestionService;
use Modules\Ingestion\Services\OrganicYouTubeIngestionService;
use Modules\Ingestion\Services\PaidGoogleIngestionService;
use Modules\Ingestion\Services\PaidMetaIngestionService;
use Modules\Ingestion\Services\StoriesWatcherService;
use Modules\Ingestion\Support\IngestionLogger;
use Modules\Ingestion\Support\PaidIngestionDateResolver;
use Modules\Ingestion\TikTok\TikTokOrganicClient;
use Modules\Ingestion\YouTube\YouTubeOrganicClient;
use Nwidart\Modules\Support\ModuleServiceProvider;

class IngestionServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Ingestion';

    protected string $nameLower = 'ingestion';

    protected array $commands = [
        IngestFacebookOrganicCommand::class,
        IngestInstagramOrganicCommand::class,
        IngestStoriesWatcherCommand::class,
        IngestPaidMetaCommand::class,
        IngestPaidGoogleCommand::class,
        IngestTikTokOrganicCommand::class,
        IngestLinkedInOrganicCommand::class,
        IngestYouTubeOrganicCommand::class,
    ];

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->job(new DispatchOrganicFacebookDailyJob)
            ->dailyAt('02:00')
            ->timezone('UTC')
            ->name('ingestion:organic-facebook-daily')
            ->withoutOverlapping();

        $schedule->job(new DispatchOrganicInstagramDailyJob)
            ->dailyAt('02:00')
            ->timezone('UTC')
            ->name('ingestion:organic-instagram-daily')
            ->withoutOverlapping();

        $schedule->job(new DispatchOrganicTikTokDailyJob)
            ->dailyAt('02:15')
            ->timezone('UTC')
            ->name('ingestion:organic-tiktok-daily')
            ->withoutOverlapping();

        $schedule->job(new DispatchOrganicLinkedInDailyJob)
            ->dailyAt('02:20')
            ->timezone('UTC')
            ->name('ingestion:organic-linkedin-daily')
            ->withoutOverlapping();

        $schedule->job(new DispatchOrganicYouTubeDailyJob)
            ->dailyAt('02:25')
            ->timezone('UTC')
            ->name('ingestion:organic-youtube-daily')
            ->withoutOverlapping();

        $schedule->job(new DispatchStoriesWatcherJob)
            ->everySixHours()
            ->timezone('UTC')
            ->name('ingestion:stories-watcher')
            ->withoutOverlapping();

        $schedule->job(new DispatchPaidMetaDailyJob)
            ->dailyAt('02:30')
            ->timezone('UTC')
            ->name('ingestion:paid-meta-daily')
            ->withoutOverlapping();

        $schedule->job(new DispatchPaidGoogleDailyJob)
            ->dailyAt('02:30')
            ->timezone('UTC')
            ->name('ingestion:paid-google-daily')
            ->withoutOverlapping();

        $schedule->job(new DispatchPaidMetaIntradayJob)
            ->everyFourHours()
            ->timezone('UTC')
            ->name('ingestion:paid-meta-intraday')
            ->withoutOverlapping();

        $schedule->job(new DispatchPaidGoogleIntradayJob)
            ->everyFourHours()
            ->timezone('UTC')
            ->name('ingestion:paid-google-intraday')
            ->withoutOverlapping();
    }

    public function register(): void
    {
        parent::register();

        $this->app->singleton(MetaOrganicFacebookClient::class, fn () => MetaOrganicFacebookClient::make());
        $this->app->singleton(MetaOrganicInstagramClient::class, fn () => MetaOrganicInstagramClient::make());
        $this->app->singleton(MetaPaidAdsClient::class, fn () => MetaPaidAdsClient::make());
        $this->app->singleton(IngestionLogger::class);
        $this->app->singleton(PaidIngestionDateResolver::class);
        $this->app->singleton(InstagramAccessTokenResolver::class);
        $this->app->singleton(OrganicFacebookIngestionService::class);
        $this->app->singleton(OrganicInstagramIngestionService::class);
        $this->app->singleton(TikTokOrganicClient::class);
        $this->app->singleton(OrganicTikTokIngestionService::class);
        $this->app->singleton(LinkedInOrganicClient::class);
        $this->app->singleton(OrganicLinkedInIngestionService::class);
        $this->app->singleton(YouTubeOrganicClient::class);
        $this->app->singleton(OrganicYouTubeIngestionService::class);
        $this->app->singleton(StoriesWatcherService::class);
        $this->app->singleton(PaidMetaIngestionService::class);
        $this->app->singleton(PaidGoogleIngestionService::class);
    }
}
