import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import LocaleSelector from '@/Components/LocaleSelector';
import { useTranslation } from '@/lib/i18n';
import { Link, usePage } from '@inertiajs/react';
import { ReactNode } from 'react';

type AppTopBarProps = {
    homeHref: string;
    onOpenSidebar?: () => void;
    sidebarCollapsed?: boolean;
    onToggleSidebarCollapse?: () => void;
    trailing?: ReactNode;
};

export default function AppTopBar({
    homeHref,
    onOpenSidebar,
    sidebarCollapsed = false,
    onToggleSidebarCollapse,
    trailing,
}: AppTopBarProps) {
    const user = usePage().props.auth.user!;
    const unreadCount = usePage().props.unreadNotificationsCount ?? 0;
    const isClientReadonly = user.is_client_readonly === true;
    const { t } = useTranslation();

    const roleLabel = (() => {
        if (user.roles.includes('super_admin')) {
            return t('nav.role_super_admin');
        }
        if (user.roles.includes('agency_admin')) {
            return t('nav.role_agency_admin');
        }
        if (user.roles.includes('client_readonly')) {
            return t('nav.role_client');
        }
        if (user.roles.includes('operator')) {
            return t('nav.role_operator');
        }
        return null;
    })();

    return (
        <header className="sticky top-0 z-30 flex h-14 shrink-0 items-center gap-3 border-b border-sp-border bg-white/90 px-4 backdrop-blur sm:gap-4 sm:px-6">
            {onOpenSidebar && (
                <button
                    type="button"
                    onClick={onOpenSidebar}
                    className="rounded-lg p-2 text-sp-muted hover:bg-sp-surface lg:hidden"
                    aria-label={t('nav.open_menu')}
                >
                    <svg
                        className="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>
            )}

            {onToggleSidebarCollapse && (
                <button
                    type="button"
                    onClick={onToggleSidebarCollapse}
                    className="hidden rounded-lg p-2 text-sp-muted hover:bg-sp-surface lg:inline-flex"
                    aria-label={
                        sidebarCollapsed
                            ? t('nav.expand_sidebar')
                            : t('nav.collapse_sidebar')
                    }
                    aria-expanded={!sidebarCollapsed}
                >
                    <svg
                        className="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        strokeWidth={1.75}
                    >
                        {sidebarCollapsed ? (
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                d="M4 6h16M4 12h16M4 18h7"
                            />
                        ) : (
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                d="M4 6h16M4 12H8m8 0H4m16 6H8"
                            />
                        )}
                    </svg>
                </button>
            )}

            <Link
                href={homeHref}
                className="hidden shrink-0 lg:block"
                aria-label={t('nav.home')}
            >
                <ApplicationLogo showWordmark />
            </Link>

            <div className="min-w-0 flex-1 lg:hidden">
                <ApplicationLogo showWordmark />
            </div>

            {trailing && (
                <div className="hidden min-w-0 flex-1 items-center lg:flex">
                    {trailing}
                </div>
            )}

            <div className="ml-auto flex items-center gap-1 sm:gap-2">
                {!isClientReadonly && (
                    <Link
                        href={route('notifications.index')}
                        className="relative rounded-lg p-2 text-sp-muted transition-colors hover:bg-sp-surface hover:text-sp-ink"
                        aria-label={t('nav.notifications')}
                    >
                        <svg
                            className="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            strokeWidth={1.75}
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                            />
                        </svg>
                        {unreadCount > 0 && (
                            <span className="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-sp-primary px-1 text-[10px] font-semibold text-white">
                                {unreadCount > 9 ? '9+' : unreadCount}
                            </span>
                        )}
                    </Link>
                )}

                <LocaleSelector variant="header" />

                <Dropdown>
                    <Dropdown.Trigger>
                        <button
                            type="button"
                            className="flex max-w-[12rem] items-center gap-2 rounded-lg py-1.5 pl-1.5 pr-2 transition-colors hover:bg-sp-surface sm:max-w-xs sm:gap-3 sm:pr-3"
                            aria-label={t('nav.user_menu')}
                        >
                            <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sp-primary text-sm font-semibold text-white">
                                {user.name.charAt(0).toUpperCase()}
                            </span>
                            <span className="hidden min-w-0 text-left sm:block">
                                <span className="block truncate text-sm font-medium text-sp-ink">
                                    {user.name}
                                </span>
                                {roleLabel && (
                                    <span className="block truncate text-[11px] text-sp-muted">
                                        {roleLabel}
                                    </span>
                                )}
                            </span>
                            <svg
                                className="hidden h-4 w-4 shrink-0 text-sp-muted sm:block"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </button>
                    </Dropdown.Trigger>
                    <Dropdown.Content align="right" width="48">
                        <div className="border-b border-sp-border px-4 py-3 sm:hidden">
                            <p className="truncate text-sm font-medium text-sp-ink">
                                {user.name}
                            </p>
                            <p className="truncate text-xs text-sp-muted">
                                {user.email}
                            </p>
                        </div>
                        <Dropdown.Link href={route('profile.edit')}>
                            {t('nav.profile')}
                        </Dropdown.Link>
                        <Dropdown.Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            className="text-red-600 hover:bg-red-50 focus:bg-red-50"
                        >
                            {t('nav.logout')}
                        </Dropdown.Link>
                    </Dropdown.Content>
                </Dropdown>
            </div>
        </header>
    );
}
