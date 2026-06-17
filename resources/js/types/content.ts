export type ContentPostMetrics = Record<string, number>;

export type ContentPost = {
    id: number;
    asset_name: string | null;
    asset_type?: string | null;
    post_type: string;
    published_at: string | null;
    content_preview: string | null;
    thumbnail_url: string | null;
    permalink_url?: string | null;
    metrics: ContentPostMetrics | null;
};

export type ActiveStory = {
    id: number;
    story_id: string;
    asset_name: string | null;
    captured_at: string | null;
    expires_at: string | null;
    reach: number | null;
    impressions: number | null;
    replies: number | null;
};

export type TopPostsByMetric = {
    by_reach: ContentPost[];
    by_engagement: ContentPost[];
    by_interactions: ContentPost[];
};
