export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string | null;
    agency_id?: number | null;
    locale?: string;
    roles: string[];
    is_client_readonly?: boolean;
}

export interface Workspace {
    id: number;
    agency_id: number;
    name: string;
    industry_category: string | null;
    region?: string | null;
    timezone: string;
    created_at: string;
    agency?: {
        id: number;
        name: string;
        plan?: string;
    };
    members?: Array<{
        id: number;
        name: string;
        email: string;
        pivot: {
            role: string;
        };
    }>;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User | null;
    };
    flash: {
        success?: string | null;
    };
    locale: string;
    localeOptions: Array<{ value: string; label: string }>;
    translations: Record<string, unknown>;
    unreadNotificationsCount?: number;
    clientHomeUrl?: string | null;
};
