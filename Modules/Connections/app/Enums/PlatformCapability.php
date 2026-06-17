<?php

namespace Modules\Connections\Enums;

enum PlatformCapability: string
{
    case AnalyticsOrganic = 'analytics_organic';
    case AnalyticsPaid = 'analytics_paid';
    case StoriesCapture = 'stories_capture';
    case ContentPublish = 'content_publish';
    case CompetitorTracking = 'competitor_tracking';
}
