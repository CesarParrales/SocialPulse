export const DASHBOARD_KPI_KEYS = [
    'reach',
    'impressions',
    'engagement_rate',
    'spend',
    'follower_growth',
    'posts_published',
] as const;

export type DashboardKpiKey = (typeof DASHBOARD_KPI_KEYS)[number];

export function isDashboardKpiKey(value: string): value is DashboardKpiKey {
    return DASHBOARD_KPI_KEYS.includes(value as DashboardKpiKey);
}
